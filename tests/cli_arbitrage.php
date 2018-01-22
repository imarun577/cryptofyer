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
fwrite(STDOUT, "\n");
fwrite(STDOUT, "-------------------------- \n");

$data = array(
  "Ask" => array(),
  "Bid" => array()
);

$bidHigh  = 0;
$bidExhange = "";

$askTMP = array();

foreach($config as $key=>$value) {
  if(isSet($exchangesInstances[$key])) {

    fwrite(STDOUT, " \n");
    fwrite(STDOUT, "Querying $key : ");

    $exchange = $exchangesInstances[$key];

    $result = $exchange->getTicker(array("_market" => $_market , "_currency" => $_currency));

    if($result != null && isSet($result["success"]) && $result["success"]==true) {

      fwrite(STDOUT, "FOUND\n");

      $bid  = number_format($result["result"]["Bid"], 8, '.', '');
      fwrite(STDOUT, "BID : $bid\n");

      if($bid > $bidHigh) {
        $bidHigh  = $bid;
        $bidExhange = $key;
      }

      $ask  = number_format($result["result"]["Ask"], 8, '.', '');
      fwrite(STDOUT, "ASK : $ask\n");

      $askTMP[$key] = $ask;

    } else {
      fwrite(STDOUT, "not found\n");
    }

  }
}

fwrite(STDOUT, "\n");
fwrite(STDOUT, "-------------------------- \n");
fwrite(STDOUT, "Sell : $bidHigh on $bidExhange\n");

unset($askTMP[$bidExhange]);

$askLow = 0;
$askExchange  = "";

foreach($askTMP as $key=>$value) {
  if($askLow == 0) {
    $askLow = $value;
    $askExchange  = $key;
  } else {
    if($value < $askLow) {
      $askLow = $value;
      $askExchange  = $key;
    }
  }
}

if($askLow > 0) {
  fwrite(STDOUT, "Buy : $askLow on $askExchange\n");

  $profit = number_format($bidHigh - $askLow, 8, '.', '');
  fwrite(STDOUT, "Profit : $profit\n");
  fwrite(STDOUT, "\n");
} else {
  fwrite(STDOUT, "Buy: not found\n");
}

fwrite(STDOUT, " \n");
?>
