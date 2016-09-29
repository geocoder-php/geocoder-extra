<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;

/**
 * @author Ganeko Guereta <ganeko.guereta@gmail.com>
 */
class Ip2cProvider extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://ip2c.org/?ip=%s';

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Ip2cProvider does not support street addresses.');
        }

        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The Ip2cProvider does not support IPv6 addresses.');
        }

        $query = sprintf(self::ENDPOINT_URL, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        throw new UnsupportedOperation('The Ip2cProvider is not able to do reverse geocoding.');
    }

    /**
     * @param string $query
     *
     * @throws \Geocoder\Exception\NoResult
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = (string) $this->getAdapter()->get($query)->getBody();

        if (null === $content || '' === $content) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        list($status, $alpha2, $alpha3, $country) = explode(';', $content);

        if (0 == $status) {
            throw new NoResult('Input string is not a valid IP address.');
        }

        if (2 == $status) {
            throw new NoResult('Invalid result returned by provider.');
        }

        return array(array_merge($this->getDefaults(), array(
            'country'       => $country,
            'countryCode'   => $alpha2
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ip2c';
    }
}
