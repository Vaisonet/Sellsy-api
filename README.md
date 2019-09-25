Sellsy API Client Library
=========================

[![Build Status](https://travis-ci.org/ferjul17/Sellsy-api.svg?branch=master)](https://travis-ci.org/ferjul17/Sellsy-api)
[![Coverage Status](https://coveralls.io/repos/ferjul17/Sellsy-api/badge.svg?branch=master&service=github)](https://coveralls.io/github/ferjul17/Sellsy-api?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/115e9ea4-129f-4557-a0c1-03a2191654bd/mini.png)](https://insight.sensiolabs.com/projects/115e9ea4-129f-4557-a0c1-03a2191654bd)

PHP library which helps to call Sellsy's API

```php
$client = new Client(['userToken'      => 'xxx', 'userSecret'     => 'xxx',
                      'consumerToken'  => 'xxx', 'consumerSecret' => 'xxx',
                     ]);
                     
var_dump($client->getService('Infos')->call('getInfos', []));
$promise = $client->getService('Infos')->callAsync('getInfos', [])->then(function($res) {
    var_dump($res);
});
$promise->wait();
```

How to install
--------------

First of all you need [Composer](https://getcomposer.org/doc/00-intro.md "Introduction - Composer"):

    php -r "readfile('https://getcomposer.org/installer');" | php

Then add Sellsy API as a dependency of your project:

    php composer.phar require ferjul17/sellsy-api
    
How to use
----------

### Setup the library

The library provide you a class called `Service` which represent a part of the Sellsy's API.
The service allows you to call api associated to this module.
For instance, the service `Accountdatas` allows to call all method which start with Accountdatas in Sellsy's API, like Accountdatas.getTaxe or Accountdatas.updateUnit

The Services are created by a factory called `Client`. So you first need to create a `Client` before having a `Service`.
To create a client just call its constructor. Its first argument is an array which contains the configuration.
You must provide the credentials to call the API which include the user token and secret, and the consumer token and secret that you can find in Sellsy interface.

```php
require 'path/to/vendor/autoload.php';
use SellsyApi\Client;

$client = new Client(['userToken'      => 'xxx', 'userSecret'     => 'xxx',
                      'consumerToken'  => 'xxx', 'consumerSecret' => 'xxx',
                     ]);
```

### Call APIs

Once you have you client, you can retreive a `Service` by calling the method `getService($serviceName)`:

```php
$service = $client->getService('Accountdatas');
```

Finally, you can call an API with the method `call($methodName, $params)`:

```php
$response = $service->call('createUnit', ['unit' => ['value' => 'Kg']);
```

There's another method `callAsync($methodName, $params)` which send an asynchronous request and return a `Promise`:

```php
$promise = $service->callAsync('createUnit', ['unit' => ['value' => 'Kg']);
$response = $promise->wait();
```

You can find more information about `Promise` [here](https://github.com/guzzle/promises "Github of Guzzle Promises").

### Handle errors

In order to handle errors, you should use retryable version of the previous methodes: `retryableCall($callable)` and
`retryableCallAsync($callable)`. In case of an error which is handle by the library, this one will try to send again the
requests by calling `$callable`.

`$callable` must be a function which return the parameters to give to the `call()` and `callAsync()` methods.

`$callable` takes 3 arguments:
  * The instance of the `ServiceInterface` used to send the call
  * The retry number. The first time this value is 0.
  * The error received

```php
var_dump($client->getService('Infos')->retryableCall(function (ServiceInterface $service, $retry, $e) {
    if ($retry > 3) {
        throw $e;
    }
    return ['getInfos', []];
}));
$promise = $client->getService('Infos')->retryableCallAsync(function (ServiceInterface $service, $retry, $e) {
    if ($retry > 3) {
        throw $e;
    }
    return ['getInfos', []];
})->then(function ($res) {
    var_dump($res);
});
$promise->wait();
```

How to run tests
----------------

If you want to test this library, you can run the test suite clone this repository:

     git clone https://github.com/ferjul17/Sellsy-api.git
     
then install dependencies:

    composer update
    
than run the tests:

    vendor/phpunit/phpunit/phpunit

License
-------

Sellsy API is an open-source project released under the MIT license. See the `LICENSE` file for more information.
