<?php
class repeatTest extends calendarComponent_TestCase
{
	
	public function testSimple()
	{											
		$comp = new valarm();
		$comp->setProperty('repeat', 1);
		$expected = 'REPEAT:1';
		$actual = $comp->createRepeat();
		$this->assertStringEquals( $expected, $actual );
	}		
	
	public function testParameter()
	{											
		$comp = new valarm();
		$comp->setProperty('repeat', 1,  array( 'xparamKey' => 'xparamValue' ));
		$expected = 'REPEAT;XPARAMKEY=xparamValue:1';
		$actual = $comp->createRepeat();
		$this->assertStringEquals( $expected, $actual );
	}		
}