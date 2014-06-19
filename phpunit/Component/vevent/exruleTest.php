<?php
class exruleTest extends calendarComponent_TestCase
{
	
	/**
	 * Test the simplest case of setting an exrule.
	 * 
	 * @dataProvider exruleProvider
	 */
	public function testSimple( $rule, $expected )
	{			
		$comp = new vevent();
		$comp->setProperty( 'exrule', $rule );
		$actual = $comp->createExrule();		
		$this->assertStringEquals($expected, $actual);		
	}
	
	public function testRepetition()
	{									
		$comp = new vevent();
		$expected = '';
		foreach( $this->exruleProvider() as $exruledata )
		{
			$comp->setProperty( 'exrule', $exruledata[0] );
			$expected.= $exruledata[1];
		}
		$actual = $comp->createExrule();
		
		$this->assertStringEquals($expected, $actual);		
	}
	
	public function testSpelling()
	{
		$exruleset = $this->exruleProvider();
		$exruledata = $exruleset[0];
		
		$comp = new vevent();		
		$comp->setProperty( 'exRule', $exruledata[0] );
		$actual = $comp->createExrule();	
		$expected = $exruledata[1];
		$this->assertStringEquals($expected, $actual);		
		
		$comp = new vevent();		
		$comp->setProperty( 'EXRULE', $exruledata[0] );
		$actual = $comp->createExrule();	
		$expected = $exruledata[1];
		$this->assertStringEquals($expected, $actual);		
	}
		
	public function exruleProvider()
	{
		$detailed = array( 'FREQ'       => "MONTHLY"
                      , 'UNTIL'      => '3 Feb 2007'
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU'
                      , 'BYSECOND'   => 2
                      , 'BYMINUTE'   => array( 2, -4, 6 )                    // single value/array of values
                      , 'BYHOUR'     => array( 2, 4, -6 )                    // single value/array of values
                      , 'BYMONTHDAY' => -2                                   // single value/array of values
                      , 'BYYEARDAY'  => 2                                    // single value/array of values
                      , 'BYWEEKNO'   => array( 2, -4, 6 )                    // single value/array of values
                      , 'BYMONTH'    => 2                                    // single value/array of values
                      , 'BYSETPOS'   => array( 2, -4, 6 )                    // single value/array of values
                      , 'BYday'      => array( array( -2, 'DAY' => 'WE' )    // array of values
                                             , array(  3, 'DAY' => 'TH' )
                                             , array(  5, 'DAY' => 'FR' )
                                             ,            'DAY' => 'SA'
                                             , array(     'DAY' => 'SU' ))
				);
		
		$detailedYear = array( 'FREQ'       => "YEARLY"
                      , 'COUNT'      => 2
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU'
                      , 'BYSECOND'   => array( -2, 4, 6 )                    // single value/array of values
                      , 'BYMINUTE'   => -2                                   // single value/array of values
                      , 'BYHOUR'     => 2                                    // single value/array of values
                      , 'BYMONTHDAY' => array( 2, -4, 6 )                    // single value/array of values
                      , 'BYYEARDAY'  => array( -2, 4, 6 )                    // single value/array of values
                      , 'BYWEEKNO'   => -2                                   // single value/array of values
                      , 'BYMONTH'    => array( 2, 4, -6 )                    // single value/array of values
                      , 'BYSETPOS'   => -2                                   // single value/array of values
                      , 'BYday'      => array( 5, 'DAY' => 'MO' )            // single value array/array of value arrays
                      , 'X-NAME'     => 'x-value');
					
		return array(
			array( array( 'FREQ' => "HOURLY", 'UNTIL' => array( 2001, 2, 3 ), 'INTERVAL' => 2 ), 'EXRULE:FREQ=HOURLY;UNTIL=20010203;INTERVAL=2' ),
			array( $detailed, 'EXRULE:FREQ=MONTHLY;UNTIL=20070203;INTERVAL=2;BYSECOND=2;BYMINUTE=2 ,-4,6;BYHOUR=2,4,-6;BYDAY=-2WE,3TH,5FR,SA,SU;BYMONTHDAY=-2;BYYEARDAY=2;BYW EEKNO=2,-4,6;BYMONTH=2;BYSETPOS=2,-4,6;WKST=SU' ),
			array( $detailedYear, 'EXRULE:FREQ=YEARLY;COUNT=2;INTERVAL=2;BYSECOND=-2,4,6;BYMINUTE=-2;BYHOUR=2; BYDAY=5MO;BYMONTHDAY=2,-4,6;BYYEARDAY=-2,4,6;BYWEEKNO=-2;BYMONTH=2,4,-6;BY SETPOS=-2;WKST=SU' ),
			array( array( 'FREQ' => "WEEKLY", 'UNTIL' => array( 2001, 2, 3, 0, 0, 0, '+0200' ), 'BYMONTHDAY' => array( 2, -4, 6 )), 'EXRULE:FREQ=WEEKLY;UNTIL=20010202T220000Z;BYMONTHDAY=2,-4,6' ),
			array( array( 'FREQ' => "HOURLY", 'UNTIL' => array( 2001, 2, 3, 4, 5, 6 )), 'EXRULE:FREQ=HOURLY;UNTIL=20010203T040506Z' ),
			array( array( 'FREQ' => "DAILY", 'UNTIL' => array( 'year' => 1, 'month' => 2, 'day' => 3 ), 'BYday' => array( 'DAY' => 'WE' )), 'EXRULE:FREQ=DAILY;UNTIL=00010203;BYDAY=WE'),
			array( array( 'FREQ' => "DAILY", 'UNTIL' => array( 'year' => 1, 'month' => 2, 'day' => 3), 'BYday' => array( 'DAY' => 'WE' )), 'EXRULE:FREQ=DAILY;UNTIL=00010203;BYDAY=WE'),
			array( array( 'FREQ' => "WEEKLY", 'UNTIL' => array( 'year' => 1, 'month' => 2, 'day' => 3, 'hour' => 4, 'min' => 5, 'sec' => 6 ), 'BYday' => array( 5, 'DAY' => 'WE' ) ) , 'EXRULE:FREQ=WEEKLY;UNTIL=00010203T040506Z;BYDAY=5WE'),
			array( array( 'FREQ' => "MONTHLY", 'UNTIL' => array( 'timestamp' => 1354291267 ), 'BYday' => array( -1, 'DAY' => 'MO' )) , 'EXRULE:FREQ=MONTHLY;UNTIL=20121130T160107Z;BYDAY=-1MO'),
		);
	}
}