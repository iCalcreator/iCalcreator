<?php
class xpropTest extends calendarComponent_TestCase
{
	/**
	 * @dataProvider xpropProvider
	 */
	public function testSimple( $name, $value )
	{								
		$comp = new vevent();
		$comp->setProperty( $name, $value );
		$actual = $comp->createXprop();		
		$expected = sprintf('%s:%s',strtoupper($name),$value);
		$this->assertStringEquals($expected, $actual);
	}
	
	/**
	 * @dataProvider xpropProvider
	 */
	public function testParams( $name, $value )
	{								
		$comp = new vevent();
		$comp->setProperty( $name, $value, array( 'xparamKey' => 'xparamValue', 'language' => 'en' ) );
		$actual = $comp->createXprop();		
		$expected = sprintf('%s;LANGUAGE=en;XPARAMKEY=xparamValue:%s',strtoupper($name),$value);
		$this->assertStringEquals($expected, $actual, 'all params should be output');
	}
	
	public function testRepetition( )
	{
		$comp = new vevent();
		$comp->setProperty( 'X-xomment', 'this one will be overwritten' );
		$comp->setProperty( 'X-xomment', 'this second comment will be displayed' );
		$actual = $comp->createXprop();		
		$expected = 'X-XOMMENT:this second comment will be displayed';
		$this->assertStringEquals($expected, $actual, 'A xprop should overwrite a previously set xprop');
	}
	
	public function xpropProvider()
	{
		return array(
			array( 'X-WR-CALNAME', 'Games Night Meetup' ),
			array( 'X-ABC-MMSUBJ', 'http://load.noise.org/mysubj.wav'),
			array( 'X-xomment', 'this one will be overwritten' ),
			);
	}
}