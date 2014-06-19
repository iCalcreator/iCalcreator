<?php
class freebusyTest extends calendarComponent_TestCase
{
	
	/**
	 * @dataProvider scheduleProvider
	 */
	public function testFree( $schedule, $expected )
	{			
		$comp = new vfreebusy();
		$comp->setProperty( 'freebusy', 'FREE', $schedule );
		$actual = $comp->createFreebusy();	
		$expected = 'FREEBUSY;FBTYPE=FREE:'.$expected;
		$this->assertStringEquals($expected, $actual);		
	}
	
	/**
	 * @dataProvider scheduleProvider
	 */
	public function testBusy( $schedule, $expected )
	{			
		$comp = new vfreebusy();
		$comp->setProperty( 'freebusy', 'BUSY', $schedule );
		$actual = $comp->createFreebusy();	
		$expected = 'FREEBUSY;FBTYPE=BUSY:'.$expected;
		$this->assertStringEquals($expected, $actual);		
	}
	
	/**
	 * @dataProvider scheduleProvider
	 */
	public function testFreeBusy( $schedule, $expected )
	{			
		$comp = new vfreebusy();
		$comp->setProperty( 'freebusy', 'FREEBUSY', $schedule );
		$actual = $comp->createFreebusy();	
		$expected = 'FREEBUSY;FBTYPE=BUSY:'.$expected;
		$this->assertStringEquals($expected, $actual, 'setting the type freebusy means busy');		
	}
	
	public function testInvalid()
	{	
		$scheduleset = $this->scheduleProvider();
		$scheduledata = $scheduleset[0];
		
		$comp = new vfreebusy();
		$comp->setProperty( 'freebusy', 'dymmyKey', $scheduledata[0] );
		$actual = $comp->createFreebusy();	
		$expected = 'FREEBUSY;FBTYPE=BUSY:'.$scheduledata[1];
		$this->assertStringEquals($expected, $actual, 'an unknown key is converted to busy');		
	}
			
	public function scheduleProvider()
	{		
		$timestamp = 1354291267;
		
		
		$fdate1  = array ( 'year' => 2001, 'month' => 1, 'day' => 1, 'hour' => 1, 'min' => 1, 'sec' => 1 );
		$fdate2  = array ( 2002, 2, 2, 2, 2, 2, '-020202'  );
		$fdate3  = array ( 2003, 3, 3, 3, 3, 3 );
		$fdate4  = '4 April 2004 4:4:4';
		//$fdate5  = array ( 'year' => 2005, 'month' => 5, 'day' => 5, 'tz' => '+1200' );
		$fdate5  = array ( 'year' => 2005, 'month' => 5, 'day' => 5 );

		$fdate6  = array ( 5 );
		// alt.
		$fdate7  = array ( 'week' => false, 'day' => 5, 'hour' => 5, 'min' => 5, 'sec' => 5 );
		$fdate8  = array ( 0, 0, 6 );             // duration for 6 hours
		$fdate9  = 'PT2H30M';                     // duration for 2 hours, 30 minutes
		$fdate10 = array( 'sec' => 3 *3600);      // duration for 3 hours in seconds		
		$fdate12 = array( 'timestamp' => mktime ( 0, 0, 0, date('m', $timestamp), date('d', $timestamp)+ 3, date('Y', $timestamp)));
		$fdate13 = array( 'timestamp' => mktime ( 0, 0, 0, date('m', $timestamp), date('d', $timestamp)+ 3, date('Y', $timestamp)));
				
		$schedule1 = array( array($fdate1, $fdate2), array($fdate3, $fdate6), array($fdate4, $fdate7));
		$schedule2 = array( array( $fdate1, $fdate5 ), array( $fdate3, $fdate6 ), array( $fdate4, $fdate9 ), array( $fdate1, $fdate8 ));
		$schedule3 = array( array( $fdate12, $fdate6 ), array( $fdate3, $fdate6 ), array( $fdate4, $fdate10 ));
		$schedule4 = array( array( $fdate12, $fdate13 ));
		return array(
			array( $schedule1, '20010101T010101Z/20020202T020202Z,20030303T030303Z/P5W,20040404T040404Z/P5DT5H5M5S' ),
			array( $schedule2, '20010101T010101Z/20050505T000000Z,20030303T030303Z/P5W,20040404T040404Z/PT2H30M0S,20010101T010101Z/PT6H0M0S' ),
			array( $schedule3, '20121202T230000Z/P5W,20030303T030303Z/P5W,20040404T040404Z/PT3H0M0S' ),
			array( $schedule4, '20121202T230000Z/20121202T230000Z' ),
		);
	}
}