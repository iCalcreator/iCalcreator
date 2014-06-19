<?php
class attendeeTest extends calendarComponent_TestCase
{
	/**
	 * @dataProvider attendeeProvider	 
	 */
	public function testSimple( $attendee_set )
	{			
		$comp = new vevent();
		$expected = '';
		
		foreach( $attendee_set as $attendee )
		{
			$comp->setProperty('attendee', $attendee[0], $attendee[1]);
			$expected.= $attendee[2];
		}
		$actual = $this->normalizeSpace($comp->createAttendee());
		$this->assertEquals( $this->normalizeSpace($expected), $actual );
	}		
	
	public function attendeeProvider()
	{
		$attendee1 = array(
			'someone@internet.com'
			, array( 'cutype'         => 'New York'
				   , 'member'         => array('kigsf1@sf.net','kigsf2@sf.net','kigsf3@sf.net' )
				   , 'role'           => 'CHAIR'
				   , 'PARTSTAT'       => 'ACCEPTED'
				   , 'RSVP'           => 'TRUE'
				   , 'DELEgated-to'   => array('"MAILTO:p1@d.to"','"p2@d.to"', 'mailto:p3@t.to' )
				   , 'delegateD-FROM' => array('"p1@d.fr"', 'p2@d.fr', 'http://www.sf.net' )
				   , 'SENT-BY'        => 'info@kigkonsult.se'
				   , 'CN'             => 'John Doe'
				   , 'DIR'            => 'http://www.domain.net/doc/info.doc'
				   , 'LANGUAGE'       => 'us-EN'
				   , 'hejsan'         => 'hoppsan'  // xparam
				   ,                     'tjosan'   // also xparam
			   )
			, 'ATTENDEE;CUTYPE=New York;MEMBER="MAILTO:kigsf1@sf.net","MAILTO:kigsf2@sf.net","MAILTO:kigsf3@sf.net";ROLE=CHAIR;PARTSTAT=ACCEPTED;RSVP=TRUE;DELEGATED-TO="MAILTO:p1@d.to","MAILTO:p2@d.to","MAILTO:p3@t.to";DELEGATED-FROM="MAILTO:p1@d.fr","MAILTO:p2@d.fr","MAILTO:http://www.sf.net";SENT-BY="MAILTO:info@kigkonsult.se";CN=John Doe;DIR="http://www.domain.net/doc/info.doc";LANGUAGE=us-EN;tjosan;HEJSAN=hoppsan:MAILTO:someone@internet.com'
			);
		$attendee2 = array( 
			'someone.other@internet.com'
			, array( 'cutype'         => 'INDIVIDUAL'
				   , 'role'           => 'REQ-PARTICIPANT'
				   , 'PARTSTAT'       => 'NEEDS-ACTION'
				   , 'RSVP'           => 'FALSE'
				   )
			,'ATTENDEE:MAILTO:someone.other@internet.com'
			);
		$attendee3 = array( 
			'someone.else@internet.com'
			, array()
			,'ATTENDEE:MAILTO:someone.else@internet.com'
				);
		
		return array(
			array( array($attendee1) ),			
			array( array($attendee2) ),			
			array( array($attendee3) ),			
			array( array($attendee3, $attendee1) ),			
		);
	}
}