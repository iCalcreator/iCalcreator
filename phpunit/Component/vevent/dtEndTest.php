<?php
class dtEndTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting the time.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtEndSimple( $time, $tzid )
	{	
		extract($this->preparetime($time, $tzid));
								
		$comp = new vevent();
		$comp->setProperty( 'dtend', $date );
		$end = $comp->createDtend();		
		$this->assertStringEquals('DTEND:'.$dateiso, $end);
		
		$comp->setProperty( 'dtend', $date, $tzoff );
		$end = $comp->createDtend();
		$this->assertStringEquals('DTEND:'.$dateiso, $end, 'The offset should have no influence on the created date output');		
	}
	
	/**
	 * Tests setting the timezone as Parameter of setProperty.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtEndtimezoneParam( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'dtend', $date, array('TZID' => $tzoff));
		$actual = $comp->createDtend();		
		$expected = 'DTEND:'.$utciso;
		$this->assertStringEquals($expected, $actual, 'If an offset is supplied as TZID, Greenwich Time (indicated by a Z) should be returned');
		
		$comp = new vevent();
		$comp->setProperty( 'dtend', $date, array('TZID' => $tzid));
		$actual = $comp->createDtend();
		$expected = 'DTEND;TZID='.$tzid.':'.$dateiso;
		$this->assertStringEquals($expected, $actual, 'If a TZIDis supplied, Greenwich Time (indicated by a Z) should be returned');		
	}
	
	
	/**
	 * Tests setting the timezone as Configuration of the component
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtEndtimezoneConfig( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
				$comp = new vevent();
		$comp->setProperty( 'dtend', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createDtend();
		$this->assertStringEquals('DTEND:'.$dateiso, $end);
	}

	/**
	 * Test passing the timeone as part of the date.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtEndtimezoneInDate( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
				
		// append timeone w.r.t. to dateformat, also tz only makes sense on dates with times
		$date = $this->appendTimezone($date, $tzid);		
		$comp = new vevent();
		$comp->setProperty( 'dtend', $date);
		$end = $comp->createDtend();
		$this->assertStringEquals('DTEND;TZID='.$tzid.':'.$dateiso, $end, 'If a dtend is set with a trailing timezone it has to be returned as TZID');
	} 
}