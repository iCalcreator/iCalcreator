<?php // recurrenceid_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$date = date('Y-m-d H:i:s', mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')));
$offset = date('O');

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'NOW without offset   = * = * = * = * =' );
$o->setProperty( 'comment', 'date='.$date);
$o->setProperty( 'Recurrence-id', $date );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'NOW without offset + setConfig(TZID)' );
$o->setProperty( 'comment', 'date='.$date);
$o->setConfig( 'TZID', 'Europe/ROM' );
$o->setProperty( 'Recurrence-id', $date );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', "NOW with offset [date('O')]" );
$o->setProperty( 'comment', "date=$date $offset" );
$o->setProperty( 'Recurrence-id', "$date $offset");

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'NOW with offset' );
$o->setProperty( 'comment', "date=$date , array( 'TZID' => $offset )" );
$o->setProperty( 'Recurrence-id', $date, array('TZID' => $offset));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'NOW with timezone');
$o->setProperty( 'comment', "date=$date ".date('T'));
$o->setProperty( 'Recurrence-id', $date.' '.date('T'));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'NOW with timezone');
$o->setProperty( 'comment', 'date='.$date." , array( 'TZID' => ".date('T').')');
$o->setProperty( 'Recurrence-id', $date, array('TZID' => date('T')));

// test date in an array (excl. timestamp)
$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A1a: short array( 2001, 2, 3 ), position replaces key' );
$o->setProperty( 'Recurrence-id', array( 2001, 2, 3 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'START, test date in an short array   + setConfig(TZID) date' );
$o->setProperty( 'comment', 'A1a: short array( 2001, 2, 3 ), position replaces key' );
$o->setConfig( 'TZID', 'Europe/ROM' );
$o->setProperty( 'Recurrence-id', array( 2001, 2, 3 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A1b: short array( 2001, 2, 3 ), position replace key, param: array(VALUE=>DATE)' );
$o->setProperty( 'Recurrence-id'
               , array( 2001, 2, 3 )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A1c: short array( 2001, 2, 3 ), position replace key, param: array(VALUE=>DATE-TIME,RANGE=THISANDFUTURE)' );
$o->setProperty( 'Recurrence-id'
               , array( 2001, 2, 3 )
               , array( 'VALUE' => 'DATE-TIME', 'RANGE' => 'THISANDFUTURE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comMent', 'A1d: short array( day=>3, month=>2, year=>2001 ), array with keys' );
$o->setProperty( 'Recurrence-ID'
               , array( 'day'=>3, 'month'=>2, 'year'=>2001 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A1e: short array( day=>3, month=>2, year=>2001 ), array with keys, param: array(VALUE=>DATE)' );
$o->setProperty( 'Recurrence-id'
               , array( 'day'=>3, 'month'=>2, 'year'=>1 )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A1f: short array( day=>3, month=>2, year=>2001 ), array with keys, param: array(VALUE=>DATE-TIME,RANGE=>THISANDPRIOR)' );
$o->setProperty( 'RECurrence-id'
               , array( 'day'=>3, 'month'=>2, 'year'=>2001 )
               , array( 'VALUE' => 'DATE-TIME'
                      , 'RANGE' => 'THISANDPRIOR' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'START, test date in an long array   = * = * = * = * =' );
$o->setProperty( 'Comment', 'A2a: long array(2001, 2, 3, 4, 5, 6, "+0200"), position replace key with UTC offset' );
$o->setProperty( 'Recurrence-id', array( 2001, 2, 3, 4, 5, 6, '+0200' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2b: long array( 2001, 2, 3, 4, 5, 6), position replace key' );
$o->setProperty( 'RecuRRence-id', array( 2001, 2, 3, 4, 5, 6 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2b: long array( 2001, 2, 3, 4, 5, 6), position replace key   + setConfig(TZID)' );
$o->setConfig( 'TZID', 'Europe/Rom' );
$o->setProperty( 'RecuRRence-id', array( 2001, 2, 3, 4, 5, 6 ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2b: long array( 2001, 2, 3, 4, 5, 6), position replace key, param: array(VALUE=>DATE)' );
$o->setProperty( 'RecuRRence-id'
               , array( 2001, 2, 3, 4, 5, 6 )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2c: long array( 2001, 2, 3, 4, 5, 6 ), position replace key, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'Recurrence-id', array( 2001, 2, 3, 4, 5, 6 ), array( 'VALUE' => 'DATE-TIME' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2d: long array( 2001, 2, 3, 4, 5, 6, "-0100" ), position replace key, param: array(RANGE=>THISANDFUTURE)' );
$o->setProperty( 'Recurrence-id'
               , array( 2001, 2, 3, 4, 5, 6, '-0100' )
               , array( 'RANGE'=>'THISANDFUTURE'));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2e: long array( 2001, 2, 3, 4, 5, 6, "-0100" ), position replace key, param: array(VALUE=>DATE)' );
$o->setProperty( 'Recurrence-id'
               , array( 2001, 2, 3, 4, 5, 6, '-0100' )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2f: long array( 2001, 2, 3, 4, 5, 6, "-0100" ), position replace key, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'Recurrence-id'
               , array( 2001, 2, 3, 4, 5, 6, '-0100' )
               , array( 'VALUE' => 'DATE-TIME' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2g: long array( 2001, 2, 3, 4, 5, 6, "Europe/Stockholm" ), position replace key, param: array(RANGE=>THISANDPRIOR)' );
$o->setProperty( 'Recurrence-id'
               , array( 2001, 2, 3, 4, 5, 6, 'Europe/Stockholm' )
               , array( 'RANGE' => 'THISANDPRIOR'));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2h: long array( 2001, 2, 3, 4, 5, 6, "Europe/Stockholm" ), position replace key, param: array(VALUE=>DATE)' );
$o->setProperty( 'Recurrence-id'
               , array( 2001, 2, 3, 4, 5, 6, 'Europe/Stockholm' )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2i: long array( 2001, 2, 3, 4, 5, 6, "Europe/Paris" ), position replace key, param: array(VALUE=>DATE-TIME)' );
$o->setProperty('Recurrence-id',array(2001,2,3,4,5,6,'Europe/Paris'),array('VALUE'=>'DATE-TIME'));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2j: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001 ), array with keys, param: array(RANGE=>THISANDFUTURE)' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001 )
               , array( 'RANGE'=>'THISANDFURURE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2k: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001 ), array with keys, param: array(VALUE=>DATE)' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001 )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2l: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001 ), array with keys, param: array(VALUE=>DATE-TIME,RANGE=>THISANDPRIOR)' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001 )
               , array( 'VALUE' => 'DATE-TIME', 'RANGE'=>'THISANDPRIOR' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2m: long array(sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=+0400),array with keys and timezone');
$o->setProperty( 'Recurrence-id'
             , array('sec'=>6,'min'=>5,'hour'=>4,'day'=>3,'month'=>2,'year'=>2001,'tz'=>'+0400' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2o: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=+0400 ), array with keys and timezon, param: array(VALUE=>DATE)' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6,'min'=>5,'hour'=>4,'day'=>3,'month'=>2,'year'=>2001,'tz'=>'+0400' )
               , array( 'VALUE'=>'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2p: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=+0400 ), array with keys and timezon, param: array(VALUE=>DATE-TIME,RANGE=>THISANDFUTURE)' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001, 'tz'=>'+0400' )
               , array( 'VALUE'=>'DATE-TIME', 'RANGE'=>'THISANDFUTURE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2q: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=Europe/Stockholm ),array with keys and timezone' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001, 'tz'=>'Europe/Stockholm' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2r: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=Europe/Stockholm ), array with keys and timezon, param: array(VALUE=>DATE)' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001, 'tz'=>'Europe/Stockholm' )
               , array( 'VALUE'=>'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2s: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=Europe/Stockholm ), array with keys and timezon, param: array(VALUE=>DATE-TIME,RANGE=>THISANDPRIOR)' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001, 'tz'=>'Europe/Stockholm' )
               , array( 'VALUE'=>'DATE-TIME'
                      , 'RANGE'=>'THISANDPRIOR' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'A2t: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=Europe/Stockholm ), array with keys and timezon, param: array(VALUE=>DATE, TZID=>Europe/Helsinki)' );
$o->setProperty( 'Recurrence-id'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001, 'tz'=>'Europe/Stockholm' )
               , array( 'VALUE'=>'DATE', 'TZID'=>'Europe/Helsinki' ));

// test date in an array (incl. timestamp)
$o = & $c->newComponent( 'vtodo' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'Comment', 'B1a '.$timestamp.' =now+4hours tre xparams' );
$o->setProperty( 'Recurrence-id'
               , array( 'timestamp' => $timestamp )
               , array ( 'jestanes'
                       , 'xkey' => 'xvalue'
                       , 'xxx' => 'yyy' ) );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'B1a '.$timestamp.' =now+4hours tre xparams   + setConfig(TZID) date' );
$o->setConfig( 'TZID', 'Europe/Rom' );
$o->setProperty( 'Recurrence-id'
               , array( 'timestamp' => $timestamp )
               , array ( 'jestanes'
                       , 'xkey' => 'xvalue'
                       , 'xxx' => 'yyy' ) );

$o = & $c->newComponent( 'vtodo' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'Comment', 'B2a '.$timestamp.' =now+4hours tz=+0100 tre xparams' );
$o->setProperty( 'Recurrence-id'
               , array( 'timestamp' => $timestamp, 'tz' => '+0100' )
               , array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy' ) );

$o = & $c->newComponent( 'vtodo' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'Comment', 'B2b '.$timestamp.' =now+4hours tz=CEST tre xparams' );
$o->setProperty( 'Recurrence-id'
               , array( 'timestamp' => $timestamp, 'tz' => 'CEST' )
               , array ( 'jestanes'
                       , 'xkey' => 'xvalue'
                       , 'xxx' => 'yyy' ) );

$o = & $c->newComponent( 'vtodo' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'Comment', 'B2c '.$timestamp.' =now+4hours tz=+0100 tre xparams+VALUE=>DATE' );
$o->setProperty( 'Recurrence-id'
               , array( 'timestamp' => $timestamp, 'tz' => '+0100' )
               , array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy', 'VALUE' => 'DATE' ) );

$o = & $c->newComponent( 'vtodo' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'Comment', 'B2d '.$timestamp.' =now+4hours tz=CEST tre xparams+VALUE=>DATE-TIME' );
$o->setProperty( 'Recurrence-id'
               , array( 'timestamp' => $timestamp, 'tz' => 'CEST' )
               , array ( 'jestanes'
                       , 'xkey' => 'xvalue'
                       , 'xxx' => 'yyy'
                       , 'VALUE' => 'DATE-TIME' ) );

// test date in a string
$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'START, test date in a string   = * = * = * = * =' );
$o->setProperty( 'comment', 'C0: 2001-02-03 04:05:06' );
$o->setProperty( 'Recurrence-id', '2001-02-03 04:05:06' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'START, test date in a string   + setConfig(TZID)' );
$o->setProperty( 'comment', 'C0: 2001-02-03 04:05:06' );
$o->setConfig( 'TZID', 'Europe/Rom' );
$o->setProperty( 'Recurrence-id', '2001-02-03 04:05:06' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'C1: 2001-02-03 04:05:06 US-Eastern' );
$o->setProperty( 'Recurrence-id', '2001-02-03 04:05:06 US-Eastern' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', "C2: 2001-02-03 04:05:06 US-Eastern, array( 'VALUE' => 'DATE' )" );
$o->setProperty( 'Recurrence-id'
               , '2001-02-03 04:05:06 US-Eastern'
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', "C3: '2001-02-03 04:05:06 US-Eastern', array( 'VALUE' => 'DATE-TIME' )" );
$o->setProperty( 'Recurrence-id'
               , '2001-02-03 04:05:06 US-Eastern'
               , array( 'VALUE' => 'DATE-TIME' ) );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'C4: 2001-02-03' );
$o->setProperty( 'Recurrence-id', '2001-02-03' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'C5: 20010203' );
$o->setProperty( 'Recurrence-id', '20010203' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'C6: 20010203040506 UTC' );
$o->setProperty( 'Recurrence-id', '20010203040506 UTC' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'C6b: 20010203T040506' );
$o->setProperty( 'recurrence-id', '20010203T040506' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'C6c: 20010203T040506 Europe/Stockholm' );
$o->setProperty( 'recurrence-id', '20010203T040506 Europe/Stockholm' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'C7: 3 Feb 2001 GMT' );
$o->setProperty( 'Recurrence-id', '3 Feb 2001 GMT' );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'C8: 02/03/2001' );
$o->setProperty( 'Recurrence-id', '02/03/2001' );

// test date with dateparts in all variables
$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'comment', 'START, test date in dateparts in (all) arguments = * = * = * = * =' );
$o->setProperty( 'Comment', 'D1: 1, 2, 3' );
$o->setProperty( 'Recurrence-id', 1, 2, 3 );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'D2: 1, 2, 3, 4, 5, 6' );
$o->setProperty( 'Recurrence-id', 1, 2, 3, 4, 5, 6 );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', 'D2: 1, 2, 3, 4, 5, 6   + setConfig(TZID)' );
$o->setConfig( 'TZID', 'Europe/Rom' );
$o->setProperty( 'Recurrence-id', 1, 2, 3, 4, 5, 6 );

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', "D3: 2006, 8, 11, 16, 30, 0, '-040000'" );
$o->setProperty( 'Recurrence-id'
               , 2006, 8, 11, 16, 30, 0, '-040000' ); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', "D4: 2006, 8, 11, 16, 30, 0, '-040000', array( 'VALUE' => 'DATE' )" );
$o->setProperty( 'Recurrence-id'
               , 2006, 8, 11, 16, 30, 0, '-040000'
               , array( 'VALUE' => 'DATE' )); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', "D5: 2006, 8, 11, 16, 30, 0, '-040000', array( 'VALUE' => 'DATE-TIME' )" );
$o->setProperty( 'Recurrence-id'
               , 2006, 8, 11, 16, 30, 0, '-040000'
               , array( 'VALUE' => 'DATE-TIME' ) ); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', "D6: 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm'" );
$o->setProperty( 'Recurrence-id'
               , 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm' ); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', "D7: 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm', array( 'VALUE' => 'DATE' )" );
$o->setProperty( 'Recurrence-id'
               , 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm'
               , array( 'VALUE' => 'DATE' )); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vtodo' );
$o->setProperty( 'Comment', "D8: 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm', array( 'VALUE' => 'DATE-TIME' )" );
$o->setProperty( 'Recurrence-id'
               , 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm'
               , array( 'VALUE' => 'DATE-TIME' ) ); // 11 august 2006 16.30.00 -040000

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