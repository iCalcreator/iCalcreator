<?php // categories_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', '1: category1' );
$e->setProperty( 'categories', 'category1' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Comment'
               , "2: 'category1, category2'"
             .", array('hejsan' => 'tjoflojt', '1-param', '2-param', 'language' => 'en' )");
$e->setProperty( 'Categories'
               , 'category1, category2'
               , array('hejsan' => 'tjoflojt', '1-param', '2-param', 'language' => 'en' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Comment'
               , "3: array( 'category1a, category1b', 'category2' )"
             .", array('hejsan' => 'tjoflojt', 'hoppsan', 'language' => 'en' )");
$e->setProperty( 'Categories'
               , array( 'category1a, category1b', 'category2' )
               , array('hejsan' => 'tjoflojt', 'hoppsan', 'language' => 'en' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "4a: 'Ficklampa'" );
$e->setProperty( 'categories'
               , 'Ficklampa');
$e->setProperty( 'location', 'Buckingham Palace' );
$e->setProperty( 'comment', "4b: array( 'Oljekanna', 'trassel' )" );
$e->setProperty( 'categories'
               , array( 'Oljekanna', 'trassel' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment',"5: array( 'Ficklampa, hammare','skruvmejsel' )" );
$e->setProperty( 'categories',   array( 'Ficklampa, hammare','skruvmejsel' ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'Comment', "6a: 'Ficklampa'" );
$e->setProperty( 'categories'
               , 'Ficklampa');
$e->setProperty( 'comment', "6b: array( 'Oljekanna', 'trassel' )" );
$e->setProperty( 'categories'
               , array( 'Oljekanna', 'trassel' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'Comment', "7a: array( 'Oljekanna', 'trassel' ), array( 'language' => 'se', 'yParam'" );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'categories'
               , array( 'Oljekanna', 'trassel' )
               , array( 'language' => 'se'
                      , 'yParam'));
$e->setProperty( 'comment', "'7b: Ficklampa', array( 'trattgrammofon' )" );
$e->setProperty( 'categories'
               , 'Ficklampa', array( 'trattgrammofon' ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'se' );
$e->setProperty( 'categories'
               , "category3", array('hejsan2' => 'tjoflöjt2', "language" => "en" ));
$e->setProperty( 'comment'
               , '8a: "category3", array("hejsan2" => "tjoflöjt2", "language" => "en" )');
$e->setProperty( 'categories'
               , "category4, category5", array('xKey' => 'xValue'));
$e->setProperty( 'comment'
               , '8b: "category4, category5", array("xKey" => "xValue")' );

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
$fs2 = $c->getConfig('filesize');
$df2 = $c->getConfig('dirfile');
$d  = str_replace(' ', chr(92).' ', $d); // Backslash-character
$f1 = str_replace(' ', chr(92).' ', $f1);
$f2 = str_replace(' ', chr(92).' ', $f2);
$cmd = 'diff -b -H --side-by-side '.$d.'/'.$f1.' '.$d.'/'.$f2;
$c->saveCalendar();
$c->setConfig( "format", "xcal" );
$c->setConfig( "filename", "t e s t .xml" );
$fs2 = $c->getConfig('filesize');
$str = $c->createCalendar();
$str = str_replace( "<", "&lt;", $str );
$str = str_replace( ">", "&gt;", $str );
echo $str; $a=array(); $n=chr(10); echo "$n 1 filezise=$fs1 dir/file='$df1'$n"; echo " 2 filezise=$fs2 dir/file='$df2'$n"; echo " cmd=$cmd$n"; exec($cmd, $a); echo " diff result:".implode($n,$a);

// $c->returnCalendar();
?>