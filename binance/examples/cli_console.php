<?php
  /*
    This example file will loop and watch the last rate of a currency.
    You can only run this example from command line!
  */
  if(php_sapi_name() != 'cli') die("you need to run this script from commandline!");

  include("../../includes/cryptoexchange.class.php");

  if(!file_exists("../binance_api.class.php")) die("cannot find ../binance_api.class.php");
  include("../binance_api.class.php");

  if(!file_exists("../config.inc.php")) die("cannot find ../config.inc.php");
  include("../config.inc.php");

  // you don't really this in production
  if(!file_exists("../../includes/tools.inc.php")) die("cannot find ../../includes/tools.inc.php");
  include("../../includes/tools.inc.php");

  $exchangeName = "binance";
  if(!isSet($config) || !isSet($config[$exchangeName])) die("no config for ". $exchangeName ." found!");
  if(!isSet($config[$exchangeName]["apiKey"])) die("please configure the apiKey");
  if(!isSet($config[$exchangeName]["apiSecret"])) die("please configure the apiSecret");

  $exchange  = new BinanceApi($config[$exchangeName]["apiKey"] , $config[$exchangeName]["apiSecret"] );



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
      fwrite(STDOUT, "[$market] << main menu >> ");
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
          $menuString = "<< sell order >>";
          fwrite(STDOUT, "[$market] --> Place sell order\n");
          fwrite(STDOUT, "[$market] $menuString Amount $_currency : ");
          $units  = fgets(STDIN);
          $units  = floatval(trim(preg_replace('/\s+/', '', $units)));
          $units  = number_format($units, 8, '.', '');
          if(!empty($units) && $units > 0) {
            fwrite(STDOUT, "[$market] $menuString Price $_market : ");
            $rate   = fgets(STDIN);
            $rate  = floatval(trim(preg_replace('/\s+/', '', $rate)));
            $rate  = number_format($rate, 8, '.', '');
            if(!empty($rate) && $rate > 0) {
                $totalValue = $units * $rate;
                $totalValue  = number_format($totalValue, 8, '.', '');


                //fwrite(STDOUT, "[$market] $menuString \n");
                fwrite(STDOUT, "[$market] $menuString Total value: $totalValue $_market\n");

                fwrite(STDOUT, "[$market] $menuString Sell $units $_currency at $rate $_market ? [n] > ");
                $proceed  = fgets(STDIN);
                $proceed  = trim(preg_replace('/\s+/', '', $proceed));
                if($proceed == "y" || $proceed == "Y") {
                  if($sellOBJ = $exchange->sell(array("_market" => $_market,"_currency" => $_currency,"amount"=>$units,"price"=>$rate))) {
                    if($sellOBJ["success"] == true) {
                      fwrite(STDOUT, "[$market] $menuString Placed sell order: $units $_currency units at rate $rate $_market ($totalValue $_market)\n");
                      fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                      fwrite(STDOUT, "\n");
                      getTicker($exchange,$market);
                    } else {
                      $error  = $sellOBJ["message"];
                      fwrite(STDOUT, "[$market] $menuString [ERROR] $error\n");
                      fwrite(STDOUT, "[[$market] $menuString Returning to main menu\n");
                      fwrite(STDOUT, "\n");
                      getTicker($exchange,$market);
                    }
                  } else {
                    fwrite(STDOUT, "[$market] $menuString [ERROR]\n");
                    fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                    fwrite(STDOUT, "\n");
                    getTicker($exchange,$market);
                  }
                } else {
                  fwrite(STDOUT, "[$market] $menuString Cancelled sell order\n");
                  fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                  fwrite(STDOUT, "\n");
                  getTicker($exchange,$market);
                }
            } else {
              fwrite(STDOUT, "[$market] $menuString [ERROR] invalid price!\n");
              fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
              fwrite(STDOUT, "\n");
              getTicker($exchange,$market);
            }
          } else {
            fwrite(STDOUT, "[$market] $menuString [ERROR] invalid amount!\n");
            fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
            fwrite(STDOUT, "\n");
            getTicker($exchange,$market);
          }
          break;
        }

        // place buy order
        case "b" : {
          $menuString = "<< buy order >>";
          fwrite(STDOUT, "[$market] --> Place buy order\n");
          fwrite(STDOUT, "[$market] $menuString Amount $_currency : ");
          $units = strtoupper(fgets(STDIN));
          if(!empty($units) && trim($units) != "") {
            $units  = number_format($units, 8, '.', '');
            //fwrite(STDOUT, "$units\n");
            fwrite(STDOUT, "[$market] $menuString Rate : ");
            $rate = strtoupper(fgets(STDIN));
            if(!empty($rate) && trim($rate) != "") {
                $rate  = number_format($rate, 8, '.', '');
                $totalValue = $units * $rate;
                $totalValue  = number_format($totalValue, 8, '.', '');
                fwrite(STDOUT, "[$market] $menuString Total value $totalValue $_market\n");
                if($sellOBJ = $exchange->buy(array("_market" => $_market,"_currency" => $_currency , "amount"=>$units,"price"=>$rate))) {
                  if($sellOBJ["success"] == true) {
                    fwrite(STDOUT, "[$market] $menuString Placed buy order: $units $_currency units at rate $rate $_market ($totalValue $_market)\n");
                    fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                    fwrite(STDOUT, "\n");
                    getTicker($exchange,$market);
                  } else {
                    $error  = $sellOBJ["message"];
                    fwrite(STDOUT, "[$market] $menuString [ERROR] $error\n");
                    fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                    fwrite(STDOUT, "\n");
                    getTicker($exchange,$market);
                  }
                } else {
                  fwrite(STDOUT, "[$market] $menuString [ERROR]\n");
                  fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                  fwrite(STDOUT, "\n");
                  getTicker($exchange,$market);
                }
            } else {
              fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
              fwrite(STDOUT, "\n");
              getTicker($exchange,$market);
            }
          } else {
            fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
            fwrite(STDOUT, "\n");
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
          $menuString = "<< cancel order >>";
          fwrite(STDOUT, "[$market] --> Cancel open order(s)\n");
          $ordersOBJ  = $exchange->getOrders(array("market" => $market));
          if(!empty($ordersOBJ)) {
            if($ordersOBJ["success"]  == true) {

              if(!empty($ordersOBJ["result"])) {

                $counter  = 1;
                fwrite(STDOUT, "[$market] $menuString [-1] cancel all\n");
                fwrite(STDOUT, "[$market] $menuString [0] return to menu \n");

                foreach($ordersOBJ["result"] as $item) {

                  $orderType    = $item["order_type"];
                  $OrderUuid    = $item['order_id'];
                  $Quantity     = $item['amount'];
                  $Quantity =  number_format($Quantity, 8, '.', '');
                  $PricePerUnit = $item['price'];
                  $QuantityRemaining  = $item['amount_remaining'];
                  $QuantityRemaining =  number_format($QuantityRemaining, 8, '.', '');

                  fwrite(STDOUT, "[$market] $menuString [$counter] $orderType $QuantityRemaining/$Quantity $PricePerUnit $OrderUuid \n");
                  $counter++;
                }

                fwrite(STDOUT, "[$market] $menuString Choose order to cancel : ");
                $selectOrder = fgets(STDIN);
                $selectOrder = intval(trim(preg_replace('/\s+/', '', $selectOrder)));
                if(!empty($selectOrder)) {
                  if($selectOrder <= 0) {
                    if($selectOrder == -1) {
                      foreach($ordersOBJ["result"] as $item) {

                        $cancelOrderOBJ = $exchange->cancel(
                          array(
                            "_market" => $_market,
                            "_currency" => $_currency,
                            "order_id" => $item["order_id"]
                          )
                        );
                        if(!empty($cancelOrderOBJ)) {
                          if($cancelOrderOBJ["success"] == true) {

                            $orderType    = $item["order_type"];
                            $OrderUuid    = $item['order_id'];
                            $Quantity     = $item['amount'];
                            $Quantity =  number_format($Quantity, 8, '.', '');
                            $PricePerUnit = $item['price'];
                            $QuantityRemaining  = $item['amount_remaining'];
                            $QuantityRemaining =  number_format($QuantityRemaining, 8, '.', '');

                            fwrite(STDOUT, "[$market] $menuString [ORDER CANCELED] $orderType $QuantityRemaining/$Quantity $PricePerUnit $OrderUuid \n");
                          } else {
                            $error  = $cancelOrderOBJ["message"];
                            fwrite(STDOUT, "[$market] $menuString [ERROR] $error\n");
                            fwrite(STDOUT, "\n");
                          }
                        }
                      }
                      fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                      fwrite(STDOUT, "\n");
                      getTicker($exchange,$market);
                    } else {
                      fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                      fwrite(STDOUT, "\n");
                      getTicker($exchange,$market);
                    }
                  } else {
                    $order  = $ordersOBJ["result"][$selectOrder-1];
                    $cancelOrderOBJ = $exchange->cancel(
                      array(
                        "_market" => $_market,
                        "_currency" => $_currency,
                        "order_id" => $item["order_id"]
                      )
                    );
                    if(!empty($cancelOrderOBJ)) {
                      if($cancelOrderOBJ["success"] == true) {

                        $orderType    = $item["order_type"];
                        $OrderUuid    = $item['order_id'];
                        $Quantity     = $item['amount'];
                        $Quantity =  number_format($Quantity, 8, '.', '');
                        $PricePerUnit = $item['price'];
                        $QuantityRemaining  = $item['amount_remaining'];
                        $QuantityRemaining =  number_format($QuantityRemaining, 8, '.', '');

                        fwrite(STDOUT, "[$market] $menuString [ORDER CANCELED] $orderType $QuantityRemaining/$Quantity $PricePerUnit $OrderUuid \n");
                        fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
                        fwrite(STDOUT, "\n");
                        getTicker($exchange,$market);
                      } else {
                        $error  = $cancelOrderOBJ["message"];
                        fwrite(STDOUT, "[$market] $menuString [ERROR] $error\n");
                        fwrite(STDOUT, "\n");
                      }
                    }
                  }
                } else {
                  fwrite(STDOUT, "\n");
                  getTicker($exchange,$market);
                }
              } else {
                fwrite(STDOUT, "[$market] $menuString You have no open orders, going back to main menu!\n");
                fwrite(STDOUT, "\n");
                getTicker($exchange,$market);
              }

            } else {
              $error  = $tickerOBJ["message"];
              fwrite(STDOUT, "[$market] $menuString [ERROR] [API] $error\n");
              fwrite(STDOUT, "[$market] $menuString Returning to main menu\n");
              fwrite(STDOUT, "\n");
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

        case "$" : {
          $menuString = "<< balance >>";
          fwrite(STDOUT, "[$market] $menuString Fetching balance on $_currency\n");
          $availableCurrency  = 0;
          $result = $exchange->getBalance(
            array(
              "_currency" => $_currency
            )
          );
          if($result["success"] == true) {
            $availableCurrency  = $result["result"]["Available"];
            $availableCurrency  = number_format($availableCurrency, 8, '.', '');
            fwrite(STDOUT, "[$market] $menuString Balance : $availableCurrency $_currency\n");
          } else {
            fwrite(STDOUT, "[$market] $menuString [ERROR] cannot fetch balance\n");
            debug($result);
            fwrite(STDOUT, "\n");
          }

          fwrite(STDOUT, "[$market] Returning to main menu\n");
          fwrite(STDOUT, "\n");
          getTicker($exchange,$market);
          break;
        }

        case "w" : {
          $menuString = "<< withdraw >>";

          $availableCurrency  = 0;
          $result = $exchange->getBalance(
            array(
              "_currency" => $_currency
            )
          );
          if($result["success"] == true) {
            $availableCurrency  = $result["result"]["Available"];
            $availableCurrency  = number_format($availableCurrency, 8, '.', '');
          }

          fwrite(STDOUT, "[$market] --> Withdraw from account\n");
          if($availableCurrency > 0) {
            fwrite(STDOUT, "[$market] $menuString You have $availableCurrency $_currency.\n");
            fwrite(STDOUT, "[$market] $menuString Enter destination wallet address : ");
            $selectAddress = fgets(STDIN);
            $selectAddress = trim(preg_replace('/\s+/', '', $selectAddress));
            if($selectAddress != "") {
              fwrite(STDOUT, "[$market] $menuString Enter ammount ($_currency) : ");
              $selectAmount = fgets(STDIN);
              $selectAmount  = floatval(trim(preg_replace('/\s+/', '', $selectAmount)));
              $selectAmount  = number_format($selectAmount, 8, '.', '');
              if($selectAmount > 0) {
                fwrite(STDOUT, "[$market] $menuString Are you sure to transfer $selectAmount $_currency to $selectAddress ? [n] > ");
                $selectWithdraw = fgets(STDIN);
                $selectWithdraw = trim(preg_replace('/\s+/', '', $selectWithdraw));
                if($selectWithdraw == "y" || $selectWithdraw == "Y") {
                  $result = $exchange->withdraw(
                      array(
                        "_currency" => $_currency,
                        "address" => $selectAddress,
                        "amount"  => $selectAmount
                      )
                  );
                  if($result["success"]) {
                    fwrite(STDOUT, "[$market] $menuString Withdraw $selectAmount $_currency in process.\n");
                    debug($result["result"]);
                    fwrite(STDOUT, "\n");
                  } else {
                    fwrite(STDOUT, "[$market] $menuString [ERROR] Withdraw $selectAmount $_currency FAILED!.\n");
                    debug($result["result"]);
                    fwrite(STDOUT, "\n");
                  }
                  fwrite(STDOUT, "[$market] $menuString Returning to main menu.\n");
                  fwrite(STDOUT, "\n");
                  getTicker($exchange,$market);
                  /*
                  fwrite(STDOUT, "[$market] $menuString Not ready yet! Going back to main menu!\n");
                  fwrite(STDOUT, "\n");
                  getTicker($exchange,$market);
                  */
                } else {
                  fwrite(STDOUT, "[$market] $menuString Cancelled! Going back to main menu!\n");
                  fwrite(STDOUT, "\n");
                  getTicker($exchange,$market);
                }
              } else {
                fwrite(STDOUT, "[$market] $menuString [ERROR] $selectAmount is invalid!\n");
                fwrite(STDOUT, "[$market] $menuString Going back to main menu!\n");
                fwrite(STDOUT, "\n");
                getTicker($exchange,$market);
              }
            } else {
              fwrite(STDOUT, "[$market] $menuString [ERROR] Invalid wallet address\n");
              fwrite(STDOUT, "[$market] $menuString Going back to main menu!\n");
              fwrite(STDOUT, "\n");
              getTicker($exchange,$market);
            }
          } else {
            fwrite(STDOUT, "[$market] $menuString Nothing to withdraw! Current balance : $availableCurrency $_currency.\n");
            fwrite(STDOUT, "[$market] $menuString Going back to main menu!\n");
            fwrite(STDOUT, "\n");
            getTicker($exchange,$market);
          }
          break;
        }
      }

    } while ($command != "q");

    fwrite(STDOUT, "[EXIT] have a nice day\n");
    exit(0);

    function getOrders($exchange,$market) {
      $menuString = "<< open orders >>";
      fwrite(STDOUT, "[$market] --> Fetching open orders for $market\n");
      $ordersOBJ  = $exchange->getOrders(array("market" => $market));
      if(!empty($ordersOBJ)) {
        if($ordersOBJ["success"]  == true) {
          if(!empty($ordersOBJ["result"])) {
            foreach($ordersOBJ["result"] as $item) {

              $orderType    = $item["order_type"];
              $OrderUuid    = $item['order_id'];
              $Quantity     = $item['amount'];
              $Quantity =  number_format($Quantity, 8, '.', '');
              $PricePerUnit = $item['price'];
              $QuantityRemaining  = $item['amount_remaining'];
              $QuantityRemaining =  number_format($QuantityRemaining, 8, '.', '');

              fwrite(STDOUT, "[$market] $menuString * $orderType $QuantityRemaining/$Quantity $PricePerUnit $OrderUuid \n");
            }
          } else {
            fwrite(STDOUT, "[$market] $menuString You have no open orders, going back to main menu!\n");
            fwrite(STDOUT, "\n");
            getTicker($exchange,$market);
          }
        } else {
          $error  = $ordersOBJ["message"];
          fwrite(STDOUT, "[$market] $menuString [ERROR] [API] $error\n");
        }
        fwrite(STDOUT, "\n");
      }
    }

    function getTicker($exchange,$market) {
      fwrite(STDOUT, "[$market] --> Fetching ticket information for $market\n");
      $tickerOBJ  = $exchange->getTicker(array("market" => $market));
      if(!empty($tickerOBJ)) {
        if($tickerOBJ["success"]  == true) {
          $last = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
          $bid = number_format($tickerOBJ["result"]["bid_price"], 8, '.', '');
          $ask = number_format($tickerOBJ["result"]["ask_price"], 8, '.', '');
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
      fwrite(STDOUT, "[$market] --> List of command(s) :\n");
      fwrite(STDOUT, "[q] quit\n");
      fwrite(STDOUT, "[m] menu\n");
      fwrite(STDOUT, "[t] get ticker information\n");
      fwrite(STDOUT, "[$] balance\n");
      fwrite(STDOUT, "[s] sell units\n");
      fwrite(STDOUT, "[b] buy units\n");
      fwrite(STDOUT, "[o] get open orders\n");
      fwrite(STDOUT, "[c] cancel orders\n");
      fwrite(STDOUT, "[w] withdraw\n");
      fwrite(STDOUT, "[cls] clear screen\n");
      fwrite(STDOUT, "\n");
    }

  ?>
