<?php
require_once('/var/www/html/config.php');

# Get JSON as a string
$json_str = file_get_contents('http://data.fixer.io/api/latest?access_key=dd284e34f6c298c4bd29c8df9378bb58&symbols=GBP');

# Get as an object
$json_obj = json_decode($json_str);

# DB connection
$conn = pg_connect("host=".$config['host']." dbname=".$config['dbname']." user=".$config['user']." password=".$config['password']);

$result = pg_query_params($conn, "UPDATE euro_exchange_rates SET conversion_value = $1, updated_at = NOW() WHERE currency = 'GBP'",array($json_obj->{'rates'}->{'GBP'}));

?>
