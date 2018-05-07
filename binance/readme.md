CryptoFyer Binance v0.9
==============

PHP client api for Binance

I am NOT associated, I repeat NOT associated to Binance. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Binance Documentation
----
Binance API documentation: https://github.com/binance-exchange/binance-official-api-docs

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Binance


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$binance  = new BinanceApi($apiKey , $apiSecret );
$result = $binance->getBalance(array("currency" => "BTC"));
```

Public API functions
----
| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| api/v3/ticker/price | getTicker() |  |  |
| api/v3/ticker/bookTicker | getOrderbookTicker() |  |  |
| api/v1/time | time() |  | Get server time |



Private API functions
----
| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| api/v3/account| getBalances() |  | Get all balances in the account |
| api/v3/account| getBalance() |  | Uses getBalances() to get a single price for now |
| api/v3/order| buy() |  |  |
| api/v3/order| sell() |  |  |
