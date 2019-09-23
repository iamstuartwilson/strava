[![Build Status](https://travis-ci.org/iamstuartwilson/strava.svg)](https://travis-ci.org/iamstuartwilson/strava)
![Minimum PHP Version](http://img.shields.io/badge/php->=5.5-8892BF.svg?style=flat)
![Packagist](https://img.shields.io/packagist/v/iamstuartwilson/strava.svg)
![Packagist Downloads](https://img.shields.io/packagist/dt/iamstuartwilson/strava.svg)

# StravaApi

The class simply houses methods to help send data to and receive data from the API. Please read the [API documentation](https://developers.strava.com/docs/reference/) to see what endpoints are available.

*There is no file upload support at this time.*

## Installation

### With Composer

``` shell
composer require iamstuartwilson/strava
```

Or add it manually to your `composer.json`:

``` json
{
    "require" : {
        "iamstuartwilson/strava" : "^1.4"
    }
}
```

### Manually

Copy `StravaApi.php` to your project and *require* it in your application as described in the next section.

## Getting Started

Instantiate the class with your **client_id** and **client_secret** from your [registered app](https://www.strava.com/settings/api):

``` php
require_once 'StravaApi.php';

$api = new Iamstuartwilson\StravaApi(
    $clientId,
    $clientSecret
);
```

If you're just testing endpoints/methods you can skip the authentication flow and just use the access token from your [settings page](https://www.strava.com/settings/api).

You will then need to [authenticate](https://developers.strava.com/docs/authentication/) your strava account by requesting an access code. You can generate a URL for authentication using the following method:

``` php
$api->authenticationUrl($redirect, $approvalPrompt = 'auto', $scope = null, $state = null);
```

When a code is returned you must then exchange it for an [access token and a refresh token](http://developers.strava.com/docs/authentication/#token-exchange) for the authenticated user:

``` php
$result = $api->tokenExchange($code);
```

The token exchange result contains among other data the tokens. You can access them as attributes of the result object:

```php
$accessToken = $result->access_token;
$refreshToken = $result->refresh_token;
$expiresAt = $result->expires_at;
```

Before making any requests you must set the access and refresh tokens as returned from your token exchange result or via your own private token from Strava:

``` php
$api->setAccessToken($accessToken, $refreshToken, $expiresAt);
```

## Example oAuth2 Authentication Flow

`examples/oauth-flow.php` demonstrates how the oAuth2 authentication flow works.

1. Choose how to load the `StravaApi.php` – either via Composer autoloader or by manually *requiring* it.
2. Replace the three config values `CALLBACK_URL`, `STRAVA_API_ID`, and `STRAVA_API_SECRET` at the top of the file
3. Place the file on your server so that it's accessible at `CALLBACK_URL`
4. Point your browser to `CALLBACK_URL` and start the authentication flow.

The scripts prints a lot of verbose information so you get an idea on how the Strava oAuth flow works.

## Example Requests

Once successfully authenticated you're able to communicate with Strava's API.

All actions that change Strava contents (`post`, `put`, `delete`) will need the **scope** set to *write* in the authentication flow.

### Get the most recent 100 KOMs from any athlete

``` php
$api->get('athletes/:id/koms', ['per_page' => 100]);
```

### Post a new activity

``` php
$api->post('activities', [
    'name'             => 'API Test',
    'type'             => 'Ride',
    'start_date_local' => date( 'Y-m-d\TH:i:s\Z'),
    'elapsed_time'     => 3600
]);
```

### Update a athlete's weight

``` php
$api->put('athlete', ['weight' => 70]);
```

### Delete an activity

``` php
$api->delete('activities/:id');
```

## Releases

See CHANGELOG.md.
