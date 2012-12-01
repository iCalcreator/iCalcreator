<?php // completed_iCal_text.php

require_once '../iCalcreator.class.php';

$fdate1  = array ( 'year' => 2001, 'month' => 1, 'day' => 1, 'hour' => 1, 'min' => 1, 'sec' => 1 );
$fdate2  = array ( 2002, 2, 2, 2, 2, 2, '-020202'  );
$fdate3  = array ( 2003, 3, 3, 3, 3, 3 );
$fdate4  = '4 April 2004 4:4:4';
//$fdate5  = array ( 'year' => 2005, 'month' => 5, 'day' => 5, 'tz' => '+1200' );
$fdate5  = array ( 'year' => 2005, 'month' => 5, 'day' => 5 );

$fdate6  = array ( 5 );
// alt.
$fdate7  = array ( 'week' => false, 'day' => 5, 'hour' => 5, 'min' => 5, 'sec' => 5 );
$fdate8  = array ( 0, 0, 6 );             // duration for 6 hours
$fdate9  = 'PT2H30M';                     // duration for 2 hours, 30 minutes
$fdate10 = array( 'sec' => 3 *3600);      // duration for 3 hours in seconds
$timestamp1 = mktime ( 0, 0, 0, date('m'), date('d')+ 1, date('Y'));
$timestamp1 = array( 'timestamp' => $timestamp1 );
$timestamp3 = mktime ( 0, 0, 0, date('m'), date('d')+ 3, date('Y'));
$timestamp3 = array( 'timestamp' => $timestamp3 );

$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vfreebusy' );
$e->setProperty( 'comment', implode('-',$fdate1).'+'.implode('-',$fdate2).' '
                          . implode('-',$fdate3).'+'.implode('-',$fdate6).' '
                          . $fdate4.'+'.implode('-',$fdate7));
$e->setProperty( 'freebusy'
               , 'FREE'
               , array( array( $fdate1, $fdate2 )
                      , array( $fdate3, $fdate6 )
                      , array( $fdate4, $fdate7 ))
               , array( 'X-Key1' => 'xValue'
                      , 'X-Key2' => 'yValue' ));

$e = & $c->newComponent( 'vfreebusy' );
$e->setProperty( 'comment', implode('-',$fdate1).'+'.implode('-',$fdate5).' '
                          . implode('-',$fdate3).'+'.implode('-',$fdate6).' '
                          . $fdate4.'+'.$fdate9.' '
                          . implode('-',$fdate1).'+'.implode('-',$fdate8));
$e->setProperty( 'Freebusy'
               , 'Buzy'
               , array( array( $fdate1, $fdate5 )
                      , array( $fdate3, $fdate6 )
                      , array( $fdate4, $fdate9 )
                      , array( $fdate1, $fdate8 )));

$e = & $c->newComponent( 'vfreebusy' );
$e->setProperty( 'comment', implode('-',$timestamp1).'+'.implode('-',$fdate6).' '
                          . implode('-',$fdate3).'+'.implode('-',$fdate6).' '
                          . $fdate4.'+'.implode('-',$fdate10));
$e->setProperty( 'freebusy'
               , 'Buzy'
               , array( array( $timestamp1, $fdate6 )
                      , array( $fdate3, $fdate6 )
                      , array( $fdate4, $fdate10 )));

$e = & $c->newComponent( 'vfreebusy' );
$e->setProperty( 'comment', implode('-',$timestamp1).'+'.implode('-',$timestamp3));
$e->setProperty( 'freebusy'
               , 'Buzy'
               , array( array( $timestamp1, $timestamp3 )));

$e = & $c->newComponent( 'vfreebusy' );
$e->setProperty( 'comment', 'unvalid fbType => BUSY' );
$e->setProperty( 'comment', implode('-',$timestamp1).'+'.implode('-',$timestamp3));
$e->setProperty( 'freebusy'
               , 'dymmyKey'
               , array( array( $timestamp1, $timestamp3 )));

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