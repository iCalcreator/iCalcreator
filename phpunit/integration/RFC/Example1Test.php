<?php
/**
 * The following test creates a calendar and compares it against its output.
 * 
 * The example is taken from rfc 2445 page 136
 */
class Example1Test extends iCalCreator_TestCase
{	
	public $example = <<<EOD
BEGIN:VCALENDAR
PRODID:-//RDU Software//NONSGML HandCal//EN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:US-Eastern
BEGIN:STANDARD
DTSTART:19981025T020000
RDATE:19981025T020000
TZOFFSETFROM:-0400
TZOFFSETTO:-0500
TZNAME:EST
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:19990404T020000
RDATE:19990404T020000
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
TZNAME:EDT
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
DTSTAMP:19980309T231000Z
UID:guid-1.host1.com
ORGANIZER;ROLE=CHAIR:MAILTO:mrbig@host.com
ATTENDEE;RSVP=TRUE;ROLE=REQ-PARTICIPANT;CUTYPE=GROUP:MAILTO:employee-A@host.com
DESCRIPTION:Project XYZ Review Meeting
CATEGORIES:MEETING
CLASS:PUBLIC
CREATED:19980309T130000Z
SUMMARY:XYZ Project Review
DTSTART;TZID=US-Eastern:19980312T083000
DTEND;TZID=US-Eastern:19980312T093000
LOCATION:1CP Conference Room 4350
END:VEVENT 
END:VCALENDAR  
EOD;

	public function testExample(){
			$timezone = new vtimezone();
			$timezone->setTzid('US-Eastern');
			$standard = new vtimezone('standard');
			$standard->setTzoffsetfrom('-0400');
			$standard->setTzoffsetto('-0500');
			$standard->setDtstart(1998, 10, 25, 2, 0, 0);
			$standard->setTzname('EST');
			$standard->setRdate(array('19981025T020000'));
			$daylight = new vtimezone('daylight');
			$daylight->setDtstart(1999, 04, 04, 02, 0, 0);
			$daylight->setRdate(array(array(1999, 04, 04, 02, 0, 0)));
			$daylight->setTzname('EDT');
			$daylight->setTzoffsetfrom('-0500');
			$daylight->setTzoffsetto('-0400');
			$timezone->addSubComponent($standard);
			$timezone->addSubComponent($daylight);
			
			$event = new vevent();
			$event->setDtstamp(1998, 3, 9, 23, 10, 0);
			$event->setUid('guid-1.host1.com');
			$event->setOrganizer('MAILTO:mrbig@host.com', array('ROLE' => 'CHAIR'));
			$event->setAttendee('MAILTO:employee-A@host.com', array('RSVP'=>'TRUE','ROLE'=>'REQ-PARTICIPANT', 'CUTYPE'=>'GROUP'));
			$event->setDescription('Project XYZ Review Meeting');
			$event->setCategories('MEETING');
			$event->setClass('PUBLIC');
			$event->setCreated('19980309T130000Z');
			$event->setSummary('XYZ Project Review');
			$event->setDtstart('12-03-1998 08:30:00', array('tzid' => 'US-Eastern'));
			$event->setDtend('12-03-1998 09:30:00', array('tzid' => 'US-Eastern'));			
			$event->setLocation('1CP Conference Room 4350');

			$cal = new vcalendar();
			$cal->setVersion( '2.0' );
			$cal->prodid = '-//RDU Software//NONSGML HandCal//EN';
			$cal->addComponent($timezone);			
			$cal->addComponent($event);			
						
			$this->assertEqualIcals($this->example, $cal->createCalendar());
	}
	
}
?>
