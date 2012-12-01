<?php

class triggerTest extends calendarComponent_TestCase
{	
	/**
	 * @dataProvider startIntervalProvider
	 */
	public function testStartInterval( $trigger, $expected = null)
	{
		$comp = new valarm;
		$comp->setProperty('trigger', $trigger);
		$actual = $comp->createTrigger();
		$this->assertStringEquals( 'TRIGGER:'.$expected, $actual );
	}
	
	/**
	 * @dataProvider dateProvider
	 */
	public function testDate( $trigger, $expected = null)
	{
		$comp = new valarm;
		$comp->setProperty('trigger', $trigger);
		$actual = $comp->createTrigger();
		$this->assertStringEquals( 'TRIGGER;VALUE=DATE-TIME:'.$expected, $actual );
	}
	
	/**
	 * @dataProvider endIntervalProvider
	 */
	public function testEndInterval( $trigger, $expected = null )
	{
		$comp = new valarm;
		$comp->setProperty('trigger', $trigger);
		$actual = $comp->createTrigger();
		$this->assertStringEquals( 'TRIGGER;RELATED=END:'.$expected, $actual );
	}
	
	/**
	 * @dataProvider startIntervalProvider
	 */
	public function testArbitraryParams( $trigger, $expected = null )
	{
		$comp = new valarm;
		$comp->setProperty('trigger', $trigger, array( 'xparamKey' => 'xparamValue' ));
		$actual = $comp->createTrigger();
		$this->assertStringEquals( 'TRIGGER;XPARAMKEY=xparamValue:'.$expected, $actual );
	}
	
	public function testindividualParam()
	{
		$comp = new valarm;
		$comp->setProperty( 'trigger', FALSE, FALSE, 1, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE );
		$actual = $comp->createTrigger();
		
		$this->assertStringEquals( 'TRIGGER;RELATED=END:P1D', $actual );
	}
	
	public function startIntervalProvider()
	{
		return array(
			array( array( 'hour' => 1, 'min' => 2, 'sec' => 3 ), '-PT1H2M3S' ),
			array( array( 'day' => 1, 'relatedStart' => TRUE,  'before' => FALSE ), 'P1D'),
			array( array( 'week' => 4, 'relatedStart' => TRUE, 'be	fore' => TRUE ), '-P4W'),
			array( array( 'week' => 4 ) , '-P4W'),
			array( array( 'week' => 4, 'before' => FALSE ), 'P4W' ),
			array( 'P1W', 'P1W' ),
			array( '-P2D', '-P2D' ),			
		);
	}
	
	public function endIntervalProvider()
	{
		return array(
			array( array( 'hour' => 1, 'min' => 2, 'sec' => 3, 'relatedStart' => FALSE, 'before' => TRUE ), '-PT1H2M3S' ),
			array( array( 'day' => 1, 'relatedStart' => FALSE,  'before' => FALSE ), 'P1D'),
			array( array( 'week' => 4, 'relatedStart' => FALSE, 'before' => TRUE ), '-P4W'),
			array( array( 'week' => 4, 'relatedStart' => FALSE, 'before' => FALSE ), 'P4W' ),						
		);
	}
	
	public function dateProvider()
	{
		return array(
		array( array( 'year'=>2007, 'month'=>6, 'day'=>5, 'hour'=>2, 'min'=>2, 'sec'=>3), '20070605T020203Z' ),
			array( '14 august 2006', '20060814T000000Z' ),
			array( '19970224T070000Z', '19970224T070000Z' ),
			array( array('timestamp' => 1354377593), '20121201T155953Z' ),
			);
	}
}