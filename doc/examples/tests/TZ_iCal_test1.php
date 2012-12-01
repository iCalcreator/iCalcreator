<?php // TZ_iCal_test1.php
/**
 * iCalcreator class v2.10
 * copyright (c) 2007-2011 Kjell-Inge Gustafsson, kigkonsult
 * www.kigkonsult.se/iCalcreator/index.php
 * ical@kigkonsult.se
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once '../iCalcreator.class.php';

echo "12345678901234567890123456789012345678901234567890123456789012345678901234567890<br />\n";
echo "         1         2         3         4         5         6         7         8<br />\n";

echo "<br>Model to create:<br>\n";
$tpl = "
    BEGIN:VTIMEZONE
    TZID:US-Eastern
    LAST-MODIFIED:19870101T000000Z
    BEGIN:STANDARD
    DTSTART:19971026T020000
    RDATE:19971026T020000
    TZOFFSETFROM:-0400
    TZOFFSETTO:-0500
    TZNAME:EST
    END:STANDARD
    BEGIN:DAYLIGHT
    DTSTART:19971026T020000
    RDATE:19970406T020000
    TZOFFSETFROM:-0500
    TZOFFSETTO:-0400
    TZNAME:EDT
    END:DAYLIGHT
    END:VTIMEZONE";
while( 0 < substr_count( $tpl, '  '))
  $tpl = str_replace('  ', ' ', $tpl );
echo $tpl;

echo "<br />\n<br />\n";

$c = new vcalendar ();
// $c->setProperty( 'X-input', $tpl );

$t = & $c->newComponent( 'timezone' );
$t->setProperty( 'Tzid', 'US-Eastern' );
$t->setProperty( 'Last-Modified', '19870101' );

$s = & $t->newComponent( 'standard' );
$s->setProperty( 'Dtstart', '19971026020000' );
$s->setProperty( 'Rdate', array( '19971026020000' ));
$s->setProperty( 'Tzoffsetfrom', '-0400' );
$s->setProperty( 'tzoffsetTo', '-0500' );
$s->setProperty( 'tzname', 'EST' );

$d = & $t->newComponent( 'daylight' );
$d->setProperty( 'dtstart', '19971026020000' );
$d->setProperty( 'rdate', array( '19970406020000' ));
$d->setProperty( 'tzoffsetfrom', '-0500' );
$d->setProperty( 'tzoffsetto', '-0400' );
$d->setProperty( 'tzname', 'EDT' );

// save calendar in file, create new calendar, parse saved file
$c->setConfig( 'filename', 'test.ics' );
$c->saveCalendar();

$c2 = new vcalendar();
$c2->setConfig( 'filename', 'test.ics' );
$c2->parse();
$c2->setConfig( 'filename', 'test2.ics' );
$c2->saveCalendar();

$str = $c2->createCalendar();
echo $str;

$a=array(); $n=chr(10); $cmd='diff ./test.ics ./test2.ics'; exec($cmd,$a); echo "$n save as 'test.ics, parse, save as 'test2.ics' and then compare files ($cmd) (if any): $n".implode($n,$a);
?>
