<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeocoderUsProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderUsProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('geocoder_us', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for given query: http://geocoding.geo.census.gov/geocoder/locations/onelineaddress?format=json&benchmark=Public_AR_Current&address=1600+Pennsylvania+Ave%2C+Washington%2C+DC
     */
    public function testgeocodeWithAddress()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter());
        $provider->geocode('1600 Pennsylvania Ave, Washington, DC');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for given query: http://geocoding.geo.census.gov/geocoder/locations/onelineaddress?format=json&benchmark=Public_AR_Current&address=foobar
     */
    public function testgeocodeWithWrongAddress()
    {
        $provider = new GeocoderUsProvider($this->getAdapter());
        $provider->geocode('foobar');
    }

    public function testgeocodeWithRealAddress()
    {
        $provider = new GeocoderUsProvider($this->getAdapter());
        $result   = $provider->geocode('1600 Pennsylvania Ave, Washington, DC');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(38.898748, $result['latitude'], '', 0.0001);
        $this->assertEquals(-77.0353, $result['longitude'], '', 0.0001);
        $this->assertSame(array('south' => null, 'west' => null, 'north' => null, 'east' => null), $result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('PENNSYLVANIA', $result['streetName']);
        $this->assertEquals('20502', $result['zipcode']);
        $this->assertEquals('WASHINGTON', $result['city']);
        $this->assertNull($result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUsProvider does not support IP addresses.
     */
    public function testgeocodeWithLocalhostIPv4()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter($this->never()));
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUsProvider does not support IP addresses.
     */
    public function testgeocodeWithLocalhostIPv6()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUsProvider does not support IP addresses.
     */
    public function testgeocodeWithIPv4()
    {
        $provider = new GeocoderUsProvider($this->getAdapter());
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUsProvider does not support IP addresses.
     */
    public function testgeocodeWithIPv6()
    {
        $provider = new GeocoderUsProvider($this->getAdapter());
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUsProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }
}
