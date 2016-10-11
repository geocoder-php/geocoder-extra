<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\What3wordsProvider;

class What3wordsProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new What3wordsProvider($this->getMockAdapter($this->never()), 'api-key');
        $this->assertEquals('what3words', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGeocodeWithNullApiKey()
    {
        $provider = new What3wordsProvider($this->getMockAdapter($this->never()), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     */
    public function testGeocodeWithAddress()
    {
        $provider = new What3wordsProvider($this->getMockAdapter(), 'api-key');
        $provider->geocode('bar');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testGetInvalidCredentials()
    {
        $json = '{"code":2,"message":"Authentication failed; invalid API key"}';

        $provider = new What3wordsProvider($this->getMockAdapterReturns($json), 'api-key');
        $results = $provider->geocode('baz');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query: https://api.what3words.com/v2/forward?addr=foobar&key=api_key
     */
    public function testGeocode()
    {
        $provider = new What3wordsProvider($this->getMockAdapterReturns('{}'), 'api_key');
        $provider->geocode('foobar');
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['WHAT3WORDS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the WHAT3WORDS_API_KEY value in phpunit.xml');
        }

        $provider = new What3wordsProvider($this->getAdapter(), $_SERVER['WHAT3WORDS_API_KEY']);
        $results = $provider->geocode('index.home.raft');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals(51.521250999999999, $result['latitude'], 0.01);
        $this->assertEquals(-0.20358599999999999, $result['longitude'], 0.01);
        $this->assertEquals(51.521237999999997, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(-0.20360700000000001, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(51.521265, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(-0.20356399999999999, $result['bounds']['east'], '', 0.01);
        $this->assertSame('index.home.raft', $result['locality']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     */
    public function testReverse()
    {
        if (!isset($_SERVER['WHAT3WORDS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the WHAT3WORDS_API_KEY value in phpunit.xml');
        }

        $provider = new What3wordsProvider($this->getMockAdapter(), $_SERVER['WHAT3WORDS_API_KEY']);
        $provider->reverse(1, 2);
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['WHAT3WORDS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the WHAT3WORDS_API_KEY value in phpunit.xml');
        }

        $provider = new What3wordsProvider($this->getAdapter(), $_SERVER['WHAT3WORDS_API_KEY']);
        $results = $provider->reverse(51.426787, -0.331321);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals(51.426786999999997, $result['latitude'], 0.01);
        $this->assertEquals(-0.33132099999999998, $result['longitude'], 0.01);
        $this->assertEquals(51.426772999999997, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(-0.331343, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(51.4268, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(-0.33129999999999998, $result['bounds']['east'], '', 0.01);
        $this->assertSame('spoken.land.complains', $result['locality']);
    }
}
