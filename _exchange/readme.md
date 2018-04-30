CryptoFyer Exchange v0.1
==============

PHP client api for Exchange

I am NOT associated, I repeat NOT associated to Exchange. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Exchange Documentation
----
Exchange API documentation:


Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Exchange


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$exchange  = new _ExchangeApi($apiKey , $apiSecret );
$result = $exchange->getBalance(array("currency" => "BTC"));
```

Public API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
|  | getTicker() |  | Not ready yet |
|  | getMarketHistory() |  | Not ready yet |


Private API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
|  | getBalance() |  | Not ready yet |
|  | getOrders() |  | Not ready yet |
|  | getOrder() |  | Not ready yet |
|  | buy() |  | Not ready yet |
|  | sell() |  | Not ready yet |
|  | cancel() |  | Not ready yet |
