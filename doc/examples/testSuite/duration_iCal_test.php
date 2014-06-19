<?php // duration_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', 1 );
$o->setProperty( 'comment', '1: 1' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'duration'
               , false, 2, FALSE, FALSE, FALSE, array( 'xparam' ) );
$o->setProperty( 'comment', "2: false, 2, FALSE, FALSE, FALSE, array( 'xparam' )" );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'duration', false, 2, 3 );
$o->setProperty( 'comment', '3: false, 2, 3' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'duration'
               , false, false, 3, 4, 5 );
$o->setProperty( 'comment', '4: false, false, 3, 4, 5' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'duration'
               , false, false, false, 4, 5 );
$o->setProperty( 'comment', '5: false, false, false, 4, 5' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'duration'
               , false, false, false, false, 5 );
$o->setProperty( 'comment', '6: false, false, false, false, 5' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'duration'
               , array( 'day' => 2, 'hour' => 3, 'sec' => 5 )
               , array( 'xparamkey' => 'xparamvalue' ));
$o->setProperty( 'comment', "7: array( 'day' => 2, 'hour' => 3,  'sec' => 5 ), array( 'xparamkey' => 'xparamvalue' )" );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'duration', 'P1W' );
$o->setProperty( 'comment', '8: P1W' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'DURATION', 'PT3H4M5S' );
$o->setProperty( 'comment', '9: PT3H4M5S' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'duration', 'P2DT4H' );
$o->setProperty( 'comment', '10: P2DT4H' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', 'PT4H' );
$o->setProperty( 'comment', '11: PT4H' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', 'PT0H1M30S' );
$o->setProperty( 'comment', '12: PT0H1M30S' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', array('sec'=>61) );
$o->setProperty( 'comment', "13: array( 'sec'=>61 )");

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', array('sec'=>7200) );
$o->setProperty( 'comment', "14: array( 'sec'=>7200 )");

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', array('sec'=>(6 * 7 * 24 * 60 * 60)) );
$o->setProperty( 'comment', "15: array( 'sec'=>(6 * 7 * 24 * 60 * 60))");

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', 'P1DT0H0M0S' );
$o->setProperty( 'comment', '16: P1DT0H0M0S' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', 'P1DT1H0M0S' );
$o->setProperty( 'comment', '17: P1DT1H0M0S' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', 'P1DT0H5M0S' );
$o->setProperty( 'comment', '18: P1DT0H5M0S' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', 'PT1H0M0S' );
$o->setProperty( 'comment', '19: PT1H0M0S' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Duration', 'PT30M' );
$o->setProperty( 'comment', '20: PT30M' );

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