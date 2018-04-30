<?php
  include("../includes/tools.inc.php");
  include("../includes/cryptoexchange.class.php");
  include("coinexchange_api.class.php");

  include("config.inc.php");

  $exchangeName = "coinexchange";
  if(!isSet($config) || !isSet($config[$exchangeName])) die("no config for ". $exchangeName ." found!");
  if(!isSet($config[$exchangeName]["apiKey"])) die("please configure the apiKey");
  if(!isSet($config[$exchangeName]["apiSecret"])) die("please configure the apiSecret");

  $exchange  = new CoinexchangeApi($config[$exchangeName]["apiKey"] , $config[$exchangeName]["apiSecret"] );

  $_market    = "BTC";
  $_currency  = "ETH";
  $market     = $exchange->getMarketPair($_market , $_currency);


  echo "<h1>Version</h1>";
  $result = $exchange->getVersion();
  debug($result);


  $result = $exchange->getTicker(array("_market" => $_market , "_currency" => $_currency));
  debug($result);
?>
