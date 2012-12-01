<?php
class COMPLETEDTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting the time.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testSimple( $time, $tzid )
	{	
		extract($this->preparetime($time, $tzid));
								
		$comp = new vtodo();
		$comp->setProperty( 'completed', $date );
		$end = $comp->createCompleted();		
		$this->assertStringEquals('COMPLETED:'.$dateiso.'Z', $end);
		
		$comp->setProperty( 'completed', $date, $tzoff );
		$end = $comp->createCompleted();
		$this->assertStringEquals('COMPLETED:'.$dateiso.'Z', $end, 'The offset should have no influence on the created date output');		
	}
	
	/**
	 * Tests setting the timezone as Parameter of setProperty.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezoneParam( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vtodo();
		$comp->setProperty( 'completed', $date, array('TZID' => $tzoff));
		$actual = $comp->createCompleted();		
		$expected = 'COMPLETED;TZID='.$tzoff.':'.$dateiso.'Z';
		$this->assertStringEquals($expected, $actual, 'If an offset is supplied as TZID, Greenwich Time (indicated by a Z) should be returned');
		
		$comp = new vtodo();
		$comp->setProperty( 'completed', $date, array('TZID' => $tzid));
		$actual = $comp->createCompleted();
		$expected = 'COMPLETED;TZID='.$tzid.':'.$dateiso.'Z';
		$this->assertStringEquals($expected, $actual, 'If a TZIDis supplied, Greenwich Time (indicated by a Z) should be returned');		
	}
	
	
	/**
	 * Tests setting the timezone as Configuration of the component
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezoneConfig( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vtodo();
		$comp->setProperty( 'completed', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createCompleted();
		$this->assertStringEquals('COMPLETED:'.$dateiso.'Z', $end, 'The timezone should be in created date output');
	}

	/**
	 * Test passing the timeone as part of the date.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezoneInDate( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vtodo();
		$comp->setProperty( 'completed', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createCompleted();
		$this->assertStringEquals('COMPLETED:'.$dateiso.'Z', $end, 'The timezone should be in created date output');
		
		// append timeone w.r.t. to dateformat, also tz only makes sense on dates with times
		$date = $this->appendTimezone($date, $tzid);		
		$comp = new vtodo();
		$comp->setProperty( 'completed', $date);
		$end = $comp->createCompleted();
		$this->assertStringEquals('COMPLETED:'.$dateiso.'Z', $end, 'If a COMPLETED is set with a trailing timezone it has to be returned as TZID');
	} 
}