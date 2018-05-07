<?php
  /*
  *
  * @package    cryptofyer
  * @class    BinanceApi
  * @author     Fransjo Leihitu
  * @version    0.8
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
    private $_version_minor  = "8";

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

        /*
        if (isset($params['wapi'])) {
            unset($params['wapi']);
            $base = $this->wapi;
        }
        */
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

    // place buy order
    public function buy($args = null) {

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

      $args["side"]       = "buy";
      $args["recvWindow"] = 60000;

      $args["type"]         = isSet($args["type"]) ? $args["type"] : "LIMIT";
      $args["timeInForce"]  = "GTC";


      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if (is_numeric($args["quantity"]) === false) {
          return $this->getErrorReturn("warning: quantity expected numeric got " . gettype($quantity));
      }

      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");
      $args["price"]  = $args["rate"];
      unset($args["rate"]);

      if (gettype($args["price"]) !== "string") {
        $args["price"] = $price = number_format($args["price"], 8, '.', '');
      }

      if (is_string($args["price"]) === false) {
        return $this->getErrorReturn("warning: price expected string got " . gettype($args["price"]));
      }

      $resultOBJ  = $this->send("api/v3/order" , $args , true , "POST");

      if($resultOBJ["success"] == true) {
        // do we need to normalize the return?
        // TODO find the orderid
        return $resultOBJ;
      }
      return $resultOBJ;
    }

    // place sell order
    public function sell($args = null) {
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

      $args["side"]       = "sell";
      $args["recvWindow"] = 60000;

      $args["type"]         = isSet($args["type"]) ? $args["type"] : "LIMIT";
      $args["timeInForce"]  = "GTC";


      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if (is_numeric($args["quantity"]) === false) {
          return $this->getErrorReturn("warning: quantity expected numeric got " . gettype($quantity));
      }

      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");
      $args["price"]  = $args["rate"];
      unset($args["rate"]);

      if (gettype($args["price"]) !== "string") {
        $args["price"] = $price = number_format($args["price"], 8, '.', '');
      }

      if (is_string($args["price"]) === false) {
          // WPCS: XSS OK.
        return $this->getErrorReturn("warning: price expected string got " . gettype($args["price"]));
      }

      $resultOBJ  = $this->send("api/v3/order" , $args , true , "POST");

      if($resultOBJ["success"] == true) {
        // do we need to normalize the return?
        // TODO find the orderid
        return $resultOBJ;
      }
      return $resultOBJ;
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

    public function time()
    {
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
  }
?>
