<?php // TZ_iCal_test2.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test-se' ));

$t = & $c->newComponent( 'timezone' );
$t->setProperty( 'Tzid', 'US-Eastern' );
$t->setProperty( 'Last-Modified', '19870101T000000' );
$t->setProperty( 'tzid', 'US-Eastern' );
$t->setProperty( 'tzurl', 'http://zones.stds_r_us.net/tz/US-Eastern' );

$s = & $t->newComponent( 'standard' );
$s->setProperty( 'dtstart', '19671029T020000' );
$s->setProperty( 'rrule'
               , array( 'FREQ'    => "YEARLY"
                      , 'BYMONTH' => 10
                      , 'BYday'   => array( -1, 'DAY' => 'SU' )));
$s->setProperty( 'tzoffsetfrom', '-0400' );
$s->setProperty( 'tzoffsetto', '-0500' );
$s->setProperty( 'tzname', 'EST' );

$dl = & $t->newComponent( 'daylight' );
$dl->setProperty( 'dtstart', '19971026020000' );
$dl->setProperty( 'rdate', array( '19970406020000' ));
$dl->setProperty( 'tzoffsetfrom', '-0500' );
$dl->setProperty( 'tzoffsetto', '-0400' );
$dl->setProperty( 'tzname', 'EDT' );

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