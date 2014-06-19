<?php
class componentHolderTest extends iCalCreator_TestCase
{	
	public function setUp()
	{
		$holder = new vcalendar();
				
		$todo = new vtodo();	
		$todo->setProperty('dtstart', '20101010T020000');
		$todo->getProperty('uid');
		
		$event = new vevent();	
		$event->setProperty('dtstart', '20202020T020000');
		$event->getProperty('uid');
		
		$todo2 = new vtodo();
		$todo2->getProperty('uid');	
		
		$holder->components[] = $todo2;
		$holder->components[] = $event;
		$holder->components[] = $todo;
		
		$this->holder = $holder;
	}
	
	public function testSetComponentsSimple()
	{
		$holder = new vcalendar();
				
		$todo = new vtodo();	
		$event = new vevent();	
		$todoid = $todo->getProperty('uid');
		
		// simple addition
		$holder ->setComponent($todo);
		$got = $holder->components[0];
		$this->assertEquals($todo, $got);		
	}
	
	public function testSetComponentsByPosition()
	{
		$holder = new vcalendar();
				
		$todo = new vtodo();	
		$event = new vevent();	
						
		// set position
		$holder->setComponent($todo, 99);
		$holder->setComponent($event);
		$this->assertEquals($todo, $holder->components[99 -1]);		
	}
	
	public function testSetComponentsType()
	{
		$holder = new vcalendar();
				
		$todo = new vtodo();	
		$event = new vevent();	
		$todo2 = new vtodo();
				
		$holder->components[] = $todo;
		$holder->components[] = $todo2;
		
		// set by type
		$holder->setComponent($todo, 'vtodo', 1);		
		$this->assertEquals($todo, $holder->components[0]);
	}
	
	public function testSetComponentsUid()
	{
		$holder = new vcalendar();
				
		$todo = new vtodo();	
		$event = new vevent();	
		$todo2 = new vtodo();
		$todoid = $todo->getProperty('uid');
		
		$holder->components[] = $todo2;
		$holder->components[] = $todo;
				
		// set by uid
		$holder->setComponent($event, $todoid);
		$this->assertEquals($event, $holder->components[1]);		
	}
		
	public function testDeleteComponents()
	{		
		$holder = $this->holder;
		
		$holdercount = count( $holder->components);
				
		$holder->deleteComponent('VEVENT', '1');
		$this->assertFalse( count($holder->components) >= $holdercount );
		$this->assertFalse(isset($holder->components[1]));
	}
	
	public function testgetComponentsSimple()
	{
		$holder = $this->holder;
		$component = $this->holder->components[0];
		
		$this->assertEquals( $component, $holder->getComponent());
	}
	
	public function testGetComponentByIndex()
	{
		$holder = $this->holder;
		$component = $this->holder->components[2];
		
		$this->assertEquals( $component, $holder->getComponent(3));
	}
	
	public function testGetComponentByUid()
	{
		$holder = $this->holder;
		$component = $this->holder->components[2];
		$id = $component->getProperty('uid');
		
		$this->assertEquals( $component, $holder->getComponent($id));
	}
	
	public function testSelectComponent()
	{
		$holder = $this->holder;
		$Y = 2010;
		$m = 10;
		$d = 10;
		$H = '02';
		$M = '00';
		$S = '00';
		$comp = $holder->components[0];
		$actual = $holder->selectComponents($Y, $m, $d);
		
		// selectComponwnt changes component in ways not allowed by
		// setproperty 
		$comp->dtstart = array( 
			'params' => array(), 
			'value' => array(
				'year' => $Y,
				'month' => $m,
				'day' => $d,
				'hour' => $H,
				'min' => $M,
				'sec' => $S)
			);
		
		$comp->xprop = array (
			'X-CURRENT-DTSTART' => array (
                'value' => "$Y-$m-$d $H:$M:$S",
                'params' => null
				)
			);
		$comp->getProperty('uid');
    
		$this->assertEquals(array('2010'), array_keys($actual));
		$this->assertEquals(array('10'), array_keys($actual['2010']));
		$this->assertEquals(array('10'), array_keys($actual['2010']['10']));
		$this->assertEqualComponents($comp, $actual['2010']['10']['10'][0]);
	}
}
?>
