<?php // relatedTo_iCal_test.php

require_once '../iCalcreator.class.php';

$c = new vcalendar( array( 'unique_id' => 'test.se' ));
/* setRelatedTo( string relid [, string reltype ] )
   "PARENT" ( Default") / "CHILD" / "SIBLING / iana-token
   ; (Some other IANA registered ; iCalendar relationship type) / x-name)
   ; A non-standard, experimental
*/
$e = & $c->newComponent( 'vevent' );
$e->setProperty ( 'Comment', '1a: 1a-080045-4000F192713@host.com' );
$e->setProperty ( 'Related-To'
                , '1a-080045-4000F192713@host.com' );
$e->setProperty ( 'Comment', '1b: 1b-12345678-1234567890@ical.com' );
$e->setProperty ( 'Related-To'
                , '1b-12345678-1234567890@ical.com' );
$e->setProperty ( 'Comment', '1d: 1c-44444444-4444444444@ical.com' );
$e->setProperty ( 'Related-To'
                , '1c-44444444-4444444444@ical.com'
                , null
                , 4 );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment'
               , "2: 2-080045-4000F192713@host.com, array( 'reltype' => 'CHILD' )" );
$e->setProperty ( 'RelATed-To'
                 , '2-080045-4000F192713@host.com'
                 , array( 'reltype' => 'CHILD' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty ( 'Comment', "3: '3-080045-4000F192713@host.com', array( 'yParam' )" );
$e->setProperty ( 'Related-To'
                , '3-080045-4000F192713@host.com'
                , array( 'yParam' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment'
                ,"4: 4-080045-4000F192713@host.com, array( 'reltype' => 'SIBLING', 'yParam', 'xparamKey' => 'xparamValue' )" );
$e->setProperty( 'Related-TO'
               , '4-080045-4000F192713@host.com'
               , array( 'reltype' => 'SIBLING'
                      , 'yParam'
                      , 'xparamKey' => 'xparamValue' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment'
                ,"5: 5-x-080045-relation11-X@host.com, array( 'reltype' => 'parent') (removal default), 2 props with the same index (11), 3rd created and later deleted" );
$e->setProperty( 'Related-TO'
               , '5-1-080045-relation11-1@host.com'
               , array( 'reltype' => 'parent', 'x-param' => 'prop1' )
               , 11 );
$e->setProperty( 'Related-To'
               , '5-2-080045-relation3@host.com'
               , FALSE
               , 3);
$e->setProperty( 'Related-TO'
               , '5-3-080045-relation11-2@host.com'
               , array( 'reltype' => 'parent', 'x-param' => 'prop2' )
               , 11 );
$e->deleteProperty( 'Related-To', 3);

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