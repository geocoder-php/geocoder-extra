<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\NaverProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class NaverProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new NaverProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('naver', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new NaverProvider($this->getMockAdapter($this->never()), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://openapi.map.naver.com/api/geocode.php?key=api_key&encoding=utf-8&coord=latlng&query=%EC%84%9C%EC%9A%B8
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new NaverProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('서울');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://openapi.map.naver.com/api/geocode.php?key=api_key&encoding=utf-8&coord=latlng&query=foobar
     */
    public function testGetGeocodedDataWithAddressGetsZeroResult()
    {
        $xml = <<<XML
<geocode><userquery>foobar</userquery><total>0</total></geocode>
XML;

        $provider = new NaverProvider($this->getMockAdapterReturns($xml), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    public function testGetGeocodedDataWithAddressGetsOneResult()
    {
        $xml = <<<XML
<geocode>
    <userquery>경북 영천시 임고면 매호리 143-9번지</userquery>
    <total>1</total>
    <item>
        <point>
            <x>128.9675615</x>
            <y>36.0062826</y>
        </point>
        <address>경상북도 영천시 임고면 매호리 143-9</address>
        <addrdetail>
            <sido>
                경상북도
                <sigugun>
                    영천시 임고면
                    <dongmyun>
                        매호리
                        <rest>143-9</rest>
                    </dongmyun>
                </sigugun>
            </sido>
        </addrdetail>
    </item>
</geocode>
XML;

        $provider = new NaverProvider($this->getMockAdapterReturns($xml), 'api_key');
        $results  = $provider->getGeocodedData('경북 영천시 임고면 매호리 143-9번지');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(128.9675615, $result['latitude'], '', 0.01);
        $this->assertEquals(36.0062826, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);

        $this->assertEquals('143-9', $result['streetNumber']);
        $this->assertEquals('매호리', $result['streetName']);
        $this->assertEquals('영천시 임고면', $result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertEquals('경상북도', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['NAVER_API_KEY'])) {
            $this->markTestSkipped('You need to configure the NAVER_API_KEY value in phpunit.xml');
        }

        $provider = new NaverProvider($this->getAdapter(), $_SERVER['NAVER_API_KEY']);
        $results  = $provider->getGeocodedData('경북 영천시 임고면 매호리 143-9번지');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(128.9675615, $result['latitude'], '', 0.01);
        $this->assertEquals(36.0062826, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);

        $this->assertEquals('143-9', $result['streetNumber']);
        $this->assertEquals('매호리', $result['streetName']);
        $this->assertEquals('영천시 임고면', $result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertEquals('경상북도', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The NaverProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new NaverProvider($this->getAdapter(), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The NaverProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new NaverProvider($this->getAdapter(), 'api_key');
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The NaverProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new NaverProvider($this->getAdapter(), 'api_key');
        $provider->getReversedData(array(1, 2));
    }
}
