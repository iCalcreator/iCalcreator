<?php // selectComponents.php
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
require_once 'displayProperties.php';

$pstartYYYYMMDD = ( isset( $_REQUEST['pstart'] )) ? $_REQUEST['pstart'] : date( 'Ymd', mktime(0, 0, 0, date("m"), date("d"), date("Y")-1 ));
$pendYYYYMMDD   = ( isset( $_REQUEST['pend'] ))   ? $_REQUEST['pend']   : date( 'Ymd', mktime(0, 0, 0, date("m"), date("d"), date("Y")+1 ));
$pstartY        = substr( $pstartYYYYMMDD, 0, 4 );
$pstartM        = substr( $pstartYYYYMMDD, 4, 2 );
$pstartD        = substr( $pstartYYYYMMDD, 6, 2 );
$pendY          = substr( $pendYYYYMMDD, 0, 4 );
$pendM          = substr( $pendYYYYMMDD, 4, 2 );
$pendD          = substr( $pendYYYYMMDD, 6, 2 );
$sort           = ( isset( $_REQUEST['sort'] ))   ? TRUE   : FALSE;

$c = new vcalendar( array( 'unique_id' => 'test.org' ));
if( isset( $_REQUEST['f'] )) {
  $c->setConfig( 'filename', $_REQUEST['f'] );
  dispHeader( '' );
  echo "<b>".$_REQUEST['f']."</b>&nbsp;<small>Display period: $pstartY-$pstartM-$pstartD - $pendY-$pendM-$pendD</small></br>\n";
  if( FALSE === $c->parse())
    echo "FALSE from parse function<br />\n"; // test ###
}
else {
$header = array( '41 Recurrence examples from rfc2445, 4.8.5.4 Recurrence Rule'
               , 'or accepting filename and display period (start and end date)'
               , 'call like [path/]testSelectComponent.php?f=[filename]&pstart=[YYYYMMDD]&pend=[YYYYMMDD]'
               , 'Displays date and all properties for recurring events '.$pstartYYYYMMDD.' - '.$pendYYYYMMDD );
//  $header[] = (defined( MB_OVERLOAD_STRING )) ? ' MB_OVERLOAD_STRING='.MB_OVERLOAD_STRING : ' MB_OVERLOAD_STRING ej laddad';
  dispHeader( $header );
  echo "<h2>Display period: $pstartY-$pstartM-$pstartD - $pendY-$pendM-$pendD</h2>\n";
  iCalUtilityFunctions::createTimezone( $c, 'America/Los_Angeles' );
/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 1' );
  $o->setProperty( 'comment', 'Daily for 10 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=DAILY;COUNT=10' );
/*
 '123456789012345678901234567890123456789012345678901234567890123456789012345'
 '         1         2         3         4         5         6         7
 description:
*/
  $o->setProperty( 'description',
            'ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i åå----åå'.
 '-åi åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ää----ää'.
 '-åi åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i öö----öö'.
 '-åi åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÅÅ----ÅÅ'.
 '-åi åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÄÄ----ÄÄ'.
 '-åi åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÖÖ----ÖÖ'.
 '-åi åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i üü----üü'.
 '-åi åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÜÜ----ÜÜ' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'COUNT'      => 10));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 2' );
  $o->setProperty( 'comment', 'Daily until December 24, 1997:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=DAILY;UNTIL=19971224T000000Z' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2-30;October 1-25' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 26-31;November 1-30;December 1-23' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => '19971224T000000Z' ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 3' );
  $o->setProperty( 'comment', 'Every other day - forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=DAILY;INTERVAL=2' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September2,4,6,8...24,26,28,30; October 2,4,6...20,22,24' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 26,28,30;November 1,3,5,7...25,27,29; Dec 1,3,...');
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'INTERVAL'   => 2 ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 4' );
  $o->setProperty( 'comment', 'Every 10 days, 5 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=DAILY;INTERVAL=10;COUNT=5' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,12,22;October 2,12' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'INTERVAL'   => 10
                      , 'COUNT'      => 5 ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 5' );
  $o->setProperty( 'comment', 'Everyday in January, for 3 years:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19980101T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;UNTIL=20000131T090000Z;BYMONTH=1;BYDAY=SU,MO,TU,WE,TH,FR,SA' );
  $o->setProperty( 'comment', '==> (1998 9:00 AM EDT)January 1-31' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EDT)January 1-31' );
  $o->setProperty( 'comment', '    (2000 9:00 AM EDT)January 1-31' );
  $o->setProperty( 'dtstart', '19980101T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'UNTIL'      => '20000131T090000Z'
                      , 'BYMONTH'    => 1
                      , 'BYDAY'      => array( array( 'DAY' => 'SU' )
                                             , array( 'DAY' => 'MO' )
                                             , array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'WE' )
                                             , array( 'DAY' => 'TH' )
                                             , array( 'DAY' => 'FR' )
                                             , array( 'DAY' => 'SA' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 6, alternative solution for Example 5' );
  $o->setProperty( 'comment', 'Everyday in January, for 3 years:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19980101T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=DAILY;UNTIL=20000131T090000Z;BYMONTH=1' );
  $o->setProperty( 'comment', '==> (1998 9:00 AM EDT)January 1-31' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EDT)January 1-31' );
  $o->setProperty( 'comment', '    (2000 9:00 AM EDT)January 1-31' );
  $o->setProperty( 'dtstart', '19980101T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => '20000131T090000Z'
                      , 'BYMONTH'    => 1 ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 7' );
  $o->setProperty( 'comment', 'Weekly for 10 occurrences' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;COUNT=10' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,9,16,23,30;October 7,14,21' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 28;November 4' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'COUNT'      => 10 ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 8' );
  $o->setProperty( 'comment', 'Weekly until December 24, 1997' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;UNTIL=19971224T000000Z' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,9,16,23,30;October 7,14,21' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 28;November 4,11,18,25; December 2,9,16,23' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => '19971224T000000Z' ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 9' );
  $o->setProperty( 'comment', 'Every other week - forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;INTERVAL=2;WKST=SU' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,16,30;October 14' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 28;November 11,25;December 9,23' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 6,20;February' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU' ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 10' );
  $o->setProperty( 'comment', 'Weekly on Tuesday and Thursday for 5 weeks:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;UNTIL=19971007T000000Z;WKST=SU;BYDAY=TU,TH' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,4,9,11,16,18,23,25,30;October 2' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => '19971007T000000Z'
                      , 'WKST'       => 'SU'
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'TH' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 11. alternative solution for Example 10' );
  $o->setProperty( 'comment', 'Weekly on Tuesday and Thursday for 5 weeks:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;COUNT=10;WKST=SU;BYDAY=TU,TH' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,4,9,11,16,18,23,25,30;October 2' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'COUNT'      => 10
                      , 'WKST'       => 'SU'
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'TH' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 12' );
  $o->setProperty( 'comment', 'Every other week on Monday, Wednesday and Friday until December 24, 1997, but' );   $o->setProperty( 'comment', 'starting on Tuesday, September 2, 1997:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;INTERVAL=2;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,3,5,15,17,19,29;October 1,3,13,15,17' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 27,29,31;November 10,12,14,24,26,28; December 8,10,12,22' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'INTERVAL'   => 2
                      , 'UNTIL'      => '19971224T000000Z'
                      , 'WKST'       => 'SU'
                      , 'BYDAY'      => array( array( 'DAY' => 'MO' )
                                             , array( 'DAY' => 'WE' )
                                             , array( 'DAY' => 'FR' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 13' );
  $o->setProperty( 'comment', 'Every other week on Tuesday and Thursday, for 8 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;INTERVAL=2;COUNT=8;WKST=SU;BYDAY=TU,TH' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,4,16,18,30;October 2,14,16' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'INTERVAL'   => 2
                      , 'COUNT'      => 8
                      , 'WKST'       => 'SU'
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'TH' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 14' );
  $o->setProperty( 'comment', 'Monthly on the 1st Friday for ten occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970905T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;COUNT=10;BYDAY=1FR' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 5;October 3' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)November 7;Dec 5' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 2;February 6;March 6;April 3' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EDT)May 1;June 5' );
  $o->setProperty( 'dtstart', '19970905T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 10
                      , 'BYDAY'      => array( array( 1, 'DAY' => 'FR' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 15' );
  $o->setProperty( 'comment', 'Monthly on the 1st Friday until December 24, 1997:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970905T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;UNTIL=19971224T000000Z;BYDAY=1FR' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 5;October 3' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)November 7;December 5' );
  $o->setProperty( 'dtstart', '19970905T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'UNTIL'      => '19971224T000000Z'
                      , 'BYDAY'      => array( array( 1, 'DAY' => 'FR' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 16' );
  $o->setProperty( 'comment', 'Every other month on the 1st and last Sunday of the month for 10 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970907T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;INTERVAL=2;COUNT=10;BYDAY=1SU,-1SU' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 7,28' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)November 2,30' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 4,25;March 1,29' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EDT)May 3,31' );
  $o->setProperty( 'dtstart', '19970907T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'INTERVAL'   => 2
                      , 'COUNT'      => 10
                      , 'BYDAY'      => array( array( 1,  'DAY' => 'SU' )
                                             , array( -1, 'DAY' => 'SU' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 17' );
  $o->setProperty( 'comment', 'Monthly on the second to last Monday of the month for 6 months:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970922T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;COUNT=6;BYDAY=-2MO' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 22;October 20' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)November 17;December 22' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 19;February 16' );
  $o->setProperty( 'dtstart', '19970922T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 6
                      , 'BYDAY'      => array( array( -2, 'DAY' => 'MO' ))));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 18' );
  $o->setProperty( 'comment', 'Monthly on the third to the last day of the month, forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970928T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;BYMONTHDAY=-3' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 28' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 29;November 28;December 29' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 29;February 26' );
  $o->setProperty( 'comment', '    ...' );
  $o->setProperty( 'dtstart', '19970928T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYMONTHDAY' => -3 ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 19' );
  $o->setProperty( 'comment', 'Monthly on the 2nd and 15th of the month for 10 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;COUNT=10;BYMONTHDAY=2,15' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,15;October 2,15' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)November 2,15;December 2,15' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 2,15' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 10
                      , 'BYMONTHDAY' => array( 2, 15 )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 20' );
  $o->setProperty( 'comment', 'Monthly on the first and last day of the month for 10 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970930T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;COUNT=10;BYMONTHDAY=1,-1' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 30;October 1' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 31;November 1,30;December 1,31' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 1,31;February 1' );
  $o->setProperty( 'dtstart', '19970930T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 10
                      , 'BYMONTHDAY' => array( 1, -1 )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 21' );
  $o->setProperty( 'comment', 'Every 18 months on the 10th thru 15th of the month for 10 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970910T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;INTERVAL=18;COUNT=10;BYMONTHDAY=10,11,12,13,14,15' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 10,11,12,13,14,15' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EST)March 10,11,12,13' );
  $o->setProperty( 'dtstart', '19970910T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'INTERVAL'   => 18
                      , 'COUNT'      => 10
                      , 'BYMONTHDAY' => array( 10, 11, 12, 13, 14, 15 )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 22' );
  $o->setProperty( 'comment', 'Every Tuesday, every other month:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;INTERVAL=2;BYDAY=TU' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 2,9,16,23,30' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)November 4,11,18,25' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 6,13,20,27;March 3,10,17,24,31' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'INTERVAL'   => 2
                      , 'BYDAY'      => array( 'DAY' => 'TU' )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 23' );
  $o->setProperty( 'comment', 'Yearly in June and July for 10 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970610T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;COUNT=10;BYMONTH=6,7' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)June 10;July 10' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EDT)June 10;July 10' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EDT)June 10;July 10' );
  $o->setProperty( 'comment', '    (2000 9:00 AM EDT)June 10;July 10' );
  $o->setProperty( 'comment', '    (2001 9:00 AM EDT)June 10;July 10' );
  $o->setProperty( 'comment', 'Note: Since none of the BYDAY, BYMONTHDAY or BYYEARDAY components' );
  $o->setProperty( 'comment', 'are specified, the day is gotten from DTSTART' );
  $o->setProperty( 'dtstart', '19970610T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'COUNT'      => 10
                      , 'BYMONTH'    => array( 6, 7 )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 24' );
  $o->setProperty( 'comment', 'Every other year on January, February, and March for 10 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970310T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;INTERVAL=2;COUNT=10;BYMONTH=1,2,3' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EST)March 10' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EST)January 10;February 10;March 10' );
  $o->setProperty( 'comment', '    (2001 9:00 AM EST)January 10;February 10;March 10' );
  $o->setProperty( 'comment', '    (2003 9:00 AM EST)January 10;February 10;March 10' );
  $o->setProperty( 'dtstart', '19970310T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'INTERVAL'   => 2
                      , 'COUNT'      => 10
                      , 'BYMONTH'    => array( 1, 2, 3 )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 25' );
  $o->setProperty( 'comment', 'Every 3rd year on the 1st, 100th and 200th day for 10 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970101T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;INTERVAL=3;COUNT=10;BYYEARDAY=1,100,200' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EST)January 1' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EDT)April 10;July 19' );
  $o->setProperty( 'comment', '    (2000 9:00 AM EST)January 1' );
  $o->setProperty( 'comment', '    (2000 9:00 AM EDT)April 9;July 18' );
  $o->setProperty( 'comment', '    (2003 9:00 AM EST)January 1' );
  $o->setProperty( 'comment', '    (2003 9:00 AM EDT)April 10;July 19' );
  $o->setProperty( 'comment', '    (2006 9:00 AM EST)January 1' );
  $o->setProperty( 'dtstart', '19970101T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'INTERVAL'   => 3
                      , 'COUNT'      => 10
                      , 'BYYEARDAY'  => array( 1, 100, 200 )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 26' );
  $o->setProperty( 'comment', 'Every 20th Monday of the year, forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970519T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;BYDAY=20MO' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)May 19' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EDT)May 18' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EDT)May 17' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970519T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYDAY'      => array( 20, 'DAY' => 'MO' )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 27' );
  $o->setProperty( 'comment', 'Monday of week number 20 (where the default start of the week is Monday), forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970512T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;BYWEEKNO=20;BYDAY=MO' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)May 12' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EDT)May 11' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EDT)May 17' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970512T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYWEEKNO'   => 20
                      , 'BYDAY'      => array( 'DAY' => 'MO' )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 28' );
  $o->setProperty( 'comment', 'Every Thursday in March, forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970313T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=TH' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EST)March 13,20,27' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)March 5,12,19,26' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EST)March 4,11,18,25' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970313T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 3
                      , 'BYDAY'      => array( 'DAY' => 'TH' )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 29' );
  $o->setProperty( 'comment', 'Every Thursday, but only during June, July, and August, forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970605T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;BYDAY=TH;BYMONTH=6,7,8' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)June 5,12,19,26;July 3,10,17,24,31;August 7,14,21,28' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EDT)June 4,11,18,25;July 2,9,16,23,30;August 6,13,20,27' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EDT)June 3,10,17,24;July 1,8,15,22,29;August 5,12,19,26' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970605T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => array( 6, 7, 8 )
                      , 'BYDAY'      => array( 'DAY' => 'TH' )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 30' );
  $o->setProperty( 'comment', 'Every Friday the 13th, forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'EXDATE;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;BYDAY=FR;BYMONTHDAY=13' );
  $o->setProperty( 'comment', '==> (1998 9:00 AM EST)February 13;March 13;November 13' );
  $o->setProperty( 'comment', '    (1999 9:00 AM EDT)August 13' );
  $o->setProperty( 'comment', '    (2000 9:00 AM EDT)October 13' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970902T090000', array( 'TZID' => 'America/Los_Angeles' ));
  $o->setProperty( 'exdate',  array( '19970902T090000' )
                          , array( 'TZID' => 'America/Los_Angeles' ));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYDAY'      => array( 'DAY' => 'FR' )
                      , 'BYMONTHDAY' => 13 ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 31' );
  $o->setProperty( 'comment', 'The first Saturday that follows the first Sunday of the month, forever:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970913T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;BYDAY=SA;BYMONTHDAY=7,8,9,10,11,12,13' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 13;October 11' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)November 8;December 13' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 10;February 7;March 7' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EDT)April 11;May 9;June 13...' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970913T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYDAY'      => array( 'DAY' => 'SA' )
                      , 'BYMONTHDAY' => array( 7, 8, 9, 10, 11, 12, 13 )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 32' );
  $o->setProperty( 'comment', "Every four years, the first Tuesday after a Monday in November, forever (U.S.' Presidential Election day):" );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19961105T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=YEARLY;INTERVAL=4;BYMONTH=11;BYDAY=TU;BYMONTHDAY=2,3,4,5,6,7,8' );
  $o->setProperty( 'comment', '==> (1996 9:00 AM EST)November 5' );
  $o->setProperty( 'comment', '    (2000 9:00 AM EST)November 7' );
  $o->setProperty( 'comment', '    (2004 9:00 AM EST)November 2' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19961105T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'INTERVAL'   => 4
                      , 'BYMONTH'    => 11
                      , 'BYDAY'      => array( 'DAY' => 'TU' )
                      , 'BYMONTHDAY' => array( 2, 3, 4, 5, 6, 7, 8 )));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 33' );
  $o->setProperty( 'comment', 'The 3rd instance into the month of one of Tuesday, Wednesday or Thursday, for the next 3 months:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970904T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;COUNT=3;BYDAY=TU,WE,TH;BYSETPOS=3' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 4;October 7' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)November 6' );
  $o->setProperty( 'comment', 'iCalcreator remark: also  1997-09-11 due to ambiguity in BYSETPOS definition' );
  $o->setProperty( 'dtstart', '19970904T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 3
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                              ,array( 'DAY' => 'WE' )
                                              ,array( 'DAY' => 'TH' ))
                      , 'BYSETPOS'   => 3 ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 34' );
  $o->setProperty( 'comment', 'The 2nd to last weekday of the month:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970929T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MONTHLY;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-2' );
  $o->setProperty( 'comment', '==> (1997 9:00 AM EDT)September 29' );
  $o->setProperty( 'comment', '    (1997 9:00 AM EST)October 30;November 27;December 30' );
  $o->setProperty( 'comment', '    (1998 9:00 AM EST)January 29;February 26;March 30' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970929T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYDAY'      => array( array( 'DAY' => 'MO' )
                                              ,array( 'DAY' => 'TU' )
                                              ,array( 'DAY' => 'WE' )
                                              ,array( 'DAY' => 'TH' )
                                              ,array( 'DAY' => 'FR' ))
                      , 'BYSETPOS'   => -2 ));
/**//* end */

/*
// no time recurrence function implemented, nothing to test here
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 35' );
  $o->setProperty( 'comment', 'Every 3 hours from 9:00 AM to 5:00 PM on a specific day:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=HOURLY;INTERVAL=3;UNTIL=19970902T170000Z' );
  $o->setProperty( 'comment', '==> (September 2, 1997 EDT)09:00,12:00,15:00' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "HOURLY"
                      , 'INTERVAL'   => 3
                      , 'UNTIL'      => '19970902T170000Z' ));


// no time recurrence function implemented, nothing to test here
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 36' );
  $o->setProperty( 'comment', 'Every 15 minutes for 6 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MINUTELY;INTERVAL=15;COUNT=6' );
  $o->setProperty( 'comment', '==> (September 2, 1997 EDT)09:00,09:15,09:30,09:45,10:00,10:15' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MONUTELY"
                      , 'INTERVAL'   => 15
                      , 'COUNT'      => 6 ));


// no time recurrence function implemented, nothing to test here
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 37' );
  $o->setProperty( 'comment', 'Every hour and a half for 4 occurrences:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MINUTELY;INTERVAL=90;COUNT=4' );
  $o->setProperty( 'comment', '==> (September 2, 1997 EDT)09:00,10:30;12:00;13:30' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MINUTELY"
                      , 'INTERVAL'   => 90
                      , 'COUNT'      => 4 ));


// no time recurrence function implemented, nothing to test here
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 38' );
  $o->setProperty( 'comment', 'Every 20 minutes from 9:00 AM to 4:40 PM every day:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=DAILY;BYHOUR=9,10,11,12,13,14,15,16;BYMINUTE=0,20,40' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MINUTELY;INTERVAL=20;BYHOUR=9,10,11,12,13,14,15,16' );
  $o->setProperty( 'comment', '==> (September 2, 1997 EDT)9:00,9:20,9:40,10:00,10:20,... 16:00,16:20,16:40' );
  $o->setProperty( 'comment', '    (September 3, 1997 EDT)9:00,9:20,9:40,10:00,10:20,... 16:00,16:20,16:40' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'BYHOUR'     => array( 9, 10, 11, 12, 13, 14, 15, 16 )
                      , 'BYMINUTE'   => array( 0, 20, 40 )));


// no time recurrence function implemented, nothing to test here
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 39' );
  $o->setProperty( 'comment', 'Every 20 minutes from 9:00 AM to 4:40 PM every day (alternative):' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970902T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=MINUTELY;INTERVAL=20;BYHOUR=9,10,11,12,13,14,15,16' );
  $o->setProperty( 'comment', '==> (September 2, 1997 EDT)9:00,9:20,9:40,10:00,10:20,... 16:00,16:20,16:40' );
  $o->setProperty( 'comment', '    (September 3, 1997 EDT)9:00,9:20,9:40,10:00,10:20,... 16:00,16:20,16:40' );
  $o->setProperty( 'comment', '     ...' );
  $o->setProperty( 'dtstart', '19970902T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "MINUTELY"
                      , 'INTERVAL'   => 20
                      , 'BYHOUR'     => array( 9, 10, 11, 12, 13, 14, 15, 16 )));
*/

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 40' );
  $o->setProperty( 'comment', 'An example where the days generated makes a difference because of WKST:' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970805T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;INTERVAL=2;COUNT=4;BYDAY=TU,SU;WKST=MO' );
  $o->setProperty( 'comment', '==> (1997 EDT)Aug 5,10,19,24' );
  $o->setProperty( 'dtstart', '19970805T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'INTERVAL'   => 2
                      , 'COUNT'      => 4
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                              ,array( 'DAY' => 'SU' ))
                      , 'WKST'       => 'MO' ));
/**//* end */

/* start *//**/
  $o = & $c->newComponent( 'vevent' );
  $o->setProperty( 'comment', 'Example 41, same as Example 40 BUT' );
  $o->setProperty( 'comment', 'changing only WKST from MO to SU, yields different results...' );
  $o->setProperty( 'comment', 'DTSTART;TZID=America/Los_Angeles:19970805T090000' );
  $o->setProperty( 'comment', 'RRULE:FREQ=WEEKLY;INTERVAL=2;COUNT=4;BYDAY=TU,SU;WKST=SU' );
  $o->setProperty( 'comment', '==> (1997 EDT)August 5,17,19,31' );
  $o->setProperty( 'dtstart', '19970805T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'dtend',   '19970806T090000', array('TZID' => 'America/Los_Angeles'));
  $o->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'INTERVAL'   => 2
                      , 'COUNT'      => 4
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                              ,array( 'DAY' => 'SU' ))
                      , 'WKST'       => 'SU' ));
/**//* end */
  //$c->saveCalendar(); // test !!!
}
$c->sort();
if( FALSE !== ( $component = $c->getComponent( 'vtimezone' )))
  vtimezoneshow( $component );
$sc = $c->selectComponents( $pstartY, $pstartM, $pstartD, $pendY, $pendM, $pendD );  // all defaults:  FALSE, FALSE, TRUE, TRUE
// $sc = $c->selectComponents( $pstartY, $pstartM, $pstartD, $pendY, $pendM, $pendD, FALSE, FALSE, TRUE, FALSE ); // no split
if( $sort )
  selsortshow( $sc);
else
  selshow( $sc);

dispFooter();
?>