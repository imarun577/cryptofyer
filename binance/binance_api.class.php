<?php
  /*
  *
  * @package    cryptofyer
  * @class    BinanceApi
  * @author     Fransjo Leihitu
  * @version    0.14
  *
  * API Documentation :
  */
  class BinanceApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://api.binance.com";
    private $wapiUrl = "https://api.binance.com/wapi/";
    private $apiVersion   = "";

    // base url for currency
    private $currencyUrl  = "https://www.binance.com/trade.html?symbol=";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "14";

    private $info = [];

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

    private function send($method = null , $args = array() , $secure = true , $transfer = "GET") {
      if(empty($method)) return array("status" => false , "error" => "method was not defined!");

      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) unset($args["_currency"]);
      if(isSet($args["market"]) && !isSet($args["symbol"]))  {
        $args["symbol"] = $args["market"];
        unset($args["market"]);
      }

      $uri  = $this->getBaseUrl() . $method;
      if($secure) {

        $ts = (microtime(true) * 1000) + $this->info['timeOffset'];
        $args['timestamp'] = number_format($ts, 0, '.', '');

        $query = http_build_query($args, '', '&');
        $uri  = $uri . "?" . $query;

        $signature = hash_hmac('sha256', $query, $this->apiSecret);
        $endpoint = $this->getBaseUrl() . $method . '?' . $query . '&signature=' . $signature;
        $uri  = $endpoint;

        $ch = curl_init($uri);

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-MBX-APIKEY: ' . $this->apiKey,
        ));

      } else {

        if(!empty($args)) {
          $query = http_build_query($args, '', '&');
          $uri  = $uri . "?" . $query;
        }
        $ch = curl_init($uri);
      }

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      if($transfer == "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
      }

      if ($transfer == "DELETE") {
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $transfer);
      }

      //debug($uri);
      curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)");
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);

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
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      if(isSet($args["depth"])) unset($args["depth"]);

      $resultOBJ  = $this->send("/api/v3/ticker/bookTicker" , $args, false);

      if($resultOBJ["success"]) {
        if(isSet($resultOBJ["result"]) && !empty($resultOBJ["result"])) {
          $result             = $resultOBJ["result"];

          $result["bid_price"]    = $result["bidPrice"];
          $result["bid_amount"]   = $result["bidQty"];

          $result["ask_price"]    = $result["askPrice"];
          $result["ask_amount"]   = $result["askQty"];

          $result["_raw"]         = $resultOBJ["result"];

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

          $result["Last"]           = $result["price"];
          $result["bid_price"]      = $orderbook["result"]["bid_price"];
          $result["ask_price"]      = $orderbook["result"]["ask_price"];
          $result["_raw"]           = $resultOBJ["result"];
          $result["_raw"]["orderbook"]  =  $orderbook["result"]["_raw"];

          return $this->getReturn($resultOBJ["success"],$resultOBJ["message"],$result);

        } else {
          return $resultOBJ;
        }
      } else {
        return $resultOBJ;
      }
    }

    // get all balances
    public function getBalances($args  = null) {
      $ts = $this->time();

      $method = "api/v3/account";

      $result = $this->send($method , []);
      return $result;
    }

    // get balance for 1 currency
    // NOTE : this function first uses $this->balances() to get all $balances
    // I haven't been able to get a single balance request yet ;)
    public function getBalance($args  = null) {

      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) {
        $args["currency"] = $args["_currency"];
        unset($args["_currency"]);
      }
      if(!isSet($args["currency"])) {
        return $this->getErrorReturn("required parameter: currency");
      }
      $args["currency"] = $this->getCurrencyAlias($args["currency"]);

      $result = $this->getBalances();

      if(isSet($result["success"])) {
        if(isSet($result["result"])) {
          if(isSet($result["result"]["balances"])) {
            $balances = $result["result"]["balances"];

            foreach($balances as $asset) {
              if($asset["asset"] == $args["currency"]) {

                $asset["_raw"]  = $asset;

                $asset["Balance"] =  $asset["free"];
                $asset["Available"] = $asset["free"];

                return $this->getReturn($result["success"],$result["message"],$asset);
              }
            }

            return $this->getErrorReturn("Cannot find " . $args["currency"]);
          }
        }
      }
      return $this->getErrorReturn("Error fetching balance " . $args["currency"] . " from server");
    }

    public function order($args = null , $side = "") {

      if(empty($side)) return $this->getErrorReturn("required parameter: side");

      $this->time();

      if(!isSet($args["symbol"])) {
        if(isSet($args["_market"]) && isSet($args["_currency"])) {
          $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
          unset($args["_market"]);
          unset($args["_currency"]);
          $args["symbol"] = $args["market"];
        }
        if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      }
      if(isSet($args["market"])) unset($args["market"]);

      $args["side"]       = $side;
      $args["recvWindow"] = 60000;

      $args["type"]         = isSet($args["type"]) ? $args["type"] : "LIMIT";
      $args["timeInForce"]  = "GTC";

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if (is_numeric($args["quantity"]) === false) {
          return $this->getErrorReturn("warning: quantity expected numeric got " . gettype($quantity));
      }

      if(!isSet($args["price"])) return $this->getErrorReturn("required parameter: price");

      if (gettype($args["price"]) !== "string") {
        $args["price"] = $price = number_format($args["price"], 8, '.', '');
      }

      if (is_string($args["price"]) === false) {
        return $this->getErrorReturn("warning: price expected string got " . gettype($args["price"]));
      }

      $resultOBJ  = $this->send("api/v3/order" , $args , true , "POST");

      if($resultOBJ["success"] == true) {

        $result = $resultOBJ["result"];
        $result["order_id"]  = $result["orderid"]  = $result["orderId"];
        $resultOBJ["result"]  = $result;

      }
      return $resultOBJ;
    }

    // place buy order
    public function buy($args = null) {
      return $this->order($args , "buy");
    }

    // place sell order
    public function sell($args = null) {
      return $this->order($args , "sell");
    }

    // get open orders
    public function getOrders($args = null) {

      if(!isSet($args["symbol"])) {
        if(isSet($args["_market"]) && isSet($args["_currency"])) {
          $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
          unset($args["_market"]);
          unset($args["_currency"]);
          $args["symbol"] = $args["market"];
        }
      }
      if(isSet($args["market"])) unset($args["market"]);

      $method = "api/v3/openOrders";

      $resultOBJ  = $this->send( $method, $args, true);

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];

        if(!empty($result)) {
          $_result  = array();
          foreach($result as $item) {
            $item["order_id"]         = $item["orderid"]  = $item["orderId"];
            $item["order_type"]       = $item["type"]  . " " . $item["side"];
            $item['amount']           = $item["origQty"];
            $item['amount_remaining'] = $item["origQty"] - $item["executedQty"];
            $_result[]  = $item;
          }
          $result = $_result;
        }

        $resultOBJ["result"]  = $result;
      }
      return $resultOBJ;
    }

    // get order
    public function getOrder($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // cancel order
    public function cancel($args = null) {
      if(!isSet($args["symbol"])) {
        if(isSet($args["_market"]) && isSet($args["_currency"])) {
          $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
          unset($args["_market"]);
          unset($args["_currency"]);
          $args["symbol"] = $args["market"];
        } else {
          $this->getErrorReturn("required parameter: symbol");
        }
      }
      if(isSet($args["market"])) unset($args["market"]);

      if(!isSet($args["order_id"])) {
        $this->getErrorReturn("required parameter: order_id");
      }
      $args["orderId"]  = $args["order_id"];
      unset($args["order_id"]);


      $method = "api/v3/order";


      $resultOBJ  = $this->send( $method, $args, true , "DELETE");


      return $resultOBJ;
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

    public function time() {
        $result = $this->send("api/v1/time" , [] , false);
        if($result && isSet($result["success"])) {
          if($result["success"] == true && isSet($result["result"])) {
            if(isSet($result["result"]["serverTime"])) {
              $this->info['timeOffset'] = $result["result"]["serverTime"] - (microtime(true) * 1000);
              return $this->info['timeOffset'];
            } else {
              return $this->info['timeOffset'] ? isSet($this->info['timeOffset']) : 0;
            }
          } else {
            return $this->info['timeOffset'] ? isSet($this->info['timeOffset']) : 0;
          }
        } else {
          return $this->info['timeOffset'] ? isSet($this->info['timeOffset']) : 0;
        }
    }

    public function withdraw($args = null) {

      if(isSet($args["_market"])) unset($args["_market"]);

      if(!isSet($args["currency"])) {
        $args["currency"] = "";
        if(isSet($args["_currency"])) {
          $args["currency"] = $args["_currency"];
          unset($args["_currency"]);
        }
      }
      $args["currency"] = trim($args["currency"]);
      if($args["currency"] == "") {
        return $this->getErrorReturn("required parameter: currency");
      }

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");

      if(isSet($args["address"])) {
        $args["wallet"] = $args["address"];
        unset($args["address"]);
      }
      if(!isSet($args["wallet"])) return $this->getErrorReturn("required parameter: address");

      $args["name"] = "API Withdraw";

      $args["address"] = $args["wallet"];
      unset($args["wallet"]);

      $args["asset"] = $this->getMarketPair("",$args["currency"]);
      unset($args["currency"]);

      $method = "wapi/v3/withdraw.html";

      $resultOBJ  = $this->send( $method, $args, true , "POST");

      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        $resultOBJ["result"]  = $result;
      }
      return $resultOBJ;
    }

  }
?>
