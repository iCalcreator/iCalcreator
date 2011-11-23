<?php
/**
 * iCalcreator v2.10.23
 * copyright (c) 2007-2011 Kjell-Inge Gustafsson, kigkonsult
 * kigkonsult.se/iCalcreator/index.php
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

$v = new vcalendar( array( 'unique_id' => 'kigkonsult.se' ));
                                                // initiate new CALENDAR

$e = & $v->newComponent( 'vevent' );           // initiate a new EVENT
$e->setProperty( 'categories'
               , 'FAMILY' );                   // catagorize
$e->setProperty( 'dtstart'
               ,  2006, 12, 24, 19, 30, 00 );  // 24 dec 2006 19.30
$e->setProperty( 'duration'
               , 0, 0, 3 );                    // 3 hours
$e->setProperty( 'description'
               , 'x-mas evening - diner' );    // describe the event
$e->setProperty( 'location'
               , 'Home' );                     // locate the event

/* alt. production */
// $v->returnCalendar();                       // generate and redirect output to user browser

/* alt. dev. and test */
echo nl2br( $v->createCalendar()) ;            // generate and get output in string, for testing?
echo "<br />\n\n";

/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

$v = new vcalendar( array( 'unique_id' => 'kigkonsult.se' ));
                                                // initiate new CALENDAR

$v->setProperty( 'X-WR-CALNAME'
               , 'Sample calendar' );          // set some X-properties, name, content.. .
$v->setProperty( 'X-WR-CALDESC'
               , 'Description of the calendar' );
$v->setProperty( 'X-WR-TIMEZONE'
               , 'Europe/Stockholm' );

$e = & $v->newComponent( 'vevent' );           // initiate a new EVENT
$e->setProperty( 'categories'
               , 'FAMILY' );                   // catagorize
$e->setProperty( 'dtstart'
               , 2007, 12, 24, 19, 30, 00 );   // 24 dec 2007 19.30
$e->setProperty( 'duration'
               , 0, 0, 3 );                    // 3 hours
$e->setProperty( 'description'
               , 'x-mas evening - diner' );    // describe the event
$e->setProperty( 'location'
               , 'Home' );                     // locate the event

$a = & $e->newComponent( 'valarm' );           // initiate ALARM
$a->setProperty( 'action'
               , 'DISPLAY' );                  // set what to do
$a->setProperty( 'description'
               , 'Buy X-mas gifts' );          // describe alarm
$a->setProperty( 'trigger'
               , array( 'week' => 1 ));        // set trigger one week before

/* alt. production */
// $v->returnCalendar();                          // generate and redirect output to user browser
/* alt. dev. and test */
echo nl2br( $v->createCalendar()) ;            // generate and get output in string, for testing?
echo "<br />\n\n";

/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
/*                                                define timezone           */
$v = new vcalendar( array( 'unique_id' => 'kigkonsult.se' ));
$t = & $v->newComponent( 'vtimezone' );
$t->setProperty( 'tzid'
               , 'US-Eastern');
$t->setProperty( 'last-modified'
               , 1987, 1, 1 );

$ts = & $t->newComponent( 'standard' );        // initiate timezone standard
$ts->setProperty( 'dtstart'
                , 1997, 10, 26, 2 );
$rdate1 = array ( 'year' => 1997, 'month' => 10, 'day' => 26, 'hour' => 02, 'min' => 0, 'sec' => 0 );
$ts->setProperty( 'rdate'
                , array( $rdate1 ));
$ts->setProperty( 'tzoffsetfrom'
                , '-0400' );
$ts->setProperty( 'tzoffsetto'
                , '-0500' );
$ts->setProperty( 'tzname'
                , 'EST' );

$td = & $t->newComponent( 'daylight' );        // initiate timezone daylight
$td->setProperty( 'dtstart'
                , 1997, 10, 26, 2 );
$rdate1 = array ( 'year' => 1997, 'month' => 4, 'day' => 6, 'hour' => 02, 'min' => 0, 'sec' => 0 );
$td->setProperty( 'rdate'
                , array( $rdate1 ));
$td->setProperty( 'tzoffsetfrom'
                , '-0500' );
$td->setProperty( 'tzoffsetto'
                , '-0400' );
$td->setProperty( 'tzname'
                , 'EDT' );

/* alt. production
$v->returnCalendar();                          // generate and redirect output to user browser
*/
/* alt. dev. and test */
echo nl2br( $v->createCalendar()) ;            // generate and get output in string, for testing?
echo "<br />\n\n";

/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

/*
 *   Samples from RFC2445, all output as strings to display
 */

