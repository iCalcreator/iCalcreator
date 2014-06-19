<?php // rdate_iCal_test.php

require_once '../iCalcreator.class.php';
/*
echo "12345678901234567890123456789012345678901234567890123456789012345678901234567890<br />
";
echo "         1         2         3         4         5         6         7         8<br />
";
*/
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

// $rdate1 = array ( 2001, 1, 1, 1, 1, 1 );
// alt.
$rdate1 = array ( 'year' => 2001, 'month' => 1, 'day' => 1, 'hour' => 1, 'min' => 1, 'sec' => 1, 'tz' => '-0200' );
$rdate2 = array ( 2002, 2, 2, 2, 2, 2, '-0200' );
$rdate3 = '3 March 2003 03.03.03';
$rdate4 = array ( 2004, 4, 4, 4, 4, 4, 'GMT' );
$rdate5 = array ( 2005, 10, 5, 5, 5, 5 );
$rdur6 = array ( 'week' => 0, 'day' => 0, 'hour' => 5, 'min' => 5, 'sec' => 5 );
$rdur7 = array ( 0, 0, 6 ); // duration for 6 hours
$rdurH8 = array ( 0, 0, 8 ); // duration for 8 hours
$rdate8 = array ( 'year' => 2007, 'month' => 7, 'day' => 7 );
$timestamp = mktime( 9, 9, 9, 9, 9, 2009);
$rdate9    = array( 'timestamp' => $timestamp );
$timestamp = mktime( 10, 10, 10, 10, date('d'), date('Y') + 1 );
$rdat10    = array( 'timestamp' => $timestamp, 'tz' => '+0100' );

$e = & $c->newComponent( 'vevent' );

