<?php
class classTest extends calendarComponent_TestCase
{
	
	/**
	 * @dataProvider classProvider
	 */
	public function testNoParams( $class )
	{											
		$comp = new vevent();
		$comp->setProperty( 'CLASS', $class );
		$expected = 'CLASS:'.$class;
		$actual = $comp->createClass();
		$this->assertStringEquals( $expected, $actual );
	}
	
	/**
	 * @dataProvider classProvider
	 */
	public function testWithParams( $class )
	{											
		$comp = new vevent();
		$comp->setProperty( 'CLASS', $class );
		$expected = 'CLASS:'.$class;
		$actual = $comp->createClass();
		$this->assertStringEquals( $expected, $actual, 'Parameters should not influence output' );
	}
		
	public function classProvider()
	{
		return array(
			array('PRIVATE'),
			array('CONFIDENTIAL'),
		);
	}
}