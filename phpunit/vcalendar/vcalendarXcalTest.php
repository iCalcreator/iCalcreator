<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class XcalTest extends renderer_TestCase
{
	protected $outputformat = 'xcal';
	
	public function testSetAndGetCalscale()
	{
		$this->assertCalscale('JULIAN', 'calscale="JULIAN"');
	}
	
	public function testSetAndGetMethod()
	{
		$this->assertMethod('COUNTER', 'method="COUNTER"');
	}
			
	public function testCreateProdId()
	{	
		$this->assertContains('prodid=',$this->cal->createProdid());
	}
	
	public function testVersion()
	{
		$this->assertVersion('2.0', 'version="2.0"');
	}
}