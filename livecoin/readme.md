CryptoFyer LiveCoin v0.3
==============

PHP client api for LiveCoin api v0.3

I am NOT associated, I repeat NOT associated to LiveCoin. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


LiveCoin Documentation
----
LiveCoin API documentation: https://www.livecoin.net/api?lang=en
LiveCoin Examples : https://www.livecoin.net/api/examples


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

Public API functions
----
- getRestrictions()
- getCoinInfo()
- getMaxbidMinask()
- getTicker()
- getCurrencyUrl()
- getAllOrderbook()
- getOrderbook()
- getMarketHistory()

Private API functions
----
- getBalance()
- getBalances()
- getOrders()
- getOrder() -> not implemented yet!
- buy() -> not implemented yet!
- sell() -> not implemented yet!
