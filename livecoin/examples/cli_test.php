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

    fwrite(STDOUT, "Using market : $_market\n");
    fwrite(STDOUT, "Using currency : $_currency\n");

    /*
      BEGIN testing here
    */
    $result = $exchange->getMarketHistory(
      array(
        "_market" => $_market,
        "_currency" => $_currency
      )
    );
    debug($result);
    fwrite(STDOUT, "\n");
  ?>
