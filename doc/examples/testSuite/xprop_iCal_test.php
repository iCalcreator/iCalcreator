<?php // xprop_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'domain.net' ));
$c->setProperty( 'X-PROP'
               , 'a test setting a x-prop property in calendar' );
$c->setProperty( 'X-WR-CALNAME', 'Games Night Meetup', array( 'xKey' => 'xValue' ));
$c->setProperty( 'X-WR-CALDESC', 'Calendar Description' );
$c->setProperty( 'X-WR-TIMEZONE', 'Europe/Stockholm' );
$c->setProperty( 'X-xomment', 'this one will be overwritten' );
$c->setProperty( 'X-xomment', 'this second comment will be displayed, with a comma before this' );

$e1 = & $c->newComponent( 'vevent' );
$e1->setProperty( 'comment', '4: Games Night Meetup' );
$e1->setProperty( 'X-WR-CALNAME', 'Games Night Meetup' );
$e1->setProperty( 'Comment', "5: 'X-ABC-MMSUBJ', 'http://load.noise.org/mysubj.wav'");
$e1->setProperty( 'X-ABC-MMSUBJ', 'http://load.noise.org/mysubj.wav' );
$e1->setProperty( 'X-xomment', 'this one will be overwritten' );
$e1->setProperty( 'X-xomment', 'this second comment will be displayed, with a comma before this' );

$e2 = & $c->newComponent( 'vevent' );
$e2->setConfig( 'language', 'de' );
$e2->setProperty( 'Comment'
               , "6: 'X-ABC-MMSUBJ', 'http://load.noise.org/mysubj.wav'");
$e2->setProperty( 'X-ABC-MMSUBJ'
               , 'http://load.noise.org/mysubj.wav' );
$e2->setProperty( 'comment', "'X-xomment', 'this one will be overwritten'" );
$e2->setProperty( 'X-xomment', 'this one will be overwritten' );
$e2->setProperty( 'X-xomment', 'this second comment will be displayed' );
$e2->setProperty( 'comment',  "'X-xomment2', 'this one will be removed'" );
$e2->setProperty( 'X-xomment2', 'this one will be deleted' );
$e2->deleteProperty( 'X-xomment2' );

$e3 = & $c->newComponent( 'vevent' );
$e3->setConfig( 'Language', 'de' );
$e3->setProperty( 'Comment', "7: 'X-ABC-MMSUBJ', 'http://load.noise.org/mysubj.wav', array( 'xparamKey' => 'xparamValue', 'language' => 'en' ) (lang=de, set at comp. level)");
$e3->setProperty( 'X-ABC-MMSUBJ', 'http://load.noise.org/mysubj.wav', array( 'xparamKey' => 'xparamValue', 'language' => 'en' ));
$e3->setProperty( 'X-xomment', 'this one will be overwritten' );
$e3->setProperty( 'X-xomment', 'this second comment will be displayed' );

$e4 = & $c->newComponent( 'vevent' );
$e4->setProperty( 'Comment'
               , "8: 'X-ABC-MMSUBJ', 'http://load.noise.org/mysubj.wav', array( 'xparamKey' => 'xparamValue', 'language' => 'en' )");
$e4->setProperty( 'Comment', '2: Denna kommer att tas bort!!' );
$e4->setProperty( 'Comment', '3: Denna kommer att tas bort!!' );
$e4->setProperty( 'Comment', '4: Denna kommer att tas bort!!' );
$e4->setProperty( 'Comment', '5: Denna kommer att tas bort!!' );
$e4->setProperty( 'X-ABC-MMSUBJ'
               , 'http://load.noise.org/mysubj.wav'
               , array( 'xparamKey' => 'xparamValue'
                      , 'language' => 'en' ));
$a1 = & $e4->newComponent( 'valarm' );
$a1->setProperty( 'Action', 'AUDIO' );
$a1->setProperty( 'Description'
               , '9: AUDIO-decription' );
$a1->setProperty( 'X-ALARM-PROPERTY'
               , 'X-ALARM-VALUE' );
$a1->setProperty( 'Attach'
               , 'http://www.domain.net/audiolib/ticktack.wav' );
$e4->setProperty( 'X-xomment', 'this one will be overwritten' );
$e4->setProperty( 'X-xomment', 'this second comment will be displayed' );
$e4->deleteProperty( 'comment', 5 );
$e4->deleteProperty( 'comment', 2 );
$e4->deleteProperty( 'comment', 3 );
$e4->deleteProperty( 'comment', 4 );

// save calendar in file, get size, create new calendar, parse saved file, get size
$d   = 'file folder';
$f1  = 't e s t .ics';
$f2  = 't e s t 2 .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
$c = new vcalendar( array( 'unique_id' => 'domain.net' ));
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