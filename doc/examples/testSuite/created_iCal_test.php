<?php // created_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'comment', '0a missing date argument, i.e. now' );
$o->setProperty( 'created' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'comment', '0b: 20010203T040506' );
$o->setProperty( 'created', '20010203T040506' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$created1 =                array( 'year'  => date( 'Y' )
                                 , 'month' => date( 'm' )
                                 , 'day'   => date( 'd' )
                                 , 'hour'  => date( 'H' )
                                 , 'min'   => date( 'i' )
                                 , 'sec'   => date( 's' )
                                 , 'tz'    => date( 'O' ));
$o->setProperty( 'created', $created1 );
$o->setProperty( 'comment', '0b  1='.implode( '-', $created1 ));
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'comment', "2: array( 'year' = 2006, 'month' => 10, 'day' => 10)" );
$o->setProperty( 'created', array( 'year' => 2006, 'month' => 10, 'day' => 10 ));
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created', 1, 2, 3 );
$o->setProperty( 'comment', '1: 1, 2, 3' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created', 1, 2, 3, 4, 5, 6, array( 'xparam' ));
$o->setProperty( 'comment', "3: 1, 2, 3, 4, 5, 6, array( 'xparam' )" );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created'
               , array( 'year'=>1, 'month'=>2, 'day'=>3, 'hour'=>4, 'min'=>5, 'sec'=>6 )
               , array( 'xparam', 'xparaMKey' => 'xparamValue' ));
$o->setComment( "4: array( 'year' => 1, 'month' => 2, 'day' => 3, 'hour' => 4, 'min' => 5, 'sec' => 6 ), array( 'xparam', 'xparaMKey' => 'xparamValue' )" );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'DTSTAMP'
               , array( 'year' => 1, 'month' => 2, 'day' => 3 )
               , array( 'xparam', 'xparaMKey' => 'xparamValue' ));
$o->setComment( "5: array( 'year' => 1, 'month' => 2, 'day' => 3), array( 'xparam', 'xparaMKey' => 'xparamValue' )" );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created', '2001-02-03 04:05:06', array( 'xparam', 'xparaMKey' => 'xparamValue' ) );
$o->setProperty( 'comment', "6: 2001-02-03 04:05:06, array( 'xparam', 'xparaMKey' => 'xparamValue' )" );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created', '2001-02-03' );
$o->setProperty( 'comment', '7: 2001-02-03' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created', '20010203' );
$o->setProperty( 'comment', '8: 20010203' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created', '20010203040506' );
$o->setProperty( 'comment', '9: 20010203040506' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'comment', 'C9b: 20010203T040506' );
$o->setProperty( 'created', '20010203T040506' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'comment', 'C9c: 20010203T040506 Europe/Stockholm' );
$o->setProperty( 'created', '20010203T040506 Europe/Stockholm' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created', '3 Feb 2001' );
$o->setProperty( 'comment', '10: 3 Feb 2001' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'created', '02/03/2001' );
$o->setProperty( 'comment', '11: 02/03/2001' );
/**/
/**/
$o = & $c->newComponent( 'vjournal' );
$timestamp = mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'created', array( 'timestamp' => $timestamp ), array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy' ) );
$o->setProperty( 'comment', '12: '.$timestamp.' =now tre xparams' );

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