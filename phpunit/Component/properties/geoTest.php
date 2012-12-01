<?php
class geoTest extends calendarComponent_TestCase
{
	
	public function testSimple()
	{							
		$longitude = 11.2345;
		$latitude = -32.5678;
		
		$comp = new vevent();
		$comp->setProperty( 'geo', $longitude, $latitude );
		$actual = $this->normalizeSpace($comp->createGeo());
		$expected = 'GEO:+11.2345;-32.5678';
		$this->assertEquals($expected, $actual);
	}
	
	public function testParams()
	{							
		$longitude = 11.2345;
		$latitude = -32.5678;
		
		$comp = new vevent();
		$comp->setProperty( 'geo', $longitude, $latitude,  array( 'xparamValue', 'yparamKey' => 'yparamValue' ) );
		$actual = $this->normalizeSpace($comp->createGeo());
		$expected = 'GEO;xparamValue;YPARAMKEY=yparamValue:+11.2345;-32.5678';
		$this->assertEquals($expected, $actual);
	}
}