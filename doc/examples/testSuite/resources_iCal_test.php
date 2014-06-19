<?php // resources_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'resources'
               , array( 'recource1a, recource1b', 'recource2' )
               , array('hejsan' => 'tjoflojt', 'hoppsan', 'language' => 'en' ));
$e->setProperty( 'Comment'
               , "1: array( 'recource1a, recource1b', 'recource2' )"
             .", array('hejsan' => 'tjoflojt', 'hoppsan', 'language' => 'en' )");

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'se' );
$e->setProperty( 'resources'
               , "recource3", array('hejsan2' => 'tjoflöjt2', "language" => "en" ));
$e->setProperty( 'comment'
               , '2a: "recource3", array("hejsan2" => "tjoflöjt2", "language" => "en" )');
$e->setProperty( 'resources'
               , "recource4, recource5", array('xKey' => 'xValue'));
$e->setProperty( 'comment'
               , '2b: "recource4, recource5", array("xKey" => "xValue")' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "3a: 'Ficklampa', array( 'altrep' => 'http://www.domain.net/doc.txt')");
$e->setProperty( 'resources'
               , 'Ficklampa'
               , array( 'altrep' => 'http://www.domain.net/doc.txt' ));
$e->setProperty( 'location', 'Buckingham Palace' );
$e->setProperty( 'comment', "3b: array( 'Oljekanna', 'trassel' )" );
$e->setProperty( 'resources'
               , array( 'Oljekanna', 'trassel' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment',"4: array( 'Ficklampa, hammare','skruvmejsel' )" );
$e->setProperty( 'resources',   array( 'Ficklampa, hammare','skruvmejsel' ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'Comment', "5a: 'Ficklampa', array( 'altrep' => 'http://www.domain.net/doc.txt' )" );
$e->setProperty( 'resources'
               , 'Ficklampa'
               , array( 'altrep' => 'http://www.domain.net/doc.txt' ));
$e->setProperty( 'Location'
               , 'http://www.domain.net/doc2.txt' );
$e->setProperty( 'comment', "5b: array( 'Oljekanna', 'trassel' )" );
$e->setProperty( 'resources'
               , array( 'Oljekanna', 'trassel' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Comment', "6a: array( 'Oljekanna', 'trassel' ), array( 'language' => 'se', 'yParam', 'altrep' => 'http://www.domain.net/doc3.txt' )" );
$e->setProperty( 'resources'
               , array( 'Oljekanna', 'trassel' )
               , array( 'language' => 'se'
                      , 'yParam'
                      , 'altrep' => 'http://www.domain.net/doc3.txt' ));
$e->setConfig( 'language', 'no' );
$e->setProperty( 'comment', "6b: setConfig('language', 'no') before 6b" );
$e->setProperty( 'Location', 'Big Ben' );
$e->setProperty( 'comment', "'6b: Ficklampa', array( 'trattgrammofon' )" );
$e->setProperty( 'resources'
               , 'Ficklampa', array( 'trattgrammofon' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment',"7 same index: array( 'Ficklampa1', 'hammare1','skruvmejsel1' ), 3" );
$e->setProperty( 'resources', array( 'Ficklampa1', 'hammare1', 'skruvmejsel1' ), FALSE, 3);
$e->setProperty( 'comment',"7 same index: array( 'Ficklampa2', 'hammare2','skruvmejsel2' ), 3" );
$e->setProperty( 'resources', array( 'Ficklampa2', 'hammare2', 'skruvmejsel2' ), FALSE, 3);

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