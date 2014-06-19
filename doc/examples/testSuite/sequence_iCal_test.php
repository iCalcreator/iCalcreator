<?php // sequence_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "0: 0" );
$e->setProperty( 'sequence', 0 );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "1: 1" );
$e->setProperty( 'sequence', 1 );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Comment', "2: 2, array( 'xparamKey' => 'xparamValue' ");
$e->setProperty( 'Sequence', 2, array( 'xparamKey' => 'xparamValue' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "3: 4, array( 'x-number' => 'FOUR' )");
$e->setProperty( 'sequence', 4, array( 'x-number' => 'FOUR' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "4: no value set at start" );
$e->setProperty( 'sequence' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "5: first call 5, second call: no value, result=6" );
$e->setProperty( 'sequence', 5 );
$e->setProperty( 'sequence' );

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