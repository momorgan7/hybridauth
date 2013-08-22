<?php
namespace Hybridauth\Entity\Facebook;

use \Hybridauth\Http\Request;

class Place extends Page
{
    protected $street = null;
    protected $city = null;
    protected $state = null;
    protected $zip = null;
    protected $country = null;
    protected $latitude = null;
    protected $longitude = null;

    function getStreet() {
        return $this->street;
    }

    function getCity() {
        return $this->city;
    }

    function getState() {
        return $this->state;
    }

    function getZip() {
        return $this->zip;
    }

    function getCountry() {
        return $this->country;
    }

    function getLatitude() {
        return $this->latitude;
    }

    function getLongitude() {
        return $this->longitude;
    }

    function setStreet($street) {
        $this->street = $street;
    }

    function setCity($city) {
        $this->city = $city;
    }

    function setState($state) {
        $this->state = $state;
    }

    function setZip($zip) {
        $this->zip = $zip;
    }

    function setCountry($country) {
        $this->country = $country;
    }

    function setLatitude($latitude) {
        $this->latitude = $latitude;
    }

    function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    public static function generateFromResponse($response,$adapter) {
        $venue = parent::generateFromResponse($response,$adapter);
        $venue->setIdentifier( static::parser( 'id',$response )        );
        $venue->setStreet    ( static::parser( 'street',$response )    );
        $venue->setCity      ( static::parser( 'city',$response )      );
        $venue->setState     ( static::parser( 'state',$response )     );
        $venue->setZip       ( static::parser( 'zip',$response )       );
        $venue->setCountry   ( static::parser( 'country',$response )   );
        $venue->setLatitude  ( static::parser( 'latitude',$response )  );
        $venue->setLongitude ( static::parser( 'longitude',$response ) );
        return $venue;
    }

    public static function generatePostDataFromEntity($venue) {
        $return = parent::generatePostDataFromEntity($venue);
        if(!is_null($x = $venue->getStreet())) $return['street'] = $x;
        if(!is_null($x = $venue->getCity())) $return['city'] = $x;
        if(!is_null($x = $venue->getState())) $return['state'] = $x;
        if(!is_null($x = $venue->getZip())) $return['zip'] = $x;
        if(!is_null($x = $venue->getCountry())) $return['country'] = $x;
        if(!is_null($x = $venue->getLatitude())) $return['latitude'] = $x;
        if(!is_null($x = $venue->getLongitude())) $return['longitude'] = $x;
        return $return;
    }
}
