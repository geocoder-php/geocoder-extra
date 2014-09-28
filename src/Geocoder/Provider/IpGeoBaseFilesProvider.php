<?php
namespace Geocoder\Provider;

use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;
use Geocoder\Exception\InvalidArgumentException;

/**
 * Class IpGeoBaseFilesProvider
 * Provides local access to IpGeoBase data files (http://ipgeobase.ru/)
 *
 * @package Geocoder\Provider
 * @author Shkarupa Alex <shkarupa.alex@gmail.com>
 */
class IpGeoBaseFilesProvider extends AbstractProvider implements ProviderInterface
{
	/**
	 * @var string
	 */
	protected $cidrFile, $cityFile;

	public function __construct($cidrFile, $cityFile)
	{
		if (false === is_file($cidrFile))
		{
			throw new InvalidArgumentException(sprintf('Given IpGeoBase CIDR file "%s" does not exist.', $cidrFile));
		}

		if (false === is_readable($cidrFile))
		{
			throw new InvalidArgumentException(sprintf('Given IpGeoBase CIDR file "%s" does not readable.', $cidrFile));
		}

		$this->cidrFile = $cidrFile;

		if (false === is_file($cityFile))
		{
			throw new InvalidArgumentException(sprintf('Given IpGeoBase City file "%s" does not exist.', $cityFile));
		}

		if (false === is_readable($cityFile))
		{
			throw new InvalidArgumentException(sprintf('Given IpGeoBase City file "%s" does not readable.', $cityFile));
		}

		$this->cityFile = $cityFile;
	}

	/**
	 * Compares line of 'cidr_optim.txt' file to long ip number
	 *
	 * @param string $line
	 * @param string $value
	 * @return int
	 * @see FileBinaryLineSearch::compare()
	 */
	public static function compareCIDR($line, $value)
	{
		$value = intval($value);
		$record = explode("\t", trim($line));

		if ($value < intval($record[0]))
		{
			return 1;
		}
		elseif (intval($record[1] < $value))
		{
			return -1;
		}

		return 0;
	}

