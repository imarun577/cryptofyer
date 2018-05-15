CryptoFyer Kucoin v0.3
==============

PHP client api for Kucoin

I am NOT associated, I repeat NOT associated to Kucoin. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Kucoin Documentation
----
Kucoin API documentation: https://kucoinapidocs.docs.apiary.io/


Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Kucoin


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$Kucoin  = new _KucoinApi($apiKey , $apiSecret );
$result = $Kucoin->getBalance(array("currency" => "BTC"));
```

Public API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| v1/open/tick | getTicker() |  |  |
| v1/open/orders | getOrderbook() |  |  |


Private API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
|  |  |  |  |


TODO
----

Quirks
----
