<?php // rdate_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$rdate1  = array ( 'year' => 2001, 'month' => 1, 'day' => 1, 'hour' => 1, 'min' => 1, 'sec' => 1, 'tz' => '+0200' );
$rdate2  = array ( 2002, 2, 2, 2, 2, 2, '+0200' );
$rdate2s = '20081020T164152 CEST';
$rdate2t = '20081020T164152 +0200';
$rdate3  = '3 March 2003 03.03.03';
$rdate4  = array ( 2004, 4, 4, 4, 4, 4, 'GMT' );
$rdate5  = array ( 2005, 10, 5, 5, 5, 5 );
$rdur6   = array ( 'week' => 0, 'day' => 0, 'hour' => 5, 'min' => 5, 'sec' => 5 );
$rdur7   = array ( 0, 0, 6 );
$rdur8   = array ( 0, 1, 1, 1, 1 );
$timestamp = mktime( 9, 9, 9, 9, 9, 2009);
$rdate9    = array( 'timestamp' => $timestamp );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'rdate', array( $rdate1 ));
$e->setProperty( 'rdate', array( $rdate2 ));
$e->setProperty( 'rdate', array( $rdate3 ));
$e->setProperty( 'rdate', array( $rdate4 ));
$e->setProperty( 'rdate', array( $rdate5 ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '4 2 period test '.implode('-',$rdate1).' + '.implode('-',$rdur8));
$e->setProperty( 'rdate', array( array( $rdate1, $rdur8 )));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '5 period test '.implode('-',$rdate9).' + '.implode('-',$rdate9));
$e->setProperty( 'rdate', array( array( $rdate9, $rdate9 ))); //, array('VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '6 2 dates test '.$rdate2s.' + '.implode('-',$rdate2));
$e->setProperty( 'rdate', array( $rdate2s, $rdate2 ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '6 2 dates test '.$rdate2s.' + '.implode('-',$rdate2).' + '.$rdate2t);
$e->setProperty( 'rdate', array( $rdate2s, $rdate2, $rdate2t ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '7 period test '.implode('-',$rdate4).' + '.implode('-',$rdate5));
$e->setProperty( 'rdate', array( array( $rdate4, $rdate5 ))); //, array('VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '8a period test '.$rdate3.' + '.implode('-',$rdate4));
$e->setProperty( 'rdate', array( array( $rdate3, $rdate4 ))); //, array('VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '8b period test '.$rdate3.' + '.implode('-',$rdate9));
$e->setProperty( 'rdate', array( array( $rdate3, $rdate9 ))); //, array('VALUE' => 'PERIOD'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '9 complex ');
$e->setProperty( 'comment', '9-1 '.implode('-',$rdate1).' + '.implode('-',$rdate5));
$e->setProperty( 'comment', '9-2 '.implode('-',$rdate2).' + '.implode('-',$rdur6));
$e->setProperty( 'comment', '9-3 '.$rdate3.' + '.implode('-',$rdur7));
$e->setProperty( 'comment', '9-4 '.implode('-',$rdate4).' + '.implode('-',$rdate5));
$e->setProperty( 'rdate'
               , array( array( $rdate1, $rdate3 )
                      , array( $rdate2, $rdur6 )
                      , array( $rdate3, $rdur7 )
                      , array( $rdate4, $rdate5 )));
//              , array('VALUE' => 'PERIOD'));

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