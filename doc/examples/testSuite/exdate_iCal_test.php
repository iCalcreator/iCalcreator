<?php // exdate_iCal_test.php

require_once '../iCalcreator.class.php';

$d1  = array( 2001, 2, 3 );
$d2a = array( 2002, 2, 3, 4, 5, 6 );
$d2  = array( 2002, 2, 3, 4, 5, 6, '-040506' );
$d2p = array( 2002, 2, 3, 4, 5, 6, '+040506' );
$d3  = array( 'year' => 2003, 'month' => 2, 'day' => 3, 'hour' => 4, 'min' => 5, 'sec' => 6 );
$d4  = array( 'year' => 2004, 'month' => 2, 'day' => 3 );
$d5  = '2005-02-03 04:05:06';
$d6  = '2006-02-03';
$d7  = '20070203';
$d8  = '20080203040506';
$d9  = '3 Feb 2009';
$d10 = '01/02/2010';
$timestamp = mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
$d11 = array( 'timestamp' => $timestamp );
$timestamp = mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
$d12 = array( 'timestamp' => $timestamp, 'tz' => '+0100' );
$timestamp = mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
$d13 = array( 'timestamp' => $timestamp, 'tz' => 'CEST' );

$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'uid', '-#- this is input uid, NOT generated-#-', array ( 'xparamKey' => 'xparamValue' ));
$o->setProperty( 'comment', '1 '.implode('-', $d1 ));
$o->setProperty( 'exdate', array( $d1 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '2 '.implode('-', $d2a).' '.implode('-', $d1));
$o->setProperty( 'exdate', array( $d2a, $d1 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '3 '.implode('-', $d1).' '.$d10 );
$o->setProperty( 'Exdate',  array( $d1, $d10 ), array( 'xparam' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '4 '.implode('-', $d1).' '.$d10.' '.implode('-', $d2 ).' value->date');
$o->setProperty( 'exdate'
               , array( $d1, $d10, $d2 )
               , array( 'xparamKey'=>'xparamValue','value'=>'date' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '5 '.$d6 );
$o->setProperty( 'exdate', array( $d6 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '6 '.$d6.' '.$d5 );
$o->setProperty( 'Exdate', array( $d6, $d5 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '7 '.$d6.' '.$d5.' '.$d9 );
$o->setProperty( 'exdate', array( $d6, $d5, $d9 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '8 '.implode('-', $d2 ));
$o->setProperty( 'Exdate', array( $d2 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '9 '.implode('-', $d2).' '.$d9.' value->date' );
$o->setProperty( 'exdate'
               , array( $d2, $d9 )
               , array( 'xparamKey'=>'xparamValue','value'=>'date' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '10 '.implode('-', $d2).' '.$d9.' '.implode('-', $d4));
$o->setProperty( 'EXDATE', array( $d2, $d9, $d4 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '11 '.implode('-', $d2p).' '.$d9.' '.implode('-', $d4));
$o->setProperty( 'EXDATE', array( $d2p, $d9, $d4 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '12 '.implode('-', $d3));
$o->setProperty( 'exdate', array( $d3 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '13 '.implode('-', $d3).' '.$d8 );
$o->setProperty( 'exdate', array( $d3, $d8 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '14 '.implode('-', $d3).' '.$d8.' '.$d6 );
$o->setProperty( 'exdate', array( $d3, $d8, $d6 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '15 '.implode('-', $d4 ));
$o->setProperty( 'exdate', array( $d4 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'CoMmEnT', '16 '.implode('-', $d4).' '.$d7 );
$o->setProperty( 'exdate', array( $d4, $d7 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '17 '.implode('-', $d4).' '.$d7.' '.$d8 );
$o->setProperty( 'ExDaTe'
               , array( $d4, $d7, $d8 )
               , array( 'xparamKey' => 'xparamValue', 'yparam' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '18 '.$d5);
$o->setProperty( 'exdate', array( $d5 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '19 '.$d5.' '.$d6 );
$o->setProperty( 'Exdate', array( $d5, $d6 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '20 '.$d5.' '.$d6.' '.$d10 );
$o->setProperty( 'exdate', array( $d5, $d6, $d10 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '21 '.$d7 );
$o->setProperty( 'Exdate', array( $d7 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '22 '.$d7.' '.implode('-',$d4 ));
$o->setProperty( 'exdate', array( $d7, $d4 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '23 '.$d7.' '.implode('-',$d4).' '.$d7 );
$o->setProperty( 'Exdate', array( $d7, $d4, $d7 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '24 '.$d8 );
$o->setProperty( 'exdate', array( $d8 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '25 '.$d8.' '.implode('-', $d3 ));
$o->setProperty( 'Exdate',array( $d8, $d3 ), array( 'xparamKey' => 'xparamValue', 'yparamKey' => 'yparamValue' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '26 '.$d8.' '.implode('-', $d3).' '.$d5 );
$o->setProperty( 'exdate', array( $d8, $d3, $d5 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '27 '.$d9 );
$o->setProperty( 'Exdate', array( $d9 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '28 '.$d9.' '.implode('-', $d2 ));
$o->setProperty( 'exdate', array( $d9, $d2 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '29 '.$d9.' '.implode('-', $d2 ).' '.implode('-',$d3 ));
$o->setProperty( 'Exdate', array( $d9, $d2, $d3 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '30 '.$d10 );
$o->setProperty( 'Exdate', array( $d10 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '31 '.$d10.' '.implode('-',$d1));
$o->setProperty( 'exdate', array( $d10, $d1 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '32 '.$d10.' '.implode('-',$d1).' '.implode('-',$d1));
$o->setProperty( 'exdatE', array( $d10, $d1, $d1 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '33 '.implode('-', $d4 ));
$o->setProperty( 'exdate', array( $d4 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', '34 '.implode('-', $d11 ).' '.implode('-', $d4) );
$o->setProperty( 'Exdate', array( $d11, $d4 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', '35 '.implode('-', $d13 ).' '.implode('-', $d11).' '.implode('-', $d12).' '.implode('-', $d4) );
$o->setProperty( 'exdate', array( $d13, $d11, $d12, $d4 ));

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