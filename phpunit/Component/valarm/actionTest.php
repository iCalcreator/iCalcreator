<?php
class actionTest extends calendarComponent_TestCase
{
	
	public function testSimple()
	{											
		$comp = new valarm();
		$comp->setProperty('action', 'AUDIO');
		$expected = 'ACTION:AUDIO';
		$actual = $comp->createAction();
		$this->assertStringEquals( $expected, $actual );		
	}		
	
	public function testParameter()
	{											
		$comp = new valarm();
		$comp->setProperty('action', 'AUDIO',  array( 'SOUND' => 'Glaskrasch' ));
		$expected = 'ACTION;SOUND=Glaskrasch:AUDIO';
		$actual = $comp->createAction();
		$this->assertStringEquals( $expected, $actual, 'If a sound was set it should be returned' );		
	}
	
	public function testMultiParameter()
	{
		$comp = new valarm();
		$comp->setProperty('action', 'AUDIO',  array('SOUND' => 'Glaskrasch', 'EX' => 'kristallkrona', 'TYPE' => 'silverbricka'));
		$expected = 'ACTION;EX=kristallkrona;SOUND=Glaskrasch;TYPE=silverbricka:AUDIO';
		$actual = $comp->createAction();
		$this->assertStringEquals( $expected, $actual, 'All passed parameters should be returned' );		
	}		
}