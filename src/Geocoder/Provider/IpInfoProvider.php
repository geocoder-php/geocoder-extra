<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\UnsupportedException;
use Geocoder\Exception\NoResultException;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class IpInfoProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://ipinfo.io/%s/json';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The IpInfoProvider does not support Street addresses.');
        }

        if (in_array($address, array('127.0.0.1', '::1'))) {
            return array($this->getLocalhostDefaults());
        }

        $query   = sprintf(self::ENDPOINT_URL, $address);
        $content = $this->getAdapter()->getContent($query);
        $data    = json_decode($content, true);

        if (empty($data) || !isset($data['loc']) || '' === $data['loc']) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $location = explode(',', $data['loc']);

        return array(array_merge($this->getDefaults(), array(
            'latitude'    => $location[0],
            'longitude'   => $location[1],
            'city'        => isset($data['city'])    ? $data['city']    : null,
            'zipcode'     => isset($data['postal'])  ? $data['postal']  : null,
            'region'      => isset($data['region'])  ? $data['region']  : null,
            'countryCode' => isset($data['country']) ? $data['country'] : null,
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The IpInfoProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ip_info';
    }
}
