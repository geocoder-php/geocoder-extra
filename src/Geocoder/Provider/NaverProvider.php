<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\UnsupportedException;
use Geocoder\Exception\NoResultException;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class NaverProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://openapi.map.naver.com/api/geocode.php?key=%s&encoding=utf-8&coord=latlng&query=%s';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey)
    {
        parent::__construct($adapter);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The NaverProvider does not support IP addresses.');
        }

        $query = sprintf(self::ENDPOINT_URL, $this->apiKey, rawurlencode($address));

        try {
            $result = new \SimpleXmlElement($this->getAdapter()->getContent($query));
        } catch (\Exception $e) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if (0 === (int) $result->total) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        return array(array_merge($this->getDefaults(), array(
            'latitude'     => (double) $result->item->point->x,
            'longitude'    => (double) $result->item->point->y,
            'region'       => isset($result->item->addrdetail->sido)
                ? trim((string) $result->item->addrdetail->sido) : null,
            'city'         => isset($result->item->addrdetail->sido->sigugun)
                ? trim((string) $result->item->addrdetail->sido->sigugun) : null,
            'streetName'   => isset($result->item->addrdetail->sido->sigugun->dongmyun)
                ? trim((string) $result->item->addrdetail->sido->sigugun->dongmyun) : null,
            'streetNumber' => isset($result->item->addrdetail->sido->sigugun->dongmyun->rest)
                ? (string) $result->item->addrdetail->sido->sigugun->dongmyun->rest : null,
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The NaverProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'naver';
    }
}