// $d = date('d M Y H:i:s');
$d1 = date('Y-m-d H:i:s');
$e->setProperty( 'comment', "-1 2008-10-23" );
$e->setProperty( 'rdate', array( '2008-10-23' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '0 20081008  -  10/08/2008');
$e->setProperty( 'rdate', array( array( '20081008', '10/08/2008' ))
                             , array( 'VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '1 '.implode( '-',$rdate1 ));
$e->setProperty( 'rdate', array( $rdate1 ));
   // one recurrence date, date in 3-params format

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '2 '.implode( '  ', array( implode('-',$rdate1), implode('-',$rdate2 ))));
$e->setProperty( 'rdate', array( $rdate1, $rdate2 ));
   // two dates, date 7-params format

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '3 '.implode('  ', array(
  implode('/',array( implode( '-',$rdate1), implode('-',$rdate5 )))
 // Both fromdate and tomdate must have 7 params !!!
, implode('/',array( implode( '-',$rdate2), implode( '-',$rdur6 )))
   // duration
, implode('/',array( $rdate3, implode( '-',$rdur7 )))
   // period, pairs of fromdate <-> tom -date/-duration
, implode('/',array( implode( '-',$rdate4), implode( '-',$rdate5 )))
)));
$e->setProperty( 'rdate', array(
  array( $rdate1, $rdate5 )
 // Both fromdate and tomdate must have 7 params !!!
, array( $rdate2, $rdur6 )
   // duration
, array( $rdate3, $rdur7 )
   // period, pairs of fromdate <-> tom -date/-duration
, array( $rdate4, $rdate5 )), array( 'VALUE' => 'PERIOD' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment','4 '.$rdate3.' tz=CEST' );
$e->setProperty( 'rdate', array( $rdate3 ), array( 'TZID' => 'CEST'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment','5 '.implode( '-',$rdate9).' = '.date('YmdHis',$rdate9['timestamp']));
$e->setProperty( 'rdate', array( $rdate9 ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '6 '.implode( '-',$rdat10 ));
$e->setProperty( 'rdate', array( $rdat10 ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '7 '.implode( '  ', array( implode('-',$rdat10), implode('-',$rdur6 ))). ' PERIOD');
$e->setProperty( 'rdate', array( array( $rdat10, $rdur6 )), array( 'VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '2nd rdate deleted');
$e->deleteProperty( 'rdate', 2);

$d1 = array( 2001, 1, 1, 1, 1, 1, '-010101' );
$d2 = array( 'year' => 2002, 'month' => 2, 'day' => 2, 'hour' => 2, 'min' => 2, 'sec' => 2 );
$d3 = array( 2003, 3, 3, 0, 0, 0, '-0300' );
$d4 = array( 2004, 5, 6 );
$d5 = array( 2005, 5, 5, 5, 5, 5, '-050505' );
$da = '5 May 2005 5:5:5';
$db =  '5/1/2005 5.2';
$timestamp = mktime( 10, 10, 10, date('m'), date('d'), date('Y') );
$dc = array( 'timestamp' => $timestamp, 'tz' => '+0100' );
$d6 = array( 0, 5, 5, 5, 5 );
$d7 = array( 'week' => 0, 'day' => 0, 'hour' => 5, 'min' => 5, 'sec' => 5 );
$d8 = array( 0, 0, 6, 0 );             // duration for 6 hours
$d9 = array( 'year' => 2007, 'month' => 7, 'day' => 7, 'hour' => 5, 'min' => 5, 'sec' => 5 );
$d0 = array( 0,1 );             // duration for 1 day/week
$dd = array( 'sec' => 6 * 3600); // duration for 3 hours in seconds

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '8 '.implode('-',$d1 ) );
$e->setProperty( 'rdate', array( $d1 ));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '8a '.implode('-',$d4 ).' * '.implode('-',$d4 ).' (period)');
$e->setProperty( 'rdate', array( array( $d4, $d4 )), array( 'VALUE' => 'PERIOD' ));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '9 '.implode('  ', array( implode('-',$d3), implode('-',$d4))));
$e->setProperty( 'rdate', array( $d3, $d4 ));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '10 '.implode('  ', array( implode('-',$d4), implode('-',$d3), implode('-',$d2), implode('-',$d1))).", array( 'VALUE' => 'DATE' )");
$e->setProperty( 'rdate'
               , array( $d4, $d3, $d2, $d1 )
               , array( 'VALUE' => 'DATE' ));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '11 '.implode(', ', array( implode('-',$dc), implode('-',$dc))));
$e->setProperty( 'rdate', array( $dc, $dc ));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '12 '.implode('  ', array( implode('/', array( implode('-',$dc), implode('-',$dd))))));
$e->setProperty( 'rdate', array( array( $dc, $dd )), array( 'VALUE' => 'PERIOD' ));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '13 '.implode('  ', array( implode('-',$d3), implode('-',$d4))));
$e->setProperty( 'rdate', array( $d3, $d4 ));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '14 '.implode('  ', array( implode('/', array( implode('-',$d9), implode('-',$d8))))));
$e->setProperty( 'rdate', array( array( $d9, $d8 )), array('VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '15 '.implode('  ', array( implode('/', array( implode('-',$d9), implode('-',$rdurH8))))));
$e->setProperty( 'rdate', array( array( $d9, $rdurH8 )), array('VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vtodo' );
//$e->setRdate( array( $d2 ));
$e->setProperty( 'comment', '16 '.implode('  ', array( implode('/', array( $db, implode('-',$d8))))));
$e->setProperty( 'rdate', array( array( $db, $d8 )), array('VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment','17 '.implode('  ', array(
  implode('/', array( implode('-',$d1), implode('-',$d0)))
, implode('/', array( implode('-',$d2), implode('-',$d7)))
)));
$e->setProperty( 'rdate'
               , array( array( $d1, $d0 )
                      , array( $d2, $d7 )
                      )
               , array( 'xKey' => 'xValue', 'VALUE' => 'PERIOD' )
               );

$dx1 = date('d M Y H:i:s').' +020000';
$dx1a = date('d M Y H:i:s T');
$dx2 = date('Y-m-d H:i:s');
$dx2a = date('YmdTHis O');
$rdate5[] = '+020000';
$dx3 = array( 2008, 10, 7 );
$dx4 = date('YmdHis').' +020000';

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', "18 $dx1 + $dx2 ");
$e->setProperty( 'rdate', array( array( $dx1, $dx2 )), array( 'VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', "18a $dx1a + $dx2a ");
$e->setProperty( 'rdate', array( array( $dx1a, $dx2a )), array( 'VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', "18b $dx1a + ".implode('-',$rdate5));
$e->setProperty( 'rdate', array( array( $dx1a, $rdate5 )), array( 'VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', "19 $dx1 + $timestamp ");
$e->setProperty( 'rdate', array( array( $dx1, array( 'timestamp' => $timestamp )))
                        , array( 'VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', "19a PERIOD ($timestamp + ($timestamp . 'CEST')), ($timestamp + $dx1)");
$e->setProperty( 'rdate', array( array( array( 'timestamp' => $timestamp )
                                      , array( 'timestamp' => $timestamp, 'tz' => 'CEST' ))
                               , array( array( 'timestamp' => $timestamp ), $dx1))
                        , array( 'VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'comment', '20 '.implode('.',$dx3)." + $dx4");
$e->setProperty( 'rdate', array( array( $dx3, $dx4 )), array( 'VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '21 VALUE=PERIOD:20110801T080000Z/PT30M,20111001T080000Z/PT3H,20111201T080000Z/P3W' );
$e->setProperty( 'rdate', array( array( '20110801T080000Z', 'PT30M' )
                               , array( '20111001T080000Z', 'PT3H' )
                               , array( '20111201T080000Z', 'P3W' ))
                        , array( 'VALUE' => 'PERIOD'));


$e = & $c->newComponent( 'vevent' );
$e->parse( 'RDATE:20010101T010101Z,19700101T010000Z,19700101T020000Z,19700101T030000Z,19700101T040000Z');
$e->parse( 'comment:"RDATE:20010101T010101Z,19700101T010000Z,19700101T020000Z,19700101T030000Z,19700101T040000Z"');

$e = & $c->newComponent( 'vtodo' );
$e->setProperty( 'rdate', array( '20010101T010101Z','19700101T010000Z','19700101T020000Z','19700101T030000Z','19700101T040000Z' ));

// save calendar in file, get size, create new calendar, parse saved file, get size
$d   = 'file folder';
$f1  = 't e s t .ics';
$f2  = 't e s t 2 .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
$c = new vcalendar( array( 'unique_id' => 'test.se' ));
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->parse();
$c->setConfig( 'filename', $f2 );
$c->saveCalendar();
$fs2 = $c->getConfig('filesize');
$df2 = $c->getConfig('dirfile');
$d  = str_replace(' ', chr(92).' ', $d); // Backslash-character
$f1 = str_replace(' ', chr(92).' ', $f1);
$f2 = str_replace(' ', chr(92).' ', $f2);
$cmd = 'diff -b -H --side-by-side '.$d.'/'.$f1.' '.$d.'/'.$f2;
$c->saveCalendar();
$fs2 = $c->getConfig('filesize');
$str = $c->createCalendar();
echo $str; $a=array(); $n=chr(10); echo "$n 1 filezise=$fs1 dir/file='$df1'$n"; echo " 2 filezise=$fs2 dir/file='$df2'$n"; echo " cmd=$cmd$n"; exec($cmd, $a); echo " diff result:".implode($n,$a);

// $c->returnCalendar();
?>