# Teamgate API SDK for PHP
A cloud-based intelligent Sales CRM for small and mid-size teams. With its simple yet playful interface, Teamgate is a great sales stack for today’s business. See https://www.teamgate.com for details.
This is the official Teamgate API wrapper-client for PHP based apps, distributed by Teamgate Ltd. freely under the MIT licence.

# Prerequisites
You will need to make sure your server meets the following requirements:
- PHP >= 5.5
- The PHP cURL extension

# Getting Started
You can install via composer or by downloading the source. 
Teamgate API client utilizes Composer to manage its dependencies. So, before using Teamgate API client, make sure you have Composer installed on your machine.

## Install Composer In Your Project
Run this in your command line:
```bash
curl -sS https://getcomposer.org/installer | php
```
## Install Dependencies
Execute this in your project root:
```bash
php composer.phar install
```

To update to the new version:
```bash
php composer.phar update
```

## Autoloading
For libraries that specify autoload information, Composer generates a `vendor/autoload.php` file. You can simply include this file and you will get autoloading for free.
```php
require __DIR__ . '/vendor/autoload.php';
```

# Basic Usage
Here's a quick example that will list some deals from your Teamgate account:
```php
require __DIR__ . '/vendor/autoload.php';

$api = new \Teamgate\API([
    'apiKey' => '_YOUR_ACCOUNT_API_KEY_', // located at account settings -> additional features -> external apps
    'authToken' => '_YOUR_PERSONAL_AUTH_TOKEN_' // located at user settings -> preferences
]);

$result = $api->deals->get([
        'offset' => 0, 
        'limit' => 10
    ]
);
var_dump($result);
```
Create a new lead:
```php
require __DIR__ . '/vendor/autoload.php';

$api = new \Teamgate\API([
    'apiKey' => '_YOUR_ACCOUNT_API_KEY_', // located at account settings -> additional features -> external apps
    'authToken' => '_YOUR_PERSONAL_AUTH_TOKEN_' // located at user settings -> preferences
]);

$result = $api->leads->create(
  [
    'title' => 'The Company Name'
  ]
);
var_dump($result);
```

## Error Handling
When you instantiate a client or make any request via service objects, exceptions can be raised for multiple of reasons e.g. a server error, an authentication error, an invalid params and etc.

Below shows how to properly handle exceptions:
```php
require __DIR__ . '/vendor/autoload.php';

try 
{
  $api = new \Teamgate\API([
      'apiKey' => '_YOUR_ACCOUNT_API_KEY_', // located at account settings -> additional features -> external apps
      'authToken' => '_YOUR_PERSONAL_AUTH_TOKEN_' // located at user settings -> preferences
  ]);
  $result = $api->leads->create(
    [
      'title' => 'The Company Name'
    ]
  ));
  var_dump($result);
} 
catch (Teamgate\Exception\ValidationException $e) 
{
  /* Invalid client configuration */
} 
catch (Teamgate\Exception\TransportException $e) 
{
  var_dump($e->getCode()); // HTTP Status Code
  var_dump($e->output); // Teamgate API Output
}
catch (Teamgate\Exception\ResponseException $e) 
{
  /* Invalid query parameters or etc. */
}
catch (Exception $e)
{
  /* Other kind of exception */
}
```

# Documentation
The documentation for the Teamgate API is located at http://developers.teamgate.com/

# Getting Help
If you need help installing or using the library, please contact Teamgate Support at `support@teamgate.com`.
If you've instead found a bug in the library or would like new features added, go ahead and open issues or pull requests against this repo!
