<?php
  /*
  *
  * @package    cryptofyer
  * @class    BittrexApi
  * @author     Fransjo Leihitu
  * @version    0.19
  *
  * API Documentation : https://bittrex.com/home/api
  */
  class BittrexApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://bittrex.com/api/";
    private $apiVersion   = "1.1";

    // base url for currency
    private $currencyUrl  = "https://www.bittrex.com/Market/Index?MarketName=";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "19";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . "v" . $this->apiVersion . "/");
    }

    private function send($method = null , $args = array() , $secure = true) {
      if(empty($method)) return $this->getErrorReturn("method was not defined!");

      if(isSet($_market)) unset($_market);
      if(isSet($_currency)) unset($_currency);

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

    }

    /* ------ BEGIN public api methodes ------ */
    public function getMarkets($args = null) {
      return $this->send("public/getmarkets" , $args , false);
    }

    public function getCurrencies($args = null){
      return $this->send("public/getcurrencies" , $args , false);
    }

    public function getCurrencyUrl($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      return $this->currencyUrl . $args["market"];
    }

    public function getTicker($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $resultOBJ  = $this->send("public/getmarketsummary" , $args, false);
      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        $result[0]["bid_price"] = $result[0]["Bid"];
        $result[0]["ask_price"] = $result[0]["Ask"];
        $result[0]["price"] = $result[0]["Last"];
        $resultOBJ["result"]  = $result[0];
      }
      return $resultOBJ;
    }

    public function getMarketSummary($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $resultOBJ  = $this->send("public/getmarketsummary" , $args , false);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"][0];
        $result["bid_price"] = $result["Bid"];
        $result["ask_price"] = $result["Ask"];
        $result["price"]      = $result["Last"];
        $resultOBJ["result"] = $result;
      }
      return $resultOBJ;
    }

    public function getOrderbook($args = null) {
      /*
        optional : depth
      */
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      if(!isSet($args["type"])) $args["type"] = "both";

      $resultOBJ  = $this->send("public/getorderbook" , $args , false);
      if($resultOBJ["success"] == true) {

        $raw  = $resultOBJ["result"];

        $resultOBJ["result"]  = array();
        $resultOBJ["result"]["_raw"]  = $raw;

        $resultOBJ["result"]["buy"] = $raw["buy"];
        $resultOBJ["result"]["sell"] =$raw["sell"];

        $resultOBJ["result"]["bid_price"] =  $raw["buy"][0]["Rate"];
        $resultOBJ["result"]["bid_amount"] =  $raw["buy"][0]["Quantity"];

        $resultOBJ["result"]["ask_price"] =  $raw["sell"][0]["Rate"];
        $resultOBJ["result"]["ask_amount"] = $raw["sell"][0]["Quantity"];
      }
      return $resultOBJ;
    }

    public function getMarketHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $resultOBJ  = $this->send("public/getmarkethistory" , $args , false);
      if($resultOBJ["success"] == true) {
        $items  = $resultOBJ["result"];
        $return = array();
        foreach($items as $item) {
          $item["price"]    = $item["Price"];
          $item["amount"]   = $item["Quantity"];
          $return[] = $item;
        }
        $resultOBJ["result"]  = $return;
      }
      return $resultOBJ;
    }

    public function getMarketSummaries() {
      return $this->send("public/getmarketsummaries" , $args , false);
    }
    /* ------END public api methodes ------ */


    /* ------ BEGIN market api methodes ------ */
    public function order($args = null , $side = "") {
      if(empty($side)) return $this->getErrorReturn("required parameter: side");

      $method = "";
      if($side == "buy") $method  = "market/buylimit";
      if($side == "sell") $method  = "market/selllimit";
      if(empty($method)) return $this->getErrorReturn("invalid side");

      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if(!isSet($args["price"])) return $this->getErrorReturn("required parameter: price");
      $args["rate"] = $args["price"];
      unset($args["price"]);

      $resultOBJ = $this->send($method , $args);

      if($resultOBJ["success"] == true) {
        $result  = $resultOBJ["result"];
        if(isSet($result["uuid"])) {
          $result["order_id"]  = $result["orderid"] = $result["uuid"];
        }
        $resultOBJ["result"] = $result;
      }
      return $resultOBJ;
    }


    public function buy($args = null) {
      return $this->order($args , "buy");
    }

    public function sell($args = null) {
      return $this->order($args , "sell");
    }

    public function cancel($args = null) {
      if(isSet($args["orderid"])) {
        $args["order_id"]  = $args["orderid"];
      }
      if(!isSet($args["order_id"])) return $this->getErrorReturn("required parameter: order_id");
      $args["uuid"] = $args["order_id"];
      unset($args["order_id"]);
      return $this->send("market/cancel" , $args);
    }

    public function getOrders($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      $result = $this->send("market/getopenorders" , $args);
      if($result["success"] == true) {
        $items  = $result["result"];
        $newItems = array();
        foreach($items as $item) {
          $item['order_id']  = $item['orderid']  = $item['OrderUuid'];

          $item["order_type"]       = $item["OrderType"];
          $item['amount']           = $item["Quantity"];
          $item['amount_remaining'] = $item["QuantityRemaining"];

          $newItems[] = $item;
        }
        $result["result"] = $newItems;
        return $result;
      } else {
        $this->getErrorReturn("API error");
      }
    }
    /* ------ END market api methodes ------ */


    /* ------ BEGIN account api methodes ------ */


    public function getBalances($args = null) {
      return $this->send("account/getbalances" , $args);
    }

    public function getBalance($args = null) {

      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) {
        $args["currency"] = trim($args["_currency"]);
        if($args["currency"] == "") unset($args["currency"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");

      $returnOBJ  = $this->send("account/getbalance" , $args);
      if($returnOBJ["success"] == true) {
        $result = $returnOBJ["result"];
        $returnOBJ["result"]  = $result;
      }
      return $returnOBJ;
    }

    public function getDepositAddress($args = null) {

      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) {
        $args["currency"] = trim($args["_currency"]);
        if($args["currency"] == "") unset($args["currency"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");

      $returnOBJ  = $this->send("account/getdepositaddress" , $args);
      if($returnOBJ["success"] == true) {
        $result = $returnOBJ["result"];
        $result["address"] = $result["Address"];
        $returnOBJ["result"]  = $result;
      }
      return $returnOBJ;
    }


    public function withdraw($args = null) {

      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) {
        $args["currency"] = trim($args["_currency"]);
        if($args["currency"] == "") unset($args["currency"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if(!isSet($args["address"])) return $this->getErrorReturn("required parameter: address");

      return $this->send("account/withdraw" , $args);
    }

    public function getOrder($args = null) {

      if(isSet($args["orderid"])) {
        $args["order_id"]  = $args["orderid"];
        unset($args["orderid"]);
      }

      if(!isSet($args["order_id"])) return $this->getErrorReturn("required parameter: order_id");
      $args["uuid"] = $args["order_id"];
      unset($args["order_id"]);

      $resultOBJ  = $this->send("account/getorder" , $args);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];
        $result["order_id"]  = $result["orderid"]  = $result["OrderUuid"];
        $resultOBJ["result"]  = $result;
      }
      return $resultOBJ;
    }

    public function getOrderHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      $resultOBJ  =  $this->send("account/getorderhistory" , $args);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];

        $items  = array();
        foreach($result as $item) {
          $item["order_id"] = $item["OrderUuid"];
          $item["price"]    = $item["Price"];
          $item["amount"]   = $item["Quantity"];
          $items[]  = $item;
        }

        $resultOBJ["result"]  = $items;
      }
      return $resultOBJ;
    }

    public function getWithdrawalHistory($args = null) {

      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) {
        $args["currency"] = trim($args["_currency"]);
        if($args["currency"] == "") unset($args["currency"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");

      $resultOBJ  =  $this->send("account/getwithdrawalhistory" , $args);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];

        $items  = array();
        foreach($result as $item) {
          $item["amount"]   = $item["Amount"];
          $items[]  = $item;
        }

        $resultOBJ["result"]  = $items;
      }
      return $resultOBJ;
    }

    public function getDepositHistory($args = null) {
      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) {
        $args["currency"] = trim($args["_currency"]);
        if($args["currency"] == "") unset($args["currency"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      $resultOBJ  = $this->send("account/getdeposithistory" , $args);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];

        $items  = array();
        foreach($result as $item) {
          $item["amount"]   = $item["Amount"];
          $items[]  = $item;
        }

        $resultOBJ["result"]  = $items;
      }
      return $resultOBJ;
    }

    /* ------ END account api methodes ------ */

  }
?>
