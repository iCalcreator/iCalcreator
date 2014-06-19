<?php // location_iCal_text.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));
$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '1  Målilla-avdelningen' );
$e->setProperty( 'location', 'Målilla-avdelningen' );

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'Comment', "2  setLanguage( 'no' ) 'Målilla-avdelningen' ");
$e->setProperty( 'Comment', "checking calendar language=".$e->getConfig( 'language' ));
$e->setProperty( 'location', 'Målilla-avdelningen' );
$e->setProperty( 'Comment', $e->createLocation());

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'COMMENT'
               , "3  setLanguage( 'no' ) 'Målilla-avdelningen', array( 'altrep' => 'http://www.domain.net/doc.txt', 'Xparam', 'language' => 'se' )" );
$e->setProperty( 'LOCATION'
               , 'Målilla-avdelningen'
               , array( 'altrep' => 'http://www.domain.net/doc.txt', 'Xparam', 'language' => 'se' ));

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