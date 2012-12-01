<?php // createComponents.php
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
// create calendar with components used in test scripts //
$c = new vcalendar( array( 'unique_id' => 'test-se' ));
$c->setProperty( 'calscale', 'gregorian' );
$c->setProperty( 'method', 'testing' );
$c->setProperty( 'X-PROP'
               , 'a test setting a x-prop property in calendar'
               , array( 'language' => 'zz'
                      , 'x-key'    => 'y-value' ));
// 1st component, 1st event component //
$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'X-order', '1st component, 1st event, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'CLASS', 'CONFIDENTIAL', array( 'xparam1', 'xparamKey' => 'xparamValue' ));
$o->setProperty( 'comment'
               , 'This is component 1, vevent number 1, created '.date('Y:m:d H.i.s')
               , array( 'x-comment-paramkey1' => 'x-comment-paramvalue1'
                      , 'x-comment-paramkey2' => 'x-comment-paramvalue2' ));
$o->setProperty( 'created' );
$o->setProperty( 'dtstart'
               , '200'.mt_rand(0,9).'-0'.mt_rand(1,9).'-'.mt_rand(10,27).' 01:01:01' );
$o->setProperty( 'duration'
               , 0, 0, 1 );
$o->setProperty( 'geo'
               , 11.1111, -11.1111
               , array( 'x-geolocation' => 'Mount Everest' ));
$o->setProperty( 'priority'
               , 2
               , array( 'x-priority' => 'HIGH'
                      , 'x-impact' => 'Important' ));
$o->setProperty( 'resources'
               , 'Ficklampa'
               , array( 'altrep' => 'http://www.resources.org/f/ficklampa.txt' ));
$o->setProperty( 'Summary'
               , "This is a summary for the event"
               , array( 'altrep'   => 'http://www.resources.org/s/summary.txt'
                      , 'x-melody' => 'April in Paris'
                      , 'language' => 'se' ));

$a1 = & $o->newComponent( 'valarm' );
$a1->setProperty( 'ACTION', 'AUDIO' );
$a1->setProperty( 'ATTACH'
                , 'ftp://host.com/pub/sounds/bell-01.aud'
                , array( 'FMTTYPE' => 'audio/basic' ));
$a1->setProperty( 'DURATION', 'PT1H' );
$a1->setProperty( 'REPEAT', 4 );
$a1->setProperty( 'trigger', '19970317T133000Z' );
$a1->setProperty( 'X-order', '1st vevent 1st alarm, created '.date('Y:m:d H.i.s') );

$a2 = & $o->newComponent( 'valarm' );
$a2->setProperty( 'ACTION', 'DISPLAY' );
$a2->setProperty( 'DESCRIPTION', 'This is alarm no 2 for 1st vevent component' );
$a2->setProperty( 'DURATION', 'PT15M' );
$a2->setProperty( 'REPEAT', 2 );
$a2->setProperty( 'trigger', '-PT30M' );
$a2->setProperty( 'X-order', '1st vevent 2nd alarm, created '.date('Y:m:d H.i.s') );

// 2nd component, 2nd event component //
$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'X-order', '2nd component, 2nd event, created '.date('Y:m:d H.i.s') );
$o->setConfig( 'language', 'fr' );
$o->setProperty( 'attendee'
               , 'someone@internet.com'
               , array( 'cutype'         => 'Boston'
                      , 'member'         => array( 'member1@domain.net', 'member2@domain.net' )
                      , 'role'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'John Doe'
                      , 'DELEgated-to'   => array( 'delfrom1@domain.net', 'delfrom2@domain.net' )
                      , 'delegateD-FROM' => array( 'delto1@domain.net', 'delto2@domain.net' )
                      , 'SENT-BY'        => 'info@kigkonsult.se'
                      , 'DIR'            => 'http://www.domain.net/doc/info.doc'
                      , 'x-par'          => 'hoppsan'   // xparam
                      , 'x-par2'         => 'tjosan'    // also xparam
                  ));
$o->setProperty( 'attendee', 'someone.else@internet.com' );
$o->setProperty( 'comment'
               , 'This is component 2, vevent number 2, comment number 1, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 2, vevent number 2, comment number 2' );
$o->setProperty( 'comment'
               , 'This is component 2, vevent number 2, comment number 3, UID is set, NOT autogenerated' );
$o->setProperty( 'dtstart'
               , '2011-04-01 08:00:00' );
$o->setProperty( 'duration', 0, 0, 2 );
$o->setProperty( 'Rrule', array( 'FREQ'=> "DAILY", 'COUNT' => 10 ));
$o->setProperty( 'Status'
               , "FINAL"
               , array ('x-final' => 'countdown' ));
$o->setProperty( 'tranSp'
               , "OPAQUE"
               , array( 'x-visible' => 'occupied' ));
