CryptoFyer 0.6
==============

An unified framework to connect to different Crypto Exchange websites.

I am NOT associated, I repeat NOT associated to any Exchange website. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ

Supported Exchanges
----

| Exchange | Url | API documentation | Remarks |
| --- | --- | --- | --- |
| Binance | http://www.binance.com/ | https://github.com/binance-exchange/binance-official-api-docs | |
| Bittrex | https://www.bittrex.com/ | https://bittrex.com/home/api |  |
| Cryptopia | https://www.cryptopia.co.nz/ | Public API : https://www.cryptopia.co.nz/Forum/Thread/255 & Private API : https://www.cryptopia.co.nz/Forum/Thread/256 |  |
| Coinexchange | https://www.coinexchange.io/ | | Only public calls |
| Livecoin | https://www.livecoin.net/ | https://www.livecoin.net/api?lang=en |  |



API keys safety
----
All the exchanges uses API keys. Each API key consists of a public and a private key. NEVER and I repeat NEVER expose your api keys to anybody! If somebody has your API keys, this person can sell/buy/withdraw from you account!

If you do suspect somebody has your api keys DELETE your api keys at once!!!

Also, a lot of exchanges have the option to make you api keys more secure with the option to sell/buy/withdraw option. So if you can have an api key with only read rights and no sell/buy/withdraw right. But that depends on the exchange.

One more time: NEVER EXPOSE YOUR API KEYS TO ANYBODY!!!!


Installation
----

fetch the project via git:
```sh
$ git clone https://github.com/fransyozef/cryptofyer
```


Config.inc.php
----
Each exchange sits in its own folder and there you'll find 'config.example.inc.php'.
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.


Required functions
----
The exchange classes have some required functions to implement:


| Function | Remarks |
| --- | --- |
| buy() | place a buy order |
| sell() | place a sell order |
| getOrders() | get open orders |
| getOrder() | get order |
| cancel() | cancel order |
| getTicker() | get currency information |
| getCurrencyUrl() | get the exchange currency detail url |
| getMarketHistory() | get market history |
| getBalance() | get balance |

Market/currency pair
----
When I started with this unified api platform, I used Bittrex's API as a model.
Bittrex's string literal for the marketpair is [market]-[currency] for example : BTC-ETH.

After Bittrex I implemented Cryptopia's API. Cryptopia's string literal for the marketpair is [currency]-[market] for example : ETH-BTC.

In order to normalize the market literal string you can use the getMarketPair() function.

```php
$_market = "USDT";
$_currency = "BTC";

$exchange  = new BittrexApi($apiKey , $apiSecret );
$market   = $exchange->getMarketPair($_market , $_currency);
```
Here you see `$market` has the value 'USDT-BTC'.

```php
$_market = "USDT";
$_currency = "BTC";

$exchange  = new CryptopiaApi($apiKey , $apiSecret );
$market   = $exchange->getMarketPair($_market , $_currency);
```
Here you see `$market` has the value 'BTC-USDT'.

In the future, each exchange api class has a `getMarketPair()` function to retrieve the right pair notation.

Unified market arguments
----
Some functions requires the market string literal as argument. For example Bittrex's ticker:

```php
$result = $exchange->getTicker(
  array(
    "market" => "BTC-ETH"
  )
);

debug($result);
```

or Cryptopia's ticker :

```php
$result = $exchange->getTicker(
  array(
    "market" => "ETH-BTC"
  )
);

debug($result);
```

As you can see, the `market` value is different. To normalize, I added 2 special arguments :

* `_market`
* `_currency`

for example :

```php
$result = $exchange->getTicker(array("_market" => "BTC" , "_currency" => "ETH"));
debug($result);
```
The function will resolve the market pair with the `getMarketPair()` function.


Return values
----
Normalizing the parameters is one of the goals of this project. But also the return values.
A typical return structure would be

```php
  array(
    "success" => true,
    "message" => "A custom string",
    "result" => array(

    )
  )
```

| Key | Type | Remarks |
| --- | --- | --- |
| success | boolean | |
| message | string | |
| result | mixed | Payload of the api call |

So when success holds true, the API call was a success.

Alias currency
----
Over time, currency's change names. But the exchanges don't always update their tickers. In order to compensate, I created the ``` getCurrencyAlias() ``` function. The function will lookup the private array ``` $currencyAlias ``` . This is an associated array with the new currency name and aliased to the old currency name.

Place a buy order
----
Required parameters

| Name | Type | Remarks |
| --- | --- | --- |
| market | string | Marketpair |
| price | long | price to sell |
| amount | long | amount to sell |

for example :

```php
$result = $exchange->buy(
  array(
    "market" => "ETH-BTC" ,
    "price" => 0.00001 ,
    "amount" => 1
  )
);

debug($result);
```

or you can use the (preferred) way using the ```_market``` and ```_currency``` method.

```php
$result = $exchange->buy(
  array(
    "_market" => "BTC" ,
    "_currency" => "ETH",
    "price" => 0.00001 ,
    "amount" => 1
  )
);

debug($result);
```

Place a sell order
----
Required parameters

| Name | Type | Remarks |
| --- | --- | --- |
| market | string | Marketpair |
| price | long | price to sell |
| amount | long | amount to sell |

for example :

```php
$result = $exchange->sell(
  array(
    "market" => "ETH-BTC" ,
    "price" => 0.00001 ,
    "amount" => 1
  )
);

debug($result);
```

or you can use the (preferred) way using the ```_market``` and ```_currency``` method.

```php
$result = $exchange->sell(
  array(
    "_market" => "BTC" ,
    "_currency" => "ETH",
    "price" => 0.00001 ,
    "amount" => 1
  )
);

debug($result);
```

Cancel a order
----
Required parameters

| Name | Type | Remarks |
| --- | --- | --- |
| market | string | Marketpair |
| order_id | long | the unique order_id |

for example :

```php
$result = $exchange->cancel(
  array(
    "market" => "ETH-BTC" ,
    "order_id" => 29011978
  )
);

debug($result);
```

or you can use the (preferred) way using the ```_market``` and ```_currency``` method.

```php
$result = $exchange->cancel(
  array(
    "_market" => "BTC" ,
    "_currency" => "ETH",
    "order_id" => 29011978
  )
);

debug($result);
```


Unified tests
----
In the `tests` folder you will find some examples where you can see the normalization of functions.  

Todo
----
* More Exchanges Api
* Better unified functions/notations
* Cleanup code
* Better documentation
