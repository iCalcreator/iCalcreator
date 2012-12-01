<?php // multipleProp_iCal_test.php
require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

            // create data
$propValues =  array( 'ATTACH'         => array( 'http://doclib1.domain1.net/lib1/doc1.txt'
                                               , 'http://doclib2.domain2.net/lib2/doc2.txt'
                                               , 'http://doclib3.domain3.net/lib3/doc3.txt'
                                               , 'http://doclib4.domain4.net/lib4/doc4.txt' )
                    , 'ATTENDEE'       => array( 'someone.else@internet.com'
                                               , 'sometwo.else@internet.com'
                                               , 'somethree.else@internet.com'
                                               , 'somefour.else@internet.com' )
                    , 'CATEGORIES'     => array( 'category1'
                                               , 'category2'
                                               , 'category3'
                                               , 'category4' )
                    , 'COMMENT'        => array( 'comment 1.'
                                               , 'comment 2.'
                                               , 'comment 3.'
                                               , 'comment 4.' )
                    , 'CONTACT'        => array( 'John One, One Ltd, 1st Avenue, Onecity'
                                               , 'John Two, Two Ltd, 2nd Avenue, Twocity'
                                               , 'John Three, Three Ltd, 3rd Avenue, Threecity'
                                               , 'John Four, Four Ltd, 4th Avenue, Fourcity' )
                    , 'DESCRIPTION'    => array( 'description 1.'
                                               , 'description 2.'
                                               , 'description 3.'
                                               , 'description 4.' )
                    , 'EXDATE'         => array( array( array( 'year' => 2001, 'month' => 1, 'day' => 1, 'hour' => 1, 'min' => 1, 'sec' => 1 )
                                                             , '20110111111111' )
                                               , array( array( 'year' => 2002, 'month' => 2, 'day' => 2, 'hour' => 2, 'min' => 2, 'sec' => 2 )
                                                             , '20120212121212' )
                                               , array( array( 'year' => 2003, 'month' => 3, 'day' => 3, 'hour' => 3, 'min' => 3, 'sec' => 3 )
                                                             , '20130313131313' )
                                               , array( array( 'year' => 2004, 'month' => 4, 'day' => 4, 'hour' => 4, 'min' => 4, 'sec' => 4 )
                                                             , '20140414141414' ))
                    , 'EXRULE'         => array( array( 'FREQ' => "DAILY", 'UNTIL' => array( 2001, 1, 1, 1, 1, 1 ), 'INTERVAL' => 1 )
                                               , array( 'FREQ' => "DAILY", 'UNTIL' => array( 2002, 2, 2, 2, 2, 2 ), 'INTERVAL' => 2 )
                                               , array( 'FREQ' => "DAILY", 'UNTIL' => array( 2003, 3, 3, 3, 3, 3 ), 'INTERVAL' => 3 )
                                               , array( 'FREQ' => "DAILY", 'UNTIL' => array( 2004, 4, 4, 4, 4, 4 ), 'INTERVAL' => 4 ))
//                    , 'FREEBUSY'       => array( )
                    , 'RDATE'          => array( array( array( 'year' => 2001, 'month' => 1, 'day' => 1, 'hour' => 1, 'min' => 1, 'sec' => 1 )
                                                             , '20110111111111' )
                                               , array( array( 'year' => 2002, 'month' => 2, 'day' => 2, 'hour' => 2, 'min' => 2, 'sec' => 2 )
                                                             , '20120212121212' )
                                               , array( array( 'year' => 2003, 'month' => 3, 'day' => 3, 'hour' => 3, 'min' => 3, 'sec' => 3 )
                                                             , '20130313131313' )
                                               , array( array( 'year' => 2004, 'month' => 4, 'day' => 4, 'hour' => 4, 'min' => 4, 'sec' => 4 )
                                                             , '20140414141414' ))
                    , 'RELATED-TO'     => array( '11111111-11111111-1111111111@ical1.com'
                                               , '22222222-22222222-2222222222@ical2.com'
                                               , '33333333-33333333-3333333333@ical3.com'
                                               , '44444444-44444444-4444444444@ical4.com' )
//                    , 'REQUEST-STATUS' => array( )
                    , 'RESOURCES'      => array( 'resource1'
                                               , 'resource2'
                                               , 'resource3'
                                               , 'resource4' )
                    , 'RRULE'          => array( array( 'FREQ' => "DAILY", 'UNTIL' => array( 2001, 1, 1, 1, 1, 1 ), 'INTERVAL' => 1 )
                                               , array( 'FREQ' => "DAILY", 'UNTIL' => array( 2002, 2, 2, 2, 2, 2 ), 'INTERVAL' => 2 )
                                               , array( 'FREQ' => "DAILY", 'UNTIL' => array( 2003, 3, 3, 3, 3, 3 ), 'INTERVAL' => 3 )
                                               , array( 'FREQ' => "DAILY", 'UNTIL' => array( 2004, 4, 4, 4, 4, 4 ), 'INTERVAL' => 4 )));
            // connect each component to properties
