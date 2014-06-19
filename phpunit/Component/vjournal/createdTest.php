<?php
class createdTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting the time.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testSimple( $time, $tzid )
	{	
		extract($this->preparetime($time, $tzid));
								
		$comp = new vjournal();
		$comp->setProperty( 'created', $date );
		$actual = $comp->createCreated();		
		$this->assertStringEquals('CREATED:'.$dateiso.'Z', $actual);
		
		$comp->setProperty( 'created', $date, $tzoff );
		$actual = $comp->createCreated();
		$this->assertStringEquals('CREATED:'.$dateiso.'Z', $actual, 'The offset should have no influence on the created date output');		
	}
	
	/**
	 * Tests setting the timezone as Parameter of setProperty.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezoneParam( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vjournal();
		$comp->setProperty( 'created', $date, array('TZID' => $tzoff));
		$actual = $comp->createCreated();		
		$expected = 'CREATED;TZID='.$tzoff.':'.$dateiso.'Z';
		$this->assertStringEquals($expected, $actual, 'If an offset is supplied as TZID, Greenwich Time (indicated by a Z) should be returned');
		
		$comp = new vjournal();
		$comp->setProperty( 'created', $date, array('TZID' => $tzid));
		$actual = $comp->createCreated();
		$expected = 'CREATED;TZID='.$tzid.':'.$dateiso.'Z';
		$this->assertStringEquals($expected, $actual, 'If a TZIDis supplied, Greenwich Time (indicated by a Z) should be returned');		
	}
	
	
	/**
	 * Tests setting the timezone as Configuration of the component
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezonePerConfig( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vjournal();
		$comp->setProperty( 'created', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createCreated();
		$this->assertStringEquals('CREATED:'.$dateiso.'Z', $end, 'Ignore configured Timezone');
	}

	/**
	 * Test passing the timeone as part of the date.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtEndtimezoneInDate( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vjournal();
		$comp->setProperty( 'created', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createCreated();
		$this->assertStringEquals('CREATED:'.$dateiso.'Z', $end, 'The timezone should be ignored');
		
		// append timeone w.r.t. to dateformat, also tz only makes sense on dates with times
		$date = $this->appendTimezone($date, $tzid);		
		$comp = new vjournal();
		$comp->setProperty( 'created', $date);
		$end = $comp->createCreated();
		$this->assertStringEquals('CREATED:'.$dateiso.'Z', $end, 'If a dtend is set with a trailing timezone, the timezone has to be ignored');
	} 
}