<?php  // action2_iCal_test.php
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
var_dump( $c );
?>