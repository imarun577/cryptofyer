<?php
  /*
  *
  * @package    cryptofyer
  * @class CryptopiaApi
  * @author     Fransjo Leihitu
  * @version    0.26
  *
  * Documentation Public Api : https://www.cryptopia.co.nz/Forum/Thread/255
  * Documentation Private Api : https://www.cryptopia.co.nz/Forum/Thread/256
  */
  class CryptopiaApi extends CryptoExchange implements CryptoExchangeInterface {

    // exchange base api url
    private $exchangeUrl   = "https://www.cryptopia.co.nz/Api/";

    // exchange currency url
    private $currencyUrl  = "https://www.cryptopia.co.nz/Exchange?market=";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "26";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl);
    }

    private function send($method = null , $args = array() , $secure = true) {
      if(empty($method)) return $this->getErrorReturn("method was not defined!");

      if(isSet($args["_market"])) unset($args["_market"]);
      if(isSet($args["_currency"])) unset($args["_currency"]);

      $urlParams  = $args;
      $uri        = $this->getBaseUrl() . $method;

      $ch = curl_init();

      if($secure) {
        $nonce                      = microtime();
        $post_data                  = json_encode( $urlParams );
        $m                          = md5( $post_data, true );
        $requestContentBase64String = base64_encode( $m );
        $signature                  = $this->apiKey . "POST" . strtolower( urlencode( $uri ) ) . $nonce . $requestContentBase64String;
        $hmacsignature              = base64_encode( hash_hmac("sha256", $signature, base64_decode( $this->apiSecret ), true ) );
        $header_value               = "amx " . $this->apiKey . ":" . $hmacsignature . ":" . $nonce;
        $headers                    = array("Content-Type: application/json; charset=utf-8", "Authorization: $header_value");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $urlParams ) );
      }

      curl_setopt($ch, CURLOPT_URL, $uri );
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      $execResult = curl_exec($ch);

      // check if there was a curl error
      if(curl_error($ch)) return $this->getErrorReturn(curl_error($ch));

      // check if we can decode the JSON string to a assoc array
      if($obj = json_decode($execResult , true)) {
        if($obj["Success"] == true) {
          if(!isSet($obj["Error"])) {
            return $this->getReturn($obj["Success"],$obj["Message"],$obj["Data"]);
          } else {
            return $this->getErrorReturn($obj["Error"]);
          }
        } else {
          return $this->getErrorReturn($obj["Error"]);
        }
      } else {
        return $this->getErrorReturn($execResult);
      }
    }

    public function getMarketPair($market = "" , $currency = "") {
      return strtoupper($currency . "-" . $market);
    }

    public function getCurrencyUrl($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"] = str_replace("-" , "_" , $args["market"]);
      $args["market"] = str_replace("/" , "_" , $args["market"]);

      return $this->currencyUrl . $args["market"];
    }

    public function getCurrencies($args = null){
      return $this->send("GetCurrencies" , $args , false);
    }

    public function getDepositAddress($args = null) {
      if(isSet($args["_currency"])) {
        $args["currency"] = $args["_currency"];
        unset($args["_currency"]);
      }
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      $returnOBJ = $this->send("GetDepositAddress" , $args);
      if($returnOBJ["success"] == true) {
        $result = $returnOBJ["result"];

        $result["address"]  = $result["Address"];

        $returnOBJ["result"]  = $result;
      }
      return $returnOBJ;
    }

    public function getMarkets($args  = null) {

      $market = isSet($args["market"]) ? "/" . $args["market"] : "";
      $hours  = isSet($args["hours"]) ? "/" . $args["hours"] : "";

      $responseOBJ = $this->send("GetMarkets".$market.$hours , null , false);

      if($responseOBJ["success"] == true) {
        $result = array();
        foreach($responseOBJ["result"] as $item) {
          $item["price"]     = $item["LastPrice"];
          $item["bid_price"]      = $item["BidPrice"];
          $item["ask_price"]      = $item["AskPrice"];
          $result[] = $item;
        }
        $responseOBJ["result"] = $result;
      }
      return $responseOBJ;
    }

    public function getTradePairs($args = null){
      return $this->send("GetTradePairs" , $args , false);
    }

    public function getBalances($args  = null) {
      return $this->getBalance(array("currency" => ""));
    }

    public function getBalance($args  = null) {

      if(isSet($args["_currency"])) {
        $args["currency"] = $args["_currency"];
        unset($args["_currency"]);
      }

      if(!isSet($args["currency"])) {
        return $this->getErrorReturn("required parameter: currency");
      }

      $args["Currency"] = $args["currency"];
      unset($args["currency"]);

      $balanceOBJ = $this->send("GetBalance" , $args);
      if($balanceOBJ["success"] == true) {
        $result = array();

        if(isSet($args["Currency"])) {
          $item = $balanceOBJ["result"][0];
          $balanceOBJ["result"] = $item;
        } else {
          foreach($balanceOBJ["result"] as $item) {
            $item["Balance"]  = $item["Total"];
            $item["Currency"] = $item["Symbol"];
            $item["address"]  = $item["Address"];
            $result[] = $item;
          }
          $balanceOBJ["result"] = $result;
        }
      }
      return $balanceOBJ;
    }

    public function getOrder($args  = null) {
      if(isSet($args["orderid"])) {
        $args["order_id"] = $args["orderid"];
        unset($args["orderid"]);
      }
      if(!isSet($args["order_id"])) return $this->getErrorReturn("required parameter: order_id");

      $resultOBJ  = $this->getOrders($args);
      if($resultOBJ["success"] == true) {
        foreach($resultOBJ["result"] as $result) {
          if($result["order_id"] == $args["order_id"]) {
            return $this->getReturn(true , null , $result);
          }
        }
        $this->getErrorReturn("cannot find order: " . $args["order_id"]);
      } else {
        return $resultOBJ;
      }
    }

    public function getOrders($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(isSet($args["market"])) {
        $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
        $args["market"]=strtoupper(str_replace("/","_",$args["market"]));
      } else {
        $args["market"] = "";
      }

      $resultOBJ  = $this->send("GetOpenOrders" , $args);
      if($resultOBJ["success"] == true) {
        $result = array();
        foreach($resultOBJ["result"] as $item) {
          $item["order_id"]  = $item["orderid"]  = $item["OrderId"];
          $result[] = $item;
        }
        $resultOBJ["result"]  = $result;
      }
      return $resultOBJ;

    }

    public function cancel($args = null) {

      if(isSet($args["orderid"])) {
        $args["order_id"] = $args["orderid"];
        unset($args["orderid"]);
      }
      if(!isSet($args["order_id"])) return $this->getErrorReturn("required parameter: order_id");

      $args["OrderId"]  = $args["order_id"];
      unset($args["order_id"]);

      if(!isSet($args["type"])) $args["type"] = "Trade";
      $args["Type"] = $args["type"];
      unset($args["type"]);

      return $this->send("CancelTrade" , $args);
    }

    public function order($args = null , $side = "") {

      if(empty($side)) return $this->getErrorReturn("required parameter: side");
      $method = "";
      if($side == "buy") {
        $args["Type"] = "Buy";
      }
      if($side == "sell") {
        $args["Type"] = "Sell";
      }
      if(!isSet($args["Type"])) return $this->getErrorReturn("incorrect side");

      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"] = str_replace("-","/",$args["market"]);
      $args["market"] = str_replace("_","/",$args["market"]);
      $args["Market"] = strtoupper($args["market"]);
      unset($args["market"]);

      if(isSet($args["price"])) {
        $args["rate"] = $args["price"];
        unset($args["price"]);
      }
      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");
      $args["Rate"] = $args["rate"];
      unset($args["rate"]);

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["Amount"] = $args["amount"];
      unset($args["amount"]);

      $resultOBJ = $this->send("SubmitTrade" , $args);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];
        $result["order_id"]  = $result["orderid"]  = $result["OrderId"];
        $resultOBJ["result"]  = $result;
      }
      return $resultOBJ;
    }

    public function buy($args = null) {
      return $this->order($args , "buy");
    }

    public function sell($args = null) {
      return $this->order($args , "sell");
    }

    public function getMarket($args = null) {
      return $this->getTicker($args);
    }
    public function getTicker($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      $hours  = isSet($args["hours"]) ? "/" . $args["hours"] : "";

      $responseOBJ = $this->send("GetMarket/".$args["market"].$hours , null , false);
      if(isSet($responseOBJ["result"]) && !empty($responseOBJ["result"])) {
        $result             = $responseOBJ["result"];
        $result["price"]     = $result["LastPrice"];
        $result["bid_price"]      = $result["BidPrice"];
        $result["ask_price"]      = $result["AskPrice"];
        $responseOBJ["result"] = $result;
      }
      return $responseOBJ;
    }

    public function getMarketOrders($args = null) {
      return $this->getOrderbook($args);
    }
    public function getOrderbook($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      if(isSet($args["depth"])) {
        $orderCount  = isSet($args["depth"]) ? "/" . $args["depth"] : "";
        unset($args["depth"]);
      }

      $resultOBJ = $this->send("GetMarketOrders/".$args["market"].$orderCount, null , false);

      if($resultOBJ["success"] == true) {
        $raw  = $resultOBJ["result"];

        $resultOBJ["result"]  = array();
        $resultOBJ["result"]["_raw"]  = $raw;

        $resultOBJ["result"]["buy"] = $raw["Buy"];
        $resultOBJ["result"]["sell"] =$raw["Sell"];

        $resultOBJ["result"]["bid_price"] =  $raw["Buy"][0]["Price"];
        $resultOBJ["result"]["bid_amount"] =  $raw["Buy"][0]["Volume"];

        $resultOBJ["result"]["ask_price"] =  $raw["Sell"][0]["Price"];
        $resultOBJ["result"]["ask_amount"] = $raw["Sell"][0]["Volume"];
      }

      return $resultOBJ;
    }

    public function getMarketOrderGroups($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    public function submitTip($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    public function getTransactions($args = null) {
      return $this->send("GetTransactions", $args);
    }

    public function getTradeHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      //$count  = isSet($args["count"]) ? "/" . $args["count"] : "";
      //$method = "GetTradeHistory/".$args["market"].$count;
      //unset($args["market"]);
      $method = "GetTradeHistory";

      $returnOBJ  = $this->send($method, $args , true);
      if($returnOBJ["success"] == true) {
        $result = $returnOBJ["result"];
        $items  = array();
        foreach($result as $item) {
          $item["amount"] = $item["Amount"];
          $item["price"] = $item["Price"];
          $items[]  = $item;
        }
        $returnOBJ["result"]  = $items;
      }
      return $returnOBJ;
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
      $args["Currency"] = trim($args["currency"]);
      unset($args["currency"]);

      if(isSet($args["amount"])) {
        $args["Amount"] = $args["amount"];
        unset($args["amount"]);
      }
      if(!isSet($args["Amount"])) return $this->getErrorReturn("required parameter: amount");

      if(isSet($args["address"])) {
        $args["Address"] = $args["address"];
        unset($args["address"]);
      }
      if(!isSet($args["Address"])) return $this->getErrorReturn("required parameter: address");


      $resultOBJ = $this->send("SubmitWithdraw" , $args , true);
      if($resultOBJ["success"] == true) {

      }
      return $resultOBJ;
    }

    public function getMarketHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      $hours  = isSet($args["hours"]) ? "/" . $args["hours"] : "";
      $method = "GetMarketHistory/".$args["market"].$hours;
      unset($args["market"]);

      $returnOBJ  = $this->send($method, $args , false);
      if($returnOBJ["success"] == true) {
        $result = $returnOBJ["result"];
        $items  = array();
        foreach($result as $item) {
          $item["amount"] = $item["Amount"];
          $item["price"] = $item["Price"];
          $items[]  = $item;
        }
        $returnOBJ["result"]  = $items;
      }
      return $returnOBJ;
    }

  }
?>
