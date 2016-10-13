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
 * Data source: City of Vienna, http://data.wien.gv.at
 *
 * @author Robert Harm <www.harm.co.at>
 */
class OGDViennaAustriaProvider extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://data.wien.gv.at/daten/OGDAddressService.svc/GetAddressInfo?CRS=EPSG:4326&Address=%s';
    const REVERSE_ENDPOINT_URL = 'http://data.wien.gv.at/daten/OGDAddressService.svc/ReverseGeocode?crs=EPSG:4326&location=%F,%F';

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The OGDViennaAustria provider does not support IP addresses.');
        }

        $query = sprintf(self::ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $longitude, $latitude);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ogd_vienna_austria';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = (string) $this->getAdapter()->get($query)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $data = json_decode($content, true);

        if (empty($data) || false === $data) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        if (isset($data['features'][0]['properties']['DistanceUnit'])
            && $data['features'][0]['properties']['DistanceUnit'] == 'meter'
            && $data['features'][0]['properties']['Distance'] > 1000
        ) {
            throw new NoResult(sprintf('Result distance to far away'));
        }
        
        $bounds = array(
            'south' => isset($data['features'][0]['bbox'][0]) ? $data['features'][0]['bbox'][0] : null,
            'west'  => isset($data['features'][0]['bbox'][1]) ? $data['features'][0]['bbox'][1] : null,
            'north' => isset($data['features'][0]['bbox'][2]) ? $data['features'][0]['bbox'][3] : null,
            'east'  => isset($data['features'][0]['bbox'][3]) ? $data['features'][0]['bbox'][2] : null,
        );

        return array(array_merge($this->getDefaults(), array(
            'longitude'    => isset($data['features'][0]['geometry']['coordinates'][0]) ? $data['features'][0]['geometry']['coordinates'][0] : null,
            'latitude'     => isset($data['features'][0]['geometry']['coordinates'][1]) ? $data['features'][0]['geometry']['coordinates'][1] : null,
            'bounds'       => $bounds,
            'streetNumber' => isset($data['features'][0]['properties']['StreetNumber']) ? $data['features'][0]['properties']['StreetNumber'] : null,
            'streetName'   => isset($data['features'][0]['properties']['StreetName']) ? $data['features'][0]['properties']['StreetName'] : null,
            'cityDistrict' => isset($data['features'][0]['properties']['CountrySubdivision']) ? $data['features'][0]['properties']['CountrySubdivision'] : null,
            'city'         => isset($data['features'][0]['properties']['Municipality']) ? $data['features'][0]['properties']['Municipality'] : null,
            'zipcode'      => isset($data['features'][0]['properties']['PostalCode']) ? $data['features'][0]['properties']['PostalCode'] : null,
            'county'       => isset($data['features'][0]['properties']['MunicipalitySubdivision']) ? $data['features'][0]['properties']['MunicipalitySubdivision'] : null, //info: ??? - not available (yet ?)
            'countyCode'   => null, //isset($data['features'][0]['properties']['Zaehlbezirk']) ? $data['features'][0]['properties']['Zaehlbezirk'] : null,
            'region'       => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'Vienna' : null,
            'regionCode'   => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'Vienna' : null,
            'country'      => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'Austria' : null,
            'countryCode'  => isset($data['features'][0]['properties']['CountryCode']) ? $data['features'][0]['properties']['CountryCode'] : null,
            'timezone'     => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'Europe/Vienna' : null,
        )));
    }
}