$o->setProperty( 'Uid', '19960401T080045Z-4000F192713-0052@host1.com' );
$o->setProperty( 'url'
               , 'http://www.icaldomain2.net'
               , array( 'X-IP-num' => '222.222.222.222' ));
$o->setProperty( 'X-ABC-MMSUBJ', 'http://load.noise.org/mysubj.wav' );

// 3rd component, 1st vtoto component //
$o = & $c->newComponent( 'todo' );
$o->setProperty( 'X-order', '3rd component, 1st vtodo, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 3, vtodo number 1, comment number 1, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 3, vtodo number 1, comment number 2, created '.date('Y:m:d H.i.s') );
$timestamp = mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'completed'
               , array( 'timestamp' => $timestamp )
               , array ( 'x-word'   =>'jestanes'
                       , 'x-key'    => 'xvalue'
                       , 'x-status' => 'yyy' ) );
$o->setProperty( 'dtstart'
               , '200'.mt_rand(0,9).'-0'.mt_rand(1,9).'-'.mt_rand(10,27).' 03:03:03' );
$o->setProperty( 'duration'
               , 0, 0, 3 );
$o->setProperty( 'LOCATION'
               , 'Målilla-kontoret, Avliden'
               , array( 'altrep'      => 'http://www.resources.org/m/målilla.txt'
                      , 'x-location2' => '2nd floor'
                      , 'language'    => 'se' ));

// 4th component, 3th event component //
$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'X-order', '4rd component, 3rd vevent, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'categories'
               , "category1, category2"
               , array('x-Key1' => 'xValue2'));
$o->setProperty( 'categories'
               , "category3"
               , array('x-Key3' => 'xValue3'));
$o->setProperty( 'comment'
               , 'This is component 4, vevent number 3, comment number 1, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 4, vevent number 3, comment number 2, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'dtstart'
               , '200'.mt_rand(0,9).'-0'.mt_rand(1,9).'-'.mt_rand(10,27).' 04:04:04' );
$o->setProperty( 'dtend'
               , '2004-04-04 04:44:44' );
$d2  = array( 2002, 2, 3, 4, 5, 6, '-040506' );
$d4  = array( 'year' => 2004, 'month' => 2, 'day' => 3, 'tz' => '+1200' );
$d9  = '3 Feb 2009';
$o->setProperty( 'EXDATE', array( $d2, $d9, $d4 ));
$rdate1 = array ( 'year' => 2001, 'month' => 1, 'day' => 1, 'hour' => 1, 'min' => 1, 'sec' => 1, 'tz' => '-0200' );
$rdate2 = array ( 2002, 2, 2, 2, 2, 2, '-0200' );
$rdate3 = '3 March 2003 03.03.03';
$rdate4 = array ( 2004, 4, 4, 4, 4, 4, 'GMT' );
$rdate5 = array ( 2005, 10, 5, 5, 5, 5 );
$rdur6  = array ( 'week' => 0, 'day' => 0, 'hour' => 5, 'min' => 5, 'sec' => 5 );
$rdur7  = array ( 0, 0, 6 );
$o->setProperty( 'exrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'COUNT'      => 2
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU'
                      , 'BYSECOND'   => array( -2, 4, 6 )          // single value/array of values
                      , 'BYMINUTE'   => -2                         // single value/array of values
                      , 'BYHOUR'     => 2                          // single value/array of values
                      , 'BYMONTHDAY' => array( 2, -4, 6 )          // single value/array of values
                      , 'BYYEARDAY'  => array( -2, 4, 6 )          // single value/array of values
                      , 'BYWEEKNO'   => -2                         // single value/array of values
                      , 'BYMONTH'    => array( 2, 4, -6 )          // single value/array of values
                      , 'BYSETPOS'   => -2                         // single value/array of values
                      , 'BYday'      => array( 5, 'DAY' => 'MO' )  // single value array/array of value arrays
                      , 'X-NAME'     => 'x-value'));
$o->setProperty( 'last-modified' );
$o->setProperty( 'rdate'
               , array( array( $rdate1, $rdate5 )
                      , array( $rdate2, $rdur6 )
                      , array( $rdate3, $rdur7 )
                      , array( $rdate4, $rdate5 ))
               , array( 'VALUE' => 'PERIOD' ));
$o->setProperty( 'Rrule'
               , array( 'FREQ'       => "MONTHLY"
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
                      , 'X-NAME'     => 'x-value' )
               , array( 'x-key1'     => 'xparamValue1'
                      , 'x-key2'     => 'yParamValue2' ));

// 5th component, 1st vjournal component //
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'X-order', '5th component, 1st vjournal, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 5, vjournal, comment number 1, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 5, vjournal, comment number 2, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'Contact'
               , 'Jim Dolittle, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle)' ));
