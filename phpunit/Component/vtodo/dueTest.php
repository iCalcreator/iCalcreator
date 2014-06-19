<?php
class dueTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting the time.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDueSimple( $time, $tzid )
	{	
		extract($this->preparetime($time, $tzid));
								
		$comp = new vtodo();
		$comp->setProperty( 'due', $date );
		$end = $comp->createDue();		
		$this->assertStringEquals('DUE:'.$dateiso, $end);
		
		$comp->setProperty( 'due', $date, $tzoff );
		$end = $comp->createDue();
		$this->assertStringEquals('DUE:'.$dateiso, $end, 'The offset should have no influence on the created date output');		
	}
	
	/**
	 * Tests setting the timezone as Parameter of setProperty.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDueTimezoneParam( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vtodo();
		$comp->setProperty( 'due', $date, array('TZID' => $tzoff));
		$actual = $comp->createDue();		
		$expected = 'DUE:'.$utciso;
		$this->assertStringEquals($expected, $actual, 'If an offset is supplied as TZID, Greenwich Time (indicated by a Z) should be returned');
		
		$comp = new vtodo();
		$comp->setProperty( 'due', $date, array('TZID' => $tzid));
		$actual = $comp->createDue();
		$expected = 'DUE;TZID='.$tzid.':'.$dateiso;
		$this->assertStringEquals($expected, $actual, 'If a TZIDis supplied, Greenwich Time (indicated by a Z) should be returned');		
	}
	
	
	/**
	 * Tests setting the timezone as Configuration of the component
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDueTimezoneConfig( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vtodo();
		$comp->setProperty( 'due', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createDue();
		$this->assertStringEquals('DUE:'.$dateiso, $end );
	}

	/**
	 * Test passing the timeone as part of the date.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDueTimezoneInDate( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
				
		// append timeone w.r.t. to dateformat, also tz only makes sense on dates with times
		$date = $this->appendTimezone($date, $tzid);		
		$comp = new vtodo();
		$comp->setProperty( 'due', $date);
		$end = $comp->createDue();
		$this->assertStringEquals('DUE;TZID='.$tzid.':'.$dateiso, $end, 'If a DUE is set with a trailing timezone it has to be returned as TZID');
	} 
}