[![Build Status](https://travis-ci.org/iamstuartwilson/strava.svg)](https://travis-ci.org/iamstuartwilson/strava)
![Minimum PHP Version](http://img.shields.io/badge/php->=5.5-8892BF.svg?style=flat)
![Packagist](https://img.shields.io/packagist/v/iamstuartwilson/strava.svg)
![Packagist Downloads](https://img.shields.io/packagist/dt/iamstuartwilson/strava.svg)

StravaApi
=============

The class simply houses methods to help send data to and recieve data from the API. Please read the [API documentation](https://developers.strava.com/docs/reference/) to see what endpoints are available.

*There is currently no file upload support at this time*

Installation
------------

### With Composer

```
$ composer require iamstuartwilson/strava
```

**Or**

Add `iamstuartwilson/strava` to your `composer.json`:

``` json
{
    "require" : {
        "iamstuartwilson/strava" : "~1.3"
    }
}
```

### Manually

Copy `StravaApi.php` to your project and *require* it in your application as described in the next section.

Getting Started
------------

Include the class and instantiate with your **client_id** and **client_secret** from your [registered app](https://www.strava.com/settings/api):

``` php
require_once 'StravaApi.php';

$api = new Iamstuartwilson\StravaApi(
    $clientId,
    $clientSecret
);
```

You will then need to [authenticate](http://strava.github.io/api/v3/oauth/) your strava account by requesting an access code<sup>1</sup>.  You can generate a URL for authentication using the following method:

``` php
$api->authenticationUrl($redirect, $approvalPrompt = 'auto', $scope = null, $state = null);
```

When a code is returned you must then exchange it for an [access token](http://strava.github.io/api/v3/oauth/#post-token) for the authenticated user:

``` php
$api->tokenExchange($code);
```

Before making any requests you must set the access token as returned from your token exchange or via your own private token from Strava:

``` php
$api->setAccessToken($accessToken);
```

Example Requests
------------

Get the most recent 100 KOMs from any athlete

``` php
$api->get('athletes/:id/koms', ['per_page' => 100]);
```

Post a new activity<sup>2</sup>

``` php
$api->post('activities', [
    'name'             => 'API Test',
    'type'             => 'Ride',
    'start_date_local' => date( 'Y-m-d\TH:i:s\Z'),
    'elapsed_time'     => 3600
]);
```

Update a athlete's weight<sup>2</sup>

``` php
$api->put('athlete', ['weight' => 70]);
```

Delete an activity<sup>2</sup>

``` php
$api->delete('activities/:id');
```

### Notes

**1**. The account you register your app will give you an access token, so you can skip this step if you're just testing endpoints/methods.

**2**. These actions will need the **scope** set to *write* when authenticating a user

---

Releases
---
Latest version **1.3.0**

- Adds possibility to use absolute URL for an endpoint to work with new [webhook functionality](https://developers.strava.com/docs/webhooks/).

Previous version **1.2.2**

- Possibility to access the HTTP response headers
- PHP 7 compatibility
- Basic PHPUnit test cases for Auth URL generation
