<?php // recurr_ical_streams_3b_impl.php

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
$e->setProperty( 'Dtstart', '20050426T100000 Eastern' );
$e->setProperty( 'Dtend', '20050426T110000 Eastern' );
$e->setProperty( 'Transp', 'OPAQUE' );
$e->setProperty( 'Rdate'
               , array( array( '20050426T100000 Eastern', '20050426T110000' )));
$e->setProperty( 'Recurrence-id', '20050426T130000Z' );
$e->setProperty( 'Comment', "Reschedule of a single instance's time only (+ 1 hr)"
               , array( 'ALTREP' => 'CID:<FFFF__=0ABBE548DFE1E66E8f9e8a93d@coffeebean.com>' ));
$e->setProperty( 'Sequence', 1 );
$e->setProperty( 'Attendee'
               , 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'Attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'Class', 'PUBLIC' );
$e->setProperty( 'Description'
               , 'body'
               , array( 'ALTREP' => 'CID:<FFFF__=0ABBE548DFE230F48f9e8a93d@coffeebean.com>'));
$e->setProperty( 'Summary', 'More complicated stream (5 day recurring)' );
$e->setProperty( 'Organizer'
               , 'iCalChair@coffeebean.com'
               , array( 'CN' => 'iCal Chair/CoffeeBean' ));
$e->setProperty( 'Uid', '6BA1ECA4D58B306C85256FDB0071B664-Lotus_Notes_Generated' );

$str = $c->createCalendar();
echo $str."<br />\n";


?>