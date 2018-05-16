CryptoFyer Cryptopia v0.25
==============

PHP client api for Cryptopia

I am NOT associated, I repeat NOT associated to Cryptopia. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Cryptopia Documentation
----
* Documentation Public Api : https://www.cryptopia.co.nz/Forum/Thread/255

* Documentation Private Api : https://www.cryptopia.co.nz/Forum/Thread/256

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Cryptopia


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$cryptopia  = new CryptopiaApi($apiKey , $apiSecret );
$result = $cryptopia->getBalance(array("currency" => "BTC"));
```

Public API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| GetMarketOrders | getOrderbook() |  |  |
| GetMarketHistory | getMarketHistory() |  |  |
| GetMarkets | getMarkets() |  |  |
| GetTradePairs | getTradePairs() |  |  |

Private API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| GetDepositAddress | getDepositAddress() |  |  |
|  | getBalances() |  |  |
| GetBalance | getBalance() |  |  |
|  | getOrder() |  |  |
| GetOpenOrders | getOrders() |  |  |
| CancelTrade | cancel() |  |  |
| SubmitTrade | buy() |  |  |
| SubmitTrade | sell() |  |  |
| GetTransactions | getTransactions() |  |  |
| GetTradeHistory | getTradeHistory() |  |  |


TODO
----

Quirks
----
