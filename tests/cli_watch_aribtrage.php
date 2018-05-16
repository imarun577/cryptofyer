<?php
  if(php_sapi_name() != 'cli') die("you need to run this script from commandline!");

  include("includes.php");
  cls(); // clear screen

  $defaultProfit  = 0.00001800;
  $defaultSleep   = 30;

  // parse CLI args
  $args = array();
  if($argc>1) {
    parse_str(implode('&',array_slice($argv, 1)), $args);
  }

  $_market    = isSet($args["market"]) ? strtolower($args["market"]) : "";
  $_currency  = isSet($args["currency"]) ? strtolower($args["currency"]) : "";
  $_sleep     = isSet($args["interval"]) ? $args["interval"] : -1;
  $_alertProfit = isSet($args["alert"]) ? $args["alert"] : -1;

  if(empty($_market)) {
    $_market  = "BTC";
    fwrite(STDOUT, "Enter market: [$_market] > ");
    $marketSelection = fgets(STDIN);
    $marketSelection  = trim(preg_replace('/\s+/', '', $marketSelection));
    $_market  = !empty($marketSelection) ? $marketSelection : $_market;
  }
  $_market  = strtoupper($_market);
  fwrite(STDOUT, "Using market\t: $_market\n");


  if(empty($_currency)) {
    $_currency  = "ETH";
    fwrite(STDOUT, "Enter currency: [$_currency] > ");
    $currencySelection = fgets(STDIN);
    $currencySelection  = trim(preg_replace('/\s+/', '', $currencySelection));
    $_currency  = !empty($currencySelection) ? $currencySelection : $_currency;
  }
  $_currency  = strtoupper($_currency);
  fwrite(STDOUT, "Using currency\t: $_currency\n");


  if($_sleep < 0) {
    $_sleep  = $defaultSleep;
    fwrite(STDOUT, "Enter interval seconds: [$_sleep] > ");
    $intervalSelection = fgets(STDIN);
    $intervalSelection  = intval(trim(preg_replace('/\s+/', '', $intervalSelection)));
    if($intervalSelection) {
      $_sleep = $intervalSelection;
    }
  }

  if($_alertProfit < 0) {
    $_alertProfit  = $defaultProfit;
    $alertProfitFormatted =  number_format($_alertProfit, 8, '.', '');
    fwrite(STDOUT, "Enter alert profit: [$alertProfitFormatted] > ");
    $alertSelection = fgets(STDIN);
    $alertSelection  = floatval(trim(preg_replace('/\s+/', '', $alertSelection)));
    if($alertSelection) {
      $_alertProfit = $alertSelection;
    }
  }

  $alertProfit  = $_alertProfit;
  $alertProfitFormatted =  number_format($alertProfit, 8, '.', '');

  fwrite(STDOUT, "\n");
  fwrite(STDOUT, "Market : $_market\n");
  fwrite(STDOUT, "Currency : $_currency\n");
  fwrite(STDOUT, "Interval : $_sleep seconds\n");
  fwrite(STDOUT, "Alert above : $alertProfitFormatted \n");

  $data = array(
    "Ask" => array(),
    "Bid" => array()
  );

  fwrite(STDOUT, "-----------------------------------------\n");

  $counter  = 0;
  while(true) {

    $bidHigh  = 0;
    $bidExhange = "";

    $askTMP = array();

    foreach($config as $key=>$value) {
      if(isSet($exchangesInstances[$key])) {
        $key  = trim($key);

        $exchange = $exchangesInstances[$key];

        $result = $exchange->getTicker(array("_market" => $_market , "_currency" => $_currency));

        if($result != null && isSet($result["success"]) && $result["success"]==true) {
          $bid  = number_format($result["result"]["bid_price"], 8, '.', '');

          if($bid > $bidHigh) {
            $bidHigh  = $bid;
            $bidExhange = $key;
          }

          $ask  = number_format($result["result"]["ask_price"], 8, '.', '');
          $askTMP[$key] = $ask;

        }
      }
    }


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

      $profit = number_format($bidHigh - $askLow, 8, '.', '');

      $qtyToSell= -1;
      if($resultOrderBookSell = $exchangesInstances[$bidExhange]->getOrderbook(
        array("_market" => $_market , "_currency" => $_currency)
      )) {
        if($resultOrderBookSell["result"]["bid_amount"]) {
          $qtyToSell  = $resultOrderBookSell["result"]["bid_amount"];
        }
      }

      $qtyToBuy = -1;
      if($resultOrderBookBuy = $exchangesInstances[$askExchange]->getOrderbook(
        array("_market" => $_market , "_currency" => $_currency)
      )) {
        if($resultOrderBookBuy["result"]["ask_amount"]) {
          $qtyToBuy  = $resultOrderBookBuy["result"]["ask_amount"];
        }
      }


      fwrite(STDOUT,"[$counter] High on $bidExhange ($bidHigh , qty : $qtyToSell) , Low on $askExchange ($askLow , qty : $qtyToBuy) , Profit : $profit\n");

      if($profit > $alertProfit) {
        fwrite(STDOUT,"** Arbitrage now : $bidExhange ($bidHigh) -> $askExchange ($askLow)\n");
      }
    }

    for($i = 0 ; $i < $_sleep ; $i++) {
        fwrite(STDOUT,".");
        sleep(1);
    }
    fwrite(STDOUT,"\n");

    $counter++;
  }

?>
