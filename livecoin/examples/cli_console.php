<?php
  /*
    This example file will loop and watch the last rate of a currency.
    You can only run this example from command line!
  */
  if(php_sapi_name() != 'cli') die("you need to run this script from commandline!");

  include("../../includes/cryptoexchange.class.php");

  if(!file_exists("../livecoin_api.class.php")) die("cannot find ../livecoin_api.class.php");
  include("../livecoin_api.class.php");

  if(!file_exists("../config.inc.php")) die("cannot find ../config.inc.php");
  include("../config.inc.php");

  // you don't really this in production
  if(!file_exists("../../includes/tools.inc.php")) die("cannot find ../../includes/tools.inc.php");
  include("../../includes/tools.inc.php");

  $exchangeName = "livecoin";
  if(!isSet($config) || !isSet($config[$exchangeName])) die("no config for ". $exchangeName ." found!");
  if(!isSet($config[$exchangeName]["apiKey"])) die("please configure the apiKey");
  if(!isSet($config[$exchangeName]["apiSecret"])) die("please configure the apiSecret");

  $exchange  = new LivecoinApi($config[$exchangeName]["apiKey"] , $config[$exchangeName]["apiSecret"] );


  cls(); // clear screen

  $_defaultMarket   = "BTC";
  $_defaultCurrency = "ETH";
  $defaultMarket    = $exchange->getMarketPair($_defaultMarket,$_defaultCurrency);
  $market           = "";

  // parse CLI args
  $args = array();
  if($argc>1) {
    parse_str(implode('&',array_slice($argv, 1)), $args);
  }
  $_market    = isSet($args["market"]) ? $args["market"] : "";
  $_currency  = isSet($args["currency"]) ? $args["currency"] : "";

  if(!empty($_market) && !empty($_currency)) {
    $market = $exchange->getMarketPair($_market,$_currency);
  }

  if(empty($market)) {
    fwrite(STDOUT, "Enter market [$_defaultMarket] : ");
    $_market = strtoupper(fgets(STDIN));
    $_market = trim(preg_replace('/\s+/', '', $_market));
    $_market  = empty($_market) ? $_defaultMarket : $_market;

    fwrite(STDOUT, "Enter currency [$_defaultCurrency] : ");
    $_currency = strtoupper(fgets(STDIN));
    $_currency = trim(preg_replace('/\s+/', '', $_currency));
    $_currency  = empty($_currency) ? $_defaultCurrency : $_currency;

    $market = $exchange->getMarketPair($_market,$_currency);
  }

  $market = trim(preg_replace('/\s+/', '', $market));
  $market = empty($market) ? $defaultMarket : $market;
  $market = trim(preg_replace('/\s+/', '', $market));
  $market = strtoupper($market);

  fwrite(STDOUT, "Ready commands for : $market\n");
  $command  = "";

  getTicker($exchange,$market);

  do {
    fwrite(STDOUT, "[$market] > ");
    $command = fgets(STDIN);
    $command = strtolower(trim(preg_replace('/\s+/', '', $command)));

    switch($command) {

      default : {
        fwrite(STDOUT, "[$market] [ERROR] I don't know that command!\n\n");
      }

      // list menu
      case "m" : {
        listMenu($market);
        break;
      }

      // quit
      case "x" : {
          $command  = "q";
      }
      case "q" : {
        break;
      }

      // place sell order
      case "s" : {
        fwrite(STDOUT, "[$market] Place sell order\n");
        fwrite(STDOUT, "amount : ");
        $units = strtoupper(fgets(STDIN));
        if(!empty($units) && trim($units) != "") {
          $units  = number_format($units, 8, '.', '');
          fwrite(STDOUT, "Rate : ");
          $rate = strtoupper(fgets(STDIN));
          if(!empty($rate) && trim($rate) != "") {
              $rate  = number_format($rate, 8, '.', '');
              $totalValue = $units * $rate;
              $totalValue  = number_format($totalValue, 8, '.', '');
              fwrite(STDOUT, "Total value: $totalValue\n");
              if($sellOBJ = $exchange->sell(array("market" => $market,"amount"=>$units,"rate"=>$rate))) {
                if($sellOBJ["success"] == true) {
                  fwrite(STDOUT, "[$market] Placed sell order: $units units at rate $rate ($totalValue)\n");
                  fwrite(STDOUT, "[$market] Returning to main menu\n");
                  getTicker($exchange,$market);
                } else {
                  $error  = $sellOBJ["message"];
                  fwrite(STDOUT, "[$market] [ERROR] $error\n");
                  fwrite(STDOUT, "[$market] Returning to main menu\n");
                  getTicker($exchange,$market);
                }
              } else {
                fwrite(STDOUT, "[$market] [ERROR]\n");
                fwrite(STDOUT, "[$market] Returning to main menu\n");
                getTicker($exchange,$market);
              }
          } else {
            fwrite(STDOUT, "[$market] Returning to main menu\n");
            getTicker($exchange,$market);
          }
        } else {
          fwrite(STDOUT, "[$market] Returning to main menu\n");
          getTicker($exchange,$market);
        }
        break;
      }

      // place buy order
      case "b" : {
        fwrite(STDOUT, "[$market] Place buy order\n");
        fwrite(STDOUT, "amount : ");
        $units = strtoupper(fgets(STDIN));
        if(!empty($units) && trim($units) != "") {
          $units  = number_format($units, 8, '.', '');
          fwrite(STDOUT, "$units\n");
          fwrite(STDOUT, "Rate : ");
          $rate = strtoupper(fgets(STDIN));
          if(!empty($rate) && trim($rate) != "") {
              $rate  = number_format($rate, 8, '.', '');
              $totalValue = $units * $rate;
              $totalValue  = number_format($totalValue, 8, '.', '');
              fwrite(STDOUT, "Total value $totalValue\n");
              if($sellOBJ = $exchange->buy(array("market" => $market,"amount"=>$units,"rate"=>$rate))) {
                if($sellOBJ["success"] == true) {
                  fwrite(STDOUT, "[$market] Placed buy order: $units units at rate $rate ($totalValue)\n");
                  fwrite(STDOUT, "[$market] Returning to main menu\n");
                  getTicker($exchange,$market);
                } else {
                  $error  = $sellOBJ["message"];
                  fwrite(STDOUT, "[$market] [ERROR] $error\n");
                  fwrite(STDOUT, "[$market] Returning to main menu\n");
                  getTicker($exchange,$market);
                }
              } else {
                fwrite(STDOUT, "[$market] [ERROR]\n");
                fwrite(STDOUT, "[$market] Returning to main menu\n");
                getTicker($exchange,$market);
              }
          } else {
            fwrite(STDOUT, "[$market] Returning to main menu\n");
            getTicker($exchange,$market);
          }
        } else {
          fwrite(STDOUT, "[$market] Returning to main menu\n");
          getTicker($exchange,$market);
        }
        break;
      }

      // get ticker information
      case "t" : {
        getTicker($exchange,$market);
        break;
      }

      case "c" : {
        fwrite(STDOUT, "[$market] Cancel order\n");
        $ordersOBJ  = $exchange->getOrders(array("market" => $market));
        if(!empty($ordersOBJ)) {
          if($ordersOBJ["success"]  == true) {
            $counter  = 1;
            fwrite(STDOUT, "[-1] cancel all\n");
            fwrite(STDOUT, "[0] return to menu \n");

            if(!empty($ordersOBJ["result"])) {
              foreach($ordersOBJ["result"] as $item) {

                $orderid      = $item["orderid"];
                $orderType    = $item["orderStatus"];
                $OrderUuid    = $item['orderid'];
                $Quantity     = $item['quantity'];
                $PricePerUnit = $item['price'];
                $QuantityRemaining  = $item['remainingQuantity'];

                fwrite(STDOUT, "[$counter] $orderType $QuantityRemaining/$Quantity $PricePerUnit $OrderUuid \n");
                $counter++;
              }

              fwrite(STDOUT, "Choose order to cancel : ");
              $selectOrder = strtoupper(fgets(STDIN));
              if(!empty($selectOrder)) {
                if($selectOrder <= 0) {
                  if($selectOrder == -1) {
                    foreach($ordersOBJ["result"] as $item) {
                      $cancelOrderOBJ = $exchange->cancel(array("market" => $market , "orderid" => $item["orderid"]));
                      if(!empty($cancelOrderOBJ)) {
                        if($cancelOrderOBJ["success"] == true) {
                          $orderid      = $item["orderid"];
                          $orderType    = $item["orderStatus"];
                          $OrderUuid    = $item['orderid'];
                          $Quantity     = $item['quantity'];
                          $PricePerUnit = $item['price'];
                          $QuantityRemaining  = $item['remainingQuantity'];
                          fwrite(STDOUT, "[ORDER CANCELED] $orderType $QuantityRemaining/$Quantity $PricePerUnit $OrderUuid \n");
                        } else {
                          $error  = $cancelOrderOBJ["message"];
                          fwrite(STDOUT, "[$market] [ERROR] $error\n");
                        }
                      }
                    }
                    fwrite(STDOUT, "[$market] Returning to main menu\n");
                    getTicker($exchange,$market);
                  } else {
                    fwrite(STDOUT, "[$market] Returning to main menu\n");
                    getTicker($exchange,$market);
                  }
                } else {
                  $order  = $ordersOBJ["result"][$selectOrder-1];
                  $cancelOrderOBJ = $exchange->cancel(array("market" => $market , "orderid" => $order["orderid"]));
                  if(!empty($cancelOrderOBJ)) {
                    if($cancelOrderOBJ["success"] == true) {
                      $item         = $order;
                      $orderid      = $item["orderid"];
                      $orderType    = $item["orderStatus"];
                      $OrderUuid    = $item['orderid'];
                      $Quantity     = $item['quantity'];
                      $PricePerUnit = $item['price'];
                      $QuantityRemaining  = $item['remainingQuantity'];
                      fwrite(STDOUT, "[ORDER CANCELED] $orderType $QuantityRemaining/$Quantity $PricePerUnit $OrderUuid \n");
                      fwrite(STDOUT, "[$market] Returning to main menu\n");
                      getTicker($exchange,$market);
                    } else {
                      $error  = $cancelOrderOBJ["message"];
                      fwrite(STDOUT, "[$market] [ERROR] $error\n");
                    }
                  }
                }
              } else {
                getTicker($exchange,$market);
              }
            } else {
              fwrite(STDOUT, "[$market] You have no open orders, going back to main menu!\n");
              getTicker($exchange,$market);
            }

            //---

          } else {
            $error  = $tickerOBJ["message"];
            fwrite(STDOUT, "[ERROR] [API] $error\n");
          }
        }
        break;
      }

      case "o" : {
        getOrders($exchange,$market);
        break;
      }

      case "cls" : { }
      case "clear" : {
        cls();
        getTicker($exchange,$market);
        break;
      }
    }

  } while ($command != "q");

  fwrite(STDOUT, "[EXIT] have a nice day\n");
  exit(0);

  function getOrders($exchange,$market) {
    fwrite(STDOUT, "[$market] Fetching open orders for $market\n");
    $ordersOBJ  = $exchange->getOrders(array("market" => $market));
    if(!empty($ordersOBJ)) {
      if($ordersOBJ["success"]  == true) {
        if(!empty($ordersOBJ["result"])) {
          foreach($ordersOBJ["result"] as $item) {

            $orderid    = $item["orderid"];
            $orderType    = $item["orderStatus"];
            $OrderUuid    = $item['orderid'];
            $Quantity     = $item['quantity'];
            $PricePerUnit = $item['price'];
            $QuantityRemaining  = $item['remainingQuantity'];

            fwrite(STDOUT, "* $orderType $QuantityRemaining/$Quantity $PricePerUnit $OrderUuid \n");
            //fwrite(STDOUT, "* $orderid \n");
          }
        } else {
          fwrite(STDOUT, "[$market] You have no open orders, going back to main menu!\n");
          getTicker($exchange,$market);
        }
      } else {
        $error  = $ordersOBJ["message"];
        fwrite(STDOUT, "[ERROR] [API] $error\n");
      }
      fwrite(STDOUT, "\n");
    }
  }

  function getTicker($exchange,$market) {
    fwrite(STDOUT, "[$market] Fetching ticket information for $market\n");
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

  function listMenu($market) {
    fwrite(STDOUT, "[$market] List of command(s) :\n");
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
