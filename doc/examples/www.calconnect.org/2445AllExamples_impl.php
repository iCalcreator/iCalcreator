<?php // 2445AllExamples_impl.php

require_once '../iCalcreator.class.php';

$c = new vcalendar ();
$c->setProperty( 'calscale', 'GREGORIAN' );
$c->setProperty( 'method', 'PUBLISH' );

$t = & $c->newComponent( 'timezone' );
$t->setProperty( 'tzid', 'US-Eastern' );
$t->setProperty( 'Last-Modified', '20040110T032845Z' );

$d = & $t->newComponent( 'daylight' );
$d->setProperty( 'Dtstart', '19900404T010000' );
$d->setProperty( 'Rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 4
                      , 'BYday'      => array( 1, 'DAY' => 'SU' )));
$d->setProperty( 'tzoffsetfrom', '-0500' );
$d->setProperty( 'tzoffsetto', '-0400' );
$d->setproperty( 'tzname', 'EDT' );

$s = & $t->newComponent( 'standard' );
$s->setProperty( 'dtstart', '19901026T060000' );
$s->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 10
                      , 'BYday'      => array( -1, 'DAY' => 'SU' )));
$s->setProperty( 'tzoffsetfrom', '-0400' );
$s->setProperty( 'tzoffsetto', '-0500' );
$s->setProperty( 'tzname', 'EST' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Daily for 10 occurrences:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'COUNT'      => 10 ));
$e->setProperty( 'summary', 'RExample01' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Daily until Dec, 24 1997' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => '19971224T000000Z' ));
$e->setProperty( 'summary', 'RExample02' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every other day - forever:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'INTERVAL'   => 2 ));
$e->setProperty( 'summary', 'RExample03' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every 10 days, 5 occurrences:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'COUNT'      => 5
                      , 'INTERVAL'   => 10 ));
$e->setProperty( 'summary', 'RExample04' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Everyday in January, for 3 years:' );
$e->setProperty( 'dtstart', '19980101T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'UNTIL'      => '20000131T090000Z'
                      , 'BYMONTH'    => 1
                      , 'BYday'      => array( array( 'DAY' => 'SU' )
                                             , array( 'DAY' => 'MO' )
                                             , array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'WE' )
                                             , array( 'DAY' => 'TH' )
                                              , array( 'DAY' => 'FR' )
                                             , array( 'DAY' => 'SA' ))));
$e->setProperty( 'summary', 'RExample05a' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Everyday in January, for 3 years:' );
$e->setProperty( 'dtstart', '19980101T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => '20000131T090000Z'
                      , 'BYMONTH'    => 1 ));
$e->setProperty( 'summary', 'RExample05b' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Weekly for 10 occurrences' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'COUNT'      => 10 ));
$e->setProperty( 'summary', 'RExample06' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Weekly until December 24, 1997' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => '19971224T000000Z' ));
$e->setProperty( 'summary', 'RExample07' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every other week - forever:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'WKST'       => 'SU' ));
$e->setProperty( 'summary', 'RExample08' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Weekly on Tuesday and Thursday for 5 weeks:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => '20000131T090000Z'
                      , 'WKST'       => 'SU'
                      , 'BYday'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'TH' ))));
$e->setProperty( 'summary', 'RExample09a' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Weekly on Tuesday and Thursday for 5 weeks:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'COUNT'      => 10
                      , 'WKST'       => 'SU'
                      , 'BYday'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'TH' ))));
$e->setProperty( 'summary', 'RExample09b' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every other week on Monday, Wednesday and Friday until December 24,1997, but starting on Tuesday, September 2, 1997:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => '19971224T000000Z'
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU'
                      , 'BYday'      => array( array( 'DAY' => 'MO' )
                                             , array( 'DAY' => 'WE' )
                                             , array( 'DAY' => 'FR' ))));
$e->setProperty( 'summary', 'RExample10' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every other week on Tuesday and Thursday, for 8 occurrences:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'COUNT'      => 8
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU'
                      , 'BYday'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'TH' ))));
