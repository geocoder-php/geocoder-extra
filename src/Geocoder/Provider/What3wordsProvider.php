<?php

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentials as InvalidCredentialsException;
use Geocoder\Exception\NoResult as NoResultException;
use Geocoder\Exception\UnsupportedOperation as UnsupportedException;

use Ivory\HttpAdapter\HttpAdapterInterface;

class What3wordsProvider extends AbstractHttpProvider implements Provider
{
    const FORWARD_URL = 'https://api.what3words.com/v2/forward?addr=%s&key=%s';
    const REVERSE_URL = 'https://api.what3words.com/v2/reverse?coords=%F,%F&key=%s';

    private $apiKey = null;

    public function __construct(HttpAdapterInterface $adapter, $apiKey)
    {
        parent::__construct($adapter);

        $this->apiKey = $apiKey;
    }

    public function getName()
    {
        return 'what3words';
    }

    public function geocode($value)
    {
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The what3words provider does not support IP addresses.');
        }

        if (null == $this->apiKey) {
            throw new InvalidCredentialsException('No what3words API key provided.');
        }

        $query = sprintf(self::FORWARD_URL, urlencode($value), $this->apiKey);
        return $this->executeQuery($query);
    }

    public function reverse($latitude, $longitude)
    {
        if (null == $this->apiKey) {
            throw new InvalidCredentialsException('No what3word API key provided.');
        }

        $query = sprintf(self::REVERSE_URL, $latitude, $longitude, $this->apiKey);

        return $this->executeQuery($query);
    }

    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->get($query)->getBody();

        if (null === $data = json_decode($content, true)) {
            throw new NoResultException(sprintf('Could not execute query: %s', $query));
        }

        if (!isset($data['status']) && isset($data['code']) && 2 === $data['code']) {
            throw new InvalidCredentialsException(sprintf('Invalid credentials: %s', $data['message']));
        }

        $results = [];
        $results[] = array_merge($this->getDefaults(), [
            'latitude' => $data['geometry']['lat'],
            'longitude' => $data['geometry']['lng'],
            'bounds' => [
                'south' => $data['bounds']['southwest']['lat'],
                'west' => $data['bounds']['southwest']['lng'],
                'north' => $data['bounds']['northeast']['lat'],
                'east' => $data['bounds']['northeast']['lng']
            ],
            'locality' => $data['words']
        ]);

        return $results;
    }
}

?>
