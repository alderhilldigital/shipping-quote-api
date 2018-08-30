# Shipping Quote API

This api accepts an API Key, an Origin, a Destination and 0 - 3 weights.  It performs a lookup, and returns price information for these parameters in the form of a JSON object.


## Required parameters

$_GET['key'] (string) - this is an api key to prevent unauthorised access to the api  
$_GET['origin'] (string) - this is a 2 letter iso country code  
$_GET['destination'] (string) - this is a 2 letter iso country code  

If any required parameters are not provided, the api will return a 401 header response


## Optional parameters

$_GET['weight1'] (double) - representing Kilograms  
$_GET['weight2'] (double) - representing Kilograms  
$_GET['weight3'] (double) - representing Kilograms  

If no weight parameters are provided the api will use default values of 15, 20 and 30.  If any weights are passed, the api will return quotes for these weights only not using default values for the other(s).


## Return format

The api will return a JSON object with three child objects "passed", "express" and "economy".  The "passed" object simply contains the values the api has been passed (excluding the key of course).  The "express" object contains shipping quotes for express delivery, and the "economy" object contains shipping quotes for economy delivery.  These quotes are in an identical format as shown below:  
  
"currency" (string) - This can be "EUR" for euros or "GBP" for stirling  
"weight1NetPrice" (double) - This will return the net price for the "weight1" parameter  
"weight2NetPrice" (double) - This will return the net price for the "weight2" parameter  
"weight3NetPrice" (double) - This will return the net price for the "weight3" parameter  
"weight1TotalPrice" (double) - This will return the total price for the "weight1" parameter after adding fuel and profit percentages  
"weight2TotalPrice" (double) - This will return the total price for the "weight2" parameter after adding fuel and profit percentages  
"weight3TotalPrice" (double) - This will return the total price for the "weight3" parameter after adding fuel and profit percentages  

{  
    &nbsp;&nbsp;&nbsp;&nbsp;"passed": {  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"origin": "DE",  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"destination": "AE",  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1": 15,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2": 20,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3": 30  
    &nbsp;&nbsp;&nbsp;&nbsp;},  
    &nbsp;&nbsp;&nbsp;&nbsp;"express": {  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"currency": "EUR",  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPrice": 121.13,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPrice": 149.73,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPrice": 206.93,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1TotalPrice": 154.56188,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2TotalPrice": 191.05548,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3TotalPrice": 264.04268  
    &nbsp;&nbsp;&nbsp;&nbsp;},  
    &nbsp;&nbsp;&nbsp;&nbsp;"economy": {  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"currency": "GBP",  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPrice": null,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPrice": null,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPrice": null,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1TotalPrice": null,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2TotalPrice": null,  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3TotalPrice": null  
    &nbsp;&nbsp;&nbsp;&nbsp;}  
}