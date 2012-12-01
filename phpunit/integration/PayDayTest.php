<?php

/**
 * The following test creates a calendar and compares it against its output.
 * 
 * Instead of a simple string comparison of the outputs, a phpunit constraint 
 * should be written that takes into account, that the position of some 
 * elements can be permutated without changing the meaning of the calendar.
 */
class PayDayTest extends renderer_TestCase
{
	
	public function testIcal()
	{
		$actual = $this->createPayday('ical');
		$expected = $this->getExpectedIcal();
		$this->assertEqualIcals($actual->createCalendar(), $expected);
	}
	
	public function testXcal()
	{
		$actual = $this->createPayday('xcal');
		$expected = $this->getExpectedXcal();
		$this->assertEqualCalendarOutput($actual->createCalendar(), $expected);
	}

	public function createPayday( $format )
	{
		$c = new vcalendar (array('format' => $format ));
		$c->setProperty('calscale', 'GREGORIAN');
		$c->setProperty('X-WR-CALNAME', 'PayDay');
		$c->prodid = "-//Cyrusoft International\, Inc.//Mulberry v4.0//EN";
		$t = & $c->newComponent('timezone');
		$t->setProperty('Last-Modified', '20040110T032845Z');
		$t->setProperty('tzid', 'US/Eastern');

		$d = & $t->newComponent('daylight');
		$d->setProperty('dtstart', '20000404T020000');
		$d->setProperty('rrule'
				, array('FREQ'		 => "YEARLY"
			, 'BYMONTH'	 => 4
			, 'BYday'		 => array(1, 'DAY' => 'SU')));
		$d->setProperty('tzoffsetfrom', '-0500');
		$d->setProperty('tzoffsetto', '-0400');
		$d->setProperty('tzname', 'EDT');
		
		$s = & $t->newComponent('standard');
		$s->setProperty('dtstart', '20001026T020000');
		$s->setProperty('rrule'
				, array('FREQ'		 => "YEARLY"
			, 'BYMONTH'	 => 10
			, 'BYday'		 => array(-1, 'DAY' => 'SU')));
		$s->setProperty('tzname', 'EST');
		$s->setProperty('tzoffsetfrom', '-0400');
		$s->setProperty('tzoffsetto', '-0500');

		$e = & $c->newComponent('vevent');
		$e->setProperty('dtstart', '20040227 ');
		$e->setProperty('rrule'
				, array('FREQ'	 => "MONTHLY"
			, 'BYday'	 => array(array(-1, 'DAY' => 'MO')
				, array(-1, 'DAY' => 'TU')
				, array(-1, 'DAY' => 'WE')
				, array(-1, 'DAY' => 'TH')
				, array(-1, 'DAY'		 => 'FR'))
			, 'BYSETPOS'	 => -1));
		$e->setProperty('summary', 'PAY DAY');
		$e->setProperty('uid', 'DC3D0301C7790B38631F1FBB@ninevah.local');
		
		return $c;
	}
	
	public function assertEqualCalendarOutput( $got, $expected )
	{
		// The Date Stamp changes with each creation
		// ical
		$got = preg_replace( '/DTSTAMP:\d{8}T\d{6}Z/', 'DTSTAMP:12345678T123456Z', $got );
		$expected = preg_replace( '/DTSTAMP:\d{8}T\d{6}Z/', 'DTSTAMP:12345678T123456Z', $expected );
		// xcal
		$got = preg_replace( '/<dtstamp>\d{8}T\d{6}Z<\/dtstamp>/', '<dtstamp>12345678T123456Z</dtstamp>', $got );
		$expected = preg_replace( '/<dtstamp>\d{8}T\d{6}Z<\/dtstamp>/', '<dtstamp>12345678T123456Z</dtstamp>', $expected );
		
		$this->assertEquals( $got, $expected );
	}

	public function getExpectedIcal()
	{
		$str = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Cyrusoft International\, Inc.//Mulberry v4.0//EN
CALSCALE:GREGORIAN
X-WR-CALNAME:PayDay
BEGIN:VTIMEZONE
TZID:US/Eastern
LAST-MODIFIED:20040110T032845Z
BEGIN:STANDARD
DTSTART:20001026T020000
TZOFFSETFROM:-0400
TZOFFSETTO:-0500
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
TZNAME:EST
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:20000404T020000
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
TZNAME:EDT
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
UID:DC3D0301C7790B38631F1FBB@ninevah.local
DTSTAMP:20121121T052137Z
DTSTART:20040227T000000
RRULE:FREQ=MONTHLY;BYDAY=-1MO,-1TU,-1WE,-1TH,-1FR;BYSETPOS=-1
SUMMARY:PAY DAY
END:VEVENT
END:VCALENDAR
";
		return $str;
	}
	
	public function getExpectedXcal()
	{
		$str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE vcalendar PUBLIC \"-//IETF//DTD XCAL/iCalendar XML//EN\"
\"http://www.ietf.org/internet-drafts/draft-ietf-calsch-many-xcal-01.txt\" [
<!ELEMENT x-wr-calname (#PCDATA)>
]>
<vcalendar
 version=\"2.0\"
 prodid=\"-//Cyrusoft International\, Inc.//Mulberry v4.0//EN\"
 calscale=\"GREGORIAN\">
<x-wr-calname>PayDay</x-wr-calname>
<vtimezone>
<tzid>US/Eastern</tzid>
<last-modified>20040110T032845Z</last-modified>
<standard>
<dtstart>20001026T020000</dtstart>
<tzoffsetfrom>-0400</tzoffsetfrom>
<tzoffsetto>-0500</tzoffsetto>
<rrule>FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10</rrule>
<tzname>EST</tzname>
</standard>
<daylight>
<dtstart>20000404T020000</dtstart>
<tzoffsetfrom>-0500</tzoffsetfrom>
<tzoffsetto>-0400</tzoffsetto>
<rrule>FREQ=YEARLY;BYDAY=1SU;BYMONTH=4</rrule>
<tzname>EDT</tzname>
</daylight>
</vtimezone>
<vevent>
<uid>DC3D0301C7790B38631F1FBB@ninevah.local</uid>
<dtstamp>20121121T053119Z</dtstamp>
<dtstart>20040227T000000</dtstart>
<rrule>FREQ=MONTHLY;BYDAY=-1MO,-1TU,-1WE,-1TH,-1FR;BYSETPOS=-1</rrule>
<summary>PAY DAY</summary>
</vevent>
</vcalendar>
";
		return $str;
	}

}