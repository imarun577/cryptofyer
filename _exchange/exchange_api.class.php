<?php
  /*
  *
  * @package    cryptofyer
  * @class    _ExchangeApi
  * @author     Fransjo Leihitu
  * @version    0.1
  *
  * API Documentation :
  */
  class _ExchangeApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "";
    private $apiVersion   = "";

    // base url for currency
    private $currencyUrl  = "";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "1";

    private $currencyAlias  = array();

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . "v" . $this->apiVersion . "/");
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
          return $this->getReturn(true,"",$obj);
        } else {
          return $this->getErrorReturn("server error");
        }
      } else {
        return $this->getErrorReturn($execResult);
      }
      return $this->getErrorReturn("unknown error");
    }


    // get ticket information
    public function getTicker($args  = null) {
      return $this->getErrorReturn("not implemented yet!");
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
      return $this->getErrorReturn("not implemented yet!");
    }

    // Get market history
    public function getMarketHistory($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }


  }
?>
