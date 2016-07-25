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
class GeocoderUsProvider extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://geocoding.geo.census.gov/geocoder/locations/onelineaddress?format=json&benchmark=Public_AR_Current&address=%s';

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GeocoderUsProvider does not support IP addresses.');
        }

        $query = sprintf(self::ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        throw new UnsupportedOperation('The GeocoderUsProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geocoder_us';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->get($query)->getBody();

        $json = json_decode($content, true);
        
        if (!empty($json['errors'])) {
            throw new NoResult(sprintf('Could not execute query: %s', $query));
        }
        
        if (empty($json['result']) || empty($json['result']['addressMatches'])) {
            throw new NoResult(sprintf('Could not find results for given query: %s', $query));
        }

        return array(array_merge($this->getDefaults(), array(
            'longitude'    => $json['result']['addressMatches'][0]['coordinates']['x'],
            'latitude'     => $json['result']['addressMatches'][0]['coordinates']['y'],
            'streetName'   => $json['result']['addressMatches'][0]['addressComponents']['streetName'],
            'city'         => $json['result']['addressMatches'][0]['addressComponents']['city'],
            'zipcode'      => $json['result']['addressMatches'][0]['addressComponents']['zip'],
            'countryCode'  => 'US',
        )));
    }
}
