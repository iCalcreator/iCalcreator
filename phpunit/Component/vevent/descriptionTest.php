<?php
class descriptionTest extends calendarComponent_TestCase
{
	
	public function testSingle()
	{
		$comp = new vevent();
		$comp->setProperty( 'description', 'This is a description.' );
		$expected = 'DESCRIPTION:This is a description.';
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual );
	}
	
	public function testMultiple()
	{
		$comp = new vevent();
		$comp->setProperty( 'description', "This is description 1a." );
		$comp->setProperty( 'description', "This is description 1b." );
		$expected = 'DESCRIPTION:This is description 1b.';
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual, 'description overwrites previous descriptions' );
	}
	
	public function testLinebreaks()
	{
		$comp = new vevent();
		$comp->setProperty( 'description', "The meaning of this veery long description (with a linebreak--:
:--here) is to test the function of line break after every 75 position and I hope that this is working properly and also a forced linebreak at char 74+1." );
		$expected = 'DESCRIPTION:The meaning of this veery long description (with a linebreak--: \n:--here) is to test the function of line break after every 75 position a nd I hope that this is working properly and also a forced linebreak at cha r 74+1.';
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual );
				
		$comp->setProperty( 'description', "This is a another


description with


2*3 linebreaks" );
		$expected = 'DESCRIPTION:This is a another\n\n\ndescription with\n\n\n2*3 linebreaks';
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual );
	}
		
	public function testNonAscii()
	{
		$comp = new vevent();
		$comp->setProperty( 'description', "3 Å i åa ä e ö, sa Yngve Öst, ärligt och ångerfyllt" );
		$expected = 'DESCRIPTION:3 Å i åa ä e ö\, sa Yngve Öst\, ärligt och ångerfyllt';
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual );
	}
	
	public function testBlanks()
	{
		$comp = new vevent();
		$comp->setProperty( 'description', "--------------------------------------------------blanks 5          5 and now 5 additional blanks     " );
		$expected = 'DESCRIPTION:--------------------------------------------------blanks 5           5 and now 5 additional blanks     ';
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual );
	}
		
	public function testParams()
	{
		$comp = new vevent();
		$params = array( 'altrep' => 'http://www.domain.net/doc.txt', 'hejsan', 'language' => 'da', 'xparamKey' => 'xparamvalue' );
		$comp->setProperty( 'description', "3 Å i åa ä e ö, sa Yngve Öst, ärligt och ångerfyllt", $params );
		$expected = 'DESCRIPTION;ALTREP="http://www.domain.net/doc.txt";LANGUAGE=da;hejsan;XPARAMKEY =xparamvalue:3 Å i åa ä e ö\, sa Yngve Öst\, ärligt och ångerfyllt';
		$expected = $expected;
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual );
	}
	
	public function testLanguageDefault()
	{
		$comp = new vevent();
		$comp->setProperty( 'description', "comment set without lang, will inherit language from calendar: 'en'" );
		$expected = 'DESCRIPTION:comment set without lang\, will inherit language from calendar: \'en\'';
		$expected = $expected;
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual, 'When no language is set default is used' );
	}
	
	public function testLanguageParam()
	{
		$comp = new vevent();
		$comp->setProperty( 'description', "comment set with language:'se'", array( 'language' => 'se' ));
		$expected = "DESCRIPTION;LANGUAGE=se:comment set with language:'se'";
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual, 'When no language is set default is used' );
	}
	
	public function testLanguageConfig()
	{
		$comp = new vevent();
		$comp->setConfig( 'language' , 'fr' );
		$comp->setProperty( 'description', "comment set without lang, will inherit language from component: 'fr'" );
		$expected = "DESCRIPTION;LANGUAGE=fr:comment set without lang\, will inherit language from c omponent: 'fr'";
		$expected = $expected;
		$actual = $comp->createDescription();
		$this->assertStringEquals($expected, $actual, 'When no language is set default is used' );
	}
}