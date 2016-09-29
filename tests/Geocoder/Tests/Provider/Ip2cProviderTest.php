<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\Ip2cProvider;

/**
 * @author Ganeko Guereta <ganeko.guereta@gmail.com>
 */
class Ip2cProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new Ip2cProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('ip2c', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Ip2cProvider does not support street addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new Ip2cProvider($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Ip2cProvider does not support street addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new Ip2cProvider($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Ip2cProvider does not support street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new Ip2cProvider($this->getMockAdapter($this->never()));
        $provider->geocode('Aranbizkarra, Vitoria-Gasteiz');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Ip2cProvider($this->getAdapter());

        $result   = $provider->geocode('127.0.0.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('latitude', $result);
        $this->assertArrayHasKey('longitude', $result);
        $this->assertArrayHasKey('country', $result);
        $this->assertArrayHasKey('countryCode', $result);
        $this->assertArrayHasKey('timezone', $result);

        $this->assertEquals(null, $result['latitude']);
        $this->assertEquals(null, $result['longitude']);

        $this->assertEquals('Reserved', $result['country']);
        $this->assertEquals('ZZ', $result['countryCode']);
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new Ip2cProvider($this->getAdapter());
        $result   = $provider->geocode('8.8.8.8');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('latitude', $result);
        $this->assertArrayHasKey('longitude', $result);
        $this->assertArrayHasKey('country', $result);
        $this->assertArrayHasKey('countryCode', $result);
        $this->assertArrayHasKey('timezone', $result);

        $this->assertEquals(null, $result['latitude']);
        $this->assertEquals(null, $result['longitude']);

        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Ip2cProvider does not support IPv6 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Ip2cProvider($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Ip2cProvider does not support IPv6 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new Ip2cProvider($this->getAdapter());
        $provider->geocode('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Ip2cProvider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new Ip2cProvider($this->getMockAdapter($this->never()));
        $provider->reverse(1,2);
    }
}
