CryptoFyer Bittrex v0.16
==============

PHP client api for Bittrex api v1.1

I am NOT associated, I repeat NOT associated to Bittrex. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Bittrex Documentation
----
Bittrex API documentation: https://bittrex.com/Home/Api


Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Bittrex


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.

Public API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| public/getmarkets | getMarkets() |  |  |
| public/getcurrencies | getCurrencies() |  |  |
| public/getmarketsummary | getTicker() |  |  |
| public/getmarketsummary | getMarketSummary() |  |  |
| public/getorderbook | getOrderbook() |  |  |
| public/getmarkethistory | getMarketHistory() |  |  |
| public/getmarketsummaries | getMarketSummaries() |  |  |


Market API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| market/buylimit | buy() |  |  |
| market/selllimit | sell() |  |  |
| market/cancel | cancel() |  |  |
| market/getopenorders | getOrders() |  |  |


Account API functions
----

| Endpoint uri | Api function | Parameters | Remarks |
| --- | --- | --- | --- |
| account/getbalances | getBalances() |  |  |
| account/getbalance | getBalance() |  |  |
| account/getdepositaddress | getDepositAddress() |  |  |
| account/withdraw | withdraw() |  |  |
| account/getorder | getOrder() |  |  |
| account/getorderhistory | getOrderHistory() |  |  |
| account/getwithdrawalhistory | getWithdrawalHistory() |  |  |
| account/getdeposithistory | getDepositHistory() |  |  |

Example
----
```php
$bittrex  = new BittrexApi($apiKey , $apiSecret );
$result = $bittrex->getBalance(array("currency" => "BTC"));
```

Example web files
----
I've prepared some example files to get you started in the examples folder.
* portfolio.php (list all current currency you with balance > 0)
* holdings.php (transactions and finding breakeven rate on a currency)
* deposits.php (overview of your total deposits)

Example CLI files
----
I've prepared some example files to get you started in the examples folder to run from your command line.
* cli_watch.php (command line script to watch a currency)
* cli_console.php (command line console like to sell/buy/cancel orders)
