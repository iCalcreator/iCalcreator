<?php // created_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', 'generated (auto) uid' );
$uid = $e->getProperty( 'Uid' );
$uid = ( FALSE === $uid ) ? 'saknas' : $uid;
$e->setProperty( 'comment',  $uid );

$a = & $e->newComponent( 'valarm' );
$uid = $a->getProperty( 'Uid' );
$a->setProperty( 'x-1'
               , 'alarm component, no uid!! ->'.$uid.'=empty');

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Uid', '19960401T080045Z-4000F192713-0052@host1.com' );
$e->setProperty( 'comment', 'input: 19960401T080045Z-4000F192713-0052@host1.com' );
$e->setProperty( 'Comment', 'set (store) uid' );
$e->setproperty( 'comment', 'get: '.$e->getProperty( 'Uid' ));

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
$c->setConfig( 'filename', 't e s t .ics' );
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