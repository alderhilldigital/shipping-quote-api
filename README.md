#Shipping Quote API

This api accepts an API Key, an Origin, a Destination and 0 - 3 weights.  It performs a lookup, and returns price information for these parameters in the form of a JSON object.


##Required parameters

$_GET['key'] (string) - this is an api key to prevent unauthorised access to the api
$_GET['origin'] (string) - this is a 2 letter iso country code
$_GET['destination'] (string) - this is a 2 letter iso country code

If any required parameters are not provided, the api will return a 401 header response


##Optional parameters

$_GET['weight1'] (double) - representing Kilograms
$_GET['weight2'] (double) - representing Kilograms
$_GET['weight3'] (double) - representing Kilograms

If no weight parameters are provided the api will use default values of 15, 20 and 30.  If any weights are passed, the api will return quotes for these weights only not using default values for the other(s).


##Return format

The api will return a JSON object with three child objects "passed", "express" and "economy".  The "passed" object simply contains the values the api has been passed (excluding the key of course).  The "express" object contains shipping quotes for express delivery, and the "economy" object contains shipping quotes for economy delivery.  These quotes are in an identical format as shown below:

"currency" (string) - This can be "EUR" for euros or "GBP" for stirling
"weight1NetPrice" (double) - This will return the net price for the "weight1" parameter
"weight2NetPrice" (double) - This will return the net price for the "weight2" parameter
"weight3NetPrice" (double) - This will return the net price for the "weight3" parameter
"weight1TotalPrice" (double) - This will return the total price for the "weight1" parameter after adding fuel and profit percentages
"weight2TotalPrice" (double) - This will return the total price for the "weight2" parameter after adding fuel and profit percentages
"weight3TotalPrice" (double) - This will return the total price for the "weight3" parameter after adding fuel and profit percentages

{
    "passed": {
        "origin": "DE",
        "destination": "AE",
        "weight1": 15,
        "weight2": 20,
        "weight3": 30
    },
    "express": {
        "currency": "EUR",
        "weight1NetPrice": 121.13,
        "weight2NetPrice": 149.73,
        "weight3NetPrice": 206.93,
        "weight1TotalPrice": 154.56188,
        "weight2TotalPrice": 191.05548,
        "weight3TotalPrice": 264.04268
    },
    "economy": {
        "currency": "GBP",
        "weight1NetPrice": null,
        "weight2NetPrice": null,
        "weight3NetPrice": null,
        "weight1TotalPrice": null,
        "weight2TotalPrice": null,
        "weight3TotalPrice": null
    }
}