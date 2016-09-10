<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\IpGeoBaseProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class IpGeoBaseProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('ip_geo_base', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpGeoBaseProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpGeoBaseProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpGeoBaseProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $result   = $provider->geocode('127.0.0.1');

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
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpGeoBaseProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $result = $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://ipgeobase.ru:7020/geo?ip=88.188.221.14
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapterReturns(null));
        $provider->geocode('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://ipgeobase.ru:7020/geo?ip=88.188.221.14
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapterReturns(''));
        $provider->geocode('88.188.221.14');
    }

    public function testGetGeocodedDataWithRealIPv4Moscow()
    {
        $provider = new IpGeoBaseProvider($this->getAdapter());
        $result   = $provider->geocode('144.206.192.6');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(55.755787, $result['latitude'], '', 0.001);
        $this->assertEquals(37.617634, $result['longitude'], '', 0.001);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Москва', $result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Москва', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertEquals('RU', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv4Kiev()
    {
        $provider = new IpGeoBaseProvider($this->getAdapter());
        $result   = $provider->geocode('46.118.0.12');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(50.450001, $result['latitude'], '', 0.001);
        $this->assertEquals(30.523333, $result['longitude'], '', 0.001);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Киев', $result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Киев', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertEquals('UA', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpGeoBaseProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new IpGeoBaseProvider($this->getAdapter());
        $provider->geocode('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpGeoBaseProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }
}
