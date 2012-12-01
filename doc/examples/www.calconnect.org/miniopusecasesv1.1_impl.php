<?php // miniopusecasesv1.1_impl.php

require_once '../iCalcreator.class.php';

echo "12345678901234567890123456789012345678901234567890123456789012345678901234567890<br />\n";
echo "         1         2         3         4         5         6         7         8<br />\n";



/*     ################################################## */
echo  "1.1 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();
$c->setProperty( 'method', 'REQUEST' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T090000 CEST' );
$e->setProperty( 'dtend', '20060928T100000 CEST' );
$e->setProperty( 'transp', 'OPAQUE' );
$e->setProperty( 'description', "Let's play tennis next Wednesday" );
$e->setProperty( 'attendee'
               , 'player1@tennis.org'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'player 1/tennis'));
$e->setProperty( 'attendee', 'player2@tennis.org'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'player 2/tennis'));
$e->setProperty( 'class', 'PUBLIC' );
$e->setProperty( 'organizer', 'player1@tennis.org' );

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "1.2 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T140000 CEST' );
$e->setProperty( 'description', "At 2 pm I need to take my pills." );
$e->setProperty( 'class', 'PRIVATE' );

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "1.2 Example #2                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T183000 CEST' );
$e->setProperty( 'description', "Party at my house starting at 6:30 pm." );
$e->setProperty( 'class', 'PUBLIC' );

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "1.2 Example #3                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20051214T190000 CEST' );
$e->setProperty( 'description', "Rolling Stones, Red Rocks Ampitheatre, 12/14/05, 7:00 pm" );
$e->setProperty( 'location', 'Red Rocks Ampitheatre' );
$e->setProperty( 'class', 'PUBLIC' );

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "1.2 Example #4                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T153000 CEST' );
$e->setProperty( 'description', "Leave at 3:30 pm to go pickup the kids." );
$e->setProperty( 'class', 'PRIVATE' );

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "1.2 Example #5                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T150000 CEST' );
$e->setProperty( 'description', " A reminder that I need to turn in a project report at 3pm" );
$e->setProperty( 'class', 'PRIVATE' );

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "1.3 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T150000' );
$e->setProperty( 'dtend', '20060928T160000' );
$e->setProperty( 'description', ". ..a meeting.. ." );

$a = & $e->newComponent( 'valarm' );
$a->setAction( 'DISPLAY' );
$a->setProperty( 'description', " I want to be reminded 5 minutes before a meeting starts." );
$a->setTrigger( FALSE, FALSE, FALSE, FALSE, FALSE, 5);

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.1 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060928T090000' );
$e->setProperty( 'dtend', '20060928T110000' );
$e->setProperty( 'description', "Class is on Tue/Thu of each week" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'TH' ))));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.1 Example #2                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060928T090000' );
$e->setProperty( 'dtend', '20060928T110000' );
$e->setProperty( 'description', "Every Wednesday we have a meeting" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'BYDAY'      => array( 'DAY' => 'WE' )));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.1 Example #3                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060928T090000' );
$e->setProperty( 'dtend', '20060928T110000' );
$e->setProperty( 'description', "Every year on July 4th" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 7
                      , 'BYMONTHDAY' => 4));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.1 Example #4                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060928T190000' );
$e->setProperty( 'dtend', '20060928T230000' );
$e->setProperty( 'description', "Every 3 Sundays play poker" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'INTERVAL'   => 3
                      , 'BYDAY'      => array( 'DAY' => 'SU' )));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.1 Example #5                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T110000' );
$e->setProperty( 'dtend', '20060928T111500' );
$e->setProperty( 'description', "Every 4 hours take a 15 min break" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "HOURLY"
                      , 'INTERVAL'   => 4));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.2 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060928T110000' );
$e->setProperty( 'dtend', '20060928T150000' );
$e->setProperty( 'description', "Every 3rd Tuesday of the month go to the beach" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYDAY'      => array( 3, 'DAY' => 'TH' )));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.2 Example #2                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T000000' );
$e->setProperty( 'dtend', '20060928T235959' );
$e->setProperty( 'description', "The last Friday in November is black Friday" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 11
                      , 'BYDAY'      => array( -1, 'DAY' => 'FR' )));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.3 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T090000' );
