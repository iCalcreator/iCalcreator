<?php
class priorityTest extends calendarComponent_TestCase
{		
	public function testSimple()
	{
		$priority = 3;
		
		$comp = new vtodo();
		$comp->setProperty( 'priority', $priority );
		$actual = $comp->createPriority();
		$expected = 'PRIORITY:'.$priority;
		$this->assertStringEquals($expected, $actual );
	}
		
	public function testParams()
	{
		$priority = 9;
		
		$comp = new vtodo();
		$comp->setProperty( 'priority', $priority, array( 'X-priority' => 'HIGH', 'X-ranking' => 'Important' ) );
		
		$actual = $comp->createPriority();
		$expected = 'PRIORITY;X-PRIORITY=HIGH;X-RANKING=Important:'.$priority;
		$this->assertStringEquals($expected, $actual );
	}
	
}