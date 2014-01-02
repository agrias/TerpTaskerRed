<?php
/**
 * This script converts ical events to a json output
 * this also includes local time offsets
 * PHP Version 5
 *
 * @author   Billy Wu
 */
require 'class.iCalReader.php';

$ical   = new ICal('mycal.ics');

$events = $ical->events();

$eventList = array();

$date = new DateTime();
$standard = $date->getTimezone();
$timezone = $standard->getOffset($date);

foreach ($events as $event) {

    $array = array(
   	"title"=>$event['SUMMARY'],
    	"start"=>$ical->iCalDateToUnixTimestamp($event['DTSTART']) + $timezone,
   	"end"=>$ical->iCalDateToUnixTimestamp($event['DTEND']) + $timezone,
	"allDay"=>False,
	"editable"=>True,
   	"dtstamp"=>$event['DTSTAMP'],
    	"uid"=>$event['UID'],
    	"created"=>$event['CREATED'],
    	"description"=>$event['DESCRIPTION'],
    	"lastmodified"=>$event['LAST-MODIFIED'],
    	"location"=>$event['LOCATION'],
    	"sequence"=>$event['SEQUENCE'],
    	"status"=>$event['STATUS'],
    	"transp"=>$event['TRANSP'],
   );

  array_push($eventList, $array);
}

echo json_encode($eventList);
?>