	/**
	 * Compares line of 'cities.txt' file to city ID
	 *
	 * @param string $line
	 * @param string $value
	 * @return int
	 * @see FileBinaryLineSearch::compare()
	 */
	public static function compareCities($line, $value)
	{
		$record = explode("\t", trim($line));

		return intval($record[0]) - intval($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getGeocodedData($address)
	{
		if (!filter_var($address, FILTER_VALIDATE_IP))
		{
			throw new UnsupportedException('The IpGeoBaseFilesProvider does not support Street addresses.');
		}

		// This API does not support IPv6
		if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
		{
			throw new UnsupportedException('The IpGeoBaseFilesProvider does not support IPv6 addresses.');
		}

		if ('127.0.0.1' === $address)
		{
			return array($this->getLocalhostDefaults());
		}

		$ip = sprintf('%u', ip2long($address));
		$cidrSearcher = new FileBinaryLineSearch($this->cidrFile,
			'Geocoder\Provider\IpGeoBaseFilesProvider::compareCIDR');
		if ($cidrLine = $cidrSearcher->search($ip))
		{
			$cidrRecord = explode("\t", trim($cidrLine));

			$cidrResult = array('countryCode' => (string)$cidrRecord[3]);
			$cityResult = array();

			if (0 < $cityId = intval($cidrRecord[4]))
			{
				$citySearcher = new FileBinaryLineSearch($this->cityFile,
					'Geocoder\Provider\IpGeoBaseFilesProvider::compareCities');
				if ($cityLine = $citySearcher->search($cityId))
				{
					$record = explode("\t", trim($cityLine));
					$record[1] = iconv("CP1251", "UTF-8", $record[1]);
					$record[2] = iconv("CP1251", "UTF-8", $record[2]);

					$cityResult = array(
						'city' => (string)$record[1],
						'region' => (string)$record[2],
						//'countryDistrict' => (string)$record[3],
						'latitude' => (double)$record[4],
						'longitude' => (double)$record[5]
					);
				}
			}

			return array(array_merge($this->getDefaults(), $cidrResult, $cityResult));//$this->fixEncoding($cityResult)
		}
		else
		{
			throw new NoResultException(sprintf('Could not find IP %s', $address));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getReversedData(array $coordinates)
	{
		throw new UnsupportedException('The IpGeoBaseFilesProvider is not able to do reverse geocoding.');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'ip_geo_base_files';
	}
}

/**
 * Class FileBinaryLineSearch
 * Search line in file with provided compare-function using binary search algorithm
 *
 * @package Geocoder\Provider
 * @author Shkarupa Alex <shkarupa.alex@gmail.com>
 */
class FileBinaryLineSearch
{
	/**
	 * @var callable|null
	 */
	protected $compareFunction = null;

	/**
	 * @var null|string
	 */
	protected $lineEnding = null;

	/**
	 * @var null|int
	 */
	protected $bufferSize = null;

	/**
	 * @var null|int
	 */
	protected $fileSize = null;

	/**
	 * @var null|resource
	 */
	protected $fileHandle = null;


	/**
	 * @param string $fileName
	 * @param callable $compareFunction
	 * @param string $lineEnding
	 * @param int $bufferSize
	 */
	public function __construct($fileName, $compareFunction, $lineEnding = "\n", $bufferSize = 1024)
	{
		if (($this->fileHandle = fopen($fileName, "rb")) === false)
		{
			throw new InvalidArgumentException(sprintf('Given file "%s" could not be opened.', $fileName));
		}

		if (!is_callable($compareFunction))
		{
			throw new InvalidArgumentException(sprintf('Given function "%s" is not callable.', $compareFunction));
		}
		$this->compareFunction = $compareFunction;

		$this->lineEnding = $lineEnding;

		$this->fileSize = filesize($fileName);

		if ($bufferSize < strlen($lineEnding) + 1)
		{
			throw new InvalidArgumentException(sprintf('Given buffer size %d should be greater then %d.',
				$bufferSize, strlen($lineEnding) + 1));
		}
		else
		{
			$this->bufferSize = $bufferSize;
		}
	}

	public function __destruct()
	{
		fclose($this->fileHandle);
	}

	/**
	 * Search $value and return string where value was found
	 *
	 * @param mixed $value
	 * @return string|bool
	 */
	public function search($value)
	{
		if ($this->fileSize === 0)
		{
			return false;
		}

		$lo = array('OFFSET_RIGHT' => 0);
		$hi = array('OFFSET_LEFT' => $this->fileSize);
		$visited = array();

		while (!array_key_exists($lo['OFFSET_RIGHT'] . '-' . $hi['OFFSET_LEFT'], $visited))
		{
			$visited[$lo['OFFSET_RIGHT'] . '-' . $hi['OFFSET_LEFT']] = true;

			$mid = $this->getLineAt(round(($lo['OFFSET_RIGHT'] + $hi['OFFSET_LEFT']) / 2));
			$cmp = $this->compare($mid['VALUE'], $value);
			if ($cmp < 0) // $line < $value
			{
				$lo = array('OFFSET_RIGHT' => $mid['OFFSET_RIGHT']);
			}
			elseif ($cmp > 0) // $line > $value
			{
				$hi = array('OFFSET_LEFT' => $mid['OFFSET_LEFT']);
			}
			else // $line == $value
			{
				return $mid['VALUE'];
			}
		}

		return $this->compare($lo['VALUE'], $value) == 0 ? $lo['VALUE'] : false;
	}

	/**
	 * Reads line at provided position
	 *
	 * @param int $offset
	 * @return array
	 */
	protected function getLineAt($offset)
	{
		if (strlen($this->lineEnding) > 1)
		{
			// So we decided to read some position.
			// What if line ending is '\r\n' and our position points to '\r' ?
			// Let's check it and correct offset if it's so

			// Let line ending is '\n\n\n\'. Current offset ca point in any part of such string.
			// We need to read such a long string to capture any position of line ending
			$readSize = ceil(strlen($this->lineEnding) / 2) * 2;
			fseek($this->fileHandle, $offset - $readSize / 2, SEEK_SET);
			$buffer = fread($this->fileHandle, $readSize);

			if (strpos($buffer, $this->lineEnding) !== false)
			{
				// Continuing example above we read 4 bytes '!\n\n\n'.
				// To be simple we always will move pointer to the next row
				$offset += $readSize / 2;
			}
		}

		$left = $this->getLineBackward($offset);
		$right = $this->getLineForward($offset);

		return array(
			'VALUE' => $left . $right,
			'OFFSET' => $offset,
			'OFFSET_LEFT' => $offset - strlen($left),
			'OFFSET_RIGHT' => $offset + strlen($right),
		);
	}

	/**
	 * Reads line back to provided position until the beginning of file or end of line found
	 *
	 * @param $offset
	 * @return mixed
	 */
	protected function getLineBackward($offset)
	{
		$result = '';

		while ($offset > 0)
		{
			// Do not move left more then $offset - start of file (0)
			$readSize = min($this->bufferSize, $offset);

			$offset -= $readSize;
			fseek($this->fileHandle, $offset, SEEK_SET);
			$buffer = fread($this->fileHandle, $readSize);

			// Buffer is empty after read
			if (!$buffer)
			{
				break;
			}
			else
			{
				$result = $buffer . $result;
			}

			// Found line ending
			if (strpos($result, $this->lineEnding) !== false)
			{
				break;
			}
		}

		return array_pop(explode($this->lineEnding, $result));
	}

	/**
	 * Reads line forward from provided position until the end of file or end of line found
	 *
	 * @param $offset
	 * @return mixed
	 */
	protected function getLineForward($offset)
	{
		fseek($this->fileHandle, $offset, SEEK_SET);

		$result = '';
		while (!feof($this->fileHandle))
		{
			// Do not move right more then left to the end of file
			$readSize = min($this->bufferSize, $this->fileSize - $offset);

			$buffer = fread($this->fileHandle, $readSize);

			// Buffer is empty after read
			if (!$buffer)
			{
				break;
			}
			else
			{
				$result .= $buffer;
			}

			// Found line ending
			if (strpos($result, $this->lineEnding) !== false)
			{
				break;
			}
		}

		return array_shift(explode($this->lineEnding, $result));
	}

	/**
	 * Compares line to value
	 *
	 * returns < 0 if $line < $value
	 * returns > 0 if $line > $value
	 * returns 0 if $line == $value
	 *
	 * @param string $line
	 * @param mixed $value
	 * @return int
	 */
	protected function compare($line, $value)
	{
		return call_user_func($this->compareFunction, $line, $value);
	}
}