<?php // newComponent_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test-se' ));
$c->setProperty( 'method', 'test' );

$d = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ));
for( $x=1; $x <= 1000; $x++ ) {
  $e = & $c->newComponent( 'vevent' );
  $e->setProperty( 'comment', $x );
  $e->setProperty( 'dtstart', date('Ymd', mktime( date('H',$d), date('i',$d), date('s',$d), date('m',$d), date('d',$d), date('Y',$d ))));
  $d = mktime( 0, 0, 0, date('m',$d), date('d',$d) - 1, date('Y',$d ));
}
$c->sort();
foreach( $c->components as $x => & $e ) {
  $y = $e->getProperty( 'comment' );
  $e->setProperty( 'comment', "$x + $y = ".($y+$x) );
}
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