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
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class HereProvider extends AbstractHttpProvider implements Provider, LocaleAwareProvider
{
    use LocaleTrait;

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geocoder.api.here.com/6.2/geocode.json?app_id=%s&app_code=%s&maxresults=%d&searchtext=%s&gen=6';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://reverse.geocoder.api.here.com/6.2/reversegeocode.json?app_id=%s&app_code=%s&maxresults=%d&prox=%F,%F,100&gen=6&mode=retrieveAddresses';

    /**
     * @var string
     */
    private $appId = null;

    /**
     * @var string
     */
    private $appCode = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $appId   An App ID.
     * @param string               $apoCode An App code.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $appId, $appCode, $locale = null)
    {
        parent::__construct($adapter);

        if ($locale) {
            $this->setLocale($locale);
        }

        $this->appId   = $appId;
        $this->appCode = $appCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'here';
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($value)
    {
        // This API doesn't handle IPs
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The HereProvider does not support IP addresses.');
        }

        if (null === $this->appId || null === $this->appCode) {
            throw new InvalidCredentials('No App ID or code provided.');
        }

        $query = sprintf(
            self::GEOCODE_ENDPOINT_URL,
            $this->appId, $this->appCode, $this->getLimit(), urlencode($value), $this->getLocale()
        );

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (null === $this->appId || null === $this->appCode) {
            throw new InvalidCredentials('No App ID or code provided.');
        }

        $query = sprintf(
            self::REVERSE_ENDPOINT_URL,
            $this->appId, $this->appCode, $this->getLimit(), $latitude, $longitude
        );

        return $this->executeQuery($query);
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $query   = null !== $this->getLocale() ? sprintf('%s&language=%s', $query, $this->getLocale()) : $query;
        $content = $this->getAdapter()->get($query)->getBody();

        if (!$data = json_decode($content, true)) {
            throw new NoResult(sprintf('Could not execute query: %s', $query));
        }

        if (!isset($data['Response']) && 'InvalidCredentials' === $data['subtype']) {
            throw new InvalidCredentials(sprintf('Invalid credentials: %s', $data['details']));
        } elseif (!isset($data['Response'])) {
            throw new NoResult(
                sprintf('Error type `%s` returned from api `%s`', $data['subtype'], $data['Details'])
            );
        }

        if (empty($data['Response']['View'])) {
            throw new NoResult(sprintf('Could not find results for given query: %s', $query));
        }

        $locations = $data['Response']['View'][0]['Result'];

        $results = array();
        foreach ($locations as $location) {
            $location       = $location['Location'];
            $coordinates    = isset($location['NavigationPosition'][0]) ? $location['NavigationPosition'][0] : $location['DisplayPosition'];
            $bounds         = $location['MapView'];
            $address        = $location['Address'];
            $additionalData = $location['Address']['AdditionalData'];

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'     => $coordinates['Latitude']  ?: null,
                'longitude'    => $coordinates['Longitude'] ?: null,
                'bounds'       => array(
                    'south' => $bounds['BottomRight']['Latitude']  ?: null,
                    'west'  => $bounds['TopLeft']['Longitude']     ?: null,
                    'north' => $bounds['TopLeft']['Latitude']      ?: null,
                    'east'  => $bounds['BottomRight']['Longitude'] ?: null,
                ),
                'streetNumber' => isset($address['HouseNumber']) ? $address['HouseNumber'] : null,
                'streetName'   => isset($address['Street'])      ? $address['Street']      : null,
                'city'         => isset($address['City'])        ? $address['City']        : null,
                'cityDistrict' => isset($address['District'])    ? $address['District']    : null,
                'zipcode'      => isset($address['PostalCode'])  ? $address['PostalCode']  : null,
                'county'       => isset($address['County'])      ? $address['County']      : null,
                'regionCode'   => isset($address['State'])       ? $address['State']       : null,
                'countryCode'  => isset($address['Country'])     ? $address['Country']     : null,
                'region'       => $this->findByKey('StateName', $additionalData),
                'country'      => $this->findByKey('CountryName', $additionalData),
                'countyCode'   => null,
            ));
        }

        return $results;
    }

    /**
     * @param string $key
     * @param array  $values
     */
    protected function findByKey($key, array $values)
    {
        foreach ($values as $value) {
            if ($key === $value['key']) {
                return $value['value'];
            }
        }
    }
}
