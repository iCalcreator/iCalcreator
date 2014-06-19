<?php // empty_Properties.php
// create calendar and components with empty properties //

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$c->setProperty( 'calscale', 'gregorian' );
$c->setProperty( 'method', 'testing' );
$c->setProperty( 'X-PROP' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'CLASS' );
$o->setProperty( 'comment');
$o->setProperty( 'created' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'duration' );
$o->setProperty( 'geo' );
$o->setProperty( 'EXrule' );
$o->setProperty( 'rrule' );
$o->setProperty( 'exdate' );
$o->setProperty( 'rdate' );
$o->setProperty( 'priority' );
$o->setProperty( 'resources');
$o->setProperty( 'Summary' );

$a1 = & $o->newComponent( 'valarm' );
$a1->setProperty( 'ACTION' );
$a1->setProperty( 'ATTACH' );
$a1->setProperty( 'DURATION' );
$a1->setProperty( 'REPEAT' );
$a1->setProperty( 'trigger' );
$a1->setProperty( 'X-old-1' );

$a2 = & $o->newComponent( 'valarm' );
$a2->setProperty( 'ACTION' );
$a2->setProperty( 'DESCRIPTION' );
$a2->setProperty( 'DURATION' );
$a2->setProperty( 'REPEAT' );
$a2->setProperty( 'trigger' );
$a2->setProperty( 'X-old-2' );

$o = & $c->newComponent( 'vevent' );
$o->setConfig( 'language', 'fr' );
$o->setProperty( 'attendee' );
$o->setProperty( 'attendee' );
$o->setProperty( 'comment' );
$o->setProperty( 'comment' );
$o->setProperty( 'comment' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'duration' );
$o->setProperty( 'Status' );
$o->setProperty( 'tranSp' );
$o->setProperty( 'Uid' );
$o->setProperty( 'url' );
$o->setProperty( 'X-ABC-MMSUBJ' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment' );
$o->setProperty( 'completed' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'duration' );
$o->setProperty( 'LOCATION' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'categories' );
$o->setProperty( 'categories' );
$o->setProperty( 'comment' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'dtend' );
$o->setProperty( 'EXDATE' );
$o->setProperty( 'rrule' );
$o->setProperty( 'exdate' );
$o->setProperty( 'rdate' );
$o->setProperty( 'last-modified' );
$o->setProperty( 'Recurrence-id' );

$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'comment' );
$o->setProperty( 'Contact' );
$o->setProperty( 'contact' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'last-modified' );
$o->setProperty( 'Recurrence-id' );
$o->setProperty( 'request-status' );

$o = & $c->newComponent( 'vfreebusy' );
$o->setProperty( 'comment' );
$o->setProperty( 'Contact' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'duration' );
$o->setProperty( 'freebusy' );
$o->setProperty( 'organizer' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment' );
$o->setProperty( 'Contact' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'due' );
$o->setProperty( 'Percent-Complete' );
$o->setProperty( 'Related-To' );
$o->setProperty( 'sequence' );

$o = & $c->newComponent( 'vjournal' );
$o->setProperty( 'comment' );
$o->setProperty( 'Contact' );
$o->setProperty( 'contact' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'last-modified' );
$o->setProperty( 'request-status' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment' );
$o->setProperty( 'Contact' );
$o->setProperty( 'dtstart' );
$o->setProperty( 'duration' );
$o->setProperty( 'Percent-Complete' );
$o->setProperty( 'Related-To' );
$o->setProperty( 'sequence' );

// save calendar in file, get size, create new calendar, parse saved file, get size
$d   = 'file folder';
$f1  = 't e s t .ics';
$f2  = 't e s t 2 .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
$c2 = new vcalendar( array( 'unique_id' => 'test.se' ));
$c2->setConfig( 'directory', $d );
$c2->setConfig( 'filename', $f1 );
$c2->setConfig( 'allowEmpty', FALSE );  // now we don't allow empty properties!!!
$c2->parse();
if( FALSE === $c2->setConfig( 'filename', $f2 )) echo "setConfig(filename.. .) = FALSE<br>";
$c2->saveCalendar();
$fs2 = $c2->getConfig('filesize');
$df2 = $c2->getConfig('dirfile');
$d  = str_replace(' ', chr(92).' ', $d); // Backslash-character
$f1 = str_replace(' ', chr(92).' ', $f1);
$f2 = str_replace(' ', chr(92).' ', $f2);
$cmd = 'diff -b -H --side-by-side '.$d.'/'.$f1.' '.$d.'/'.$f2;
$c2->saveCalendar();
$fs2 = $c2->getConfig('filesize');
$str = $c2->createCalendar();
echo $str; $a=array(); $n=chr(10); echo "$n 1 filezise=$fs1 dir/file='$df1'$n"; echo " 2 filezise=$fs2 dir/file='$df2'$n"; echo " cmd=$cmd$n"; exec($cmd, $a); echo " diff result:".implode($n,$a);

// $c->returnCalendar();

?>