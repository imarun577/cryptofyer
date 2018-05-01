<?php
  /*
  *
  * @package    cryptofyer
  * @class    BinanceApi
  * @author     Fransjo Leihitu
  * @version    0.6
  *
  * API Documentation :
  */
  class BinanceApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://api.binance.com";
    private $apiVersion   = "";

    // base url for currency
    private $currencyUrl  = "https://www.binance.com/trade.html?symbol=";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "6";

    private $currencyAlias  = array(
      "ETHOS" => "BQX"
    );

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . "/");
        parent::setCurrencyAlias($this->currencyAlias);
    }

    private function send($method = null , $args = array() , $secure = true) {
      if(empty($method)) return array("status" => false , "error" => "method was not defined!");

      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) unset($args["_currency"]);
      if(isSet($args["market"]) && !isSet($args["symbol"]))  {
        $args["symbol"] = $args["market"];
        unset($args["market"]);
      }

      $uri  = $this->getBaseUrl() . $method;

      if(!empty($args)) {
        $query = http_build_query($args, '', '&');
        $uri  = $uri . "?" . $query;
      }

      $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      if($secure) {

      }

      $execResult = curl_exec($ch);

      // check if there's a curl error
      if(curl_error($ch)) return $this->getErrorReturn(curl_error($ch));

      // try to convert json repsonse to assoc array
      if($obj = json_decode($execResult , true)) {
        if($obj !== null) {
          if(!isSet($obj["code"])) {
            return $this->getReturn(true,"",$obj);
          } else {
            return $this->getReturn(false,$obj["msg"],$obj);
          }
        } else {
          return $this->getErrorReturn("error");
        }

      } else {
          return $this->getErrorReturn($execResult);
      }
      return false;

      /*
      if($secure) $args["apikey"] = $this->apiKey;
      $args["nonce"] = time();

      $urlParams  = array();
      foreach($args as $key => $val) {
        $urlParams[]  = $key . "=" . $val;
      }

      $uri  = $this->getBaseUrl() . $method;

      $argsString = join("&" , $urlParams);
      if(!empty($urlParams)) {
          $uri  = $uri . "?" . $argsString;
      }

      $sign = $secure == true ? hash_hmac('sha512',$uri,$this->apiSecret) : null;

      $uri = trim(preg_replace('/\s+/', '', $uri));

      $ch = curl_init($uri);
      if($secure) curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $execResult = curl_exec($ch);

      // check if there's a curl error
      if(curl_error($ch)) return $this->getErrorReturn(curl_error($ch));

      // try to convert json repsonse to assoc array
      if($obj = json_decode($execResult , true)) {
        if($obj["success"] == true) {
          return $this->getReturn($obj["success"],$obj["message"],$obj["result"]);
        } else {
          return $this->getErrorReturn($obj["message"]);
        }
      } else {
          return $this->getErrorReturn($execResult);
      }
      */

    }

    public function getMarketPair($market = "" , $currency = "") {
      return strtoupper($this->getCurrencyAlias($currency) . "" . $market);
    }

    public function getOrderbook($args = null) {
      return $this->getOrderbookTicker($args);
    }
    public function getOrderbookTicker($args = null) {
      // /api/v3/ticker/price
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      $resultOBJ  = $this->send("/api/v3/ticker/bookTicker" , $args, false);

      if($resultOBJ["success"]) {
        if(isSet($resultOBJ["result"]) && !empty($resultOBJ["result"])) {
          $result             = $resultOBJ["result"];

          $result["Bid"]      = $result["bidPrice"];
          $result["BidQty"]   = $result["bidQty"];

          $result["Ask"]      = $result["askPrice"];
          $result["AskQty"]      = $result["askQty"];

          $result["_raw"]      = $resultOBJ["result"];

          return $this->getReturn($resultOBJ["success"],$resultOBJ["message"],$result);
        } else {
          return $resultOBJ;
        }
      } else {
        return $resultOBJ;
      }
    }


    // get ticket information
    public function getTicker($args  = null) {
      // /api/v3/ticker/price
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      $resultOBJ  = $this->send("api/v3/ticker/price" , $args, false);

      if($resultOBJ["success"]) {
        if(isSet($resultOBJ["result"]) && !empty($resultOBJ["result"])) {
          $result             = $resultOBJ["result"];

          $orderbook  = $this->getOrderbookTicker($args);

          $result["Last"]     = $result["price"];
          $result["Bid"]      = $orderbook["result"]["Bid"];
          $result["Ask"]      = $orderbook["result"]["Ask"];
          $result["_raw"]      = $resultOBJ["result"];
          $result["_raw"]["orderbook"]  =  $orderbook["result"]["_raw"];

          return $this->getReturn($resultOBJ["success"],$resultOBJ["message"],$result);

        } else {
          return $resultOBJ;
        }
      } else {
        return $resultOBJ;
      }
    }

    // get balance
    public function getBalance($args  = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // place buy order
    public function buy($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // place sell order
    public function sell($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // get open orders
    public function getOrders($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // get order
    public function getOrder($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // Get the exchange currency detail url
    public function getCurrencyUrl($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getCurrencyAlias($args["_currency"]) . "_" . $args["_market"];
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      return $this->currencyUrl . $args["market"];
    }

    // Get market history
    public function getMarketHistory($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }


  }
?>
