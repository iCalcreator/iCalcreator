<?php // repeat_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));
$e = & $c->newComponent( 'vevent' );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'duration'
               , array( 'day' => 1, 'hour' => 1, 'sec' => 1 )
               , array( 'x-nr' => '0' ));
$a->setProperty( 'repeat', 0 );
$a->setProperty( 'x-comment', "'repeat', 0" );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'duration'
               , array( 'day' => 1, 'hour' => 1, 'sec' => 1 )
               , array( 'x-nr' => '1' ));
$a->setProperty( 'repeat', 1 );
$a->setProperty( 'x-comment', "'repeat', 1" );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'duration'
               , array( 'day' => 2, 'hour' => 2, 'sec' => 2 )
               , array( 'x-nr' => '2' ));
$a->setProperty( 'repeat', 2, array( 'xparamKey' => 'xparamValue' ));
$a->setProperty( 'x-comment', "'repeat', 2, array( 'xparamKey' => 'xparamValue' )" );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'x-comment', 9 );
$a->setProperty( 'duration'
               , array( 'day' => 9, 'hour' => 9, 'sec' => 9 )
               , array( 'x-nr' => 9 ));
$a->setProperty( 'repeat', 9 );
$a->setProperty( 'x-comment', "'repeat', 9" );

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