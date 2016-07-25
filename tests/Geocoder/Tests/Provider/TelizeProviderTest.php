<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\TelizeProvider;

/**
 * @author Tudor Matei <tudor@tudormatei.com>
 */
class TelizeBaseProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new TelizeProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('telize', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TelizeProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new TelizeProvider($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TelizeProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new TelizeProvider($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TelizeProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new TelizeProvider($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new TelizeProvider($this->getMockAdapter($this->never()));
        $result   = $provider->geocode('127.0.0.1');

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
        $provider = new TelizeProvider($this->getMockAdapter($this->never()));
        $result = $provider->geocode('::1');

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
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://www.telize.com/geoip/88.188.221.14
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new TelizeProvider($this->getMockAdapterReturns(null));
        $provider->geocode('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://www.telize.com/geoip/88.188.221.14
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new TelizeProvider($this->getMockAdapterReturns(''));
        $provider->geocode('88.188.221.14');
    }

    public function testGetGeocodedDataWithRealIPv4UnitedStates()
    {
        $provider = new TelizeProvider($this->getAdapter());
        $result   = $provider->geocode('74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(33.0347, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('75093', $result['zipcode']);
        $this->assertNull($result['cityDistrict']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('America/Chicago', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv4France()
    {
        $provider = new TelizeProvider($this->getAdapter());
        $result   = $provider->geocode('88.188.221.14');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(45.7797, $result['latitude'], '', 0.0001);
        $this->assertEquals(3.0863, $result['longitude'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Clermont', $result['city']);
        $this->assertEquals('63023', $result['zipcode']);
        $this->assertNull($result['cityDistrict']);
        $this->assertEquals('Auvergne', $result['region']);
        $this->assertEquals('98', $result['regionCode']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
        $this->assertEquals('Europe/Paris', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv6France()
    {
        $provider = new TelizeProvider($this->getAdapter());
        $result   = $provider->geocode('::ffff:88.188.221.14');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(45.7797, $result['latitude'], '', 0.0001);
        $this->assertEquals(3.0863, $result['longitude'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Clermont', $result['city']);
        $this->assertEquals('63023', $result['zipcode']);
        $this->assertNull($result['cityDistrict']);
        $this->assertEquals('Auvergne', $result['region']);
        $this->assertEquals('98', $result['regionCode']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
        $this->assertEquals('Europe/Paris', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TelizeProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new TelizeProvider($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }
}
