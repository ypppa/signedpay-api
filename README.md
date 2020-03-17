# SignedPay API


This library provides basic API options of SignedPay payment gateway.

## Installation

### With Composer

```
$ composer require signedpay/api
```

```json
{
    "require": {
        "signedpay/api": "~1.0"
    }
}
```

## Usage

```php
<?php

use Signedpay\API\Api;

$api = new Api('YourMerchantId', 'YourPrivateKey');

$response = $api->charge(['SomePaymentAttributes from API reference']);

```