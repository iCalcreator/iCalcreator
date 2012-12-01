
<?php // recurr_ical_streams_1a_impl.php

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
$e->setProperty( 'Uid', 'E88157FE01BE8A5C85256FDB006EBCC3-Lotus_Notes_Generated' );
$e->setProperty( 'Class', 'PUBLIC' );
$e->setProperty( 'Dtstart', '20050411T090000 Eastern' );
$e->setProperty( 'Dtend', '20050411T100000 Eastern' );
$e->setProperty( 'Rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'COUNT'      => 5 ));
$e->setProperty( 'Sequence', 0 );
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
$e->setProperty( 'Organizer'
               , 'iCalChair@coffeebean.com'
               , array( 'CN' => 'iCal Chair/CoffeeBean' ));
$e->setProperty( 'Summary', '5 day daily repeating meeting' );
$e->setProperty( 'Transp', "OPAQUE" );

$str = $c->createCalendar();
echo $str."<br />\n";

?>