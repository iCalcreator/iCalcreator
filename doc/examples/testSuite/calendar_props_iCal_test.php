<?php // calendar_props_iCal_test.php
require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$c->setProperty( 'calscale', 'Gregorian' );
$c->setProperty( 'Method' , 'Testing' );
$comment = <<<EOT
ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i åå>>>åå i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ää>>>ää i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i öö>>>öö i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÅÅ>>>ÅÅ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÄÄ>>>ÄÄ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÖÖ>>>ÖÖ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i üü>>>üü i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÜÜ>>>ÜÜ
EOT;
$c->setProperty( 'X-propx', "ä e $comment" );
$c->setProperty( 'X-num-zero', 0 );
$c->setProperty( 'X-string-zero', '0' );
$c->setProperty( 'X-PROPY', "one str_ect xyz Review Meeting Minutes
 Agenda
1. Review of project version 1.0 requirements.
2. Definition of project processes.
3. Review of project schedule.
Participants: John Smith, Jane Doe, Jim Dandy 
-It was decided that the requirements need to be signed off by product marketing.
-Project processes were accepted.
-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.
- New schedule will be distributed by Friday.
- Next weeks meeting is cancelled. No meeting until 3/23." );
$nl = chr(92).'n';
$c->setProperty( 'X-PROPz2', 
"str with eol-mark Review Meeting Minutes$nl".
" Agenda$nl".
"1. Review of project version 1.0 requirements.$nl".
"2. Definition of project processes.$nl".
"3. Review of project schedule.$nl".
"Participants: John Smith, Jane Doe, Jim Dandy$nl".
"-It was decided that the requirements need to be signed off by product marketing.$nl".
"-Project processes were accepted.$nl".
"-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.$nl".
"- New schedule will be distributed by Friday.$nl".
"- Next weeks meeting is cancelled. No meeting until 3/23." );
$c->setProperty( 'X-PROPz', 
'str m.PHP_EOL Review Meeting Minutes'.PHP_EOL.
' Agenda'.PHP_EOL.
'1. Review of project version 1.0 requirements.'.PHP_EOL.
'2. Definition of project processes.'.PHP_EOL.
'3. Review of project schedule.'.PHP_EOL.
'Participants: John Smith, Jane Doe, Jim Dandy'.PHP_EOL.
'-It was decided that the requirements need to be signed off by product marketing.'.PHP_EOL.
'-Project processes were accepted.'.PHP_EOL.
'-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.'.PHP_EOL.
'- New schedule will be distributed by Friday.'.PHP_EOL.
'- Next weeks meeting is cancelled. No meeting until 3/23.' );

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