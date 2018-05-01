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

  if($command == null)  {
    cls();
  }

  switch($command) {

    default : {
      fwrite(STDOUT, "$prefixConsole [ERROR] I don't know that option!\n\n");
      break;
    }

    case -1 : {
      break;
    }

    case 0 : {
      listMenu();
      break;
    }

    case 1 : {
      getTicker($exchange,$market);
      break;
    }

    case 2 : { }
    case 3 : { }
    case 4 : { }
    case 5 : {
      fwrite(STDOUT, "$prefixConsole not ready yet!\n\n");
    }

    case 6 : {
      fwrite(STDOUT, "$prefixConsole current market : $_market\n");
      fwrite(STDOUT, "$prefixConsole enter a new market : ");
      $command1 = fgets(STDIN);
      $command1 = strtolower(trim(preg_replace('/\s+/', '', $command1)));
      if($command1 != "") {
        $command1 = strtoupper($command1);
        fwrite(STDOUT, "$prefixConsole new market : $command1\n");
        $_market  = $command1;
        $market = $exchange->getMarketPair($_market , $_currency);
        $prefixConsole  = "[" . $_exchange . " " . $exchange->getVersion() . " " . $market  . "]";
        getTicker($exchange,$market);
      }
      break;
    }

    case 7 : {
      fwrite(STDOUT, "$prefixConsole current currency : $_currency\n");
      fwrite(STDOUT, "$prefixConsole enter a new currency : ");
      $command1 = fgets(STDIN);
      $command1 = strtolower(trim(preg_replace('/\s+/', '', $command1)));
      if($command1 != "") {
        $command1 = strtoupper($command1);
        fwrite(STDOUT, "$prefixConsole new currency : $command1\n");
        $_currency  = $command1;
        $market = $exchange->getMarketPair($_market , $_currency);
        $prefixConsole  = "[" . $_exchange . " " . $exchange->getVersion() . " " . $market  . "]";
        getTicker($exchange,$market);
      }
      break;
    }

    case 10 : {
      fwrite(STDOUT, "$prefixConsole current exchange : $_exchange\n");
      fwrite(STDOUT, "$prefixConsole Active exchanges\n");
      $counter  = 1;
      $exchanges  = array();
      foreach($config as $key=>$value) {
        if(isSet($exchangesInstances[$key])) {
          fwrite(STDOUT, "[$counter] $key\n");
          $exchanges[$counter . "_exchange"]  = $key;
          $counter++;
        }
      }
      fwrite(STDOUT, "Select new exchange : ");
      $selectOrder  = fgets(STDIN);
      $selectOrder  = strtolower(trim(preg_replace('/\s+/', '', $selectOrder)));

      if($selectOrder != null) {
        $index        = $selectOrder . "_exchange";
        $keyExchange  = isSet($exchanges[$index]) ? $exchanges[$index] : null;
        if($keyExchange != null) {

          fwrite(STDOUT, "New exchange selected : $keyExchange\n\n");

          $exchange   = $exchangesInstances[$keyExchange];
          $_exchange  = $keyExchange;
          $market = $exchange->getMarketPair($_market , $_currency);
          $prefixConsole  = "[" . $_exchange . " " . $exchange->getVersion() . " " . $market  . "]";
          getTicker($exchange,$market);
        }
      }
      break;
    }

    case 20 : {
      $data = array(
        "Ask" => array(),
        "Bid" => array()
      );

      $bidHigh  = 0;
      $bidExhange = "";

      $askTMP = array();

      fwrite(STDOUT, "EXCHANGE\t|BID\t\t|ASK\t\t|\n");
      fwrite(STDOUT, "-------------------------------------------------\n");

      foreach($config as $key=>$value) {
        if(isSet($exchangesInstances[$key])) {

          //fwrite(STDOUT, " \n");
          //fwrite(STDOUT, "Querying $key : ");

          fwrite(STDOUT, "$key");
          $len  = strlen($key);
          if(strlen($key)<=7) {
              fwrite(STDOUT, "\t\t");
          } else {
            fwrite(STDOUT, "\t");
          }
          fwrite(STDOUT, "|");

          $exchange1 = $exchangesInstances[$key];

          $result = $exchange1->getTicker(array("_market" => $_market , "_currency" => $_currency));

          if($result != null && isSet($result["success"]) && $result["success"]==true) {

            $bid  = number_format($result["result"]["Bid"], 8, '.', '');
            //fwrite(STDOUT, "BID : $bid\n");
            fwrite(STDOUT, "$bid\t|");

            if($bid > $bidHigh) {
              $bidHigh  = $bid;
              $bidExhange = $key;
            }

            $ask  = number_format($result["result"]["Ask"], 8, '.', '');
            fwrite(STDOUT, "$ask\t|");

            $askTMP[$key] = $ask;

          } else {
            fwrite(STDOUT, "\t\t|\t\t|");
          }
          fwrite(STDOUT, "\n");
        }
      }
      fwrite(STDOUT, "-------------------------------------------------\n");
      fwrite(STDOUT, "\n");
      fwrite(STDOUT, "Sell\t: $bidHigh on $bidExhange\n");

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
        fwrite(STDOUT, "Buy\t: $askLow on $askExchange\n");

        $profit = number_format($bidHigh - $askLow, 8, '.', '');
        fwrite(STDOUT, "Profit\t: $profit\n");
        fwrite(STDOUT, "\n");
      } else {
        fwrite(STDOUT, "Buy\t: not found\n");
      }
      break;
    } // end 20

    case 100 : {
      cls();
      break;
    }


  }

} while ($command != -1);

fwrite(STDOUT, "-- Game over --\n\n");
die();

function getTicker($exchange,$market) {
  fwrite(STDOUT, "** Fetching ticket information for $market\n");
  $tickerOBJ  = $exchange->getTicker(array("market" => $market));
  if(!empty($tickerOBJ)) {
    if($tickerOBJ["success"]  == true) {
      $last = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
      $bid = number_format($tickerOBJ["result"]["Bid"], 8, '.', '');
      $ask = number_format($tickerOBJ["result"]["Ask"], 8, '.', '');
      fwrite(STDOUT, "Last\t: $last\n");
      fwrite(STDOUT, "Bid\t: $bid\n");
      fwrite(STDOUT, "Ask\t: $ask\n");
    } else {
      $error  = $tickerOBJ["message"];
      fwrite(STDOUT, "[ERROR] [API] $error\n");
    }
    fwrite(STDOUT, "\n");
  }
}

function listMenu() {
  fwrite(STDOUT, "** Menu :\n");
  fwrite(STDOUT, "[-1]\tquit\n");
  fwrite(STDOUT, "[0]\tmenu\n");
  fwrite(STDOUT, "[1]\tget ticker information\n");
  fwrite(STDOUT, "[2]\tsell units\n");
  fwrite(STDOUT, "[3]\tbuy units\n");
  fwrite(STDOUT, "[4]\tget open orders\n");
  fwrite(STDOUT, "[5]\tcancel orders\n");
  fwrite(STDOUT, "[6]\tchange market\n");
  fwrite(STDOUT, "[7]\tchange currency\n");
  fwrite(STDOUT, "[10]\tchange exchange\n");
  fwrite(STDOUT, "[20]\tarbitrage check\n");
  fwrite(STDOUT, "[100]\tclear screen\n");
  fwrite(STDOUT, "\n");
}
?>