$e->setProperty( 'summary', 'RExample11' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Monthly on the 1st Friday for ten occurrences:' );
$e->setProperty( 'dtstart', '19970905T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 10
                      , 'BYday'      => array( 1, 'DAY' => 'FR' )));
$e->setProperty( 'summary', 'RExample12' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Monthly on the 1st Friday until December 24, 1997:' );
$e->setProperty( 'dtstart', '19970905T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'MONTHLY'    => '19971224T000000Z'
                      , 'BYday'      => array( 1, 'DAY' => 'FR' )));
$e->setProperty( 'summary', 'RExample13' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every other month on the 1st and last Sunday of the month for 10 occurrences:' );
$e->setProperty( 'dtstart', '19970907T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 10
                      , 'INTERVAL'   => 2
                      , 'BYday'      => array( array(  1, 'DAY' => 'SU' )
                                             , array( -1, 'DAY' => 'SU' ))));
$e->setProperty( 'summary', 'RExample14' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Monthly on the second to last Monday of the month for 6 months:' );
$e->setProperty( 'dtstart', '19970922T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 6
                      , 'BYday'      => array( -2, 'DAY' => 'MO' )));
$e->setProperty( 'summary', 'RExample15' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Monthly on the third to the last day of the month, forever:' );
$e->setProperty( 'dtstart', '19970928T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYMONTHDAY' => array( -3, -2, -1 )));
$e->setProperty( 'summary', 'RExample16' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Monthly on the 2nd and 15th of the month for 10 occurrences:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 10
                      , 'BYMONTHDAY' => array( 2, 15 )));
$e->setProperty( 'summary', 'RExample17' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Monthly on the first and last day of the month for 10 occurrences:' );
$e->setProperty( 'dtstart', '19970930T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 10
                      , 'BYMONTHDAY' => array( 1, -1 )));
$e->setProperty( 'summary', 'RExample18' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every 18 months on the 10th thru 15th of the month for 10 occurrences:' );
$e->setProperty( 'dtstart', '19970910T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 10
                      , 'INTERVAL'   => 18
                      , 'BYMONTHDAY' => array( 10, 11, 12, 13, 14, 15 )));
$e->setProperty( 'summary', 'RExample19' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every Tuesday, every other month:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'INTERVAL'   => 2
                      , 'BYday'      => array( 'DAY' => 'TU' )));
$e->setProperty( 'summary', 'RExample20' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Yearly in June and July for 10 occurrences:' );
$e->setProperty( 'dtstart', '19970610T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'COUNT'      => 10
                      , 'BYMONTH'    => array( 6, 7 )));
$e->setProperty( 'summary', 'RExample21' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every other year on January, February, and March for 10 occurrences:' );
$e->setProperty( 'dtstart', '19970610T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'COUNT'      => 10
                      , 'INTERVAL'   => 2
                      , 'BYMONTH'    => array( 1, 2, 3 )));
$e->setProperty( 'summary', 'RExample22' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every 3rd year on the 1st, 100th and 200th day for 10 occurrences:' );
$e->setProperty( 'dtstart', '19970610T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'COUNT'      => 10
                      , 'INTERVAL'   => 3
                      , 'BYYEARDAY'  => array( 1, 100, 200 )));
$e->setProperty( 'summary', 'RExample23' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every 20th Monday of the year, forever:' );
$e->setProperty( 'dtstart', '19970519T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYDAY'      => array( 20, 'DAY' => 'MO' )));
$e->setProperty( 'summary', 'RExample24' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Monday of week number 20 (where the default start of the week is Monday), forever:' );
$e->setProperty( 'dtstart', '19970512T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'WKST'       => 'MO'
                      , 'BYWEEKNO'   => 20
                      , 'BYDAY'      => array( 'DAY' => 'MO' )));
$e->setProperty( 'summary', 'RExample25' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every Thursday in March, forever:' );
$e->setProperty( 'dtstart', '19970313T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => 3
                      , 'BYDAY'      => array( 'DAY' => 'TH' )));
