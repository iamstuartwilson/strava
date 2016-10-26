# Change Log

All notable changes to `iamstuartwilson/strava` will be documented in this file.
Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [1.2.2] - 2016-10-26

### Added

* MIT LICENSE file added to project root

## [1.2.1] - 2016-10-04

### Changed

* `CURLOPT_FOLLOWLOCATION` is no longer set to `true`

## [1.2.0] - 2016-07-12

### Added

* It's now possible to access the HTTP response headers with two added methods:

  - `getResponseHeaders()`
  - `getResponseHeader($header)`

  The first one returns all HTTP headers as an array while the second returns
  the header value for the given header name (and throws an exception if the
  header name does not exist).

  The existing public API of the StravaAPI class is unchanged, only two new
  public methods were introduced.

## [1.1.2] - 2016-05-28

### Fixed

* The exception for a failed API request could not be thrown because of a missing
  namespace declaration.
