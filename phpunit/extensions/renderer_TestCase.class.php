<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class renderer_TestCase extends iCalCreator_TestCase
{
	protected $outputformat = 'ical';
	
	public function setUp()
	{		
		$this->cal = new vcalendar( array('format' => $this->outputformat));		
	}
	
	public function assertVersion( $value, $expected )
	{		
		$this->cal->setVersion($value);						
		$this->assertContains($expected,$this->cal->createVersion());
	}
	
	// According to the rfc GREGORIAN is the only recognized scale.
	// We want to test non-default values.
	public function assertCalscale( $value, $expected )
	{		 
		$this->cal->setcalscale( $value );
		$scale = $this->cal->createCalscale();
		$this->assertFalse( empty($scale), 'The created scale is empty!' );				
		$this->assertContains($expected,$scale);
	}
	
	// Methods is one of PUBLISH, REQUEST , REFRESH, CANCEL, ADD, REPLY, COUNTER, DECLINECOUNTER
	public function assertMethod( $value, $expected )
	{		
		$this->cal->setMethod($value);				
		
		$this->assertContains($expected,$this->cal->createMethod());
	}
}