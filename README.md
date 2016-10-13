Geocoder Extra
==============

[![Build
Status](https://travis-ci.org/geocoder-php/geocoder-extra.png?branch=master)](https://travis-ci.org/geocoder-php/geocoder-extra)
[![Latest Stable
Version](https://poser.pugx.org/geocoder-php/geocoder-extra/v/stable.png)](https://packagist.org/packages/geocoder-php/geocoder-extra)

This repository hosts Geocoder extra features that do not belong to the core
but can be nonetheless interesting to share with other developers. It mostly
contains **providers**.


Installation
------------

The recommended way to install this package is through
[Composer](http://getcomposer.org/):

``` json
{
    "require": {
        "geocoder-php/geocoder-extra": "@stable"
    }
}
```

**Protip:** you should browse the
[`geocoder-php/geocoder-extra`](https://packagist.org/packages/geocoder-php/geocoder-extra)
page to choose a stable version to use, avoid the `@stable` meta constraint.


Usage
-----

Please, read the [Geocoder's documentation](http://geocoder-php.org/Geocoder/).

### Providers

| Provider       | Address? | IPv4? | IPv6? | Reverse?  | SSL Support?      | Coverage  | Terms |
|:-------------- |----------|-------|-------|-----------|-------------------|:--------- |:----- |
| [OGD Vienna](https://open.wien.at/site/datensatz/?id=c223b93a-2634-4f06-ac73-8709b9e16888) | yes | no | no | yes | yes | Vienna / Austria | |
| [Naver](http://developer.naver.com/wiki/pages/SrchAPI) | yes | no | no | no | no | South Korea | |
| [Geocodio](http://geocod.io/) | yes | no | no | yes | no | USA | |
| [IpInfo](http://ipinfo.io/developers) | no | yes | yes | no | no | |
| [Here](http://developer.here.com/rest-apis/documentation/geocoder/topics/overview.html) | yes | no | no | yes | no | |
| [IpGeoBase](http://ipgeobase.ru/) | no | yes | no | yes | no | Russia | |
| [Baidu](http://developer.baidu.com/map/geocoding-api.htm) | yes | no | no | yes | no | China | API key required |
| [DataScienceToolkit](http://www.datasciencetoolkit.org/) | yes | yes | no | no | no | USA, Canada | |
| [GeoCoder.ca](http://geocoder.ca/) | yes | no | no | yes | yes | USA, Canada | Optional API key can be provided. $1 CAD for 400 lookups |
| [GeoCoder.us](http://geocoder.us/) | yes | no | no | no | no | USA | Free throttled service. $50 USD for 20000 requests for paid service |
| [OIORest](http://geo.oiorest.dk/) | yes | no | no | yes | no | Denmark | |
| [IGN OpenLS](http://api.ign.fr/accueil) | yes | no | no | no | no | France | API key required |
| [what3words](https://docs.what3words.com/api/v2/) | yes (3 word address only) | no | no | yes | yes | Global | API key required |
| [ip2c](http://about.ip2c.org/) | no | yes | no | no | no | | | |


Contributing
------------

See [Geocoder's
CONTRIBUTING](https://github.com/geocoder-php/Geocoder/blob/master/CONTRIBUTING.md)
file.


Unit Tests
----------

To run unit tests, you'll need `cURL` and a set of dependencies you can install
using Composer:

```
composer install --dev
```

Once installed, run the following command:

```
phpunit
```

You'll obtain some _skipped_ unit tests due to the need of API keys.

Rename the `phpunit.xml.dist` file to `phpunit.xml`, then uncomment the
following lines and add your own API keys:

``` xml
<php>
    <!-- <server name="BAIDU_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="IGN_WEB_API_KEY" value="YOUR_API_KEY" /> -->
</php>
```

You're done!


Contributor Code of Conduct
---------------------------

Please note that this project is released with a Contributor Code of Conduct.
By participating in this project you agree to abide by its terms.


License
-------

geocoder-extra is released under the MIT License. See the bundled LICENSE file
for details.
