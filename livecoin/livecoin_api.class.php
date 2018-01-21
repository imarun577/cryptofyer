<?php
  /*
  *
  * @package    cryptofyer
  * @class    LiveCoinApi
  * @author     Fransjo Leihitu
  * @version    0.7
  *
  * API Documentation :
  */
  class LiveCoinApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://api.livecoin.net/";
    private $apiVersion   = "1.0";

    // base url for currency
    private $currencyUrl  = "https://www.livecoin.net/en/trade/index?currencyPair=";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "7";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl);
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

      $ch   = curl_init($uri);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      if($secure) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'API-key: '.$this->apiKey,
          'Sign: '.$sign
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
              return $this->getReturn(false,"",$obj);
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

    public function getMyTrades($args = null) {
      // /exchange/trades
      return $this->getErrorReturn("not implemented yet!");
    }

    public function getMarketPair($market = "" , $currency = "") {
      return strtoupper($currency . "/" . $market);
    }


    // Returns public data for currencies:
    public function getRestrictions($args = null) {

      $method     = "exchange/restrictions";
      $resultOBJ  = $this->send( $method, $args, false);

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // Returns public data for currencies:
    public function getCoinInfo($args = null) {

      $method     = "info/coinInfo";
      $resultOBJ  = $this->send( $method, $args, false);

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // get ticket information
    public function getMaxbidMinask($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      $method = "exchange/maxbid_minask";
      $args["currencyPair"] = $args["market"];

      $resultOBJ  = $this->send( $method, $args, false);

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // get ticket information
    public function getTicker($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      $method = "exchange/ticker";
      $args["currencyPair"] = $args["market"];

      $resultOBJ  = $this->send( $method, $args, false);

      if($resultOBJ["success"]) {

        if(isSet($resultOBJ["result"]) && !empty($resultOBJ["result"])) {
          $result             = $resultOBJ["result"];

          $result["Last"]     = $result["last"];
          $result["Bid"]      = $result["best_bid"];
          $result["Ask"]      = $result["best_ask"];
          $result["_raw"]     = $resultOBJ["result"];

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
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      $method = "payment/balance";
      return $this->send($method , $args);
    }

    // get balance
    public function getBalances($args  = null) {
      //if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      $method = "payment/balances";
      return $this->send($method , $args);
    }

    public function cancel($args = null) {
      if(!isSet($args["orderid"])) return $this->getErrorReturn("required parameter: orderid");
      $args["orderId"]  = $args["orderid"];
      unset($args["orderid"]);

      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      $method = "/exchange/cancellimit";
      $args["currencyPair"] = $args["market"];

      $resultOBJ  = $this->send( $method, $args , true , "POST");

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // place buy order
    public function buy($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");
      $args["price"] = $args["rate"];
      unset($args["rate"]);

      $method = "/exchange/buylimit";
      $args["currencyPair"] = $args["market"];

      $resultOBJ  = $this->send( $method, $args , true , "POST");

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // place sell order
    public function sell($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");
      $args["price"] = $args["rate"];
      unset($args["rate"]);

      $method = "/exchange/selllimit";
      $args["currencyPair"] = $args["market"];

      $resultOBJ  = $this->send( $method, $args , true , "POST");

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // get open orders
    public function getOrders($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      $method = "exchange/client_orders";
      $args["currencyPair"] = $args["market"];

      $resultOBJ  = $this->send( $method, $args);

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // get order
    public function getOrder($args = null) {
      // /exchange/order
      return $this->getErrorReturn("not implemented yet!");
    }

    // Get the exchange currency detail url
    public function getCurrencyUrl($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

       $args["market"]  = str_replace("/" , "%2F" , $args["market"]);
      return $this->currencyUrl . $args["market"];
    }


    // Returns orderbook for every currency pair.
    public function getAllOrderbook($args = null) {

      $method = "exchange/all/order_book";

      if(!isSet($args["groupByPrice"])) $args["groupByPrice"]  = true;
      if(!isSet($args["depth"])) $args["depth"]  = -10;

      $resultOBJ  = $this->send( $method, $args, false);

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // Returns orderbook
    public function getOrderbook($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      if(!isSet($args["groupByPrice"])) $args["groupByPrice"]  = true;
      if(!isSet($args["depth"])) $args["depth"]  = -10;

      $method = "exchange/order_book";
      $args["currencyPair"] = $args["market"];

      $resultOBJ  = $this->send( $method, $args, false);

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }

    // Get market history
    public function getMarketHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      $method = "exchange/last_trades";
      $args["currencyPair"] = $args["market"];

      if(!isSet($args["minutesOrHour"])) $args["minutesOrHour"] = false;

      $resultOBJ  = $this->send( $method, $args, false);

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $result;
      } else {
        return $resultOBJ;
      }
    }


  }
?>
