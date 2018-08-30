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

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['key']) && isset($_GET['origin']) && !empty($_GET['origin']) && isset($_GET['destination']) && !empty($_GET['destination'])) {
    $origin = $_GET['origin'];
    $destination = $_GET['destination'];
    $weight1 = isset($_GET['weight1']) ? $_GET['weight1'] : null;
    $weight2 = isset($_GET['weight2']) ? $_GET['weight2'] : null;
    $weight3 = isset($_GET['weight3']) ? $_GET['weight3'] : null;

    // if weight1 not supplied use defaults
    if(!isset($weight1) || $weight1 == null) {
        $weight1 = 15;
        $weight2 = 20;
        $weight3 = 30;
    }
} else {
    $response = array(
        "passed" => array(
            "origin" => $origin,
            "destination" => $destination,
            "weight1" => $weight1,
            "weight2" => $weight2,
            "weight3" => $weight3
        ),
        "express" => null,
        "economy" => null
    );
    header('Content-Type: application/json');
    echo json_encode($response);
//    return json_encode($response);
}

$conn = pg_connect("host=".$_ENV['DB_HOST']." dbname=".$_ENV['DB_NAME']." user=".$_ENV['DB_USER']." password=".$_ENV['DB_PASSWORD']);


// express calculations
$result = pg_query($conn, "SELECT express_zone FROM country_zones WHERE iso_code = '$origin';");
$expressZone1 = null;
if ($result) {
    while ($row = pg_fetch_row($result)) {
        $expressZone1 = $row[0];
    }
}

if(!empty($expressZone1)) {
    $result = pg_query($conn, "SELECT express_zone FROM country_zones WHERE iso_code = '$destination';");
    $expressZone2 = null;
    if($result) {
        while ($row = pg_fetch_row($result)) {
            $expressZone2 = $row[0];
        }
    }

    if(!empty($expressZone2)) {
        $result = pg_query($conn, "SELECT price_zone FROM zone_lookup WHERE start_zone = $expressZone1 AND end_zone = $expressZone2;");
        $expressPriceZone = null;
        if($result) {
            while ($row = pg_fetch_row($result)) {
                $expressPriceZone = $row[0];
            }
        }

        if(empty($expressPriceZone)) {
            die('express price zone empty');
        }

        //weight1 is always supplied whether provided or the defaults are used
        $query = "SELECT price, 1 AS ordering FROM weight_zone_price WHERE zone = '$expressPriceZone' AND weight = $weight1";

        // if weight2 is provided add a union for this
        if(isset($weight2) && !empty($weight2)) {
            $query .= " UNION ALL SELECT price, 2 AS ordering FROM weight_zone_price WHERE zone = '$expressPriceZone' AND weight = $weight2";
        }

        // if weight3 is provided add a union for this
        if(isset($weight3) && !empty($weight3)) {
            $query .= " UNION ALL SELECT price, 3 AS ordering FROM weight_zone_price WHERE zone = '$expressPriceZone' AND weight = $weight3";
        }

        $query .= " ORDER BY ordering ASC";

        $result = pg_query($conn, $query);

        $expressPrices = array();
        if($result) {
            while ($row = pg_fetch_row($result)) {
                $expressPrices[] = $row[0];
            }
        }
    }
}
// end express calculations



// economy calculations
$result = pg_query($conn, "SELECT economy_zone FROM country_zones WHERE iso_code = '$origin';");
$economyZone1 = null;
if ($result) {
    while ($row = pg_fetch_row($result)) {
        $economyZone1 = $row[0];
    }
}

if(!empty($economyZone1)) {
    $result = pg_query($conn, "SELECT economy_zone FROM country_zones WHERE iso_code = '$destination';");
    $economyZone2 = null;
    if($result) {
        while ($row = pg_fetch_row($result)) {
            $economyZone2 = $row[0];
        }
    }

    if(!empty($economyZone2)) {
        $result = pg_query($conn, "SELECT price_zone FROM economy_zone_lookup WHERE start_zone = $economyZone1 AND end_zone = $economyZone2;");
        $economyPriceZone = null;
        if($result) {
            while ($row = pg_fetch_row($result)) {
                $economyPriceZone = $row[0];
            }
        }

        if(empty($economyPriceZone)) {
            die('economy price zone empty');
        }

        //weight1 is always supplied whether provided or the defaults are used
        $query = "SELECT price, 1 AS ordering FROM economy_weight_zone_price WHERE zone = '$economyPriceZone' AND weight = $weight1";

        // if weight2 is provided add a union for this
        if(isset($weight2) && !empty($weight2)) {
            $query .= " UNION ALL SELECT price, 2 AS ordering FROM economy_weight_zone_price WHERE zone = '$economyPriceZone' AND weight = $weight2";
        }

        // if weight3 is provided add a union for this
        if(isset($weight3) && !empty($weight3)) {
            $query .= " UNION ALL SELECT price, 3 AS ordering FROM economy_weight_zone_price WHERE zone = '$economyPriceZone' AND weight = $weight3";
        }

        $query .= " ORDER BY ordering ASC";

        $result = pg_query($conn, $query);

        $economyPrices = array();
        if($result) {
            while ($row = pg_fetch_row($result)) {
                $economyPrices[] = $row[0];
            }
        }
    }
}
// end economy calculation