$e->setProperty( 'description', "Pay bills on the 15th of the month." );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYMONTHDAY' => 15 ));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.3 Example #2                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T090000' );
$e->setProperty( 'description', "Pay day is the last day of the month." );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYMONTHDAY' => -1 ));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.3 Example #3                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'dtstart', '20060928T090000' );
$e->setProperty( 'description', "Annual report due by end of February every year." );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 2
                      , 'BYMONTHDAY' => -1 ));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.4 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060927T110000' );
$e->setProperty( 'dtend', '20060927T150000' );
$e->setProperty( 'description', "The dates for a lecture series: Tuesday this week, Wednesday next week, & Friday the following week." );
$e->setProperty( 'rdate', array( '20061004', 20061013 ));

$str = $c->createCalendar();
echo $str."<br />\n";


/*     ################################################## */
echo  "2.5 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060927T110000' );
$e->setProperty( 'dtend', '20060927T150000' );
$e->setProperty( 'description', "The 2nd Sunday every 3 months for a small church that only has communion every 3 months." );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'INTERVAL'   => 3
                      , 'BYDAY'      => array( 2, 'DAY' => 'SU' )));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.5 Example #2                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060927T110000' );
$e->setProperty( 'dtend', '20060927T150000' );
$e->setProperty( 'description', "The 1st day of every other month" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYMONTHDAY' => 1 )); // ?? every other month ??

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.6 Example #1                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060927T110000' );
$e->setProperty( 'dtend', '20060927T150000' );
$e->setProperty( 'description', "Last Friday every month except November" );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYMONTH'    => array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12 )
                      , 'BYDAY'      => array( -1, 'DAY' => 'FR' )));

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.6 Example #2                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060927T110000' );
$e->setProperty( 'dtend', '20060927T150000' );
$e->setProperty( 'description', "Meeting on Mondays January through March except for Monday holidays." );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'UNTIL'      => '20060331'
                      , 'BYMONTH'    => array( 1, 2, 3 )
                      , 'BYDAY'      => array( 'DAY' => 'MO' )));
$e->setProperty( 'exdate', array( '20060109' )); // ?? holiday.. . !!

$str = $c->createCalendar();
echo $str."<br />\n";


/*     ################################################## */
echo  "2.6 Example #3                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060101T110000' );
$e->setProperty( 'dtend', '20060101T150000' );
$e->setProperty( 'description', "Moving a meeting. We have a status meeting every Monday except next Monday is Labor Day, so we'll have to move that meeting to Tuesday." );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'BYDAY'      => array( 'DAY' => 'MO' )));
$e->setProperty( 'exdate', array( '20060401' )); // ?? Labor Day.. . !!

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
                , array( 'ROLE'           => 'REQ-PARTICIPANT'
                       , 'PARTSTAT'       => 'NEEDS-ACTION'
                       , 'RSVP'           => 'TRUE'
                       , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060404T110000' );
$e->setProperty( 'dtend', '20060404T150000' );

$str = $c->createCalendar();
echo $str."<br />\n";

/*     ################################################## */
echo  "2.6 Example #4                             <br />\n";
/*     ################################################## */
$c = new vcalendar ();

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060101T110000' );
$e->setProperty( 'dtend', '20060101T150000' );
$e->setProperty( 'description', "Meeting every 5 weeks on Thursday plus next Wednesday." );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'INTERVAL'   => 5
                      , 'BYDAY'      => array( 'DAY' => 'TH' )));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'attendee', 'iCalChair@coffeebean.com'
               , array( 'ROLE'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'FALSE'
                      , 'CN'             => 'iCal Chair/CoffeeBean'));
$e->setProperty( 'attendee', 'iCalParticipant@coffeebean.com'
               , array( 'ROLE'           => 'REQ-PARTICIPANT'
                      , 'PARTSTAT'       => 'NEEDS-ACTION'
                      , 'RSVP'           => 'TRUE'
                      , 'CN'             => 'iCal Participant/CoffeeBean'));
$e->setProperty( 'dtstart', '20060108T110000' );
$e->setProperty( 'dtend', '20060108T50000' );
$e->setProperty( 'description', "Meeting every 5 weeks on Thursday plus next Wednesday." );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'INTERVAL'   => 5
                      , 'BYDAY'      => array( 'DAY' => 'WE' )));

$str = $c->createCalendar();
echo $str."<br />\n";

?>