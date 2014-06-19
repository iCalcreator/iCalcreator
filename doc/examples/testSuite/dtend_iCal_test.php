<?php // dtend_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test-se' ));

$date = date('Y-m-d H:i:s', mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')));
$offset = date('O');

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'NOW without offset   = * = * = * = * =' );
$o->setProperty( 'comment', 'date='.$date);
$o->setProperty( 'dtend', $date );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'NOW without offset + setConfig(TZID)' );
$o->setProperty( 'comment', 'date='.$date);
$o->setConfig( 'TZID', 'Europe/ROM' );
$o->setProperty( 'dtend', $date );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "NOW with offset [date('O')]" );
$o->setProperty( 'comment', "date=$date $offset" );
$o->setProperty( 'dtend', "$date $offset");

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'NOW with offset' );
$o->setProperty( 'comment', "date=$date , array( 'TZID' => $offset )" );
$o->setProperty( 'dtend', $date, array('TZID' => $offset));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'NOW with timezone');
$o->setProperty( 'comment', "date=$date ".date('T'));
$o->setProperty( 'dtend', $date.' '.date('T'));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'NOW with timezone');
$o->setProperty( 'comment', 'date='.$date." , array( 'TZID' => ".date('T').')');
$o->setProperty( 'dtend', $date, array('TZID' => date('T')));


