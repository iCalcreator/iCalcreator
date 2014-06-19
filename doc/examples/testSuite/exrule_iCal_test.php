<?php // exrule_iCal_text.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
  /* freq = "SECONDLY" / "MINUTELY" / "HOURLY" / "DAILY" / "WEEKLY" / "MONTHLY" / "YEARLY" */

$e->parse("EXRULE:FREQ=SECONDLY;INTERVAL=7;BYSECOND=17,45;BYMINUTE=26,5;BYHOUR=4,9;BYDAY=+6TU,+6MO,+6MO,+3MO;BYMONTHDAY=6,15;BYYEARDAY=347,192,154;BYWEEKNO=24,51,2,15;BYMONTH=8,7,9,8;BYSETPOS=55,93,230,69;WKST=SU;UNTIL=20081019T105326Z " );
$e->parse(array( "EXRULE:FREQ=MINUTELY;INTERVAL=7;BYSECOND=17,45;BYMINUTE=26,5;"
               , "BYHOUR=4,9;BYDAY=+6TU,+6MO,+6MO,+3MO;BYMONTHDAY=6,15;"
               , "BYYEARDAY=347,192,154;BYWEEKNO=24,51,2,15;BYMONTH=8,7,9,8;"
               , "BYSETPOS=55,93,230,69;WKST=SU;UNTIL=20081019T105326Z " ));
$e->setProperty( 'Exrule'
               , array( 'FREQ'       => "HOURLY"
                      , 'UNTIL'      => array( 2001, 2, 3 )
                      , 'INTERVAL'   => 2 )
               , array( 'x-key' => 'y-value' ));
$e->setProperty( 'exrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => array( 2001, 2, 3, 0, 0, 0, '+0200' )
                      , 'BYMONTHDAY' => array( 2, -4, 6 ) ));   // single value/array of values
$e->setProperty( 'exrule'
             , array( 'FREQ'       => "HOURLY"
                    , 'UNTIL'      => array( 2001, 2, 3, 4, 5, 6 ))
             , array( 'xparamkey'  => 'xparamValue' ));
$e->setProperty( 'exrule'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => array( 'year' => 1, 'month' => 2, 'day' => 3 )
                      , 'BYday'      => array( 'DAY' => 'WE' )));
$e->setProperty( 'ExRuLe'
             , array( 'FREQ'       => "DAILY"
                    , 'UNTIL'      => array( 'year' => 1, 'month' => 2, 'day' => 3)
                    , 'BYday'      => array( 'DAY' => 'WE' )));
$e->setProperty( 'exrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => array( 'year' => 1, 'month' => 2, 'day' => 3, 'hour' => 4, 'min' => 5, 'sec' => 6 )
                      , 'BYday'      => array( 5, 'DAY' => 'WE' )            // single value/array of values
 ));
$e->setProperty( 'exrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'UNTIL'      => '3 Feb 2007'
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU'
                      , 'BYSECOND'   => 2
                      , 'BYMINUTE'   => array( 2, -4, 6 )                    // single value/array of values
                      , 'BYHOUR'     => array( 2, 4, -6 )                    // single value/array of values
                      , 'BYMONTHDAY' => -2                                   // single value/array of values
                      , 'BYYEARDAY'  => 2                                    // single value/array of values
                      , 'BYWEEKNO'   => array( 2, -4, 6 )                    // single value/array of values
                      , 'BYMONTH'    => 2                                    // single value/array of values
                      , 'BYSETPOS'   => array( 2, -4, 6 )                    // single value/array of values
                      , 'BYday'      => array( array( -2, 'DAY' => 'WE' )    // array of values
                                             , array(  3, 'DAY' => 'TH' )
                                             , array(  5, 'DAY' => 'FR' )
                                             ,            'DAY' => 'SA'
                                             , array(     'DAY' => 'SU' ))
                      , 'X-NAME'     => 'x-value' )
               , array( 'xparamkey'  => 'xparamValue'
                    ,                 'yParamValue' ));
$e->setProperty( 'exrule'
               , array( 'FREQ'       => "YEARLY"
                      , 'COUNT'      => 2
                      , 'INTERVAL'   => 2
                      , 'WKST'       => 'SU'
                      , 'BYSECOND'   => array( -2, 4, 6 )                    // single value/array of values
                      , 'BYMINUTE'   => -2                                   // single value/array of values
                      , 'BYHOUR'     => 2                                    // single value/array of values
                      , 'BYMONTHDAY' => array( 2, -4, 6 )                    // single value/array of values
                      , 'BYYEARDAY'  => array( -2, 4, 6 )                    // single value/array of values
                      , 'BYWEEKNO'   => -2                                   // single value/array of values
                      , 'BYMONTH'    => array( 2, 4, -6 )                    // single value/array of values
                      , 'BYSETPOS'   => -2                                   // single value/array of values
                      , 'BYday'      => array( 5, 'DAY' => 'MO' )            // single value array/array of value arrays
                      , 'X-NAME'     => 'x-value'));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment'
               , 'every last Monday in month until (timestamp =) now(Ymd only) + 7 month' );
  /* freq = "SECONDLY" / "MINUTELY" / "HOURLY" / "DAILY" / "WEEKLY" / "MONTHLY" / "YEARLY" */
$timestamp = mktime ( 0, 0, 0, date('m') + 7, date('d'), date('Y'));
$e->setProperty( 'eXrUlE'
               , array( 'FREQ'       => "MONTHLY"
                      , 'UNTIL'      => array( 'timestamp' => $timestamp )
                      , 'BYday'      => array( -1, 'DAY' => 'MO' )));

  /* freq = "SECONDLY" / "MINUTELY" / "HOURLY" / "DAILY" / "WEEKLY" / "MONTHLY" / "YEARLY" */
$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment'
               , '3 different exrules with the same index, only DAILY left' );
$timestamp = mktime ( 0, 0, 0, date('m') + 7, date('d'), date('Y'));
$e->setProperty( 'eXrUlE'
               , array( 'FREQ'       => "MINUTELY"
                      , 'UNTIL'      => array( 2001, 2, 3 )
                      , 'INTERVAL'   => 2 ));
$e->setProperty( 'eXrUlE'
               , array( 'FREQ'       => "HOURLY"
                      , 'UNTIL'      => array( 2001, 2, 3 )
                      , 'INTERVAL'   => 2 )
               , FALSE
               , 1 );
$e->setProperty( 'eXrUlE'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => array( 2001, 2, 3 )
                      , 'INTERVAL'   => 2 )
               , FALSE
               , 1 );

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