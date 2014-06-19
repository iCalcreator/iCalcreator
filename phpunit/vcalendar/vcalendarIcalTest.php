<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class IcalTest extends renderer_TestCase
{
	protected $outputformat = 'ical';
	
	public function testSetAndGetCalscale()
	{
		$this->assertCalscale('JULIAN', 'CALSCALE:JULIAN');
	}
	
	public function testSetAndGetMethod()
	{
		$this->assertMethod('COUNTER', 'METHOD:COUNTER');
	}
			
	public function testCreateProdId()
	{		
		$this->assertContains('PRODID:',$this->cal->createProdid());
	}
	
	public function testVersion()
	{
		$this->assertVersion('2.0', 'VERSION:2.0');
	}
}