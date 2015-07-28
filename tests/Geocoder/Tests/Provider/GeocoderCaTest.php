<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeocoderCa;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderCaTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeocoderCa($this->getMockAdapter($this->never()));
        $this->assertEquals('geocoder_ca', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://geocoder.ca/?geoit=xml&locate=1600+Pennsylvania+Ave%2C+Washington%2C+DC
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeocoderCa($this->getMockAdapter());
        $provider->geocode('1600 Pennsylvania Ave, Washington, DC');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://geocoder.ca/?geoit=xml&locate=foobar
     */
    public function testGetGeocodedDataWithWrongAddress()
    {
        $provider = new GeocoderCa($this->getAdapter());
        $provider->geocode('foobar');
    }

    public function testGetGeocodedDataUsingSSL()
    {
        $provider = new GeocoderCa($this->getAdapter(), true);
        $provider->geocode('1600 Pennsylvania Ave, Washington, DC');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Invalid authentification token https://geocoder.ca/?geoit=xml&locate=foobar&auth=bad-api-key
     */
    public function testGetGeocodedDataWithWrongInvalidApiKey()
    {
        $provider = new GeocoderCa($this->getAdapter(), true, 'bad-api-key');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceededException
     * @expectedExceptionMessage Account ran out of credits https://geocoder.ca/?geoit=xml&locate=foobar&auth=api-key
     */
    public function testGetGeocodedDataRanOutCredits()
    {
        $xml = <<<XML
<geodata>
    <error>
        <code>002</code>
        <description> auth has ran out of credits. (in case you have used over 100 credits over your total balance)</description>
    </error>
</geodata>
XML;
        $provider = new GeocoderCa($this->getMockAdapterReturns($xml), true, 'api-key');
        $provider->geocode('foobar');
    }

    public function testGetGeocodedDataWithRealAddressUS()
    {
        $provider = new GeocoderCa($this->getAdapter());
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

    public function testGetGeocodedDataWithRealAddressCA()
    {
        $provider = new GeocoderCa($this->getAdapter());
        $result   = $provider->geocode('4208 Gallaghers, Kelowna, BC');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(49.831515, $result['latitude'], '', 0.0001);
        $this->assertEquals(-119.381857, $result['longitude'], '', 0.0001);
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
     * @expectedExceptionMessage The GeocoderCa does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeocoderCa($this->getMockAdapter($this->never()));
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderCa does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeocoderCa($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderCa does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new GeocoderCa($this->getAdapter());
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeocoderCa does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new GeocoderCa($this->getAdapter());
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not resolve coordinates 1, 2
     */
    public function testGetReverseDataWithWrongCoordinate()
    {
        $provider = new GeocoderCa($this->getAdapter());
        $provider->reverse(array(1, 2));
    }

    public function testGetReversedDataUsingSSL()
    {
        $provider = new GeocoderCa($this->getAdapter(), true);
        $provider->reverse(array('40.707507', '-74.011255'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Invalid authentification token https://geocoder.ca/?geoit=xml&reverse=1&latt=40.707507&longt=-74.011255&auth=bad-api-key
     */
    public function testGetReversedDataWithWrongInvalidApiKey()
    {
        $provider = new GeocoderCa($this->getAdapter(), true, 'bad-api-key');
        $provider->reverse(array('40.707507', '-74.011255'));
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceededException
     * @expectedExceptionMessage Account ran out of credits https://geocoder.ca/?geoit=xml&reverse=1&latt=40.707507&longt=-74.011255&auth=api-key
     */
    public function testGetReversedDataRanOutCredits()
    {
        $xml = <<<XML
<geodata>
    <error>
        <code>002</code>
        <description> auth has ran out of credits. (in case you have used over 100 credits over your total balance)</description>
    </error>
</geodata>
XML;
        $provider = new GeocoderCa($this->getMockAdapterReturns($xml), true, 'api-key');
        $provider->reverse(array('40.707507', '-74.011255'));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new GeocoderCa($this->getAdapter());
        $result   = $provider->reverse(array('40.707507', '-74.011255'));

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(40.707507, $result['latitude'], '', 0.0001);
        $this->assertEquals(-74.011255, $result['longitude'], '', 0.0001);
        $this->assertEquals(2, $result['streetNumber']);
        $this->assertEquals('New St', $result['streetName']);
        $this->assertEquals(10005, $result['zipcode']);
        $this->assertEquals('New York', $result['city']);
        $this->assertEquals('NY', $result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);

    }
}
