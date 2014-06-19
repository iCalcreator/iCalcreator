<?php
class percentCompleteTest extends calendarComponent_TestCase
{		
	public function testSimple()
	{
		$percentage = 90;
		
		$comp = new vtodo();
		$comp->setProperty( 'percent-complete', $percentage );
		$actual = $comp->createPercentComplete();
		$expected = 'PERCENT-COMPLETE:'.$percentage;
		$this->assertStringEquals($expected, $actual );
	}
		
	public function testParams()
	{
		$percentage = 90;
		
		$comp = new vtodo();
		$comp->setProperty( 'percent-complete', $percentage, array( 'xparamKey' => 'xparamValue', 'yParam' ) );
		$actual = $comp->createPercentComplete();
		$expected = 'PERCENT-COMPLETE;yParam;XPARAMKEY=xparamValue:'.$percentage;
		$this->assertStringEquals($expected, $actual );
	}
	
}