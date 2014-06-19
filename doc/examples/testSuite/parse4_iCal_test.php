<?php // parse4_iCal_text.php, input from string, complete calendar

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ) );
if( FALSE === $c->parse(
'BEGIN:VCALENDAR'.PHP_EOL.
'PRODID:-//test.org//NONSGML kigkonsult.se iCalcreator 2.10//'.PHP_EOL.
'VERSION:2.0'.PHP_EOL.
'BEGIN:VEVENT'.PHP_EOL.
'DTSTART:20101224T190000Z'.PHP_EOL.
'DTSTAMP:20101020T103827Z'.PHP_EOL.
'UID:20101020T113827-1234GkdhFR@test.org'.PHP_EOL.
'DESCRIPTION:this is a very long string and here is a test checking line folding and here is a line break ->'.chr(92).'n<- included'.PHP_EOL.
'END:VEVENT'.PHP_EOL.
'END:VCALENDAR' ))
  exit( 'FALSE from parse<br />'.PHP_EOL );

$str = 'BEGIN:VCALENDAR'.PHP_EOL.
'PRODID:-//test.org//NONSGML kigkonsult.se iCalcreator 2.10//'.PHP_EOL.
'VERSION:2.0'.PHP_EOL.
'BEGIN:VEVENT'.PHP_EOL.
'DTSTART:20101224T190000Z'.PHP_EOL.
'DTSTAMP:20101020T103827Z'.PHP_EOL.
'DESCRIPTION:this is event number 2 without any UID property'.PHP_EOL.
'END:VEVENT'.PHP_EOL.
'END:VCALENDAR';
if( FALSE === $c->parse( $str ))
  exit( 'FALSE from parse 2<br />'.PHP_EOL );

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