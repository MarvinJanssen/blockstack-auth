# Blockstack Authentication in PHP

Verify Blockstack Authentication responses (`authResponse`) in PHP to build client-server applications. This library verifies the claims, signature, and expiry. Uses the excellent [elliptic-php](https://github.com/simplito/elliptic-php) library by Simplito.

Originally made for https://simpleco.in. If you use this library for your project and would like to have it listed here, send me a message.

## Installation

Composer:

```
composer require marvinjanssen/blockstack-auth
```

## Usage

```php
// Basic method:

use Blockstack\Authentication;

Authentication::handle(function($token)
	{
	// This callable is called if the GET parameter
	// authResponse is set and contains a valid
	// token. The first parameter holds the
	// decoded token.
	
	// Take the user address.
	$address = $token['payload']['iss'];

	// Place your login logic here.
	});


// Manually:

$result = Authentication::verify($_GET['authResponse']);

if ($result)
	{
	// Get the decoded token.
	$token = Authentication::token();

  // Take the user address.
  $address = $token['payload']['iss'];

	// Place your login logic here.
	}


// Decode token without verification:

$token = Authentication::decode($_GET['authResponse']);
```

The `verify()` and `decode()` may throw `Blockstack\AuthenticationException`, be sure to handle them properly.


## Decoded token

A decoded token looks like this:

```
array(4) {
  'header' =>
  array(2) {
    'typ' =>
    string(3) "JWT"
    'alg' =>
    string(6) "ES256K"
  }
  'payload' =>
  array(15) {
    'jti' =>
    string(36) "5278a0da-3c16-4a0f-bba6-0dc53e766e4a"
    'iat' =>
    int(1563328871)
    'exp' =>
    int(1566007271)
    'iss' =>
    string(47) "did:btc-addr:16D1WiCKtzeDZF4jqgNVBKYi4TPHJXTdsz"
    'private_key' =>
    string(780) "7b226976223a226233636533646135353139313335313437393234"...
    'public_keys' =>
    array(1) {
      [0] =>
      string(66) "0315651eac16b57ecdade73fcc257a015b581c019b9d374884b395902a5218eebe"
    }
    'profile' =>
    NULL
    'username' =>
    string(25) "localtestid.id.blockstack"
    'core_token' =>
    NULL
    'email' =>
    NULL
    'profile_url' =>
    string(79) "https://gaia.blockstack.org/hub/16D1WiCKtzeDZF4jqgNVBKYi4TPHJXTdsz/profile.json"
    'hubUrl' =>
    string(26) "https://hub.blockstack.org"
    'blockstackAPIUrl' =>
    NULL
    'associationToken' =>
    NULL
    'version' =>
    string(5) "1.3.1"
  }
  'signature' => [signature binary]
  'signature_input' =>
  string(1777) "eyJ0eXAiOiJKV1QiLCJhbGciONTM3MzczM"...
}
```

## Donate

If this library was useful and you want to express your gratitude, feel free to send some Satoshis my way.

Bitcoin (BTC)

```
39KqZ4t3zQs7wRrgtDFtAZ2x3QEDEjxYST
```

## License

This project is licensed under the MIT License.
