<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\IpInfoProvider;

class IpInfoTest extends TestCase
{
    public function testGetName()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('ip_info', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('127.0.0.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('::1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://ipinfo.io/74.200.247.59/json
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new IpInfoProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://ipinfo.io/74.200.247.59/json
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new IpInfoProvider($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
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
        $result = $provider->getGeocodedData('74.200.247.59');

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

    public function testGetGeocodedDataWithRealIPv6()
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
        $result = $provider->getGeocodedData('::ffff:74.200.247.59');

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
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://ipinfo.io/255.255.255.255/json
     */
    public function testGetGeocodedDataWithoutLocation()
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
        $provider->getGeocodedData('255.255.255.255');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://ipinfo.io/::ffff:74.200.247.59/json
     */
    public function testGetGeocodedDataWithRealIPv6GetsNullContent()
    {
        $provider = new IpInfoProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new IpInfoProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
