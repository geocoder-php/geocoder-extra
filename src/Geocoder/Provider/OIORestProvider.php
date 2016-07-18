<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Exception\NoResult;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class OIORestProvider extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geo.oiorest.dk/adresser.json?q=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://geo.oiorest.dk/adresser/%F,%F.json';

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The OIORest provider does not support IP addresses.');
        }

        $address = rawurlencode($address);

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $address);

        $data = $this->executeQuery($query);

        $results = array();

        foreach ($data as $item) {
            $results[] = $this->getResultArray($item);
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $latitude, $longitude);

        $data = $this->executeQuery($query);

        return array($this->getResultArray($data));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oio_rest';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = (string) $this->getAdapter()->get($query)->getBody();

        if (null === $content) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $data = (array) json_decode($content, true);

        if (empty($data) || false === $data) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getResultArray(array $data)
    {
        return array_merge($this->getDefaults(), array(
            'latitude'     => isset($data['wgs84koordinat']['bredde']) ? $data['wgs84koordinat']['bredde'] : null,
            'longitude'    => isset($data['wgs84koordinat']['længde']) ? $data['wgs84koordinat']['længde'] : null,
            'streetNumber' => isset($data['husnr']) ? $data['husnr'] : null,
            'streetName'   => isset($data['vejnavn']['navn']) ? $data['vejnavn']['navn'] : null,
            'city'         => isset($data['postnummer']['navn']) ? $data['postnummer']['navn'] : null,
            'zipcode'      => isset($data['postnummer']['nr']) ? $data['postnummer']['nr'] : null,
            'cityDistrict' => isset($data['kommune']['navn']) ? $data['kommune']['navn'] : null,
            'region'       => isset($data['region']['navn']) ? $data['region']['navn'] : null,
            'regionCode'   => isset($data['region']['nr']) ? $data['region']['nr'] : null,
            'country'      => 'Denmark',
            'countryCode'  => 'DK',
            'timezone'     => 'Europe/Copenhagen'
        ));
    }
}
