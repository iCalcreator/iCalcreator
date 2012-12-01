<?php
class commentTest extends calendarComponent_TestCase
{
	
	public function testSingle()
	{
		$comp = new vevent();
		$comp->setProperty( 'comment', "This is comment 1a." );
		$expected = 'COMMENT:This is comment 1a.';
		$actual = $comp->createComment();
		$this->assertStringEquals($expected, $actual );
	}
	
	public function testMultiple()
	{
		$comp = new vevent();
		$comp->setProperty( 'comment', "This is comment 1a." );
		$comp->setProperty( 'comment', "This is comment 1b." );
		$expected = 'COMMENT:This is comment 1a.COMMENT:This is comment 1b.';
		$actual = $comp->createComment();
		$this->assertStringEquals($expected, $actual );
	}
	
	public function testLinebreaks()
	{
		$comp = new vevent();
		$comp->setProperty( 'comment', "This is comment 1d with hard(
 )line-(
 )breaks." );
		$comp->setProperty( 'comment', 'This is comment 1c with two soft line-(
 ) breaks (
 )here.' );
		$expected = 'COMMENT:This is comment 1d with hard(\n )line-(\n )breaks.COMMENT:This is comment 1c with two soft line-(\n ) breaks (\n )here.';
		$actual = $comp->createComment();
		$this->assertStringEquals($expected, $actual );
	}
	
	public function testHereDoc()
	{
		$comp = new vevent();
		$comment = <<<EOT
ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i åå>>>åå i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ää>>>ää i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i öö>>>öö i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÅÅ>>>ÅÅ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÄÄ>>>ÄÄ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÖÖ>>>ÖÖ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i üü>>>üü i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÜÜ>>>ÜÜ
EOT;

		$comp->setProperty( 'comment', $comment );
		$expected = 'COMMENT:ä e ö sa Yngve Öst\, Ärligt och Ångerfyllt---brytning mitt i åå>>>å å i åa ä e ö sa Yngve Öst\, Ärligt och Ångerfyllt---brytning mitt i ää>>>ä ä i åa ä e ö sa Yngve Öst\, Ärligt och Ångerfyllt---brytning mitt i öö>>>öö i åa ä e ö sa Yngve Öst\, Ärligt och Ångerfyllt---brytning mitt i ÅÅ>>>ÅÅ i åa ä e ö sa Yngve Öst\, Ärligt och Ångerfyllt---brytning mitt i ÄÄ>>>ÄÄ i åa ä e ö sa Yngve Öst\, Ärligt och Ångerfyllt---brytning mitt i ÖÖ>>>ÖÖ i åa ä e ö sa Yngve Öst\, Ärligt och Ångerfyllt---brytning mitt i üü>>>üü i åa ä e ö sa Yngve Öst\, Ärligt och Ångerfyllt---brytning mitt i ÜÜ>>>ÜÜ';
		$actual = $comp->createComment();
		$this->assertStringEquals($expected, $actual );
	}
		
	public function testNonAscii()
	{
		$comp = new vevent();
		$comp->setProperty( 'comment', "3 Å i åa ä e ö, sa Yngve Öst, ärligt och ångerfyllt" );
		$expected = 'COMMENT:3 Å i åa ä e ö\, sa Yngve Öst\, ärligt och ångerfyllt';
		$actual = $comp->createComment();
		$this->assertStringEquals($expected, $actual );
	}
	
	public function testParams()
	{
		$comp = new vevent();
		$params = array( 'altrep' => 'http://www.domain.net/doc.txt', 'hejsan', 'language' => 'da', 'xparamKey' => 'xparamvalue' );
		$comp->setProperty( 'comment', "3 Å i åa ä e ö, sa Yngve Öst, ärligt och ångerfyllt", $params );
		$expected = 'COMMENT;ALTREP="http://www.domain.net/doc.txt";LANGUAGE=da;hejsan;XPARAMKEY =xparamvalue:3 Å i åa ä e ö\, sa Yngve Öst\, ärligt och ångerfyllt';
		$actual = $comp->createComment();
		$this->assertStringEquals($expected, $actual );
	}
	
	public function testLanguageDefault()
	{
		$comp = new vevent();
		$comp->setProperty( 'comment', "comment set without lang, will inherit language from calendar: 'en'" );
		$expected = 'COMMENT:comment set without lang\, will inherit language from calendar: \'en\'';
		$expected = str_replace(' ','',$expected);
		$actual = str_replace(' ','',$comp->createComment());
		$this->assertStringEquals($expected, $actual, 'When no language is set default is used' );
	}
	
	public function testLanguageParam()
	{
		$comp = new vevent();
		$comp->setProperty( 'comment', "comment set with language:'se'", array( 'language' => 'se' ));
		$expected = "COMMENT;LANGUAGE=se:comment set with language:'se'";
		$actual = $comp->createComment();
		$this->assertStringEquals($expected, $actual, 'When no language is set default is used' );
	}
	
	public function testLanguageConfig()
	{
		$comp = new vevent();
		$comp->setConfig( 'language' , 'fr' );
		$comp->setProperty( 'comment', "comment set without lang, will inherit language from component: 'fr'" );
		$expected = "COMMENT;LANGUAGE=fr:comment set without lang\, will inherit language from c omponent: 'fr'";
		$actual = $comp->createComment();
		$this->assertStringEquals($expected, $actual, 'When no language is set default is used' );
	}
}