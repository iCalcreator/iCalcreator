<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class iCalCreator_TestCase extends PHPUnit_Framework_TestCase
{
	protected $outputformat = 'ical';
	
	public function setUp()
	{
		$this->cal = new vcalendar( array('format' => $this->outputformat));		
	}
	
	public function assertEqualIcals( $expected, $actual, $message = null )
	{
		$constraint = new PHPUnit_Framework_Constraint_icalsEqual($expected);
		
		$this->assertThat( $actual, $constraint, $message );
	}
	
	public function assertEqualComponents( $expected, $actual, $message = null )
	{
		$constraint = new PHPUnit_Framework_Constraint_ComponentsEqual($expected);
		
		$this->assertThat( $actual, $constraint, $message );
	}
}
