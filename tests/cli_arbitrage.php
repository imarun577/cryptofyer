<?php
/*
  This example file will try and find the best exchange to sell and buy a currency for arbitrage
  You can only run this example from command line!
*/
if(php_sapi_name() != 'cli') die("you need to run this script from commandline!");

include("includes.php");

cls(); // clear screen

// parse CLI args
$args = array();
if($argc>1) {
  parse_str(implode('&',array_slice($argv, 1)), $args);
}

$_market    = isSet($args["market"]) ? strtolower($args["market"]) : "";
$_currency  = isSet($args["currency"]) ? strtolower($args["currency"]) : "";

if(empty($_market)) {
  $_market  = "BTC";
  fwrite(STDOUT, "Enter market: [$_market] > ");
  $marketSelection = fgets(STDIN);
  $marketSelection  = trim(preg_replace('/\s+/', '', $marketSelection));
  $_market  = !empty($marketSelection) ? $marketSelection : $_market;
}
$_market  = strtoupper($_market);
fwrite(STDOUT, "Using market: $_market\n");


if(empty($_currency)) {
  $_currency  = "ETH";
  fwrite(STDOUT, "Enter currency: [$_currency] > ");
  $currencySelection = fgets(STDIN);
  $currencySelection  = trim(preg_replace('/\s+/', '', $currencySelection));
  $_currency  = !empty($currencySelection) ? $currencySelection : $_currency;
}
$_currency  = strtoupper($_currency);
fwrite(STDOUT, "Using currency: $_currency\n");
fwrite(STDOUT, "Trying to find the exchange to sell and buy.\n");

$data = array(
  "Ask" => array(),
  "Bid" => array()
);

foreach($config as $key=>$value) {
  if(isSet($exchangesInstances[$key])) {
    $exchange = $exchangesInstances[$key];

    $result = $exchange->getTicker(array("_market" => $_market , "_currency" => $_currency));

    if(isSet($result["success"]) && $result["success"]==true) {

      $value  = number_format($result["result"]["Ask"], 8, '.', '');
      if($data["Ask"] == null) {
        $data["Ask"]  = array(
          "exchange"  => $key,
          "value"     => $value
        );
      } else {
        if($value < $data["Ask"]["value"]) {
          $data["Ask"]  = array(
            "exchange"  => $key,
            "value"     => $value
          );
        }
      }

      $value  = number_format($result["result"]["Bid"], 8, '.', '');
      if($data["Bid"] == null) {
        $data["Bid"]  = array(
          "exchange"  => $key,
          "value"     => $value
        );
      } else {
        if($value < $data["Bid"]["value"]) {
          $data["Bid"]  = array(
            "exchange"  => $key,
            "value"     => $value
          );
        }
      }
    }

  }
}

if($data["Ask"] != null && $data["Bid"] != null) {
  $exchange   =  $data['Ask']['exchange'];
  $value      =  $data['Ask']['value'];
  fwrite(STDOUT, "Sell $_currency at exchange: $exchange -> $value\n");

  $exchange   =  $data['Bid']['exchange'];
  $value      =  $data['Bid']['value'];
  fwrite(STDOUT, "Buy $_currency at exchange: $exchange -> $value\n");
} else {
  fwrite(STDOUT, "Failed to lookup\n");
}
?>