// test date in an array (excl. timestamp)
$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in an short array   = * = * = * = * =' );
$o->setProperty( 'comment', 'A1a: short array( 2001, 2, 3 ), position replaces key' );
$o->setProperty( 'dtend', array( 2001, 2, 3 ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in an short array   + setConfig(TZID) date' );
$o->setProperty( 'comment', 'A1a: short array( 2001, 2, 3 ), position replaces key' );
$o->setConfig( 'TZID', 'Europe/ROM' );
$o->setProperty( 'dtend', array( 2001, 2, 3 ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A1b: short array( 2001, 2, 3 ), position replace key, param: array(VALUE=>DATE)' );
$o->setProperty( 'dtend'
               , array( 2001, 2, 3 )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A1c: short array( 2001, 2, 3 ), position replace key, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'dtend'
               , array( 2001, 2, 3 )
               , array( 'VALUE' => 'DATE-TIME' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A1d: short array( day=>3, month=>2, year=>1 ), array with keys' );
$o->setProperty( 'dtend', array( 'day'=>3, 'month'=>2, 'year'=>1 ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A1e: short array( day=>3, month=>2, year=>1 ), array with keys, param: array(VALUE=>DATE)' );
$o->setProperty( 'dtend'
               , array( 'day'=>3, 'month'=>2, 'year'=>1 )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A1f: short array( day=>3, month=>2, year=>1 ), array with keys, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'dtend'
               , array( 'day'=>3, 'month'=>2, 'year'=>1 )
               , array( 'VALUE' => 'DATE-TIME' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in an long array   = * = * = * = * =' );
$o->setProperty( 'comment', 'A2a: long array( 2001, 2, 3, 4, 5, 6 ), position replace key' );
$o->setProperty( 'dtend', array( 2001, 2, 3, 4, 5, 6 ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in an long array   + setConfig(TZID)' );
$o->setProperty( 'comment', 'A2a: long array( 2001, 2, 3, 4, 5, 6 ), position replace key' );
$o->setConfig( 'TZID', 'Europe/Rom' );
$o->setProperty( 'dtend', array( 2001, 2, 3, 4, 5, 6 ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2b: long array( 2001, 2, 3, 4, 5, 6), position replace key, param: array(VALUE=>DATE)' );
$o->setProperty( 'dtend'
               , array( 2001, 2, 3, 4, 5, 6 )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2c: long array( 2001, 2, 3, 4, 5, 6 ), position replace key, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'dtend', array( 2001, 2, 3, 4, 5, 6 ), array( 'VALUE' => 'DATE-TIME' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2d: long array( 2001, 2, 3, 4, 5, 6, "-0100" ),position replace key');
$o->setProperty( 'dtend', array( 2001, 2, 3, 4, 5, 6, '-0100' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2e: long array( 2001, 2, 3, 4, 5, 6, "-0100" ), position replace key, param: array(VALUE=>DATE)' );
$o->setProperty( 'dtend'
               , array( 2001, 2, 3, 4, 5, 6, '-0100' )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2f: long array( 2001, 2, 3, 4, 5, 6, "-0100" ), position replace key, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'dtend'
                , array( 2001, 2, 3, 4, 5, 6, '-0100' )
                , array( 'VALUE' => 'DATE-TIME' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2g: long array( 2001, 2, 3, 4, 5, 6, "Europe/Stockholm" ), position replace key' );
$o->setProperty( 'dtend', array( 2001, 2, 3, 4, 5, 6, 'Europe/Stockholm' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment','A2h: long array( 2001, 2, 3, 4, 5, 6, "Europe/Stockholm" ), position replace key, param: array(VALUE=>DATE)' );
$o->setProperty( 'dtend'
                , array( 2001, 2, 3, 4, 5, 6, 'Europe/Stockholm' )
                , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2i: long array( 2001, 2, 3, 4, 5, 6, "Europe/Stockholm" ), position replace key, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'dtend'
               , array( 2001, 2, 3, 4, 5, 6, 'Europe/Stockholm' )
               , array( 'VALUE' => 'DATE-TIME' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2j: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001 ), array with keys' );
$o->setProperty( 'dtend'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001 ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2k: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001 ), array with keys, param: array(VALUE=>DATE)' );
$o->setProperty( 'dtend'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001 )
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2l: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001 ), array with keys, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'dtend'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001 )
               , array( 'VALUE' => 'DATE-TIME' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2m: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=+0400 ), array with keys and timezone' );
$o->setProperty( 'dtend'
               , array( 'sec'=>6,'min'=>5,'hour'=>4,'day'=>3,'month'=>2,'year'=>2001,'tz'=>'+0400' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2o: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=+0400 ), array with keys and timezon, param: array(VALUE=>DATE)' );
$o->setProperty( 'dtend'
               , array( 'sec'=>6,'min'=>5,'hour'=>4,'day'=>3,'month'=>2,'year'=>2001,'tz'=>'+0400' )
               , array( 'VALUE'=>'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2p: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=+0400 ), array with keys and timezon, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'dtend'
               , array( 'sec'=>6,'min'=>5,'hour'=>4,'day'=>3,'month'=>2,'year'=>1,'tz'=>'+0400' )
               , array( 'VALUE'=>'DATE-TIME' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2q: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>1,tz=CEST), array with keys and timezone' );
$o->setProperty( 'dtend'
                ,array('sec'=>6,'min'=>5,'hour'=>4,'day'=>3,'month'=>2,'year'=>2001,'tz'=>'CEST'));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2r: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=Europe/Prag ), array with keys and timezon, param: array(VALUE=>DATE)' );
$o->setProperty( 'dtend'
               , array('min'=>5,'hour'=>4,'day'=>3,'month'=>2,'year'=>2001,'tz'=>'Europe/Prag' )
               , array( 'VALUE'=>'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2s: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>2001,tz=Europe/Stockholm ), array with keys and timezon, param: array(VALUE=>DATE-TIME)' );
$o->setProperty( 'dtend'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001, 'tz'=>'Europe/Stockholm' )
               , array( 'VALUE'=>'DATE-TIME' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'A2t: long array( sec=>6,min=>5,hour=>4,day=>3,month=>2,year=>1,tz=Europe/Stockholm ), array with keys and timezon, param: array(VALUE=>DATE, TZID=>Europe/Helsinki)' );
$o->setProperty( 'dtend'
               , array( 'sec'=>6, 'min'=>5, 'hour'=>4, 'day'=>3, 'month'=>2, 'year'=>2001, 'tz'=>'Europe/Stockholm' )
               , array( 'VALUE'=>'DATE', 'TZID'=>'Europe/Helsinki' ));

// test date in an array (incl. timestamp)
$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in an array with timestamp   = * = * = * = * =' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'comment', 'B1a '.$timestamp.' =now+4hours tre xparams date(offset)='.date('YmdTHisT', $timestamp).' tz='.date('P').' tz='.date('Z'));
$o->setProperty( 'comment', 'B1a '.$timestamp.' =now+4hours tre xparams date='.date('Y-m-d H:i:s', $timestamp));
$o->setProperty( 'dtend'
                , array( 'timestamp' => $timestamp )
                , array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in an array with timestamp   + setConfig(TZID) date');
$o->setProperty( 'comment', 'B1a samma datum som ovan' );
$o->setConfig( 'TZID', 'Europe/Rom' );
$o->setProperty( 'dtend'
                , array( 'timestamp' => $timestamp )
                , array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy' ));


$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'B1b '.$timestamp.' =now+4hours tre xparams date()offset='.date('YmdTHisT', $timestamp).' tz='.date('P').' tz='.date('Z'));
$o->setProperty( 'comment', 'B1b '.$timestamp.' =now+4hours value=DATE date='.date('Y-m-d H:i:s', $timestamp));
$o->setProperty( 'dtend'
                , array( 'timestamp' => $timestamp )
                , array( 'value' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'comment', 'B2a '.$timestamp.' =now+4hours tz=+0100 tre xparams' );
$o->setProperty( 'dtend'
               , array( 'timestamp' => $timestamp, 'tz' => '+0100' )
               , array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy' ) );

$o = & $c->newComponent( 'vevent' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'comment', 'B2b '.$timestamp.' =now+4hours tz=CEST tre xparams' );
$o->setProperty( 'dtend'
               , array( 'timestamp' => $timestamp, 'tz' => 'CEST' )
               , array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy' ) );

$o = & $c->newComponent( 'vevent' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'comment', 'B2c '.$timestamp.' =now+4hours tz=+0100 tre xparams+VALUE=>DATE' );
$o->setProperty( 'dtend'
               , array( 'timestamp' => $timestamp, 'tz' => '+0100' )
               , array ( 'hurrican', 'xkey' => 'xvalue', 'xxx' => 'yyy', 'VALUE' => 'DATE' ) );

$o = & $c->newComponent( 'vevent' );
$timestamp = mktime ( date('H') + 4, date('i'), date('s'), date('m'), date('d'), date('Y'));
$o->setProperty( 'comment', 'B2d '.$timestamp.' =now+4hours tz=CEST tre xparams+VALUE=>DATE-TIME' );
$o->setProperty( 'dtend'
                , array( 'timestamp' => $timestamp, 'tz' => 'CEST' )
                , array ( 'jestanes','xkey'=>'xvalue','xxx'=>'yyy','VALUE'=>'DATE-TIME' ) );

// test date in a string
$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in a string   = * = * = * = * =' );
$o->setProperty( 'comment', 'C0: 2001-02-03 04:05:06' );
$o->setProperty( 'dtend', '2001-02-03 04:05:06' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in a string   + setConfig(TZID)' );
$o->setProperty( 'comment', 'C0: 2001-02-03 04:05:06' );
$o->setConfig( 'TZID', 'Europe/Rom' );
$o->setProperty( 'dtend', '2001-02-03 04:05:06' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C1a: 2001-02-03 04:05:06 US-Eastern' );
$o->setProperty( 'dtend', '2001-02-03 04:05:06 US-Eastern' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C1b: 2001-02-03 04:05:06 US_Eastern' );
$o->setProperty( 'dtend', '2001-02-03 04:05:06 US_Eastern' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "C2a: 2001-02-03 04:05:06 US-Eastern, array( 'VALUE' => 'DATE' )" );
$o->setProperty( 'dtend'
               , '2001-02-03 04:05:06 US-Eastern'
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "C2b: 2001-02-03 04:05:06 US_Eastern, array( 'VALUE' => 'DATE' )" );
$o->setProperty( 'dtend'
               , '2001-02-03 04:05:06 US_Eastern'
               , array( 'VALUE' => 'DATE' ));

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "C3a: '2001-02-03 04:05:06 US-Eastern', array( 'VALUE'=>'DATE-TIME' )" );
$o->setProperty( 'dtend'
               , '2001-02-03 04:05:06 US-Eastern'
               , array( 'VALUE' => 'DATE-TIME' ) );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "C3b: '2001-02-03 04:05:06 US/Eastern', array( 'VALUE'=>'DATE-TIME' )" );
$o->setProperty( 'dtend'
               , '2001-02-03 04:05:06 US/Eastern'
               , array( 'VALUE' => 'DATE-TIME' ) );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C4: 2001-02-03' );
$o->setProperty( 'dtend', '2001-02-03' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C5: 20010203' );
$o->setProperty( 'dtend', '20010203' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C6: 20010203040506 UTC' );
$o->setProperty( 'dtend', '20010203040506 UTC' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C6ba: 20010203T040506' );
$o->setProperty( 'dtend', '20010203T040506' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C6bb: 20110101T080000Z' );
$o->setProperty( 'dtend', '20110101T080000Z' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C6c: 20010203T040506 Europe/Stockholm' );
$o->setProperty( 'dtend', '20010203T040506 Europe/Stockholm' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C7: 3 Feb 2001 GMT' );
$o->setProperty( 'dtend', '3 Feb 2001 GMT' );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'C8: 02/03/2001' );
$o->setProperty( 'dtend', '02/03/2001' );

// test date with dateparts in all arguments
$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in dateparts in (all) arguments = * = * = * = * =' );
$o->setProperty( 'comment', 'D1: 1, 2, 3' );
$o->setProperty( 'dtend', 1, 2, 3 );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'START, test date in dateparts in (all) arguments   + setConfig(TZID)' );
$o->setProperty( 'comment', 'D2: 2001, 2, 3, 4, 5, 6' );
$o->setConfig( 'TZID', 'Europe/Rom' );
$o->setProperty( 'dtend', 2001, 2, 3, 4, 5, 6 );

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', 'D2: 2001, 2, 3, 4, 5, 6' );
$o->setProperty( 'dtend', 2001, 2, 3, 4, 5, 6 );


$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "D3: 2006, 8, 11, 16, 30, 0, '-040000'" );
$o->setProperty( 'dtend', 2006, 8, 11, 16, 30, 0, '-040000' ); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "D4: 2006, 8, 11, 16, 30, 0, '-040000', array( 'VALUE' => 'DATE' )" );
$o->setProperty( 'dtend'
                , 2006, 8, 11, 16, 30, 0, '-040000'
                , array( 'VALUE' => 'DATE' )); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "D5: 2006, 8, 11, 16, 30, 0, '-040000', array( 'VALUE' => 'DATE-TIME' )" );
$o->setProperty( 'dtend', 2006, 8, 11, 16, 30, 0, '-040000', array( 'VALUE' => 'DATE-TIME' ) ); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "D6: 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm'" );
$o->setProperty( 'dtend', 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm' ); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "D7: 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm', array( 'VALUE' => 'DATE' )" );
$o->setProperty( 'dtend'
               , 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm'
               , array( 'VALUE' => 'DATE' )); // 11 august 2006 16.30.00 -040000

$o = & $c->newComponent( 'vevent' );
$o->setProperty( 'comment', "D8: 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm', array( 'VALUE' => 'DATE-TIME' )" );
$o->setProperty( 'dtend'
                , 2006, 8, 11, 16, 30, 0, 'Europe/Stockholm'
                , array( 'VALUE' => 'DATE-TIME' ) ); // 11 august 2006 16.30.00 -040000

// save calendar in file, get size, create new calendar, parse saved file, get sizea
$d   = 'file folder';
$f1  = 't e s t .ics';
$f2  = 't e s t 2 .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
$c = new vcalendar( array( 'unique_id' => 'test-se' ));
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