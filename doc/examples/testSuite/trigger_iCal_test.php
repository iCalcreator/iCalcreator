<?php /* trigger_iCal_test.php */

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));
$e = & $c->newComponent( 'vevent' );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description', '1: FALSE, FALSE, 1, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE (end, after)' );
$a->setProperty( 'trigger'
               , FALSE, FALSE, 1, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description', "array( 'day' => 1, 'relatedStart' => TRUE,  'before' => FALSE )" );
$a->setProperty( 'trigger'
               , array( 'day' => 1, 'relatedStart' => TRUE,  'before' => FALSE ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "1B: array( 'hour' => 1, 'min' => 2, 'sec' => 3, ->FALSE, ->TRUE) (end, before)" );
$a->setProperty( 'Trigger'
               ,array( 'hour' => 1, 'min' => 2, 'sec' => 3, 'relatedStart' => FALSE, 'before' => TRUE ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description', '2: FALSE, FALSE, FALSE, 4 (start, before' );
$a->setProperty( 'trigger'
               , FALSE, FALSE, FALSE, 4 );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2b: array( 'week' => 4 ), T, T (start, before), array( 'VALUE' => 'DURATION')" );
$a->setProperty( 'trigger'
               , array( 'week' => 4, 'relatedStart' => TRUE, 'before' => TRUE )
               , array( 'VALUE' => 'DURATION' ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description', "2c: array( 'day' => 4 ), FALSE (end, before)" );
$a->setProperty( 'trigger'
               , array( 'day' => 4, 'relatedStart' => FALSE, 'before' => TRUE ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2d: array( 'week' => 4 ) (start, before)" );
$a->setProperty( 'Trigger'
               , array( 'week' => 4 ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description'
               , "2e: array( 'week' => 4 ), FALSE, FALSE (end, after)" );
$a->setProperty( 'trigger'
               , array( 'week' => 4, 'relatedStart' => FALSE, 'before' => FALSE ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2f: array( 'week' => 4 ), TRUE, FALSE (start, after)" );
$a->setProperty( 'Trigger'
               , array( 'week' => 4, 'relatedStart' => TRUE, 'before' => FALSE ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description'
               , "2g: array( 'week' => 4 ), FALSE, TRUE (end, before),array('VALUE'=>'DURATION'" );
$a->setProperty( 'trigger'
               , array( 'week' => 4, 'relatedStart' => FALSE, 'before' => TRUE )
               , array('VALUE'=>'DURATION' ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2h: array( 'week' => 4 ) (start, before)" );
$a->setProperty( 'Trigger'
               , array( 'week' => 4 ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2hs1: array( 'min' => 39 ) (end, before)" );
$a->setProperty( 'Trigger'
               , array( 'min' => 39, 'relatedStart' => FALSE, 'before' => TRUE ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2hs2: array( 'sec' => 39 ) (end, before)" );
$a->setProperty( 'Trigger'
               , array( 'sec' => 39, 'relatedStart' => FALSE, 'before' => TRUE ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2i: 'P1W' (start, after)" );
$a->setProperty( 'trigger'
               , 'P1W' );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2j: '-P2D' (start, before)" );
$a->setProperty( 'trigger'
               , '-P2D' );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2k: 'PT1H2M3S', array( 'related' => 'end') (end, after)" );
$a->setProperty( 'trigger'
               , 'PT1H2M3S'
               , array( 'related' => 'end'));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2l: 'PT0S' (at the same time event starts)" );
if( FALSE === $a->setProperty( 'trigger'
               , 'PT0S' ))
  $a->setProperty( 'x-comment', 'FALSE when setting empty duration' );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "2m: array('sec'=>0) (at the same time event starts)" );
if( FALSE === $a->setProperty( 'Trigger'
               , array( 'sec' => 0 )))
  $a->setProperty( 'x-comment', 'FALSE when setting empty duration' );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description'
               , "3a: FALSE, FALSE, 5, FALSE, 1, 2, 3, FALSE, FALSE (end, after),   array('VALUE'=>'DURATION' )" );
$a->setProperty( 'trigger'
               , FALSE, FALSE, 5, FALSE, 1, 2, 3
               , FALSE, FALSE
               , array('VALUE'=>'DURATION' ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'Description'
               , "3b: array('day'=>5,'hour'=>1,'min'=>2,'sec'=>3)  (start, before" );
$a->setProperty( 'Trigger'
               , array( 'day' => 5, 'hour' => 1, 'min' => 2, 'sec' => 3 ));

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description'
               , "4b1: array( 'year'=>2007,'month'=>6,'day'=>5,'hour'=>2,'min'=>2,'sec'=>3,array( 'xparamKey' => 'xparamValue' )" );
$a->setProperty( 'trigger'
               , array( 'year'=>2007, 'month'=>6, 'day'=>5, 'hour'=>2, 'min'=>2, 'sec'=>3)
               , array( 'xparamKey' => 'xparamValue' ) );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description'
               , "4b2: array( 'year'=>2007,'month'=>6,'day'=>5),array( 'xparamKey' => 'xparamValue' )" );
$a->setProperty( 'triGGer'
               , array( 'year' => 2007, 'month' => 6, 'day' => 5 )
               , array( 'xparamKey' => 'xparamValue' ) );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description'
               , "5: '14 august 2006 16.00.00', array( 'xparamKey' => 'xparamValue' )" );
$a->setProperty( 'trigger'
               , '14 august 2006 16.00.00'
               , array( 'xparamKey' => 'xparamValue' ) );

$a = & $e->newComponent( 'valarm' );
$a->setProperty( 'description'
               , "5b: '19970224T070000Z', array( 'VALUE' => 'DATE-TIME' )" );
$a->setProperty( 'trigger'
               , '19970224T070000Z'
               , array( 'VALUE' => 'DATE-TIME' ) );

$a = & $e->newComponent( 'valarm' );

$a->setProperty( 'Description'
               , "6: '14 august 2006', array( 'xparamKey'=>'xparamValue', 'VALUE'=>'DATE-TIME' )");
$a->setProperty( 'Trigger'
               , '14 august 2006'
               , array( 'xparamKey' => 'xparamValue' ) );

$a = & $e->newComponent( 'valarm' );
$timestamp = mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
$a->setTrigger( array( 'timestamp' => $timestamp ), array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy' ) );
$a->setProperty( 'description'
               , '7a: '.$timestamp.'=now tre xparams' );

$a = & $e->newComponent( 'valarm' );
$timestamp = mktime ( date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
$a->setProperty( 'Description'
               , '7b: '.$timestamp.'=now and two xparams' );
$a->setProperty( 'triggeR'
               , array( 'timestamp' => $timestamp )
               , array ( 'jestanes', 'xkey' => 'xvalue', 'xxx' => 'yyy' ) );

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