# Shipping Quote API

This api accepts an API Key, an Origin, a Destination, 0 - 3 weights and options to convert currency and add preset sub values of tax, vat and profit.  It performs a lookup, and returns price information for these parameters in the form of a JSON object.


# Usage

## Required parameters

- key (text) - this is an api key to prevent unauthorised access to the api  
- origin (text) - this is a 2 letter iso country code
- destination (string) - this is a 2 letter iso country code

If the api key is not provided, the api will return a 401 header response.
If an origin or destination is not provided, only the parameters passed in will be returned.

### Example Request
http://44.44.44.44/?key=123456789101112&origin=FR&destination=DE


## Optional parameters

- weight1 (numeric) - representing Kilograms  
- weight2 (numeric) - representing Kilograms  
- weight3 (numeric) - representing Kilograms  
- inclusions (numeric) - representing true / false for adding fuel, vat and profit
- conversion (numeric) - representing true / false for converting between euro and pounds sterling

If no weight parameters are provided the api will use default values of 15, 20 and 30.  If any weights are passed, the api will return quotes for those weights only not using default values for the other(s).

### Examples
http://44.44.44.44/?key=123456789101112&origin=FR&destination=DE&weight1=10  
http://44.44.44.44/?key=123456789101112&origin=FR&destination=DE&weight1=10&weight2=20  
http://44.44.44.44/?key=123456789101112&origin=FR&destination=DE&weight1=10&inclusions=1  
http://44.44.44.44/?key=123456789101112&origin=FR&destination=DE&weight1=10&conversions=1  

## Response format

The api will return a JSON object with three child objects "passed", "express" and "economy".  
The "passed" object simply contains the values the api has been passed (excluding the key of course).  
The "express" object contains shipping quotes for express delivery, and the "economy" object contains shipping quotes for economy delivery.

## Default Response

Calling the default method with just an origin and destination will return the following values for express and economy objects

- "weight1NetPriceEUR" (double) - This will return the net price for the "weight1" parameter  
- "weight2NetPriceEUR" (double) - This will return the net price for the "weight2" parameter  
- "weight3NetPriceEUR" (double) - This will return the net price for the "weight3" parameter    

### Example Response

Here is a sample of the output returned by the Shipping Quote API passing only a key, origin and destination:  

{  
&nbsp;&nbsp;&nbsp;&nbsp;"passed": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"origin": "FR",  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"destination": "DE"  
&nbsp;&nbsp;&nbsp;&nbsp;
},  
&nbsp;&nbsp;&nbsp;&nbsp;"express": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPriceEUR": 70.19,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPriceEUR": 85.79,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPriceEUR": 116.99  
&nbsp;&nbsp;&nbsp;&nbsp;
},  
&nbsp;&nbsp;&nbsp;&nbsp;"economy": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPriceEUR": 21.86,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPriceEUR": 25.37,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPriceEUR": 31.31  
&nbsp;&nbsp;&nbsp;&nbsp;
}  
}


## Response with inclusions

Calling the API with an option to include sub items of tax, vat and profit will return net values and total values for each weight.

### Example Response

Here is a sample of the output returned by the Shipping Quote API passing only a key, origin, destination and inclusions:  

{  
&nbsp;&nbsp;&nbsp;&nbsp;"passed": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"origin": "FR",  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"destination": "DE",  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"inclusions": "1"  
&nbsp;&nbsp;&nbsp;&nbsp;
},  
&nbsp;&nbsp;&nbsp;&nbsp;"express": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPriceEUR": 70.19,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPriceEUR": 85.79,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPriceEUR": 116.99,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1TotalPriceEUR": 89.56,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2TotalPriceEUR": 109.47,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3TotalPriceEUR": 149.28  
&nbsp;&nbsp;&nbsp;&nbsp;
},  
&nbsp;&nbsp;&nbsp;&nbsp;"economy": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPriceEUR": 21.86,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPriceEUR": 25.37,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPriceEUR": 31.31,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1TotalPriceEUR": 37.51,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2TotalPriceEUR": 43.53,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3TotalPriceEUR": 53.73  
&nbsp;&nbsp;&nbsp;&nbsp;
}  
}


## Response with conversions

Calling the API with an option to include currency conversions will return Euro and Pounds Sterling values for each weight.

### Example Response

Here is a sample of the output returned by the Shipping Quote API passing only a key, origin, destination and conversions:  

{  
&nbsp;&nbsp;&nbsp;&nbsp;"passed": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"origin": "FR",  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"destination": "DE",  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"conversions": "1"  
&nbsp;&nbsp;&nbsp;&nbsp;
},  
&nbsp;&nbsp;&nbsp;&nbsp;"express": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPriceEUR": 70.19,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPriceEUR": 85.79,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPriceEUR": 116.99,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPriceGBP": 63.06,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPriceGBP": 77.07,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPriceGBP": 105.10  
&nbsp;&nbsp;&nbsp;&nbsp;
},  
&nbsp;&nbsp;&nbsp;&nbsp;"economy": {  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPriceEUR": 21.86,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPriceEUR": 25.37,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPriceEUR": 31.31,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight1NetPriceGBP": 19.64,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight2NetPriceGBP": 22.79,  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"weight3NetPriceGBP": 28.13  
&nbsp;&nbsp;&nbsp;&nbsp;
}  
}

# Infrastructure

## Server Software
Linux Ubuntu 16.04  
Apache 2  
Php 7  
Postgres 9.5  
libapache2-mod-php (Apache php module)  
php7.0-pgsql (php postgresql module)  

## API setup

### Web Server

default apache site used  
web root - /var/www/html/

### Composer packages
phpdotenv - package to enable php to use environment variables file  


# Caveats

- Express lookup doesn't have Republic of Ireland
- Economy lookup doesn't have United Kingdom
- Lookups for weights above 30kg (economy) don't calulate to same value as lookup table
- Economy 300kg - 1000kg Zone 3 doesn't match 70kg - 300kg value where rest of zones do match (only off by 0.01)
