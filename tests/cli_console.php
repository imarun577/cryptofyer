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
$_exchange  = isSet($args["exchange"]) ? strtolower($args["exchange"]) : "";

$exchange = null;

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

fwrite(STDOUT, "\n");
do {
  fwrite(STDOUT, "Active exchanges\n");
  $counter  = 1;
  $exchanges  = array();
  fwrite(STDOUT, "[0] exit\n");
  foreach($config as $key=>$value) {
    if(isSet($exchangesInstances[$key])) {
      fwrite(STDOUT, "[$counter] $key\n");
      $exchanges[$counter . "_exchange"]  = $key;
      $counter++;
    }
  }
  fwrite(STDOUT, "Select exchange : ");
  $selectOrder  = fgets(STDIN);
  $selectOrder  = strtolower(trim(preg_replace('/\s+/', '', $selectOrder)));

  $index        = $selectOrder . "_exchange";
  if($index == "0_exchange") {
    die();
  }

  $keyExchange  = isSet($exchanges[$index]) ? $exchanges[$index] : null;
  if($keyExchange != null) {
    $exchange   = $exchangesInstances[$keyExchange];
    $_exchange  = $keyExchange;
  }
  cls();
} while ($_exchange == "");

$market = $exchange->getMarketPair($_market , $_currency);
$prefixConsole  = "[" . $_exchange . " " . $exchange->getVersion() . " " . $market  . "]";

$command  = "";

getTicker($exchange,$market);
listMenu();

do {
  fwrite(STDOUT, "$prefixConsole Enter command: ");

  $command = fgets(STDIN);
  $command = strtolower(trim(preg_replace('/\s+/', '', $command)));

  switch($command) {

    default : {
      fwrite(STDOUT, "$prefixConsole [ERROR] I don't know that command!\n\n");
      break;
    }

    case "x" : {
        $command  = "q";
    }
    case "q" : {
      break;
    }

    case "m" : {
      listMenu();
      break;
    }

    case "cls" : {
      cls();
      break;
    }

    case "t" : {
      getTicker($exchange,$market);
      break;
    }
  }

} while ($command != "q");


function getTicker($exchange,$market) {
  fwrite(STDOUT, "** Fetching ticket information for $market\n");
  $tickerOBJ  = $exchange->getTicker(array("market" => $market));
  if(!empty($tickerOBJ)) {
    if($tickerOBJ["success"]  == true) {
      $last = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
      $bid = number_format($tickerOBJ["result"]["Bid"], 8, '.', '');
      $ask = number_format($tickerOBJ["result"]["Ask"], 8, '.', '');
      fwrite(STDOUT, "Last = $last\n");
      fwrite(STDOUT, "Bid = $bid\n");
      fwrite(STDOUT, "Ask = $ask\n");
    } else {
      $error  = $tickerOBJ["message"];
      fwrite(STDOUT, "[ERROR] [API] $error\n");
    }
    fwrite(STDOUT, "\n");
  }
}

function listMenu() {
  fwrite(STDOUT, "** List of command(s) :\n");
  fwrite(STDOUT, "[q] quit\n");
  fwrite(STDOUT, "[m] menu\n");
  fwrite(STDOUT, "[t] get ticker information\n");
  fwrite(STDOUT, "[s] sell units\n");
  fwrite(STDOUT, "[b] buy units\n");
  fwrite(STDOUT, "[o] get open orders\n");
  fwrite(STDOUT, "[c] cancel orders\n");
  fwrite(STDOUT, "[cls] clear screen\n");
  fwrite(STDOUT, "\n");
}
?>
