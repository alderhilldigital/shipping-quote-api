<?php

require 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

if(!isset($_GET['key']) || $_GET['key'] !== $_ENV['API_KEY']) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$origin = null;
$destination = null;
$weight1 = null;
$weight2 = null;
$weight3 = null;
$originalWeight1 = null;
$originalWeight2 = null;
$originalWeight3 = null;
$inclusions = null;
$conversions = null;

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['key']) && isset($_GET['origin']) && !empty($_GET['origin']) && isset($_GET['destination']) && !empty($_GET['destination'])) {
    $origin = $_GET['origin'];
    $destination = $_GET['destination'];
    $weight1 = isset($_GET['weight1']) ? $_GET['weight1'] : null;
    $weight2 = isset($_GET['weight2']) ? $_GET['weight2'] : null;
    $weight3 = isset($_GET['weight3']) ? $_GET['weight3'] : null;
    $originalWeight1 = $weight1;
    $originalWeight2 = $weight2;
    $originalWeight3 = $weight3;
    $inclusions = isset($_GET['inclusions']) ? $_GET['inclusions'] : 0;
    $conversions = isset($_GET['conversions']) ? $_GET['conversions'] : 0;

    // if weight1 not supplied use defaults
    if(!isset($weight1) || $weight1 == null) {
        $weight1 = 15;
        $weight2 = 20;
        $weight3 = 30;
    }
} else {
    $response = array(
        "passed" => array(
            "origin" => (isset($_GET['origin']) && !empty($_GET['origin'])) ? $_GET['origin'] : 'origin not supplied',
            "destination" => (isset($_GET['destination']) && !empty($_GET['destination'])) ? $_GET['destination'] : 'destination not supplied'
        )
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$conn = pg_connect("host=".$_ENV['DB_HOST']." dbname=".$_ENV['DB_NAME']." user=".$_ENV['DB_USER']." password=".$_ENV['DB_PASSWORD']);


// express calculations
//$result = pg_query($conn, "SELECT express_zone FROM country_zones WHERE iso_code = '$origin';");
$result = pg_query_params($conn, "SELECT express_zone FROM country_zones WHERE iso_code = $1;", array($origin));
$expressZone1 = null;
if ($result) {
    while ($row = pg_fetch_row($result)) {
        $expressZone1 = $row[0];
    }
}

$expressPrices = array();
if(!empty($expressZone1)) {
//    $result = pg_query($conn, "SELECT express_zone FROM country_zones WHERE iso_code = '$destination';");
    $result = pg_query_params($conn, "SELECT express_zone FROM country_zones WHERE iso_code = $1;", array($destination));
    $expressZone2 = null;
    if($result) {
        while ($row = pg_fetch_row($result)) {
            $expressZone2 = $row[0];
        }
    }

    if(!empty($expressZone2)) {
//        $result = pg_query($conn, "SELECT price_zone FROM zone_lookup WHERE start_zone = $expressZone1 AND end_zone = $expressZone2;");
        $result = pg_query_params($conn, "SELECT price_zone FROM zone_lookup WHERE start_zone = $1 AND end_zone = $2;", array($expressZone1, $expressZone2));
        $expressPriceZone = null;
        if($result) {
            while ($row = pg_fetch_row($result)) {
                $expressPriceZone = $row[0];
            }
        }

        if(empty($expressPriceZone)) {
            $response = array(
                "error" => "express price zone empty"
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // weight calculations - if weights are over 10KG these are the calculations instead of lookups
        // express additions is only to be added to the final price if they want calculations rather than lookups
        $expressAdditions = array();

        // lookup values for this zone and
//        $query = 'SELECT amount FROM additional_values WHERE type = \'express\' AND zone = \''.$expressPriceZone.'\' ORDER BY amount ASC';
//        $result = pg_query($conn, $query);
        $query = 'SELECT amount FROM additional_values WHERE type = \'express\' AND zone = $1 ORDER BY amount ASC';
        $result = pg_query_params($conn, $query, array($expressPriceZone));
        $below30 = pg_fetch_row($result)[0];
        $above30 = pg_fetch_row($result)[0];

        // if weights are over 30KG then use over 30KG calculations
        if($weight1 > 30) {
            // get number of 1kg intervals above 30 and multiply by the amount from the additional_values table
            $multiplier = ceil($weight1) - 30;
            $expressAdditions[] = ($multiplier * $above30) + (40 * $below30); // 40 * 0.5 = 20kg (range of the 10-30 kg additions)
        } elseif($weight1 > 10) { // use over 10KG calculations
            // get number of 0.5 intervals above 10
            $minus10 = $weight1 - 10;
            $int = floor($minus10 / 0.5);
            $remainder = fmod($minus10, 0.5) > 0 ? 1 : 0;
            $multiplier = $int + $remainder;
            $expressAdditions[] = $multiplier * $below30;
        }

        if($weight2 > 30) {
            // get number of 1kg intervals above 30 and multiply by the amount from the additional_values table
            $multiplier = ceil($weight2) - 30;
            $expressAdditions[] = ($multiplier * $above30) + (40 * $below30); // 40 * 0.5 = 20kg (range of the 10-30 kg additions)
        } elseif($weight2 > 10) { // use over 10KG calculations
            // get number of 0.5 intervals above 10
            $minus10 = $weight2 - 10;
            $int = floor($minus10 / 0.5);
            $remainder = fmod($minus10, 0.5) > 0 ? 1 : 0;
            $multiplier = $int + $remainder;
            $expressAdditions[] = $multiplier * $below30;
        }
        if($weight3 > 30) {
            // get number of 1kg intervals above 30 and multiply by the amount from the additional_values table
            $multiplier = ceil($weight3) - 30;
            $expressAdditions[] = ($multiplier * $above30) + (40 * $below30); // 40 * 0.5 = 20kg (range of the 10-30 kg additions)
        } elseif($weight3 > 10) { // use over 10KG calculations
            // get number of 0.5 intervals above 10
            $minus10 = $weight3 - 10;
            $int = floor($minus10 / 0.5);
            $remainder = fmod($minus10, 0.5) > 0 ? 1 : 0;
            $multiplier = $int + $remainder;
            $expressAdditions[] = $multiplier * $below30;
        }

        // If they turn out to want calculations rather than lookups for additional values, run these below queries with weight of 10 and add the values from $expressAdditions

        // end weight calculations

        $params = array($expressPriceZone);

        // weight1 is always supplied whether provided or the defaults are used
        $weight1Calc = $weight1 > 10 ? 10 : $weight1;
        array_push($params, $weight1Calc);
        $query = "SELECT price, 1 AS ordering FROM weight_zone_price WHERE zone = $1 AND weight = $2";

        // if weight2 is provided add a union for this
        if(isset($weight2) && !empty($weight2)) {
            $weight2Calc = $weight2 > 10 ? 10 : $weight2;
            array_push($params, $expressPriceZone);
            array_push($params, $weight2Calc);
            $query .= " UNION ALL SELECT price, 2 AS ordering FROM weight_zone_price WHERE zone = $3 AND weight = $4";
        }

        // if weight3 is provided add a union for this
        if(isset($weight3) && !empty($weight3)) {
            $weight3Calc = $weight3 > 10 ? 10 : $weight3;
            array_push($params, $expressPriceZone);
            array_push($params, $weight3Calc);
            $query .= " UNION ALL SELECT price, 3 AS ordering FROM weight_zone_price WHERE zone = $5 AND weight = $6";
        }

        $query .= " ORDER BY ordering ASC";

        $result = pg_query_params($conn, $query, $params);

        $counter = 0;
        $selector = 0;
        if($result) {
            while ($row = pg_fetch_row($result)) {
                switch($counter) {
                    case 0:
                        if($weight1 > 10 && isset($expressAdditions[$selector])) {
                            $expressPrices[] = $row[0] + $expressAdditions[$selector];
                            $selector++;
                        } else {
                            $expressPrices[] = $row[0];
                        }
                        break;
                    case 1:
                        if($weight2 > 10 && isset($expressAdditions[$selector])) {
                            $expressPrices[] = $row[0] + $expressAdditions[$selector];
                            $selector++;
                        } else {
                            $expressPrices[] = $row[0];
                        }
                        break;
                    case 2:
                        if($weight3 > 10 && isset($expressAdditions[$selector])) {
                            $expressPrices[] = $row[0] + $expressAdditions[$selector];
                            $selector++;
                        } else {
                            $expressPrices[] = $row[0];
                        }
                        break;
                }
                $counter++;
            }
        }
    }
}
// end express calculations



// economy calculations
//$result = pg_query($conn, "SELECT economy_zone FROM country_zones WHERE iso_code = '$origin';");
$result = pg_query_params($conn, "SELECT economy_zone FROM country_zones WHERE iso_code = $1;", array($origin));
$economyZone1 = null;
if ($result) {
    while ($row = pg_fetch_row($result)) {
        $economyZone1 = $row[0];
    }
}

$economyPrices = array();
if(!empty($economyZone1)) {
//    $result = pg_query($conn, "SELECT economy_zone FROM country_zones WHERE iso_code = '$destination';");
    $result = pg_query_params($conn, "SELECT economy_zone FROM country_zones WHERE iso_code = $1;", array($destination));
    $economyZone2 = null;
    if($result) {
        while ($row = pg_fetch_row($result)) {
            $economyZone2 = $row[0];
        }
    }

    if(!empty($economyZone2)) {
//        $result = pg_query($conn, "SELECT price_zone FROM economy_zone_lookup WHERE start_zone = $economyZone1 AND end_zone = $economyZone2;");
        $result = pg_query_params($conn, "SELECT price_zone FROM economy_zone_lookup WHERE start_zone = $1 AND end_zone = $2;", array($economyZone1, $economyZone2));
        $economyPriceZone = null;
        if($result) {
            while ($row = pg_fetch_row($result)) {
                $economyPriceZone = $row[0];
            }
        }

        if(empty($economyPriceZone)) {
            $response = array(
                "error" => "economy price zone empty"
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // weight calculations - if weights are over 30KG these are the calculations instead of lookups
        // express additions is only to be added to the final price if they want calculations rather than lookups
        $economyAdditions = array();

        // lookup values for this zone and
//        $query = 'SELECT amount FROM additional_values WHERE type = \'economy\' AND zone = \''.$economyPriceZone.'\' ORDER BY amount ASC';
        $query = 'SELECT amount FROM additional_values WHERE type = \'economy\' AND zone = $1 ORDER BY amount ASC';
        $result = pg_query_params($conn, $query, array($economyPriceZone));
        $below70 = pg_fetch_row($result)[0];
        $above70 = pg_fetch_row($result)[0];

        // if weights are over 70KG then use over 70KG calculations
        if($weight1 > 70) {
            // get number of 5kg intervals above 70 and multiply by the amount from the additional_values table
            $multiplier = ceil(($weight1 - 70)/5);
            $economyAdditions[] = ($multiplier * $above70) + (40 * $below70); // 40 * 0.5 = 20kg (range of the 30-70 kg additions)
        } elseif($weight1 > 30) { // use over 30KG calculations
            // get number of 1kg intervals above 30
            $minus30 = $weight1 - 30;
            $int = floor($minus30);
            $remainder = fmod($minus30, 1) > 0 ? 1 : 0;
            $multiplier = $int + $remainder;
            $economyAdditions[] = $multiplier * $below70;
        }

        if($weight2 > 70) {
            // get number of 5kg intervals above 70 and multiply by the amount from the additional_values table
            $multiplier = ceil(($weight2 - 70)/5);
            $economyAdditions[] = ($multiplier * $above70) + (40 * $below70); // 40 * 0.5 = 20kg (range of the 30-70 kg additions)
        } elseif($weight2 > 30) { // use over 30KG calculations
            // get number of 1kg intervals above 30
            $minus30 = $weight2 - 30;
            $int = floor($minus30);
            $remainder = fmod($minus30, 1) > 0 ? 1 : 0;
            $multiplier = $int + $remainder;
            $economyAdditions[] = $multiplier * $below70;
        }

        if($weight3 > 70) {
            // get number of 5kg intervals above 70 and multiply by the amount from the additional_values table
            $multiplier = ceil(($weight3 - 70)/5);
            $economyAdditions[] = ($multiplier * $above70) + (40 * $below70); // 40 * 0.5 = 20kg (range of the 30-70 kg additions)
        } elseif($weight3 > 30) { // use over 30KG calculations
            // get number of 1kg intervals above 30
            $minus30 = $weight3 - 30;
            $int = floor($minus30);
            $remainder = fmod($minus30, 1) > 0 ? 1 : 0;
            $multiplier = $int + $remainder;
            $economyAdditions[] = $multiplier * $below70;
        }
        // end weight calculations

        $params = array($economyPriceZone);

        //weight1 is always supplied whether provided or the defaults are used
        $weight1Calc = $weight1 > 30 ? 30 : $weight1;
        array_push($params, $weight1Calc);
        $query = "SELECT price, 1 AS ordering FROM economy_weight_zone_price WHERE zone = $1 AND weight = $2";

        // if weight2 is provided add a union for this
        if(isset($weight2) && !empty($weight2)) {
            $weight2Calc = $weight2 > 30 ? 30 : $weight2;
            array_push($params, $economyPriceZone);
            array_push($params, $weight2Calc);
            $query .= " UNION ALL SELECT price, 2 AS ordering FROM economy_weight_zone_price WHERE zone = $3 AND weight = $4";
        }

        // if weight3 is provided add a union for this
        if(isset($weight3) && !empty($weight3)) {
            $weight3Calc = $weight3 > 30 ? 30 : $weight3;
            array_push($params, $economyPriceZone);
            array_push($params, $weight3Calc);
            $query .= " UNION ALL SELECT price, 3 AS ordering FROM economy_weight_zone_price WHERE zone = $5 AND weight = $6";
        }

        $query .= " ORDER BY ordering ASC";

        $result = pg_query_params($conn, $query, $params);

        $counter = 0;
        $selector = 0;
        if($result) {
            while ($row = pg_fetch_row($result)) {
                switch($counter) {
                    case 0:
                        if($weight1 > 30 && isset($economyAdditions[$selector])) {
                            $economyPrices[] = $row[0] + $economyAdditions[$selector];
                            $selector++;
                        } else {
                            $economyPrices[] = $row[0];
                        }
                        break;
                    case 1:
                        if($weight2 > 30 && isset($expressAdditions[$selector])) {
                            $economyPrices[] = $row[0] + $economyAdditions[$selector];
                            $selector++;
                        } else {
                            $economyPrices[] = $row[0];
                        }
                        break;
                    case 2:
                        if($weight3 > 30 && isset($economyAdditions[$selector])) {
                            $economyPrices[] = $row[0] + $economyAdditions[$selector];
                            $selector++;
                        } else {
                            $economyPrices[] = $row[0];
                        }
                        break;
                }
                $counter++;
            }
        }
    }
}
// end economy calculation

// percentages are 1+% for easy calculations
$expressFuel = 1.16;
$expressProfit = 1.1;

$economyFuel = 1.1;
$economyProfit = 1.3;
$economyVAT = 1.2;
// end percentages

// GBP EUR conversion
$poundsPerEuro = null;
$eurosPerPound = null;
$result = pg_query($conn, "SELECT conversion_value FROM euro_exchange_rates");
if($result){
    while ($row = pg_fetch_row($result)) {
        $conversion = (float)$row[0];
        $poundsPerEuro = $conversion;
        $eurosPerPound = (1.0/$conversion);
    }
}

$expressPricesGBP = array();
foreach($expressPrices as $price) {
    $expressPricesGBP[] = ($price * $poundsPerEuro);
}

$economyPricesEUR = array();
foreach($economyPrices as $price) {
    $economyPricesEUR[] = ($price * $eurosPerPound);
}
// end GBP EUR conversion


$response = array(
    "passed" => array(
        "origin" => isset($origin) ? $origin : null,
        "destination" => isset($destination) ? $destination : null,
        "weight1" => isset($originalWeight1) ? $originalWeight1 : null,
        "weight2" => isset($originalWeight2) ? $originalWeight2 : null,
        "weight3" => isset($originalWeight3) ? $originalWeight3 : null,
        "inclusions" => isset($inclusions) ? $inclusions : null,
        "conversions" => isset($conversions) ? $conversions : null
    ),
    "express" => array(
        "weight1NetPriceEUR" => isset($expressPrices[0]) ? round($expressPrices[0], 2) : null,
        "weight2NetPriceEUR" => isset($expressPrices[1]) ? round($expressPrices[1], 2) : null,
        "weight3NetPriceEUR" => isset($expressPrices[2]) ? round($expressPrices[2], 2) : null,
        "weight1TotalPriceEUR" => isset($expressPrices[0]) ? round(($expressPrices[0] * $expressFuel * $expressProfit), 2) : null,
        "weight2TotalPriceEUR" => isset($expressPrices[1]) ? round(($expressPrices[1] * $expressFuel * $expressProfit), 2) : null,
        "weight3TotalPriceEUR" => isset($expressPrices[2]) ? round(($expressPrices[2] * $expressFuel * $expressProfit), 2) : null,
        "weight1NetPriceGBP" => isset($expressPricesGBP[0]) ? round($expressPricesGBP[0], 2) : null,
        "weight2NetPriceGBP" => isset($expressPricesGBP[1]) ? round($expressPricesGBP[1], 2) : null,
        "weight3NetPriceGBP" => isset($expressPricesGBP[2]) ? round($expressPricesGBP[2], 2) : null,
        "weight1TotalPriceGBP" => isset($expressPricesGBP[0]) ? round(($expressPricesGBP[0] * $expressFuel * $expressProfit), 2) : null,
        "weight2TotalPriceGBP" => isset($expressPricesGBP[1]) ? round(($expressPricesGBP[1] * $expressFuel * $expressProfit), 2) : null,
        "weight3TotalPriceGBP" => isset($expressPricesGBP[2]) ? round(($expressPricesGBP[2] * $expressFuel * $expressProfit), 2) : null
    ),
    "economy" => array(
        "weight1NetPriceEUR" => isset($economyPricesEUR[0]) ? round($economyPricesEUR[0], 2) : null,
        "weight2NetPriceEUR" => isset($economyPricesEUR[1]) ? round($economyPricesEUR[1], 2) : null,
        "weight3NetPriceEUR" => isset($economyPricesEUR[2]) ? round($economyPricesEUR[2], 2) : null,
        "weight1TotalPriceEUR" => isset($economyPricesEUR[0]) ? round(($economyPricesEUR[0] * $economyFuel * $economyProfit * $economyVAT), 2) : null,
        "weight2TotalPriceEUR" => isset($economyPricesEUR[1]) ? round(($economyPricesEUR[1] * $economyFuel * $economyProfit * $economyVAT), 2) : null,
        "weight3TotalPriceEUR" => isset($economyPricesEUR[2]) ? round(($economyPricesEUR[2] * $economyFuel * $economyProfit * $economyVAT), 2) : null,
        "weight1NetPriceGBP" => isset($economyPrices[0]) ? round($economyPrices[0], 2) : null,
        "weight2NetPriceGBP" => isset($economyPrices[1]) ? round($economyPrices[1], 2) : null,
        "weight3NetPriceGBP" => isset($economyPrices[2]) ? round($economyPrices[2], 2) : null,
        "weight1TotalPriceGBP" => isset($economyPrices[0]) ? round(($economyPrices[0] * $economyFuel * $economyProfit * $economyVAT), 2) : null,
        "weight2TotalPriceGBP" => isset($economyPrices[1]) ? round(($economyPrices[1] * $economyFuel * $economyProfit * $economyVAT), 2) : null,
        "weight3TotalPriceGBP" => isset($economyPrices[2]) ? round(($economyPrices[2] * $economyFuel * $economyProfit * $economyVAT), 2) : null
    )
);

// deciding what to return
foreach($response["passed"] as $key => $value) {
    if($value == null) {
        unset($response['passed'][$key]);
    }
}
foreach($response["express"] as $key => $value) {
    if(!$inclusions) {
        if(strpos($key, 'Total')) {
            unset($response['express'][$key]);
        }
    }
    if(!$conversions) {
        if(strpos($key, 'GBP')) {
            unset($response['express'][$key]);
        }
    }
    if($value == null) {
        unset($response['express'][$key]);
    }
}

foreach($response["economy"] as $key => $value) {
    if(!$inclusions) {
        if(strpos($key, 'Total')) {
            unset($response['economy'][$key]);
        }
    }
    if(!$conversions) {
        if(strpos($key, 'EUR')) {
            unset($response['economy'][$key]);
        }
    }
    if($value == null) {
        unset($response['economy'][$key]);
    }
}
// end deciding what to return

header('Content-Type: application/json');
echo json_encode($response);
