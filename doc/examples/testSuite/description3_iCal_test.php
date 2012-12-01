<?php // description3_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se', 'directory' => 'file folder', 'filename' => 't e s t .ics' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'description 1.' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'description 2.' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'description 3.' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'description 4.' );


// save calendar in file
$d   = 'file folder';
$f1  = 't e s t .ics';
$c->saveCalendar();

// create new calendar, parse saved file
$c2 = new vcalendar( array( 'unique_id' => 'test.se', 'directory' =>  $d, 'filename' => $f1 ));
$c2->parse();

$c2->deleteComponent( 'vevent', 2 );
$c2->deleteComponent( 'vevent', 2 );

$str = $c2->createCalendar();
echo $str;

// $c->returnCalendar();
?>