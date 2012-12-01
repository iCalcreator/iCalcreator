<?php // rrule_iCal_text.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
  /* freq = "SECONDLY" / "MINUTELY" / "HOURLY" / "DAILY" / "WEEKLY" / "MONTHLY" / "YEARLY" */

$e->setProperty( 'rrule'
               , array( 'FREQ'       => "MINUTELY"
                      , 'UNTIL'      => array( 2001, 2, 3 )
                      , 'INTERVAL'   => 2 ));
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => array( 2001, 2, 3, 0, 0, 0, '+0200' )
                      , 'BYMONTHDAY' => array( 2, -4, 6 ) ));   // single value/array of values
$e->setProperty( 'RRULE'
               , array( 'FREQ'       => "HOURLY"
                      , 'UNTIL'      => array( 2001, 2, 3, 4, 5, 6 )
                      , 'BYday'      => array( 'MO' ))
               , array( 'xparamkey'  => 'xparamValue' ));
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => array( 'year' => 2001, 'month' => 2, 'day' => 3 )
                      , 'BYday'      => array( 'DAY' => 'WE' )));
$e->setProperty( 'Rrule'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => array('year'=>2001,'month'=>2,'day'=>3 )
                      , 'BYday'      => array(array( 'DAY' => 'MO' )
                                             ,array( 'DAY' => 'WE' )
                                             ,array( 'DAY' => 'FR' ))));
$e->setProperty( 'rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => array( 'year' => 2001, 'month' => 2, 'day' => 3, 'hour' => 4, 'min' => 5, 'sec' => 6 )
                      , 'BYday'      => array( 5, 'DAY' => 'WE' )            // single value/array of values
 ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Rrule'
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

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'rrule'
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
                      , 'X-NAME'     => 'x-value'));$c->addComponent( $e );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Comment'
               , 'every last Monday in month until (timestamp =) now(Ymd only) + 7 month' );
  /* freq = "SECONDLY" / "MINUTELY" / "HOURLY" / "DAILY" / "WEEKLY" / "MONTHLY" / "YEARLY" */
$timestamp = mktime ( 0, 0, 0, date('m') + 7, date('d'), date('Y'));
$e->setProperty( 'Rrule'
               , array( 'FREQ'       => "MONTHLY"
                      , 'UNTIL'      => array( 'timestamp' => $timestamp )
                      , 'BYday'      => array( -1, 'DAY' => 'MO' )));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment'
               , '3 different rrules with the same index, only DAILY left' );
$timestamp = mktime ( 0, 0, 0, date('m') + 7, date('d'), date('Y'));
$e->setProperty( 'rRuLe'
               , array( 'FREQ'       => "MINUTELY"
                      , 'UNTIL'      => array( 2001, 2, 3 )
                      , 'INTERVAL'   => 2 ));
$e->setProperty( 'rRuLe'
               , array( 'FREQ'       => "HOURLY"
                      , 'UNTIL'      => array( 2001, 2, 3 )
                      , 'INTERVAL'   => 2 )
               , FALSE
               , 1 );
$e->setProperty( 'rRuLe'
               , array( 'FREQ'       => "DAILY"
                      , 'UNTIL'      => array( 2001, 2, 3 )
                      , 'INTERVAL'   => 2 )
               , FALSE
               , 1 );

$e = & $c->newComponent( 'vevent' );
$e->setProperty('comment',"RRULE:FREQ=WEEKLY;UNTIL=20071009T000000Z;INTERVAL=2;BYDAY=MO;WKST=MO");
$e->setProperty( 'Rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => '20071009T000000Z'
                      , 'INTERVAL'   => 2
                      , 'BYday'      => array( -1, 'DAY' => 'MO' )
                      , 'WKST'       => 'MO' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty('comment',"parse RRULE:FREQ=WEEKLY;UNTIL=20110701;BYDAY=MO,TU,WE,TH,FR" );
$e->parse( 'RRULE:FREQ=WEEKLY;UNTIL=20110701;BYDAY=MO,TU,WE,TH,FR' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty('comment',"setRRULE RRULE:FREQ=WEEKLY;UNTIL=20110701;BYDAY=MO,TU,WE,TH,FR" );
$e->setProperty( 'Rrule'
               , array( 'FREQ'       => "WEEKLY"
                      , 'UNTIL'      => '20110701'
                      , 'BYday'      => array( array( 'DAY' => 'MO' )
                                              ,array( 'DAY' => 'TU' )
                                              ,array( 'DAY' => 'WE' )
                                              ,array( 'DAY' => 'TH' )
                                              ,array( 'DAY' => 'FR' ))));

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