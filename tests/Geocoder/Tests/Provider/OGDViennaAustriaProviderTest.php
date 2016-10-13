<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\OGDViennaAustriaProvider;

/**
 * @author Robert Harm <www.harm.co.at>
 * Data source: City of Vienna, http://data.wien.gv.at
 */
class OGDViennaAustriaProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('ogd_vienna_austria', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query https://data.wien.gv.at/daten/OGDAddressService.svc/GetAddressInfo?CRS=EPSG:4326&Address=Stephansplatz
     */
    public function testGeocodeWithAddress()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter());
        $provider->geocode('Stephansplatz');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query https://data.wien.gv.at/daten/OGDAddressService.svc/GetAddressInfo?CRS=EPSG:4326&Address=yyyyyyy
     */
    public function testGeocodeWithWrongAddress()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $provider->geocode('yyyyyyy');
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $result   = $provider->geocode('Stephansplatz');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.208583576583, $result['latitude'], '', 0.0001);
        $this->assertEquals(16.373089928434, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertEquals('Stephansplatz', $result['streetName']);
        $this->assertEmpty($result['zipcode']);
        $this->assertEquals('Wien', $result['city']);
        $this->assertEquals('Vienna', $result['region']);
        $this->assertEquals('Vienna', $result['regionCode']);
        $this->assertEquals('AT', $result['countryCode']);
        $this->assertEquals('Europe/Vienna', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OGDViennaAustria provider does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter($this->never()));
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OGDViennaAustria provider does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OGDViennaAustria provider does not support IP addresses.
     */
    public function testGeocodeWithIPv4()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OGDViennaAustria provider does not support IP addresses.
     */
    public function testGeocodeWithIPv6()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $provider->geocode('::ffff:74.200.247.59');
    }

    public function testReverse()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $result = $provider->reverse(48.230149, 16.350108);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.230149, $result['latitude'], '', 0.0001);
        $this->assertEquals(16.350108, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertEquals('Währinger Gürtel', $result['streetName']);
        $this->assertEquals('121', $result['streetNumber']);
        $this->assertEquals('1180', $result['zipcode']);
        $this->assertEquals('Wien', $result['city']);
        $this->assertEquals('Vienna', $result['region']);
        $this->assertEquals('Vienna', $result['regionCode']);
        $this->assertEquals('AT', $result['countryCode']);
        $this->assertEquals('Europe/Vienna', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Result distance to far away
     */
    public function testReverseWithInvalidCoordinates()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $provider->reverse(16.35, 48.23);
    }
}
