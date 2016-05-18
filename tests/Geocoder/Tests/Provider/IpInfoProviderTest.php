<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\IpInfoProvider;

class IpInfoProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('ip_info', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfo provider does not support Street addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfo provider does not support Street addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfo provider does not support Street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $result = $provider->geocode('127.0.0.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);
        $this->assertArrayNotHasKey('city', $result);
        $this->assertArrayNotHasKey('region', $result);
        $this->assertArrayNotHasKey('county', $result);

        $this->assertEquals('localhost', $result['locality']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $result = $provider->geocode('::1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);
        $this->assertArrayNotHasKey('city', $result);
        $this->assertArrayNotHasKey('region', $result);
        $this->assertArrayNotHasKey('county', $result);

        $this->assertEquals('localhost', $result['locality']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://ipinfo.io/74.200.247.59/json
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new IpInfoProvider($this->getMockAdapterReturns(null));
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://ipinfo.io/74.200.247.59/json
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new IpInfoProvider($this->getMockAdapterReturns(''));
        $provider->geocode('74.200.247.59');
    }

    public function testGeocodeWithRealIPv4()
    {
        $json = <<<JSON
{
    "ip": "74.200.247.59",
    "hostname": "wordpress.com",
    "city": "Plano",
    "region": "Texas",
    "country": "US",
    "loc": "33.0347,-96.8134",
    "org": "AS22576 Layered Technologies, Inc.",
    "postal": "75093"
}
JSON;

        $provider = new IpInfoProvider($this->getMockAdapterReturns($json));
        $result = $provider->geocode('74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(33.0347, $result['latitude'], '', 0.01);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.01);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('US', $result['countryCode']);
    }

    public function testGeocodeWithRealIPv6()
    {
        $json = <<<JSON
{
    "ip": "74.200.247.59",
    "hostname": "No Hostname",
    "city": "Plano",
    "region": "Texas",
    "country": "US",
    "loc": "33.0347,-96.8134",
    "org": "AS22576 Layered Technologies, Inc.",
    "postal": "75093"
}
JSON;

        $provider = new IpInfoProvider($this->getMockAdapterReturns($json));
        $result = $provider->geocode('::ffff:74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(33.0347, $result['latitude'], '', 0.01);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.01);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('US', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://ipinfo.io/255.255.255.255/json
     */
    public function testGeocodeWithoutLocation()
    {
        $json = <<<JSON
{
    "ip": "255.255.255.255",
    "hostname": "No Hostname",
    "loc": "",
    "bogon": true
}
JSON;

        $provider = new IpInfoProvider($this->getMockAdapterReturns($json));
        $provider->geocode('255.255.255.255');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://ipinfo.io/::ffff:74.200.247.59/json
     */
    public function testGeocodeWithRealIPv6GetsNullContent()
    {
        $provider = new IpInfoProvider($this->getMockAdapterReturns(null));
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfo provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }
}
