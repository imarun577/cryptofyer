CryptoFyer LiveCoin v0.8
==============

PHP client api for LiveCoin

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

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| info/coinInfo | getCoinInfo() |  |  |
| exchange/restrictions | getRestrictions() |  |  |
| exchange/maxbid_minask | getMaxbidMinask() |  |  |
| exchange/ticker | getTicker() |  |  |
| exchange/all/order_book | getAllOrderbook() |  |  |
| exchange/order_book | getOrderbook() |  |  |
| exchange/last_trades | getMarketHistory() |  |  |

Private API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| payment/balance | getBalance() |  |  |
| payment/balances | getBalances() |  |  |
| exchange/client_orders | getOrders() |  |  |
| exchange/order | getOrder() |  |  |
| exchange/buylimit | buy() |  |  |
| exchange/selllimit | sell() |  |  |
| exchange/cancellimit | cancel() |  |  |
