<?php
 // action_iCal_test.php
require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );

$a1 = & $e->newComponent( 'valarm' );
$a1->setproperty( 'description', 'This is a very long description of an ALARM component with ACTION property set to AUDIO. The meaning of this very long description (with a number of meaningless words) is to test the function of line break after every 75 position and I hope that this is working properly.' );
$a1->setProperty( 'action', 'AUDIO' );

$a2 = & $e->newComponent( 'valarm' );
$a2->setProperty( 'description', "'AUDIO', array( 'SOUND' => 'Glaskrasch' )");
$a2->setProperty( 'Action' ,'AUDIO', array( 'SOUND' => 'Glaskrasch' ));

$a3 = & $e->newComponent( 'valarm' );
$a3->setProperty( 'description'
                , "'AUDIO', array('SOUND' => 'Glaskrasch', 'EX' => 'kristallkrona', 'TYPE' => 'silverbricka' )");
$a3->setProperty( 'action'
                , 'AUDIO'
                , array('SOUND' => 'Glaskrasch', 'EX' => 'kristallkrona', 'TYPE' => 'silverbricka' ));

// save calendar in file, get size, create new calendar, parse saved file, save again, get size
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