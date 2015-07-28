<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\Baidu;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class BaiduTest extends TestCase
{
    public function testGetName()
    {
        $provider = new Baidu($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('baidu', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new Baidu($this->getMockAdapter($this->never()), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://api.map.baidu.com/geocoder?output=json&key=api_key&address=
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new Baidu($this->getMockAdapter(), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://api.map.baidu.com/geocoder?output=json&key=api_key&address=
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new Baidu($this->getMockAdapter(), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage ould not execute query http://api.map.baidu.com/geocoder?output=json&key=api_key&address=%E7%99%BE%E5%BA%A6%E5%A4%A7%E5%8E%A6
     */
    public function testGetGeocodedDataWithAddressContentReturnNull()
    {
        $provider = new Baidu($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('百度大厦'); // Baidu Building
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage ould not execute query http://api.map.baidu.com/geocoder?output=json&key=api_key&address=%E7%99%BE%E5%BA%A6%E5%A4%A7%E5%8E%A6
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new Baidu($this->getMockAdapter(), 'api_key');
        $provider->geocode('百度大厦'); // Baidu Building
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Baidu provider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new Baidu($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Baidu provider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new Baidu($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['BAIDU_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BAIDU_API_KEY value in phpunit.xml');
        }

        $provider = new Baidu($this->getAdapter(), $_SERVER['BAIDU_API_KEY'], 'fr-FR');
        $result   = $provider->geocode('上地十街10号 北京市'); // Beijing

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertEquals(40.056885, $result['latitude'], '', 0.01);
        $this->assertEquals(116.30815, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://api.map.baidu.com/geocoder?output=json&key=api_key&location=1.000000,2.000000
     */
    public function testGetReversedData()
    {
        $provider = new Baidu($this->getMockAdapter(), 'api_key');
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No API Key provided
     */
    public function testGetReversedDataWithoutApiKey()
    {
        $provider = new Baidu($this->getMockAdapter($this->never()), null);
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://api.map.baidu.com/geocoder?output=json&key=api_key&location=39.983424,116.322987
     */
    public function testGetReversedDataWithCoordinatesContentReturnNull()
    {
        $provider = new Baidu($this->getMockAdapterReturns(null), 'api_key');
        $provider->reverse(39.983424, 116.322987);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['BAIDU_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BAIDU_API_KEY value in phpunit.xml');
        }

        $provider = new Baidu($this->getAdapter(), $_SERVER['BAIDU_API_KEY']);
        $result   = $provider->reverse(39.983424, 116.322987);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertEquals(39.983424, $result['latitude'], '', 0.01);
        $this->assertEquals(116.322987, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertEquals('27号1101-08室', $result['streetNumber']);
        $this->assertEquals('中关村大街', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('北京市', $result['city']);
        $this->assertEquals('海淀区', $result['cityDistrict']);
        $this->assertEquals('北京市', $result['county']);
        $this->assertEquals(131, $result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Baidu provider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['BAIDU_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BAIDU_API_KEY value in phpunit.xml');
        }

        $provider = new Baidu($this->getAdapter(), $_SERVER['BAIDU_API_KEY']);
        $provider->geocode('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Baidu provider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        if (!isset($_SERVER['BAIDU_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BAIDU_API_KEY value in phpunit.xml');
        }

        $provider = new Baidu($this->getAdapter(), $_SERVER['BAIDU_API_KEY']);
        $provider->geocode('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testInvalidCredential()
    {
        $json = <<<JSON
{
    "status":"INVALID_KEY",
    "result":[ ]
}
JSON;

        $provider = new Baidu($this->getMockAdapterReturns($json), 'api_key');
        $provider->geocode('百度大厦'); // Baidu Building
    }
}
