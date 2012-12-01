<?php
class durationTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting the time.
	 * 
	 * @dataProvider intervalProvider
	 */
	public function testNonIntParams( $duration, $intervaliso )
	{											
		$comp = new vevent();
		$comp->setProperty( 'duration', $duration );
		$actual = $comp->createDuration();		
		$this->assertStringEquals('DURATION:'.$intervaliso, $actual, 'Duration should be returned as well formed interval');
		
		$comp->setProperty( 'duration', $duration, array( 'xparamkey' => 'xparamvalue' ) );
		$actual = $comp->createDuration();
		$this->assertStringEquals('DURATION;XPARAMKEY=xparamvalue:'.$intervaliso, $actual, 'The offset should have no influence on the created date output');		
	}
	
	/**
	 * @dataProvider intargProvider
	 * @param type $duration
	 * @param type $intervaliso
	 */
	public function testIntParameters($duration, $intervaliso)
	{
		$comp = new vevent();
		
		array_unshift($duration, 'duration');
		call_user_func_array(array($comp,'setProperty'), $duration);
		$actual = $comp->createDuration();		
		$this->assertStringEquals('DURATION:'.$intervaliso, $actual, 'Duration should be returned as well formed interval');
		
		// Want to append parameters, which have to be passed as 6th function-parameter
		foreach( range(2,5) as $i )
		{
			$duration[$i] = isset($duration[$i])?$duration[$i]:FALSE;
		}
		$duration[] = array('xparam');
		call_user_func_array(array($comp,'setProperty'), $duration);
		$actual = $comp->createDuration();		
		$this->assertStringEquals('DURATION;xparam:'.$intervaliso, $actual, 'Duration should be returned as well formed interval');
	}
	
	public function intervalProvider()
	{
		return array(
			// Interval description, expected iso representation
			array('P1W', 'P1W'),
			array('PT3H4M5S', 'PT3H4M5S'),
			array('P2DT4H', 'P2DT4H0M0S'),
			array('PT4H', 'PT4H0M0S'),
			array('PT30M', 'PT0H30M0S'),
			array('PT0H1M30S', 'PT0H1M30S'),
			array('PT1H0M0S', 'PT1H0M0S'),
			array('P1T0H0M0S', 'PT10H0M0S'),
			array('P1T1H0M0S', 'PT11H0M0S'),
			array('P1T0H5M0S', 'PT10H5M0S'),
			array(array('day' => 2, 'hour' => 3, 'sec' => 5), 'P2DT3H0M5S'),
			array(array( 'sec' => 61 ),'PT0H1M1S'),
			array(array( 'sec' => 7200 ),'PT2H0M0S'),
			array(array( 'sec' => 6 * 7 * 24 * 60 * 60 ),'P6W'),
		);
	}
	
	public function intargProvider()
	{
		return array( 
			array(array(1), 'P1W'),
			array(array(FALSE, 2), 'P2D'),
			array(array(FALSE, 2, 3), 'P2DT3H0M0S'),
			array(array(FALSE, FALSE, 3, 4, 5), 'PT3H4M5S'),
			array(array(FALSE, FALSE, FALSE, 4, 5), 'PT0H4M5S'),
			array(array(FALSE, FALSE, FALSE, FALSE, 5), 'PT0H0M5S'),
			);
	}
}