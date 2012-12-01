<?php
class vcalendarTest extends iCalCreator_TestCase
{

	/**
	 * @dataProvider provideProperties
	 */
	public function testProperties($propertyname, $propertyvalue)
	{		
		$this->cal->setProperty($propertyname, $propertyvalue);
		$got = $this->cal->getProperty($propertyname);
		
		$this->assertEquals($propertyvalue, $got);
		
	}
	
	public function testComponents()
	{
		$component = new vtodo();
		$this->cal->addComponent($component);
		$got = $this->cal->getComponent('VTODO', '1');
		
		$this->assertEquals($component, $got);
		
		$got = $this->cal->deleteComponent('VTODO', '1');
		$got = $this->cal->getComponent('VTODO', '1');
				
		$this->assertFalse($got);
		
		$comp = new vtodo();	
		$comp2 = new vevent();	
		$compuid = $comp->getProperty('uid');
		$this->cal->setComponent($comp, 99);
		$this->cal->addComponent($comp2);
		$this->assertEquals($comp, $this->cal->components[99 -1]);
		$this->cal->setComponent($comp2, $compuid);
		$this->assertEquals($comp2, $this->cal->components[99 -1]);
		$this->cal->setComponent($comp, 'vevent', 1);		
		$this->assertEquals($comp, $this->cal->components[99 -1]);
	}
	
	public function provideProperties()
	{
		return array(
			array('calscale', 'JULIAN'),
			array('method', 'REQUEST')
		);
	}
}
?>
