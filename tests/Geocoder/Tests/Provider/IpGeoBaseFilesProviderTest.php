<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\IpGeoBaseFilesProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class IpGeoBaseFilesProviderTest extends TestCase
{
	protected $cidrFile, $cityFile;

	public function setUp()
	{
		$this->cidrFile = __DIR__ . '/fixtures/cidr_optim.txt';
		$this->cityFile = __DIR__ . '/fixtures/cities.txt';
	}


	public function testGetName()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$this->assertEquals('ip_geo_base_files', $provider->getName());
	}

	/**
	 * @expectedException \Geocoder\Exception\InvalidArgumentException
	 * @expectedExceptionMessage Given IpGeoBase CIDR file "not_exist.dat" does not exist.
	 */
	public function testThrowIfNotExistCidrFileGiven()
	{
		new IpGeoBaseFilesProvider('not_exist.dat', $this->cityFile);
	}

	/**
	 * @expectedException \Geocoder\Exception\InvalidArgumentException
	 * @expectedExceptionMessage Given IpGeoBase City file "not_exist.dat" does not exist.
	 */
	public function testThrowIfNotExistCityFileGiven()
	{
		new IpGeoBaseFilesProvider($this->cidrFile, 'not_exist.dat');
	}

	/**
	 * @expectedException \Geocoder\Exception\UnsupportedException
	 * @expectedExceptionMessage The IpGeoBaseFilesProvider does not support Street addresses.
	 */
	public function testGetGeocodedDataWithNull()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$provider->getGeocodedData(null);
	}

	/**
	 * @expectedException \Geocoder\Exception\UnsupportedException
	 * @expectedExceptionMessage The IpGeoBaseFilesProvider does not support Street addresses.
	 */
	public function testGetGeocodedDataWithEmpty()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$provider->getGeocodedData('');
	}

	/**
	 * @expectedException \Geocoder\Exception\UnsupportedException
	 * @expectedExceptionMessage The IpGeoBaseFilesProvider does not support Street addresses.
	 */
	public function testGetGeocodedDataWithAddress()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$provider->getGeocodedData('10 avenue Gambetta, Paris, France');
	}

	public function testGetGeocodedDataWithLocalhostIPv4()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$result   = $provider->getGeocodedData('127.0.0.1');

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
	 * @expectedException \Geocoder\Exception\UnsupportedException
	 * @expectedExceptionMessage The IpGeoBaseFilesProvider does not support IPv6 addresses.
	 */
	public function testGetGeocodedDataWithLocalhostIPv6()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$result = $provider->getGeocodedData('::1');
	}

	/**
	 * @expectedException \Geocoder\Exception\NoResultException
	 * @expectedExceptionMessage Could not find IP 2.16.1.0
	 */
	public function testGetGeocodedDataWithRealIPv4GetsNullContent()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$provider->getGeocodedData('2.16.1.0');
	}

	public function testGetGeocodedDataWithRealIPv4Moscow()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$result   = $provider->getGeocodedData('2.17.20.1');

		$this->assertInternalType('array', $result);
		$this->assertCount(1, $result);

		$result = $result[0];
		$this->assertInternalType('array', $result);
		$this->assertNull($result['latitude']);
		$this->assertNull($result['longitude']);
		$this->assertNull($result['streetNumber']);
		$this->assertNull($result['streetName']);
		$this->assertNull($result['city']);
		$this->assertNull($result['zipcode']);
		$this->assertNull($result['region']);
		$this->assertNull($result['regionCode']);
		$this->assertNull($result['country']);
		$this->assertEquals('DE', $result['countryCode']);
		$this->assertNull($result['timezone']);
	}

	public function testGetGeocodedDataWithRealIPv4Kiev()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$result   = $provider->getGeocodedData('213.197.73.96');

		$this->assertInternalType('array', $result);
		$this->assertCount(1, $result);

		$result = $result[0];
		$this->assertInternalType('array', $result);
		$this->assertEquals(50.450001, $result['latitude'], '', 0.001);
		$this->assertEquals(30.523333, $result['longitude'], '', 0.001);
		$this->assertNull($result['streetNumber']);
		$this->assertNull($result['streetName']);
		$this->assertEquals('Киев', $result['city']);
		$this->assertNull($result['zipcode']);
		$this->assertEquals('Киев', $result['region']);
		$this->assertNull($result['regionCode']);
		$this->assertNull($result['country']);
		$this->assertEquals('UA', $result['countryCode']);
		$this->assertNull($result['timezone']);
	}

	public function testGetGeocodedDataWithoutCity()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$result   = $provider->getGeocodedData('213.197.73.96');

		$this->assertInternalType('array', $result);
		$this->assertCount(1, $result);

		$result = $result[0];
		$this->assertInternalType('array', $result);
		$this->assertEquals(50.450001, $result['latitude'], '', 0.001);
		$this->assertEquals(30.523333, $result['longitude'], '', 0.001);
		$this->assertNull($result['streetNumber']);
		$this->assertNull($result['streetName']);
		$this->assertEquals('Киев', $result['city']);
		$this->assertNull($result['zipcode']);
		$this->assertEquals('Киев', $result['region']);
		$this->assertNull($result['regionCode']);
		$this->assertNull($result['country']);
		$this->assertEquals('UA', $result['countryCode']);
		$this->assertNull($result['timezone']);
	}

	/**
	 * @expectedException \Geocoder\Exception\UnsupportedException
	 * @expectedExceptionMessage The IpGeoBaseFilesProvider does not support IPv6 addresses.
	 */
	public function testGetGeocodedDataWithRealIPv6()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$provider->getGeocodedData('::ffff:88.188.221.14');
	}

	/**
	 * @expectedException \Geocoder\Exception\UnsupportedException
	 * @expectedExceptionMessage The IpGeoBaseFilesProvider is not able to do reverse geocoding.
	 */
	public function testGetReverseData()
	{
		$provider = new IpGeoBaseFilesProvider($this->cidrFile, $this->cityFile);
		$provider->getReversedData(array(1, 2));
	}
}
