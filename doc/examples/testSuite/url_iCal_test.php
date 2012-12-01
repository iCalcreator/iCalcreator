<?php // url_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'domain2.net' ));
$c->setProperty( 'x-unique_id', 'unique_id set at calendar level to "domain2.net" in first file' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "URL set to http://www.icaldomain.net, array( 'x-IP-num' => '123.456.789.123' )" );
$e->setProperty( 'url'
               , 'http://www.icaldomain.net'
               , array( 'x-IP-num' => '123.456.789.123' ));

$e2 = & $c->newComponent( 'vevent' );
$e2->setProperty( 'Comment'
                , "URL set to http://www.icaldomain2.net, xparam= 'x-IP-num' => '222.222.222.222'" );
$e2->setProperty( 'url'
                , 'http://www.icaldomain2.net'
                , array( 'x-IP-num' => '222.222.222.222' ));

// save calendar in file, get size, create new calendar, parse saved file, get size
$d   = 'file folder';
$f1  = 't e s t .ics';
$f2  = 't e s t 2 .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
$c = new vcalendar();
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