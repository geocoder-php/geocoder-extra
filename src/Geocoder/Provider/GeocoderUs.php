<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Geocoder;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderUs extends AbstractHttpProvider implements Geocoder
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://geocoder.us/service/rest/?address=%s';

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GeocoderUs provider does not support IP addresses.');
        }

        $query = sprintf(self::ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        throw new UnsupportedOperation('The GeocoderUs provider is not able to do reverse geocoding.');
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
        $content = (string) $this->getAdapter()->get($query)->getBody();

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $xpath = new \SimpleXMLElement($content);
        $xpath->registerXPathNamespace('geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
        $lat  = $xpath->xpath('//geo:lat');
        $long = $xpath->xpath('//geo:long');

        return array(array_merge($this->getDefaults(), array(
            'latitude'  => isset($lat[0]) ? (double) $lat[0] : null,
            'longitude' => isset($long[0]) ? (double) $long[0] : null,
        )));
    }
}
