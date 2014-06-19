<?php
class locationTest extends calendarComponent_TestCase
{
	public function testSimple()
	{
		$location = 'Målilla-avdelningen';
		
		$comp = new vevent();
		$comp->setProperty( 'location', $location );
		$actual = $comp->createLocation();
		$expected = 'LOCATION:'.$location;
		$this->assertStringEquals( $expected, $actual );
	}
	
	public function testLanguage()
	{
		$location = 'Målilla-avdelningen';
		
		$comp = new vevent();
		$comp->setConfig( 'language', 'no' );
		$comp->setProperty( 'location', $location );
		$actual = $comp->createLocation();
		$expected = 'LOCATION;LANGUAGE=no:'.$location;
		$this->assertStringEquals( $expected, $actual, 'setting a language must not influence location' );
	}
	
	public function testParams()
	{
		$location = 'Målilla-avdelningen';
		
		$comp = new vevent();
		$comp->setConfig( 'language', 'no' );
		$comp->setProperty( 'location', $location, array( 'altrep' => 'http://www.domain.net/doc.txt', 'Xparam', 'language' => 'se' ) );
		$actual = $comp->createLocation();
		$expected = 'LOCATION;ALTREP="http://www.domain.net/doc.txt";LANGUAGE=se;Xparam:'.$location;
		$this->assertStringEquals( $expected, $actual, 'setting a language may not influence location' );
	}
}