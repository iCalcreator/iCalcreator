<?php // percentcomplete_iCal_text.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test-se' ));

$t = & $c->newComponent( 'vtodo' );
$t->setProperty( 'comment', '0: 0' );
$t->setProperty( 'Percent-Complete', 0 );

$t = & $c->newComponent( 'vtodo' );
$t->setProperty( 'comment', "2: 75, array( 'xparamKey' => 'xparamValue', 'yParam' )");
$t->setProperty( 'Percent-Complete'
               , 75
               , array( 'xparamKey' => 'xparamValue'
                      , 'yParam' ));

$t = & $c->newComponent( 'vtodo' );
$t->setProperty( 'comment', '3: 90' );
$t->setProperty( 'Percent-Complete', 90 );

$t = & $c->newComponent( 'vtodo' );
$t->setProperty( 'comment', '4: 100' );
$t->setProperty( 'Percent-Complete', 100 );

// save calendar in file, get size, create new calendar, parse saved file, get size
$d   = 'file folder';
$f1  = 't e s t .ics';
$f2  = 't e s t 2 .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
$c = new vcalendar( array( 'unique_id' => 'test-se' ));
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