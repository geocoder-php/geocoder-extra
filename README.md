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

* [OGD Vienna](https://open.wien.at/site/datensatz/?id=c223b93a-2634-4f06-ac73-8709b9e16888) as Address-Based geocoding provider (exclusively in Vienna / Austria);
* [Naver](http://developer.naver.com/wiki/pages/SrchAPI) as Address-Base geocoding provider (exclusively in South Korea);
* [Geocodio](http://geocod.io/) as Address-Based geocoding and reverse geocoding provider (exclusively in USA);
* [IpInfo](http://ipinfo.io/developers) as IP-Based geocoding provider;
* [IpGeoBase local](http://ipgeobase.ru/cgi-bin/Archive.cgi) as IP-Based geocoding provider with local-stored geobase
 files;
* [Here](http://developer.here.com/rest-apis/documentation/geocoder/topics/overview.html) as Address-Based geocoding and reverse geocoding provider.
* [Telize](http://www.telize.com) as IP-Based geocoding provider;


Contributing
------------

See [Geocoder's
CONTRIBUTING](https://github.com/geocoder-php/Geocoder/blob/master/CONTRIBUTING.md)
file.


License
-------

geocoder-extra is released under the MIT License. See the bundled LICENSE file
for details.
