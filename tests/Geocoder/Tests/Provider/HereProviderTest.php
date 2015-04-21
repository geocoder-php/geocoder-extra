<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\HereProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class HereProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new HereProvider($this->getMockAdapter($this->never()), 'app_id', 'app_code');
        $this->assertEquals('here', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGeocodeWithNullAppIdAndAppCode()
    {
        $provider = new HereProvider($this->getMockAdapter($this->never()), null, null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage http://geocoder.api.here.com/6.2/geocode.json?app_id=app_id&app_code=app_code&maxresults=5&searchtext=bar&gen=6
     */
    public function testGeocodeWithAddress()
    {
        $provider = new HereProvider($this->getMockAdapter(), 'app_id', 'app_code');
        $provider->geocode('bar');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid credentials: invalid credentials for app_id
     */
    public function testGetInvalidCredentials()
    {
        $json = '{"details":"invalid credentials for app_id","additionalData":[],"type":"PermissionError","subtype":"InvalidCredentials"}';

        $provider = new HereProvider($this->getMockAdapterReturns($json), 'app_id', 'app_code', 'fr-FR');
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Error type `CustomError` returned from api `custom text`
     */
    public function testGetCustomError()
    {
        $json = '{"Details":"custom text","additionalData":[],"type":"PermissionError","subtype":"CustomError"}';

        $provider = new HereProvider($this->getMockAdapterReturns($json), 'app_id', 'app_code');
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for given query: http://geocoder.api.here.com/6.2/geocode.json?app_id=app_id&app_code=app_code&maxresults=5&searchtext=foobarbaz&gen=6
     */
    public function testGetEmptyResultsWithWrongAddress()
    {
        $json = '{"Response":{"MetaInfo":{"Timestamp":"2014-09-13T12:32:57.201+0000"},"View":[]}}';

        $provider = new HereProvider($this->getMockAdapterReturns($json), 'app_id', 'app_code');
        $provider->geocode('foobarbaz');
    }

    public function testGeocodeWithRealAddress()
    {
        if (isset($_SERVER['HERE_APP_ID']) && isset($_SERVER['HERE_APP_CODE'])) {
            $provider = new HereProvider($this->getAdapter(), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
        } else {
            $json = '{"Response":{"MetaInfo":{"Timestamp":"2014-09-13T12:49:41.003+0000"},"View":[{"_type":"SearchResultsViewType","ViewId":0,"Result":[{"Relevance":1.0,"MatchLevel":"city","MatchQuality":{"City":1.0},"Location":{"LocationId":"QFHB0vmQzWSYrJyorvKt1D","LocationType":"area","DisplayPosition":{"Latitude":55.67569,"Longitude":12.5676},"NavigationPosition":[{"Latitude":55.67569,"Longitude":12.5676}],"MapView":{"TopLeft":{"Latitude":55.73259,"Longitude":12.45295},"BottomRight":{"Latitude":55.615,"Longitude":12.65075}},"Address":{"Label":"København, Hovedstaden, Danmark","Country":"DNK","State":"Hovedstaden","County":"København","City":"København","PostalCode":"1620","AdditionalData":[{"value":"Danmark","key":"CountryName"},{"value":"Hovedstaden","key":"StateName"}]}}}]}]}}';

            $provider = new HereProvider($this->getMockAdapterReturns($json), 'app_id', 'app_code');
        }

        $results  = $provider->geocode('Copenhagen');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals(55.67569, $result['latitude'], '', 0.01);
        $this->assertEquals(12.5676, $result['longitude'], '', 0.01);
        $this->assertEquals(55.615, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(12.45295, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(55.73259, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(12.65075, $result['bounds']['east'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertSame('København', $result['city']);
        $this->assertSame('1620', $result['zipcode']);
        $this->assertSame('København', $result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertSame('Hovedstaden', $result['region']);
        $this->assertSame('Hovedstaden', $result['regionCode']);
        $this->assertSame('Danmark', $result['country']);
        $this->assertSame('DNK', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HereProvider does not support IP addresses.
     */
    public function testGeocodeWithIPv4()
    {
        $provider = new HereProvider($this->getAdapter(), 'app_id', 'app_code');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HereProvider does not support IP addresses.
     */
    public function testGeocodeWithIPv6()
    {
        $provider = new HereProvider($this->getAdapter(), 'app_id', 'app_code');
        $provider->geocode('::ffff:74.200.247.59');
    }

    public function testReverseWithRealCoordinates()
    {
        if (isset($_SERVER['HERE_APP_ID']) && isset($_SERVER['HERE_APP_CODE'])) {
            $provider = new HereProvider($this->getAdapter(), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
        } else {
            $json = '{"Response":{"MetaInfo":{"Timestamp":"2014-09-13T12:26:49.223+0000","NextPageInformation":"2"},"View":[{"_type":"SearchResultsViewType","ViewId":0,"Result":[{"Relevance":1.0,"Distance":5.3,"MatchLevel":"houseNumber","MatchQuality":{"Country":1.0,"State":1.0,"County":1.0,"City":1.0,"District":1.0,"Street":[1.0],"HouseNumber":1.0,"PostalCode":1.0},"MatchType":"pointAddress","Location":{"LocationId":"LINK_883824031_L_PA_75379966_S","LocationType":"address","DisplayPosition":{"Latitude":60.4539,"Longitude":22.2568},"NavigationPosition":[{"Latitude":60.45378,"Longitude":22.25697}],"MapView":{"TopLeft":{"Latitude":60.4550242,"Longitude":22.2545203},"BottomRight":{"Latitude":60.4527758,"Longitude":22.2590797}},"Address":{"Label":"Läntinen Pitkäkatu 35, FI-20100 Turku, Suomi","Country":"FIN","State":"Lounais-Suomi","County":"Varsinais-Suomi","City":"Turku","District":"Keskusta","Street":"Läntinen Pitkäkatu","HouseNumber":"35","PostalCode":"20100","AdditionalData":[{"value":"Suomi","key":"CountryName"},{"value":"Lounais-Suomi","key":"StateName"}],"Building":"Kortteliravintola Kerttu"},"MapReference":{"ReferenceId":"883824031","MapId":"EUAM143W3","MapVersion":"Q3/2014","Spot":0.3,"SideOfStreet":"left","CountryId":"20241487","StateId":"20428850","CountyId":"20499886","CityId":"20500028","DistrictId":"25377101","BuildingId":"0"}}},{"Relevance":1.0,"Distance":40.4,"MatchLevel":"houseNumber","MatchQuality":{"Country":1.0,"State":1.0,"County":1.0,"City":1.0,"District":1.0,"Street":[1.0],"HouseNumber":1.0,"PostalCode":1.0},"MatchType":"pointAddress","Location":{"LocationId":"LINK_813585004_R_PA_74756096_S","LocationType":"address","DisplayPosition":{"Latitude":60.45431,"Longitude":22.25679},"NavigationPosition":[{"Latitude":60.45446,"Longitude":22.25656}],"MapView":{"TopLeft":{"Latitude":60.4554342,"Longitude":22.2545103},"BottomRight":{"Latitude":60.4531858,"Longitude":22.2590697}},"Address":{"Label":"Ratapihankatu 36, FI-20100 Turku, Suomi","Country":"FIN","State":"Lounais-Suomi","County":"Varsinais-Suomi","City":"Turku","District":"Keskusta","Street":"Ratapihankatu","HouseNumber":"36","PostalCode":"20100","AdditionalData":[{"value":"Suomi","key":"CountryName"},{"value":"Lounais-Suomi","key":"StateName"}]},"MapReference":{"ReferenceId":"813585004","MapId":"EUAM143W3","MapVersion":"Q3/2014","Spot":0.31,"SideOfStreet":"right","CountryId":"20241487","StateId":"20428850","CountyId":"20499886","CityId":"20500028","DistrictId":"25377101","BuildingId":"0"}}},{"Relevance":1.0,"Distance":47.0,"MatchLevel":"houseNumber","MatchQuality":{"Country":1.0,"State":1.0,"County":1.0,"City":1.0,"District":1.0,"Street":[1.0],"HouseNumber":1.0,"PostalCode":1.0},"MatchType":"pointAddress","Location":{"LocationId":"LINK_813585003_R_PA_75423412_S","LocationType":"address","DisplayPosition":{"Latitude":60.45408,"Longitude":22.25597},"NavigationPosition":[{"Latitude":60.45419,"Longitude":22.25581}],"MapView":{"TopLeft":{"Latitude":60.4552042,"Longitude":22.2536903},"BottomRight":{"Latitude":60.4529558,"Longitude":22.2582497}},"Address":{"Label":"Ratapihankatu 38, FI-20100 Turku, Suomi","Country":"FIN","State":"Lounais-Suomi","County":"Varsinais-Suomi","City":"Turku","District":"Keskusta","Street":"Ratapihankatu","HouseNumber":"38","PostalCode":"20100","AdditionalData":[{"value":"Suomi","key":"CountryName"},{"value":"Lounais-Suomi","key":"StateName"}]},"MapReference":{"ReferenceId":"813585003","MapId":"EUAM143W3","MapVersion":"Q3/2014","Spot":0.69,"SideOfStreet":"right","CountryId":"20241487","StateId":"20428850","CountyId":"20499886","CityId":"20500028","DistrictId":"25377101","BuildingId":"0"}}},{"Relevance":1.0,"Distance":53.6,"MatchLevel":"street","MatchQuality":{"Country":1.0,"State":1.0,"County":1.0,"City":1.0,"District":1.0,"PostalCode":1.0},"Location":{"LocationId":"LINK_813585006_L","LocationType":"address","DisplayPosition":{"Latitude":60.45436,"Longitude":22.25628},"MapView":{"TopLeft":{"Latitude":60.45477,"Longitude":22.25598},"BottomRight":{"Latitude":60.45436,"Longitude":22.25719}},"Address":{"Label":"FI-20100 Turku, Suomi","Country":"FIN","State":"Lounais-Suomi","County":"Varsinais-Suomi","City":"Turku","District":"Keskusta","PostalCode":"20100","AdditionalData":[{"value":"Suomi","key":"CountryName"},{"value":"Lounais-Suomi","key":"StateName"}]},"MapReference":{"ReferenceId":"813585006","MapId":"EUAM143W3","MapVersion":"Q3/2014","SideOfStreet":"left","CountryId":"20241487","StateId":"20428850","CountyId":"20499886","CityId":"20500028","DistrictId":"25377101"}}},{"Relevance":1.0,"Distance":75.0,"MatchLevel":"street","MatchQuality":{"Country":1.0,"State":1.0,"County":1.0,"City":1.0,"District":1.0,"Street":[1.0],"PostalCode":1.0},"Location":{"LocationId":"LINK_813585008_R","LocationType":"address","DisplayPosition":{"Latitude":60.4546131,"Longitude":22.2565643},"MapView":{"TopLeft":{"Latitude":60.45557,"Longitude":22.2561},"BottomRight":{"Latitude":60.45446,"Longitude":22.25941}},"Address":{"Label":"Ratapihankatu, FI-20100 Turku, Suomi","Country":"FIN","State":"Lounais-Suomi","County":"Varsinais-Suomi","City":"Turku","District":"Keskusta","Street":"Ratapihankatu","PostalCode":"20100","AdditionalData":[{"value":"Suomi","key":"CountryName"},{"value":"Lounais-Suomi","key":"StateName"}]},"MapReference":{"ReferenceId":"813585008","MapId":"EUAM143W3","MapVersion":"Q3/2014","SideOfStreet":"right","CountryId":"20241487","StateId":"20428850","CountyId":"20499886","CityId":"20500028","DistrictId":"25377101"}}}]}]}}';

            $provider = new HereProvider($this->getMockAdapterReturns($json), 'app_id', 'app_code');
        }

        $results = $provider->reverse(60.4539471768582, 22.2567842183875);

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $result = $results[0];
        $this->assertEquals(60.45378, $result['latitude'], '', 0.01);
        $this->assertEquals(22.25697, $result['longitude'], '', 0.01);
        $this->assertEquals(60.4527758, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(22.2545203, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(60.4550242, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(22.2590797, $result['bounds']['east'], '', 0.01);
        $this->assertSame('35', $result['streetNumber']);
        $this->assertSame('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertSame('Turku', $result['city']);
        $this->assertSame('20100', $result['zipcode']);
        $this->assertSame('Keskusta', $result['cityDistrict']);
        $this->assertSame('Varsinais-Suomi', $result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertSame('Lounais-Suomi', $result['region']);
        $this->assertSame('Lounais-Suomi', $result['regionCode']);
        $this->assertSame('Suomi', $result['country']);
        $this->assertSame('FIN', $result['countryCode']);
        $this->assertNull($result['timezone']);

        $result = $results[1];
        $this->assertEquals(60.45446, $result['latitude'], '', 0.01);
        $this->assertEquals(22.25656, $result['longitude'], '', 0.01);
        $this->assertEquals(60.4531858, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(22.2545103, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(60.4554342, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(22.2590697, $result['bounds']['east'], '', 0.01);
        $this->assertSame('36', $result['streetNumber']);
        $this->assertSame('Ratapihankatu', $result['streetName']);
        $this->assertSame('Turku', $result['city']);
        $this->assertSame('20100', $result['zipcode']);
        $this->assertSame('Keskusta', $result['cityDistrict']);
        $this->assertSame('Varsinais-Suomi', $result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertSame('Lounais-Suomi', $result['region']);
        $this->assertSame('Lounais-Suomi', $result['regionCode']);
        $this->assertSame('Suomi', $result['country']);
        $this->assertSame('FIN', $result['countryCode']);
        $this->assertNull($result['timezone']);

        $result = $results[2];
        $this->assertEquals(60.45419, $result['latitude'], '', 0.01);
        $this->assertEquals(22.25581, $result['longitude'], '', 0.01);
        $this->assertEquals(60.4529558, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(22.2536903, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(60.4552042, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(22.2582497, $result['bounds']['east'], '', 0.01);
        $this->assertSame('38', $result['streetNumber']);
        $this->assertSame('Ratapihankatu', $result['streetName']);
        $this->assertSame('Turku', $result['city']);
        $this->assertSame('20100', $result['zipcode']);
        $this->assertSame('Keskusta', $result['cityDistrict']);
        $this->assertSame('Varsinais-Suomi', $result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertSame('Lounais-Suomi', $result['region']);
        $this->assertSame('Lounais-Suomi', $result['regionCode']);
        $this->assertSame('Suomi', $result['country']);
        $this->assertSame('FIN', $result['countryCode']);
        $this->assertNull($result['timezone']);

        $result = $results[3];
        $this->assertEquals(60.45436, $result['latitude'], '', 0.01);
        $this->assertEquals(22.25628, $result['longitude'], '', 0.01);
        $this->assertEquals(60.45436, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(22.25598, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(60.45477, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(22.25719, $result['bounds']['east'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertSame('Turku', $result['city']);
        $this->assertSame('20100', $result['zipcode']);
        $this->assertSame('Keskusta', $result['cityDistrict']);
        $this->assertSame('Varsinais-Suomi', $result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertSame('Lounais-Suomi', $result['region']);
        $this->assertSame('Lounais-Suomi', $result['regionCode']);
        $this->assertSame('Suomi', $result['country']);
        $this->assertSame('FIN', $result['countryCode']);
        $this->assertNull($result['timezone']);

        $result = $results[4];
        $this->assertEquals(60.4546131, $result['latitude'], '', 0.01);
        $this->assertEquals(22.2565643, $result['longitude'], '', 0.01);
        $this->assertEquals(60.45446, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(22.2561, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(60.45557, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(22.25941, $result['bounds']['east'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        $this->assertSame('Ratapihankatu', $result['streetName']);
        $this->assertSame('Turku', $result['city']);
        $this->assertSame('20100', $result['zipcode']);
        $this->assertSame('Keskusta', $result['cityDistrict']);
        $this->assertSame('Varsinais-Suomi', $result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertSame('Lounais-Suomi', $result['region']);
        $this->assertSame('Lounais-Suomi', $result['regionCode']);
        $this->assertSame('Suomi', $result['country']);
        $this->assertSame('FIN', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }
}
