<?php // status_iCal_test.php

require_once '../iCalcreator.class.php';
/*
     statvalue  = "TENTATIVE"           ;Indicates event is tentative.
                / "CONFIRMED"           ;Indicates event is definite.
                / "CANCELLED"           ;Indicates event was cancelled.
        ;Status values for a "VEVENT"

     statvalue  =/ "NEEDS-ACTION"       ;Indicates to-do needs action.
                / "COMPLETED"           ;Indicates to-do completed.
                / "IN-PROCESS"          ;Indicates to-do in process of
                / "CANCELLED"           ;Indicates to-do was cancelled.
        ;Status values for "VTODO".

     statvalue  =/ "DRAFT"              ;Indicates journal is draft.
                / "FINAL"               ;Indicates journal is final.
                / "CANCELLED"           ;Indicates journal is removed.
        ;Status values for "VJOURNAL".
*/
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "1: CONFIRMED" );
$e->setProperty( 'status', "CONFIRMED" );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Comment', "2: 'FINAL', array ('x-final' => 'countdown' )");
$e->setProperty( 'Status', "FINAL", array ('x-final' => 'countdown' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "3: 'DRAFT', array ( 'x-status' => 'RFC' )");
$e->setProperty( 'status', "DRAFT", array ( 'x-status' => 'RFC' ));

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