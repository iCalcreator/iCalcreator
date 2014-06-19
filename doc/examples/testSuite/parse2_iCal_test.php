<?php // completed_iCal_text.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->parse( 'DTSTAMP:19980309T231000Z' );
$e->parse( array( 'UID:guid-1.host1.com'
                , 'ORGANIZER;ROLE=CHAIR:MAILTO:mrbig@host.com'));
$e->parse( 'ATTENDEE;RSVP=TRUE;ROLE=REQ-PARTICIPANT;CUTYPE=GROUP:MAILTO:employee-A@host.com' );
$e->parse( 'ATTENDEE;CUTYPE="GROUP";PARTSTAT="ORGANIZER";X-NUM-GUESTS="0";CN="Noon Duty Volunteers":MAILTO:m1kjfs4339gmg8i8h0m8qr1ioc@group.calendar.google.com' );
$e->parse( array( 'CATEGORIES:MEETING'
                , 'CLASS:PUBLIC'
                , 'CREATED:19980309T130000Z'
                , 'SUMMARY:XYZ Project Review'
                , 'DTSTART;TZID=US-Eastern:19980312T083000' ));

$e = & $c->newComponent( 'vevent' );
$e->parse( 'ATTENDEE;CUTYPE="GROUP";PARTSTAT="ORGANIZER";X-NUM-GUESTS="0";CN="Noon Duty Volunteers":MAILTO:m1kjfs4339gmg8i8h0m8qr1ioc@group.calendar.google.com' );
$e->parse( 'RDATE:20010101T010101Z/P1D,20020101T010101Z/P5W,20030303T030303Z/20040404T04040
 4Z,20050101T010101Z/20060404T040404Z,20070202T020202Z/P6DT6H' );


// save calendar in file, get size, create new calendar, parse saved file, get size
$d   = 'file folder';
$f1  = 't e s t .ics';
$f2  = 't e s t 2 .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();


$f1  = 'massa.ics';
$c->setConfig( 'filename', $f1 );

$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
$c = new vcalendar( array( 'unique_id' => 'test.se' ));
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );

// $rows = file( $d.'/'.$f1, FILE_IGNORE_NEW_LINES );
// echo 'antal:'.count($rows).'<br />'.PHP_EOL; // test ###
$rows = file_get_contents( $d.'/'.$f1 );
echo 'antal:'.substr_count($rows,PHP_EOL).'<br />'.PHP_EOL; // test ###

if( FALSE === $c->parse( $rows ))
  exit( "FALSE from parse of $d/$f1" );

$f1  = 'massa2.ics';
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
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