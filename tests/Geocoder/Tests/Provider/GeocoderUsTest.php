<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeocoderUs;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderUsTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeocoderUs($this->getMockAdapter($this->never()));
        $this->assertEquals('geocoder_us', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://geocoder.us/service/rest/?address=1600+Pennsylvania+Ave%2C+Washington%2C+DC
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeocoderUs($this->getMockAdapter());
        $provider->geocode('1600 Pennsylvania Ave, Washington, DC');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://geocoder.us/service/rest/?address=foobar
     */
    public function testGetGeocodedDataWithWrongAddress()
    {
        $provider = new GeocoderUs($this->getAdapter());
        $provider->geocode('foobar');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new GeocoderUs($this->getAdapter());
        $result   = $provider->geocode('1600 Pennsylvania Ave, Washington, DC');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(38.898748, $result['latitude'], '', 0.0001);
        $this->assertEquals(-77.037684, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUs does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeocoderUs($this->getMockAdapter($this->never()));
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUs does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeocoderUs($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUs does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new GeocoderUs($this->getAdapter());
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUs does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new GeocoderUs($this->getAdapter());
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderUs provider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeocoderUs($this->getMockAdapter($this->never()));
        $provider->reverse(array(1, 2));
    }
}
