CryptoFyer LiveCoin v0.2
==============

PHP client api for LiveCoin api v0.2

I am NOT associated, I repeat NOT associated to LiveCoin. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


LiveCoin Documentation
----
LiveCoin API documentation: https://www.livecoin.net/api?lang=en

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at LiveCoin


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$liveCoin  = new LiveCoinApi($apiKey , $apiSecret );
$result = $liveCoin->getBalance(array("currency" => "BTC"));
```
