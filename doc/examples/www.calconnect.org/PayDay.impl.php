<?php // PayDay_iCal_test.php
echo "12345678901234567890123456789012345678901234567890123456789012345678901234567890<br />\n";
echo "         1         2         3         4         5         6         7         8<br />\n";
require_once '../iCalcreator.class.php';

$str = "
BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:-//Cyrusoft International\, Inc.//Mulberry v4.0//EN
VERSION:2.0
X-WR-CALNAME:PayDay
BEGIN:VTIMEZONE
LAST-MODIFIED:20040110T032845Z
TZID:US/Eastern

BEGIN:DAYLIGHT
DTSTART:20000404T020000
RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
TZNAME:EDT
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
END:DAYLIGHT

BEGIN:STANDARD
DTSTART:20001026T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
TZNAME:EST
TZOFFSETFROM:-0400
TZOFFSETTO:-0500
END:STANDARD
END:VTIMEZONE

BEGIN:VEVENT
DTSTAMP:20050211T173501Z
DTSTART;VALUE=DATE:20040227
RRULE:FREQ=MONTHLY;BYDAY=-1MO,-1TU,-1WE,-1TH,-1FR;BYSETPOS=-1
SUMMARY:PAY DAY
UID:DC3D0301C7790B38631F1FBB@ninevah.local
END:VEVENT
END:VCALENDAR
";
while( 0 < substr_count( $str, '  '))
  $str = str_replace('  ', ' ', $str );
// $str = str_replace(',', ",\n", $str );
echo $str."<br />#################################################\n";

$c = new vcalendar ();
$c->setProperty( 'calscale', 'GREGORIAN' );
$c->setProperty( 'X-WR-CALNAM', 'PayDay' );

$t = & $c->newComponent( 'timezone' );
$t->setProperty( 'Last-Modified', '20040110T032845Z' );
$t->setProperty( 'tzid', 'US/Eastern' );

$d = & $t->newComponent( 'daylight' );
$d->setProperty( 'dtstart', '20000404T020000' );
$d->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 4
                      , 'BYday'      => array( 1, 'DAY' => 'SU' )));
$d->setProperty( 'tzoffsetfrom', '-0500' );
$d->setProperty( 'tzoffsetto', '-0400' );
$d->setProperty( 'tzname', 'EDT' );

$s = & $t->newComponent( 'standard' );
$s->setProperty( 'dtstart', '20001026T020000' );
$s->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 10
                      , 'BYday'      => array( -1, 'DAY' => 'SU' )));
$s->setProperty( 'tzname', 'EST' );
$s->setProperty( 'tzoffsetfrom', '-0400' );
$s->setProperty( 'tzoffsetto', '-0500' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20040227 ');
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYday'      => array( array( -1, 'DAY' => 'MO' )
                                             , array( -1, 'DAY' => 'TU' )
                                             , array( -1, 'DAY' => 'WE' )
                                             , array( -1, 'DAY' => 'TH' )
                                             , array( -1, 'DAY' => 'FR' ))
                      , 'BYSETPOS'   => -1 ));
$e->setProperty( 'summary', 'PAY DAY' );
$e->setProperty( 'uid', 'DC3D0301C7790B38631F1FBB@ninevah.local' );

$str = $c->createCalendar();
echo $str."<br />\n";

?>