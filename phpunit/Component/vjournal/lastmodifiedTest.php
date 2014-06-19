<?php
class lastmodifiedTest extends calendarComponent_TestCase
{
	
	public function testdefault()
	{					
		extract($this->preparetime(time(), ini_get('date.timezone')));
		
		$comp = new vjournal();
		$comp->setProperty('last-modified');
		$actual = $comp->createLastModified();
		$expected = 'LAST-MODIFIED:'.$utciso;
		$this->assertStringEquals($expected, $actual, 'If no argument is supplied assume now');
	}		
	
	/**
	 * @dataProvider timeProvider
	 */
	public function testSimple( $time , $tzid )
	{
		extract($this->preparetime($time, $tzid));
								
		$comp = new vjournal();
		$comp->setProperty( 'last-modified', $date );
		$actual = $comp->createLastModified();		
		$this->assertStringEquals('LAST-MODIFIED:'.$dateiso.'Z', $actual);
	}		
	
	/**
	 * @dataProvider timeProvider
	 */
	public function testParams( $time , $tzid )
	{
		extract($this->preparetime($time, $tzid));
								
		$comp = new vjournal();
		$comp->setProperty( 'last-modified', $date, array( 'xparam', 'xparaMKey' => 'xparamValue' ) );
		$actual = $comp->createLastModified();		
		$this->assertStringEquals('LAST-MODIFIED;xparam;XPARAMKEY=xparamValue:'.$dateiso.'Z', $actual);
	}
}