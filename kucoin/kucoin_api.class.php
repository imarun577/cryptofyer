<?php
  /*
  *
  * @package    cryptofyer
  * @class    KucoinApi
  * @author     Fransjo Leihitu
  * @version    0.3
  *
  * API Documentation :
  */
  class KucoinApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://api.kucoin.com/";
    private $apiVersion   = "";

    // base url for currency
    private $currencyUrl  = "https://www.kucoin.com/#/trade.pro/";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "3";

    private $currencyAlias  = array();

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . "/");
        parent::setCurrencyAlias($this->currencyAlias);
    }

    private function send($method = null , $args = array() , $secure = true , $type="GET") {
      if(empty($method)) return array("status" => false , "error" => "method was not defined!");

      if(isSet($args["market"])) unset($args["market"]);

      $fields = null;
      if(!empty($args)) {
        ksort($args);
        $fields     = !empty($args) ? http_build_query($args, '', '&') : "";
      }
      $sign       = strtoupper(hash_hmac('sha256', $fields, $this->apiSecret));

      $args["nonce"] = time();

      $uri  = $this->getBaseUrl() . $method;
      $uri  .= (!empty($fields) && $type == "GET") ? "?" . $fields : "";

      //debug($uri , true);

      $ch   = curl_init($uri);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      if($secure) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'KC-API-KEY: '. $this->apiKey,
          'KC-API-SIGNATURE: '. $sign,
          'KC-API-NONCE: '. $args["nonce"]
        ));
      }

      if($type == "POST") {
        curl_setopt($ch, CURLOPT_POST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      }

      $execResult = curl_exec($ch);

      // check if there's a curl error
      if(curl_error($ch)) return $this->getErrorReturn(curl_error($ch));

      // try to convert json repsonse to assoc array
      if($obj = json_decode($execResult , true)) {
        if($obj !== null) {
          if(isSet($obj["success"])) {
            if($obj["success"] == true) {
              return $this->getReturn(true,"",$obj);
            } else {
              return $this->getReturn(false,$obj["msg"],$obj);
            }
          } else {
            return $this->getReturn(true,"",$obj);
          }
        } else {
          return $this->getErrorReturn("error");
        }

      } else {
          return $this->getErrorReturn($execResult);
      }
      return false;

    }

    public function getMarketPair($market = "" , $currency = "") {
      return strtoupper($currency . "-" . $market);
    }

    // get ticket information
    public function getTicker($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["symbol"] = $args["market"];
      unset($args["market"]);

      $resultOBJ  = $this->send("v1/open/tick" , $args, false);
      if($resultOBJ["success"]) {
        if(isSet($resultOBJ["result"]) && !empty($resultOBJ["result"])) {
          $result             = $resultOBJ["result"]["data"];

          $result["Last"]     = $result["lastDealPrice"];
          $result["Bid"]      = $result["buy"];
          $result["Ask"]      = $result["sell"];
          $result["_raw"]     = $resultOBJ["result"];

          return $this->getReturn($resultOBJ["success"],$resultOBJ["message"],$result);
        }
      }
      return $resultOBJ;
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
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      return $this->currencyUrl . $args["market"];
    }

    // Get market history
    public function getMarketHistory($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    public function getOrderbook($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["symbol"] = $args["market"];
      unset($args["market"]);

      $resultOBJ  = $this->send("v1/open/orders" , $args, false);

      if($resultOBJ["success"]) {
        if(isSet($resultOBJ["result"]) && !empty($resultOBJ["result"])) {
          $raw  = $resultOBJ["result"];
          $resultOBJ["result"]  = array();
          $resultOBJ["result"]["_raw"]  = $raw;

          $resultOBJ["result"]["buy"]   = $raw["data"]["BUY"];
          $resultOBJ["result"]["sell"]  = $raw["data"]["SELL"];

          $resultOBJ["result"]["Bid"]     =  $raw["data"]["BUY"][0][1];
          $resultOBJ["result"]["BidQty"]  =  $raw["data"]["BUY"][0][2];

          $resultOBJ["result"]["Ask"]     = $raw["data"]["SELL"][0][1];
          $resultOBJ["result"]["AskQty"]  = $raw["data"]["SELL"][0][2];
        }
      }
      return $resultOBJ;
    }


  }
?>
