<?php
class organizerTest extends calendarComponent_TestCase
{
	/**
	 * @dataProvider organizerProvider	 
	 */
	public function testSimple( $attendee_set )
	{			
		$comp = new vevent();
		$expected = '';
		
		foreach( $attendee_set as $attendee )
		{
			$comp->setProperty('organizer', $attendee[0], $attendee[1]);
			$expected.= $attendee[2];
		}
		$actual = $comp->createOrganizer();
		$this->assertStringEquals( $expected, $actual );
	}		
	
	public function organizerProvider()
	{
		$attendee1 = array(
			'jsmith@host1.com'
			, array()
			, 'ORGANIZER:MAILTO:jsmith@host1.com'
			);
		$attendee2 = array( 
			'MAILTO:jsmith@host1.com'
			, array( 'xparamKey' => 'xparamValue', 'yParam' )
			,'ORGANIZER;yParam;XPARAMKEY=xparamValue:MAILTO:jsmith@host1.com'
			);
		$attendee3 = array( 
			'jsmith@host1.com'
			, array( 'language'  => 'se'
                      , 'CN'        => 'John Smith'
                      , 'DIR'       => 'ldap://host.com:6666/o=3DDC%20Associates,c=3DUS??(cn=3DJohn%20Smith)'
                      , 'SENT-BY'   => 'info1@host1.com'
                      , 'xparamKey' => 'xparamValue'
                      ,                'yParam' )
			,'ORGANIZER;CN=JohnSmith;DIR="ldap://host.com:6666/o=3DDC%20Associates,c=3DUS??(cn=3DJohn%20Smith)";SENT-BY="MAILTO:info1@host1.com";LANGUAGE=se;yParam;XPARAMKEY=xparamValue:MAILTO:jsmith@host1.com'
				);
		
		return array(
			array( array($attendee1) ),			
			array( array($attendee2) ),			
			array( array($attendee3) ),				
		);
	}
}