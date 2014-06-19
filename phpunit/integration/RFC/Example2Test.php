<?php
/**
 * The following test creates a calendar and compares it against its output.
 * 
 * The example is taken from rfc 2445 page 136
 */
class Example2Test extends iCalCreator_TestCase
{	
	public $example = <<<EOD
BEGIN:VCALENDAR
METHOD:xyz
VERSION:2.0
PRODID:-//ABC Corporation//NONSGML My Product//EN
BEGIN:VEVENT
DTSTAMP:19970324T120000Z
SEQUENCE:0
UID:uid3@host1.com
ORGANIZER:MAILTO:jdoe@host1.com
ATTENDEE;RSVP=TRUE:MAILTO:jsmith@host1.com
DTSTART:19970324T1230000Z
DTEND:19970324T210000Z
CATEGORIES:MEETING,PROJECT
CLASS:PUBLIC
SUMMARY:Calendaring Interoperability Planning Meeting
DESCRIPTION:Discuss how we can test c&s interoperability\n using iCalendar and other IETF standards.
LOCATION:LDB Lobby
ATTACH;FMTTYPE=application/postscript:ftp://xyzCorp.com/pub/ conf/bkgrnd.ps
END:VEVENT
END:VCALENDAR
EOD;

	public function testExample(){			
			$event = new vevent();
			$event->setDtstamp(1997, 3, 24, 12, 0, 0);
			$event->setSequence(0);
			$event->setUid('uid3@host1.com');
			$event->setOrganizer('MAILTO:jdoe@host1.com');
			$event->setAttendee('MAILTO:jsmith@host1.com', array('RSVP'=>'TRUE'));			
			$event->setDtstart('24-03-1997 23:00:00');
			$event->setDtend('24-03-1997 21:00:00');
			$event->setCategories(array('MEETING', 'PROJECT'));
			$event->setClass('PUBLIC');
			$event->setSummary('Calendaring Interoperability Planning Meeting');
			$event->setDescription('Discuss how we can test c&s interoperability\n using iCalendar and other IETF standards.');
			$event->setLocation('LDB Lobby');
			$event->setAttach('ftp://xyzCorp.com/pub/ conf/bkgrnd.ps', array('FMTTYPE' => 'application/postscript'));

			$cal = new vcalendar();
			$cal->setVersion( '2.0' );
			$cal->prodid = '-//ABC Corporation//NONSGML My Product//EN';
			$cal->setMethod('xyz');		
			$cal->addComponent($event);			
						
			$this->assertEqualIcals($this->example, $cal->createCalendar());
	}
	
}
?>