$comps = array( 'vevent'     => array( 'ATTACH',  'ATTENDEE', 'CATEGORIES'
                                     , 'COMMENT', 'CONTACT',  'EXDATE'
                                     , 'EXRULE',  'RDATE',    'RELATED-TO'
                                     , 'REQUEST-STATUS',      'RESOURCES', 'RRULE' )
              , 'vtodo'      => array( 'ATTACH',  'ATTENDEE', 'CATEGORIES'
                                     , 'COMMENT', 'CONTACT',  'EXDATE'
                                     , 'EXRULE',  'RDATE',    'RELATED-TO'
                                     , 'REQUEST-STATUS',      'RESOURCES', 'RRULE' )
              , 'vjournal'   => array( 'ATTACH',  'ATTENDEE', 'CATEGORIES'
                                     , 'COMMENT', 'CONTACT',  'DESCRIPTION'
                                     , 'EXDATE',  'EXRULE',   'RDATE'
                                     , 'RELATED-TO', 'REQUEST-STATUS', 'RRULE' )
              , 'vfreebusy'  => array( 'ATTENDEE', 'COMMENT', 'FREEBUSY', 'REQUEST-STATUS'));

            // update calendar with data
$order = 0;
foreach( $comps as $comp => $props ) {
  $e = & $c->newComponent( $comp );
  $e->setProperty( 'X-ORDER', ++$order );
  foreach( $props as $propName ) {
    if( !isset( $propValues[$propName] )) continue;
    foreach( $propValues[$propName] as $value )
      $e->setProperty( $propName, $value );
  }
}

            // save calendar in file
$d   = 'file folder';
$f1  = 't e s t .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
            // create new calendar, parse saved file
$c2 = new vcalendar( array( 'unique_id' => 'test.se' ));
$c2->setConfig( 'directory', $d );
$c2->setConfig( 'filename', $f1 );
$c2->parse();

$order = 10;
foreach( $comps as $comp => $props ) {
            // retrieve component
  $e2 = $c2->getComponent( $comp ); // ta ut en kopia
            // remove the old component
  $c2->deleteComponent( $e2->getProperty('uid'));
  $e2->setProperty( 'X-OLD-UID', $e2->getProperty('uid'));
            // create new component && insert the new component
  $e3 = & $c2->newComponent( $comp );
  $e3->setProperty( 'X-ORDER2', ++$order );
  foreach( $props as $propName ) {
    $propValues[$propName] = array();
            // retrieve all properties
    while( $data = $e2->getProperty( $propName )) {
      $propValues[$propName][] = $data;
  //  echo 'php nr '.count($props['description'])." = $d<br />"; // test ###
    }
            // delete all specified properties from component $e2
    while( $e2->deleteProperty( $propName )) continue;
            // insert the properties into the new component
    $ex = count( $propValues[$propName] );
    foreach( $propValues[$propName] as $dix => $data ) {
      if( !$e3->setProperty( $propName, $data, FALSE, $ex-- )) echo "set $propName, nr $dix fung EJ<br />";
    }
  }
            // insert the old component again, but now last in chain
  $e2->deleteProperty('uid');
  $c2->setComponent( $e2);
}
$str = $c2->createCalendar();
echo $str;

// $c->returnCalendar();
?>