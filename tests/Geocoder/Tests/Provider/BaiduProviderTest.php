<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\BaiduProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class BaiduProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new BaiduProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('baidu', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGeocodeWithNullApiKey()
    {
        $provider = new BaiduProvider($this->getMockAdapter($this->never()), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://api.map.baidu.com/geocoder/v2/?output=json&pois=0&ak=api_key&address=
     */
    public function testGeocodeWithNull()
    {
        $provider = new BaiduProvider($this->getMockAdapter(), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://api.map.baidu.com/geocoder/v2/?output=json&pois=0&ak=api_key&address=
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new BaiduProvider($this->getMockAdapter(), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage ould not execute query http://api.map.baidu.com/geocoder/v2/?output=json&pois=0&ak=api_key&address=%E7%99%BE%E5%BA%A6%E5%A4%A7%E5%8E%A6
     */
    public function testGeocodeWithAddressContentReturnNull()
    {
        $provider = new BaiduProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('百度大厦'); // Baidu Building
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage ould not execute query http://api.map.baidu.com/geocoder/v2/?output=json&pois=0&ak=api_key&address=%E7%99%BE%E5%BA%A6%E5%A4%A7%E5%8E%A6
     */
    public function testGeocodeWithAddress()
    {
        $provider = new BaiduProvider($this->getMockAdapter(), 'api_key');
        $provider->geocode('百度大厦'); // Baidu Building
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Baidu provider does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new BaiduProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Baidu provider does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new BaiduProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['BAIDU_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BAIDU_API_KEY value in phpunit.xml');
        }

        $provider = new BaiduProvider($this->getAdapter(), $_SERVER['BAIDU_API_KEY'], 'fr-FR');
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
     * @expectedExceptionMessage Could not execute query http://api.map.baidu.com/geocoder/v2/?output=json&pois=0&ak=api_key&location=1.000000,2.000000
     */
    public function testReverse()
    {
        $provider = new BaiduProvider($this->getMockAdapter(), 'api_key');
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No API Key provided
     */
    public function testReverseWithoutApiKey()
    {
        $provider = new BaiduProvider($this->getMockAdapter($this->never()), null);
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://api.map.baidu.com/geocoder/v2/?output=json&pois=0&ak=api_key&location=39.983424,116.322987
     */
    public function testReverseWithCoordinatesContentReturnNull()
    {
        $provider = new BaiduProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->reverse(39.983424, 116.322987);
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['BAIDU_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BAIDU_API_KEY value in phpunit.xml');
        }

        $provider = new BaiduProvider($this->getAdapter(), $_SERVER['BAIDU_API_KEY']);
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
    public function testGeocodeWithRealIPv4()
    {
        if (!isset($_SERVER['BAIDU_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BAIDU_API_KEY value in phpunit.xml');
        }

        $provider = new BaiduProvider($this->getAdapter(), $_SERVER['BAIDU_API_KEY']);
        $provider->geocode('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Baidu provider does not support IP addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['BAIDU_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BAIDU_API_KEY value in phpunit.xml');
        }

        $provider = new BaiduProvider($this->getAdapter(), $_SERVER['BAIDU_API_KEY']);
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

        $provider = new BaiduProvider($this->getMockAdapterReturns($json), 'api_key');
        $provider->geocode('百度大厦'); // Baidu Building
    }
}
