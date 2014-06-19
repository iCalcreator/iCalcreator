<?php
class dtStartTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting the time.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtStartSimple( $time, $tzid )
	{	
		extract($this->preparetime($time, $tzid));
				
		$comp = new vevent();
		$comp->setProperty( 'dtstart', $date );
		$actual = $comp->createDtstart();		
		$this->assertStringEquals('DTSTART:'.$dateiso, $actual);
		
		$comp->setProperty( 'dtstart', $date, $tzoff );
		$actual = $comp->createDtstart();
		$this->assertStringEquals('DTSTART:'.$dateiso, $actual, 'The offset should have no influence on the created date output');		
	}
	
	/**
	 * Tests setting the timezone as Parameter of setProperty.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtStartTimezoneParam( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'dtstart', $date, array('TZID' => $tzoff));
		$actual = $comp->createDtstart();		
		$expected = 'DTSTART:'.$utciso;
		$this->assertStringEquals($expected, $actual, 'If an offset is supplied as TZID, Greenwich Time (indicated by a Z) should be returned');
		
		$comp = new vevent();
		$comp->setProperty( 'dtstart', $date, array('TZID' => $tzid));
		$actual = $comp->createDtstart();
		$expected = 'DTSTART;TZID='.$tzid.':'.$dateiso;
		$this->assertStringEquals($expected, $actual, 'If a TZIDis supplied, Greenwich Time (indicated by a Z) should be returned');		
	}
	
	
	/**
	 * Tests setting the timezone as Configuration of the component
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtStartTimezoneConfig( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'dtstart', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createDtstart();
		$this->assertStringEquals('DTSTART:'.$dateiso, $end, 'The timezone should be in created date output');
	}

	/**
	 * Test passing the timeone as part of the date.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtStartTimezoneInDate( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
				
		// append timeone w.r.t. to dateformat, also tz only makes sense on dates with times
		$date = $this->appendTimezone($date, $tzid);		
		$comp = new vevent();
		$comp->setProperty( 'dtstart', $date);
		$end = $comp->createDtstart();
		$this->assertStringEquals('DTSTART;TZID='.$tzid.':'.$dateiso, $end, 'If a DTSTART is set with a trailing timezone it has to be returned as TZID');
	} 
}