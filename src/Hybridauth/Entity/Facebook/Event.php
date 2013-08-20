<?php
namespace Hybridauth\Entity\Facebook;

use \Hybridauth\Http\Request;
use \Hybridauth\Entity\Facebook\Venue;

class Event extends \Hybridauth\Entity\Entity
{
    protected $date = null;
    protected $name = null;
    protected $description = null;
    protected $start_time = null;
    protected $end_time = null;
    protected $location = null;
    protected $ticketURI = null;
    protected $venue = null;

    function getDate() {
        return $this->date;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function getStartTime() {
        return $this->start_time;
    }

    function getEndTime() {
        return $this->end_time;
    }

    function getLocation() {
        return $this->location;
    }

    function getStreet() {
        return is_null($this->venue) ? null : $this->venue->getStreet();
    }

    function getCity() {
        return is_null($this->venue) ? null : $this->venue->getCity();
    }

    function getState() {
        return is_null($this->venue) ? null : $this->venue->getState();
    }

    function getZip() {
        return is_null($this->venue) ? null : $this->venue->getZip();
    }

    function getCountry() {
        return is_null($this->venue) ? null : $this->venue->getCountry();
    }

    function getLatitude() {
        return is_null($this->venue) ? null : $this->venue->getLatitude();
    }

    function getLongitude() {
        return is_null($this->venue) ? null : $this->venue->getLongitude();
    }

    function getTicketURI() {
        return $this->ticketURI;
    }

    function getVenue() {
        return $this->venue;
    }

    function setDate($date) {
        $this->date = $date;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setStartTime($start_time) {
        if(is_null($start_time)) {
            $this->start_time = null;
        } else {
            $this->start_time = is_numeric($start_time) ? $start_time : strtotime($start_time);
        }
    }

    function setEndTime($end_time) {
        if(is_null($end_time)) {
            $this->end_time = null;
        } else {
            $this->end_time = is_numeric($end_time) ? $end_time : strtotime($end_time);
        }
    }

    function setLocation($location) {
        $this->location = $location;
    }

    function setStreet($street) {
        $this->getOrCreateVenue()->setStreet($street);
    }

    function setCity($city) {
        $this->getOrCreateVenue()->setCity($city);
    }

    function setState($state) {
        $this->getOrCreateVenue()->setStreet($state);
    }

    function setZip($zip) {
        $this->getOrCreateVenue()->setZip($zip);
    }

    function setCountry($country) {
        $this->getOrCreateVenue()->setCountry($country);
    }

    function setLatitude($latitude) {
        $this->getOrCreateVenue()->setLatitude($latitude);
    }

    function setLongitude($longitude) {
        $this->getOrCreateVenue()->setLongitude($longitude);
    }

    function setTicketURI($ticketURI) {
        $this->ticketURI = $ticketURI;
    }

    function setVenue($venue) {
        $this->venue = $venue;
    }

    private function getOrCreateVenue() {
        if(is_null($this->venue)) {
            $this->venue = new Venue($this->getAdapter());
        }
        return $this->venue;
    }

    function delete() {
        $identifier = $this->getIdentifier();
        if(empty($identifier)) return true;
        $response = $this->getAdapter()->signedRequest('/'.$identifier,Request::DELETE);
        $response = json_decode($response);
        if(isset($response->error) || $response === false) return false;
        $this->setIdentifier(null);
        return true;
    }

    protected static function formatDate($date) {
        return date(\DateTime::ISO8601, $date);
    }

    public static function generateFromResponse($response,$adapter) {
        $event = parent::generateFromResponse($response,$adapter);
        $event->setIdentifier  ( static::parser( 'id',$response )          );
        $event->setName        ( static::parser( 'name',$response )        );
        $event->setDescription ( static::parser( 'description',$response ) );
        $event->setStartTime   ( static::parser( 'start_time',$response  ) );
        $event->setEndTime     ( static::parser( 'end_time',$response )    );
        $event->setLocation    ( static::parser( 'location',$response )    );
        $event->setTicketURI   ( static::parser( 'ticket_uri',$response )  );
        if(property_exists($response, 'venue')) {
            $event->venue = Venue::generateFromResponse($response->venue,$adapter);
        }
        return $event;
    }

    public static function generatePostDataFromEntity($event) {
        $return = parent::generatePostDataFromEntity($event);
        if(!is_null($x = $event->getName()))        $return['name']         = $x;
        if(!is_null($x = $event->getDescription())) $return['description']  = $x;
        if(!is_null($x = $event->getStartTime()))   $return['start_time']   = static::formatDate($x);
        if(!is_null($x = $event->getEndTime()))     $return['end_time']     = static::formatDate($x);
        if(!is_null($x = $event->getLocation()))    $return['location']     = $x;
        if(!is_null($x = $event->getVenue()))       $return['venue']        = $x->generatePostData();
        if(!is_null($x = $event->getTicketURI()))   $return['ticket_uri']   = $x;
        return $return;
    }
}