$e->setProperty( 'summary', 'RExample26' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every Thursday, but only during June, July, and August, forever:' );
$e->setProperty( 'dtstart', '19970605T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'BYMONTH'    => array( 6, 7, 8 )
                      , 'BYDAY'      => array( 'DAY' => 'TH' )));
$e->setProperty( 'summary', 'RExample27' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every Friday the 13th, forever:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'exdate'
               , array( '19970902T090000 US-Eastern' ));
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYMONTHDAY' => 13
                      , 'BYDAY'      => array( 'DAY' => 'FR' )));
$e->setProperty( 'summary', 'RExample28' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'The first Saturday that follows the first Sunday of the month, forever:' );
$e->setProperty( 'dtstart', '19970913T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYMONTHDAY' => array( 7, 8, 9, 19, 11, 12, 13 )
                      , 'BYDAY'      => array( 'DAY' => 'SA' )));
$e->setProperty( 'summary', 'RExample29' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every four years, the first Tuesday after a Monday in November,forever (U.S. Presidential Election day):' );
$e->setProperty( 'dtstart', '19961105T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'INTERVAL'   => 4
                      , 'BYMONTH'    => 11
                      , 'BYMONTHDAY' => array( 2, 3, 4, 5, 6, 7, 8 )
                      , 'BYDAY'      => array( 'DAY' => 'TU' )));
$e->setProperty( 'summary', 'RExample30' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'The 3rd instance into the month of one of Tuesday, Wednesday orThursday, for the next 3 months:' );
$e->setProperty( 'dtstart', '19970904T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'COUNT'      => 3
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'WE' )
                                             , array( 'DAY' => 'TH' ))
                      , 'BYSETPOS'   => 3 ));
$e->setProperty( 'summary', 'RExample31' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'The 2nd to last weekday of the month:' );
$e->setProperty( 'dtstart', '19970929T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'BYDAY'      => array( array( 'DAY' => 'MO' )
                                             , array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'WE' )
                                             , array( 'DAY' => 'TH' )
                                             , array( 'DAY' => 'FR' ))
                      , 'BYSETPOS'   => -2 ));
$e->setProperty( 'summary', 'RExample32' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every 3 hours from 9:00 AM to 5:00 PM on a specific day:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "HOURLY"
                      , 'UNTIL'      => '19970902T170000Z'
                      , 'INTERVAL'   => 3 ));
$e->setProperty( 'summary', 'RExample33' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every 15 minutes for 6 occurrences:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MINUTELY"
                      , 'COUNT'      => 6
                      , 'INTERVAL'   => 15 ));
$e->setProperty( 'summary', 'RExample34' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every hour and a half for 4 occurrences:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MINUTELY"
                      , 'COUNT'      => 4
                      , 'INTERVAL'   => 90 ));
$e->setProperty( 'summary', 'RExample35' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every 20 minutes from 9:00 AM to 4:40 PM every day:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'BYMINUTE'   => array( 0, 20, 40 )
                      , 'BYHOUR'     => array( 9, 10, 11, 12, 13, 14, 15, 16 )));
$e->setProperty( 'summary', 'RExample36a' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'Every 20 minutes from 9:00 AM to 4:40 PM every day:' );
$e->setProperty( 'dtstart', '19970902T090000 US-Eastern' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MINUTELY"
                      , 'INTERVAL'   => 20
                      , 'BYHOUR'     => array( 9, 10, 11, 12, 13, 14, 15, 16 )));
$e->setProperty( 'summary', 'RExample36b' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'An example where the days generated makes a difference because of WKST:' );
$e->setProperty( 'dtstart', '19970805' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'COUNT'      => 4
                      , 'INTERVAL'   => 2
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'SU' ))));
$e->setProperty( 'summary', 'RExample37a' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'changing only WKST from MO to SU, yields different results...' );
$e->setProperty( 'dtstart', '19970805' );
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'COUNT'      => 4
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU'
                      , 'BYDAY'      => array( array( 'DAY' => 'TU' )
                                             , array( 'DAY' => 'SU' ))));
$e->setProperty( 'summary', 'RExample37b' );

$str = $c->createCalendar();
echo $str;
?>