/*
 * Example: The following is an example of the "VEVENT" calendar
 * component used to represent a meeting that will also be opaque to
 * searches for busy time:
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123401@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970903T163000Z
 *   DTEND:19970903T190000Z
 *   SUMMARY:Annual Employee Review
 *   CLASS:PRIVATE
 *   CATEGORIES:BUSINESS,HUMAN RESOURCES
 *   END:VEVENT
 */
$c = new vcalendar( array( 'unique_id' => 'kigkonsult.se' ));
$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart'
               , '19970901T163000Z' );
$e->setProperty( 'dtend'
               , '19970903T190000Z' );
$e->setProperty( 'summary'
               , 'Annual Employee Review' );
$e->setProperty( 'class'
               , 'PRIVATE' );
$e->setProperty( 'categories'
               , 'BUSINESS' );
$e->setProperty( 'categories'
               , 'HUMAN RESOURCES' );

echo nl2br( $v->createCalendar()) ;            // generate and get output in string, for testing?
echo "<br />\n\n";
/*
 * The following is an example of the "VEVENT" calendar component used
 * to represent a reminder that will not be opaque, but rather
 * transparent, to searches for busy time:
 *
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123402@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970401T163000Z
 *   DTEND:19970402T010000Z
 *   SUMMARY:Laurel is in sensitivity awareness class.
 *   CLASS:PUBLIC
 *   CATEGORIES:BUSINESS,HUMAN RESOURCES
 *   TRANSP:TRANSPARENT
 *   END:VEVENT
 */

$c = new vcalendar( array( 'unique_id' => 'kigkonsult.se' ));
$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart'
               , '19970401T163000Z' );
$e->setProperty( 'dtend'
               , '19970402T010000Z' );
$e->setProperty( 'summary'
               , 'Laurel is in sensitivity awareness class.' );
$e->setProperty( 'class'
               , 'PUBLIC' );
$e->setProperty( 'categories'
               , 'BUSINESS' );
$e->setProperty( 'categories'
               , 'HUMAN RESOURCES' );
$e->setProperty( 'transp'
               , 'TRANSPARENT' );
echo nl2br( $v->createCalendar()) ;            // generate and get output in string, for testing?
echo "<br />\n\n";
/*
 * The following is an example of the "VEVENT" calendar component used
 * to represent an anniversary that will occur annually. Since it takes
 * up no time, it will not appear as opaque in a search for busy time;
 * no matter what the value of the "TRANSP" property indicates:
 *
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123403@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19971102
 *   SUMMARY:Our Blissful Anniversary
 *   CLASS:CONFIDENTIAL
 *   CATEGORIES:ANNIVERSARY,PERSONAL,SPECIAL OCCASION
 *   RRULE:FREQ=YEARLY
 *   END:VEVENT
 */

$c = new vcalendar( array( 'unique_id' => 'kigkonsult.se' ));
$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart'
               , '19971102' );
$e->setProperty( 'summary'
               , 'Our Blissful Anniversary' );
$e->setProperty( 'class'
               , 'CONFIDENTIAL' );
$e->setProperty( 'categories'
               , 'ANNIVERSARY' );
$e->setProperty( 'categories'
               , 'PERSONAL' );
$e->setProperty( 'categories'
               , 'SPECIAL OCCASION' );
$e->setProperty( 'rrule'
               , array( 'FREQ' => 'YEARLY' ));

echo nl2br( $v->createCalendar()) ;            // generate and get output in string, for testing?
echo "<br />\n\n";
/*
 *   BEGIN:VTODO
 *   UID:19970901T130000Z-123404@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970415T133000Z
 *   DUE:19970416T045959Z
 *   SUMMARY:1996 Income Tax Preparation
 *   CLASS:CONFIDENTIAL
 *   CATEGORIES:FAMILY,FINANCE
 *   PRIORITY:1
 *   STATUS:NEEDS-ACTION
 *   END:VTODO
 */
$c = new vcalendar( array( 'unique_id' => 'kigkonsult.se' ));
$t = & $c->newComponent( 'vtodo' );
$t->setProperty( 'dtstart'
               , '19970415T133000 GMT' );
$t->setProperty( 'due'
               , '19970416T045959 GMT' );
$t->setProperty( 'summary'
               , '1996 Income Tax Preparation' );
$t->setProperty( 'class'
               , 'CONFIDENTIAL' );
$t->setProperty( 'categories'
               , 'FAMILY' );
$t->setProperty( 'categories'
               , 'FINANCE' );
$t->setProperty( 'priority'
               , 1 );
$t->setProperty( 'status'
               , 'NEEDS-ACTION' );

echo nl2br( $v->createCalendar()) ;            // generate and get output in string, for testing?
?>