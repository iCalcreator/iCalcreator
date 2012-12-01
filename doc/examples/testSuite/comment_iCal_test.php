<?php // comment_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->parse( array(
 "COMMENT:arr_Project xyz Review Meeting Minutes"
," Agenda"
,"1. Review of project version 1.0 requirements."
,"2. Definition of project processes."
,"3. Review of project schedule."
,"Participants: John Smith, Jane Doe, Jim Dandy "
,"-It was decided that the requirements need to be signed off by product marketing."
,"-Project processes were accepted."
,"-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates."
,"-New schedule will be distributed by Friday."
,"-Next weeks meeting is cancelled. No meeting until 3/23." ));

$br = chr(92).'n ';
$e->parse( 
 "COMMENT:str_Project xyz Review Meeting Minutes".$br
." Agenda".$br
."1. Review of project version 1.0 requirements.".$br
."2. Definition of project processes.".$br
."3. Review of project schedule.".$br
."Participants: John Smith, Jane Doe, Jim Dandy".$br
."-It was decided that the requirements need to be signed off by product marketing.".$br
."-Project processes were accepted.".$br
."-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.".$br
."-New schedule will be distributed by Friday.".$br
."-Next weeks meeting is cancelled. No meeting until 3/23." );


$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "This is comment 1a." );
$e->setProperty( 'comment', "This is comment 1b." );
$e->setProperty( 'comment', 'This is comment 1c with two soft line-(
 ) breaks (
 )here.' );
$e->setProperty( 'comment', "This is comment 1d with hard(
 )line-(
 )breaks." );
$e->setProperty( 'description', "This is comments 1 a+b+c+d." );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', "'This is comment 2.', array( 'altrep' => 'http://www.domain.net/doc.txt', 'hejsan', 'language' => 'da' )");
$e->setProperty( 'Comment', "This is comment 2.", array( 'altrep' => 'http://www.domain.net/doc.txt', 'hejsan', 'language' => 'da' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', "3 'Å i åa ä e ö, sa Yngve Öst, ärligt och ångerfyllt', array( 'altrep' => 'http://www.domain.net/doc.txt', 'hejsan', 'language' => 'da', 'xparamKey' => 'xparamvalue' )");
$e->setProperty( 'comment', "3 Å i åa ä e ö, sa Yngve Öst, ärligt och ångerfyllt", array( 'altrep' => 'http://www.domain.net/doc.txt', 'hejsan', 'language' => 'da', 'xparamKey' => 'xparamvalue' ));
$e->setProperty( 'Comment', "4 This is a another comment" );

$e = & $c->newComponent( 'vevent' );
$comment = <<<EOT
ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i åå>>>åå i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ää>>>ää i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i öö>>>öö i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÅÅ>>>ÅÅ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÄÄ>>>ÄÄ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÖÖ>>>ÖÖ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i üü>>>üü i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÜÜ>>>ÜÜ
EOT;
$e->setProperty( 'Comment', $comment );

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