//percentages are 1+% for easy calculations
$expressFuel = 1.16;
$expressProfit = 1.1;

$economyFuel = 1.1;
$economyProfit = 1.3;
$economyVAT = 1.2;
//end percentages

//GBP EUR conversion
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
        "origin" => $origin,
        "destination" => $destination,
        "weight1" => $weight1,
        "weight2" => $weight2,
        "weight3" => $weight3
    ),
    "express" => array(
        "weight1NetPriceEUR" => isset($expressPrices[0]) ? (double)$expressPrices[0] : null,
        "weight2NetPriceEUR" => isset($expressPrices[1]) ? (double)$expressPrices[1] : null,
        "weight3NetPriceEUR" => isset($expressPrices[2]) ? (double)$expressPrices[2] : null,
        "weight1TotalPriceEUR" => isset($expressPrices[0]) ? (double)($expressPrices[0] * $expressFuel * $expressProfit) : null,
        "weight2TotalPriceEUR" => isset($expressPrices[1]) ? (double)($expressPrices[1] * $expressFuel * $expressProfit) : null,
        "weight3TotalPriceEUR" => isset($expressPrices[2]) ? (double)($expressPrices[2] * $expressFuel * $expressProfit) : null,
        "weight1NetPriceGBP" => isset($expressPricesGBP[0]) ? (double)$expressPricesGBP[0] : null,
        "weight2NetPriceGBP" => isset($expressPricesGBP[1]) ? (double)$expressPricesGBP[1] : null,
        "weight3NetPriceGBP" => isset($expressPricesGBP[2]) ? (double)$expressPricesGBP[2] : null,
        "weight1TotalPriceGBP" => isset($expressPricesGBP[0]) ? (double)($expressPricesGBP[0] * $expressFuel * $expressProfit) : null,
        "weight2TotalPriceGBP" => isset($expressPricesGBP[1]) ? (double)($expressPricesGBP[1] * $expressFuel * $expressProfit) : null,
        "weight3TotalPriceGBP" => isset($expressPricesGBP[2]) ? (double)($expressPricesGBP[2] * $expressFuel * $expressProfit) : null
    ),
    "economy" => array(
        "weight1NetPriceGBP" => isset($economyPrices[0]) ? (double)$economyPrices[0] : null,
        "weight2NetPriceGBP" => isset($economyPrices[1]) ? (double)$economyPrices[1] : null,
        "weight3NetPriceGBP" => isset($economyPrices[2]) ? (double)$economyPrices[2] : null,
        "weight1TotalPriceGBP" => isset($economyPrices[0]) ? (double)($economyPrices[0] * $economyFuel * $economyProfit * $economyVAT) : null,
        "weight2TotalPriceGBP" => isset($economyPrices[1]) ? (double)($economyPrices[1] * $economyFuel * $economyProfit * $economyVAT) : null,
        "weight3TotalPriceGBP" => isset($economyPrices[2]) ? (double)($economyPrices[2] * $economyFuel * $economyProfit * $economyVAT) : null,
        "weight1NetPriceEUR" => isset($economyPricesEUR[0]) ? (double)$economyPricesEUR[0] : null,
        "weight2NetPriceEUR" => isset($economyPricesEUR[1]) ? (double)$economyPricesEUR[1] : null,
        "weight3NetPriceEUR" => isset($economyPricesEUR[2]) ? (double)$economyPricesEUR[2] : null,
        "weight1TotalPriceEUR" => isset($economyPricesEUR[0]) ? (double)($economyPricesEUR[0] * $economyFuel * $economyProfit * $economyVAT) : null,
        "weight2TotalPriceEUR" => isset($economyPricesEUR[1]) ? (double)($economyPricesEUR[1] * $economyFuel * $economyProfit * $economyVAT) : null,
        "weight3TotalPriceEUR" => isset($economyPricesEUR[2]) ? (double)($economyPricesEUR[2] * $economyFuel * $economyProfit * $economyVAT) : null
    )
);

header('Content-Type: application/json');
echo json_encode($response);
//return json_encode($response);

?>