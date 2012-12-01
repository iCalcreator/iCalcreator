<?php
class exdateTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting the time.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testSimple( $time, $tzid )
	{	
		extract($this->preparetime($time, $tzid));
								
		$comp = new vevent();
		$comp->setProperty( 'exdate', array($date) );
		$actual = $comp->createExdate();		
		$this->assertStringEquals('EXDATE:'.$dateiso, $actual);		
	}
	
	/**
	 * Test setting the property repeatedly
	 * 
	 * @dataProvider timeProvider
	 */
	public function testRepetition( $time, $tzid )
	{	
		extract($this->preparetime($time, $tzid));
								
		$comp = new vevent();
		$comp->setProperty( 'exdate', array($date) );
		$comp->setProperty( 'exdate', array($date) );
		$actual = $comp->createExdate();		
		$expected = 'EXDATE:'.$dateiso.'EXDATE:'.$dateiso;
		$this->assertStringEquals($expected, $actual);		
	}
		
	/**
	 * Tests setting the timezone as Parameter of setProperty.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezoneOffsetParam( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'exdate', array($date), array('TZID' => $tzoff));
		$actual = $comp->createExdate();		
		$expected = 'EXDATE;TZID='.$tzoff.':'.$dateiso;
		$this->assertStringEquals($expected, $actual, 'If an offset is supplied as TZID, it should be returned');		
	}
	
	/**
	 * Tests setting the timezone as Parameter of setProperty.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezoneIdParam( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'exdate', array($date), array('TZID' => $tzid));
		$actual = $comp->createExdate();
		$expected = 'EXDATE;TZID='.$tzid.':'.$dateiso;
		$this->assertStringEquals($expected, $actual, 'If a TZIDis supplied, it should be returned');		
	}
	
	
	/**
	 * Tests setting the timezone as Configuration of the component
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezoneConfig( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
		
		$comp = new vevent();
		$comp->setProperty( 'exdate', array( $date ) );
		$comp->setConfig( 'TZID', $tzid );
		$end = $comp->createExdate();
		$this->assertStringEquals('EXDATE:'.$dateiso, $end, 'The configured timezone should be ignored');
	}

	/**
	 * Test passing the timeone as part of the date.
	 * 
	 * @dataProvider timeProvider
	 */
	public function testTimezoneInDate( $time, $tzid )
	{		
		extract($this->preparetime($time, $tzid));
				
		// append timeone w.r.t. to dateformat, also tz only makes sense on dates with times
		$date = $this->appendTimezone($date, $tzid);		
		$comp = new vevent();
		$comp->setProperty( 'exdate', array($date));
		$end = $comp->createExdate();
		$this->assertStringEquals('EXDATE;TZID='.$tzid.':'.$dateiso, $end, 'If a dtend is set with a trailing timezone it has to be returned as TZID');
	} 
	
	public function testSettingMultipleDates()
	{
		$dateSet = $this->timeProvider();
		
		$param = array( $dateSet[0][0], $dateSet[3][0], $dateSet[12][0]);
		
		$expected = '';
		foreach( $param as $p )
		{
			extract($this->preparetime($p, $dateSet[0][1]));
			$expected.='EXDATE:'.$dateiso;
		}
		$comp = new vevent();
		$comp->setProperty( 'exdate', array($date) );
		$actual = $comp->createExdate();		
		$this->assertStringEquals('EXDATE:'.$dateiso, $actual);		
	}
}