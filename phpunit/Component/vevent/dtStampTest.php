<?php
class DtStampTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting the time.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtStampSimple( $time, $tzid )
	{	
		extract($this->preparetime($time, $tzid));
								
		$comp = new vevent();
		$comp->setProperty( 'dtstamp', $date );
		$end = $comp->createDtstamp();		
		$this->assertStringEquals('DTSTAMP:'.$dateiso.'Z', $end, 'A supplied timestamp is assumed to be gmt');
		
		$comp->setProperty( 'dtstamp', $date, $tzoff );
		$end = $comp->createDtstamp();
		$this->assertStringEquals('DTSTAMP:'.$dateiso.'Z', $end, 'The offset should have no influence on the created date output');		
	}
	
	/**
	 * Tests setting the timezone as Parameter of setProperty.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtStamptimezoneParam( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'dtstamp', $date, array('TZID' => $tzoff));
		$actual = $comp->createDtstamp();		
		$expected = 'DTSTAMP;TZID='.$tzoff.':'.$dateiso.'Z';
		$this->assertStringEquals($expected, $actual, 'If an offset is supplied as TZID, Greenwich Time (indicated by a Z) should be returned');
		
		$comp = new vevent();
		$comp->setProperty( 'dtstamp', $date, array('TZID' => $tzid));
		$actual = $comp->createDtstamp();
		$expected = 'DTSTAMP;TZID='.$tzid.':'.$dateiso.'Z';
		$this->assertStringEquals($expected, $actual, 'If a TZIDis supplied, Greenwich Time (indicated by a Z) should be returned');		
	}
	
	
	/**
	 * Tests setting the timezone as Configuration of the component
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtStamptimezoneConfig( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'dtstamp', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createDtstamp();
		$this->assertStringEquals('DTSTAMP:'.$dateiso.'Z', $end, 'The timezone should be in created date output');
	}

	/**
	 * Test passing the timeone as part of the date.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testPropertyDtStamptimezoneInDate( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'dtstamp', $date );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createDtstamp();
		$this->assertStringEquals('DTSTAMP:'.$dateiso.'Z', $end, 'The timezone should be in created date output');
		
		// append timeone w.r.t. to dateformat, also tz only makes sense on dates with times
		$date = $this->appendTimezone($date, $tzid);		
		$comp = new vevent();
		$comp->setProperty( 'dtstamp', $date);
		$end = $comp->createDtstamp();
		$this->assertStringEquals('DTSTAMP:'.$dateiso.'Z', $end, 'If a DTSTAMP is set with a trailing timezone it has to be returned as TZID');
	} 
}