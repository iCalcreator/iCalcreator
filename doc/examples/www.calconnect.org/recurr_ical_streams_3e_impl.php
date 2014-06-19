<?php // recurr_ical_streams_3e_impl.php

require_once '../iCalcreator.class.php';

echo "12345678901234567890123456789012345678901234567890123456789012345678901234567890<br />\n";
echo "         1         2         3         4         5         6         7         8<br />\n";

$c = new vcalendar ();
$c->setProperty( 'Method', 'REQUEST' );
$c->setProperty( 'X-LOTUS-CHARSET', 'UTF-8' );

$t = & $c->newComponent( 'timezone' );
$t->setProperty( 'Tzid', 'Eastern' );

$s = & $t->newComponent( 'standard' );
$s->setProperty( 'Dtstart', '19501029T020000' );
$s->setProperty( 'Rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 10
                      , 'BYDAY'      => array( -1, 'DAY' => 'SU' )
                      , 'BYHOUR'     => 2
                      , 'BYMINUTE'   => 0 ));
$s->setProperty( 'Tzoffsetfrom', '-0400' );
$s->setProperty( 'Tzoffsetto', '-0500' );

$d = & $t->newComponent( 'daylight' );
$d->setProperty( 'Dtstart', '19500402T020000' );
$d->setProperty( 'Rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 4
                      , 'BYDAY'      => array( 1, 'DAY' => 'SU' )
                      , 'BYHOUR'     => 2
                      , 'BYMINUTE'   => 0 ));
$d->setProperty( 'Tzoffsetfrom', '-0500' );
$d->setProperty( 'Tzoffsetto', '-0400' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Dtstart', '20050425T090000 Eastern' );
$e->setProperty( 'Dtend', '20050425T091500 Eastern' );
$e->setProperty( 'Transp', 'OPAQUE' );
$e->setProperty( 'Rdate'
               , array( array( '20050425T090000 Eastern', '20050425T091500' )
                      , array( '20050426T090000 Eastern', '20050426T091500' )
                      , array( '20050427T090000 Eastern', '20050427T091500' )
                      , array( '20050428T090000 Eastern', '20050428T091500' )
                      , array( '20050429T090000 Eastern', '20050429T091500' )));
$e->setProperty( 'Comment'
               , "Set the Start and End Time to be implicit - 9 to 9:15am"
               , array( 'ALTREP' => 'CID:<FFFF__=0ABBE548DFE147488f9e8a93d@coffeebean.com>' ));
$e->setSequence( 3 );
$e->setProperty( 'Attendee'
               , 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'Attendee'
               , 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'Class', 'PUBLIC' );
$e->setProperty( 'Description'
               , 'body'
               , array( 'ALTREP' => 'CID:<FFFE__=0ABBE548DFE147488f9e8a93d@coffeebean.com>'));
$e->setProperty( 'Summary', 'More complicated stream (5 day recurring)' );
$e->setProperty( 'Organizer', 'iCalChair@coffeebean.com', array( 'CN' => 'iCal Chair/CoffeeBean' ));
$e->setProperty( 'Uid(', '6BA1ECA4D58B306C85256FDB0071B664-Lotus_Notes_Generated' );

$str = $c->createCalendar();
echo $str."<br />\n";
?>