$o->setProperty( 'contact'
               , 'Johnny Dolittle, Acme & Co, +12 34 56 78 90' );
$o->setProperty( 'dtstart'
               , '200'.mt_rand(0,9).'-0'.mt_rand(1,9).'-'.mt_rand(10,27).' 05:05:05' );
$o->setProperty( 'last-modified' );
$o->setProperty( 'request-status'
               , 3.00
               , '3 hejsan hejsan'
               , '3 gammalt fel, som skickats igen'
               , array( 'language'  => 'se'
                      , 'x-errstat' => 'major' ));

// 6th component, 1st vfreebusy component //
$o = & $c->newComponent( 'freebusy' );
$o->setProperty( 'X-order', '6th component, 1st vfreebusy, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 6, vfreebusy, comment number 1, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 6, vfreebusy, comment number 2, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'Contact'
               , 'Jim Dolittle Jr, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle1)' ));
$o->setProperty( 'Contact'
               , 'Jim Dolittle Sr, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle2)' ));
$o->setProperty( 'dtstart'
               , '200'.mt_rand(0,9).'-0'.mt_rand(1,9).'-'.mt_rand(10,27).' 06:06:06' );
$o->setProperty( 'duration'
               , 0, 0, 6 );
$fdate1 = array ( 'year' => 2001, 'month' => 1, 'day' => 1, 'hour' => 1, 'min' => 1, 'sec' => 1 );
$fdate2 = array ( 2002, 2, 2, 2, 2, 2, '-020202'  );
$fdate3 = array ( 2003, 3, 3, 3, 3, 3 );
$fdate4 = '4 April 2004 4:4:4';
$fdate6 = array ( 5 );
$fdate7 = array ( 'week' => false, 'day' => 5, 'hour' => 5, 'min' => 5, 'sec' => 5 );
$o->setProperty( 'freebusy'
               , 'FREE'
               , array( array( $fdate1, $fdate2 )
                      , array( $fdate3, $fdate6 )
                      , array( $fdate4, $fdate7 ))
               , array( 'x-impact' => 'low'
                      , 'x-type' => 'holiday' ));
$o->setProperty( 'organizer', 'jsmith@host1.com' );

// 7th component, 2nd vtoto component //
$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'X-order', '7th component, 2nd vtodo, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 7, vtodo number 2, comment number 1, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 7, vtodo number 2, comment number 2, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'Contact'
               , 'Jim Dolittle Jr, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle1)' ));
$o->setProperty( 'Contact'
               , 'Jim Dolittle Sr, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle2)' ));
$o->setProperty( 'dtstart'
               , '200'.mt_rand(0,9).'-0'.mt_rand(1,9).'-'.mt_rand(10,27).' 06:06:06' );
$o->setProperty( 'due'
               , '2006-06-26 06:26:26' );
$o->setProperty( 'Percent-Complete', 90 );
$o->setProperty( 'Related-To'
               , '19960401-080045-4000F192713@host.com' );
$o->setProperty( 'sequence', 2 );

// 8th component, 2nd vjournal component //
$o = & $c->newComponent( 'freebusy' );
$o->setProperty( 'X-order', '8th component, 2nd vjournal, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 8, vjournal, comment number 1, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 8, vjournal, comment number 2, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'Contact'
               , 'Jim Dolittle Jr, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle1)' ));
$o->setProperty( 'Contact'
               , 'Jim Dolittle Sr, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle2)' ));
$o->setProperty( 'dtstart'
               , '200'.mt_rand(0,9).'-0'.mt_rand(1,9).'-'.mt_rand(10,27).' 05:05:05' );
$o->setProperty( 'last-modified' );
$o->setProperty( 'request-status'
               , 3.00
               , '3 hejsan hejsan'
               , '3 gammalt fel, som skickats igen'
               , array( 'language'  => 'se'
                      , 'x-errstat' => 'major' ));

// 9th component, 3nd vtoto component //
$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'X-order', '9th component, 3rd vtodo, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 9, vtodo number 3, comment number 1, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'comment'
               , 'This is component 9, vtodo number 3, comment number 2, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'Contact'
               , 'Jim Dolittle Jr, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle1)' ));
$o->setProperty( 'Contact'
               , 'Jim Dolittle Sr, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle2)' ));
$o->setProperty( 'dtstart'
               , '200'.mt_rand(0,9).'-0'.mt_rand(1,9).'-'.mt_rand(10,27).' 09:09:09' );
$o->setProperty( 'duration'
               , 0, 0, 9 );
$o->setProperty( 'Percent-Complete', 99 );
$o->setProperty( 'Related-To'
               , '19960401-080045-4000F192713@host.com' );
$o->setProperty( 'sequence', 2 );

// save calendar in file //
$c->setConfig( 'filename', 'test.ics' );
$c->saveCalendar();
?>