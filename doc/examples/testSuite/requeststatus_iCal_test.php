<?php // requeststatus_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "1.00, '1 hejsan hejsan', '1 gammalt fel, som skickats igen'" );
$e->setProperty( 'request-status', 1.00, '1 hejsan hejsan', '1 gammalt fel, som skickats igen' );
$e->setProperty( 'comment', "1.50, '1.5 hejsan hejsan', '1.5 gammalt fel, som skickats igen'" );
$e->setProperty( 'request-status', 1.50, '1.5 hejsan hejsan', '1.5 gammalt fel, som skickats igen' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Comment', "2.00, '2 hejsan hejsan', '2 gammalt fel, som skickats igen', array ( 'xparamKey' => 'xparamValue')");
$e->setProperty( 'Request-Status'
               , 2.00
               , '2 hejsan hejsan'
               , '2 gammalt fel, som skickats igen'
               , array ( 'xparamKey' => 'xparamValue'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "3.00, '3 hejsan hejsan', '3 gammalt fel, som skickats igen', array( 'language' => 'se', 'yParam' )");
$e->setProperty( 'request-status'
               , 3.00
               , '3 hejsan hejsan'
               , '3 gammalt fel, som skickats igen'
               , array( 'language' => 'se', 'yParam' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "3.00, '3, 2 props with the same index', '3 2 props with the same index', array( 'language' => 'se', 'yParam' )");
$e->setProperty( 'request-status'
               , 3.00
               , '3, 2 props with the same index 1'
               , '3, 2 props with the same index 1'
               , array( 'language' => 'se', 'yParam' )
               , 4 );
$e->setProperty( 'request-status'
               , 3.00
               , '3, 2 props with the same index 2'
               , '3, 2 props with the same index 2'
               , array( 'language' => 'se', 'yParam' )
               , 4 );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "4.00, '4 requeststatus nr 4'");
$e->setProperty( 'request-status'
               , 4.00
               , '4 requeststatus nr 4' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "5, '5 nr 5', FALSE, array( 'language' => 'se', 'yParam' )");
$e->setProperty( 'request-status'
               , 5
               , '5 nr 5'
               , FALSE
               , array( 'language' => 'se', 'yParam' ));

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