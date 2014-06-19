<?php // contact_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "1a: 'Very long Contact, ABC Industries, 1234 Aveny Road, 567 890 Town City, +1-919-555-1234'" );
$e->setProperty( 'comment', "1b: 'Johnny Dolittle, Acme & Co, +12 34 56 78 90'" );
$e->setProperty( 'comment', "1c: 'John Doe, Acme Ltd, 2468 Some Avenue, AnyWhere'" );
$e->setProperty( 'contact', 'Very long Contact, ABC Industries, 1234 Aveny Road, 567 890 Town City, +1-919-555-1234' );
$e->setProperty( 'contact', 'Johnny Dolittle, Acme & Co, +12 34 56 78 90' );
$e->setProperty( 'contact', 'John Doe, Acme Ltd, 2468 Some Avenue, AnyWhere' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "2: 'Very long Contact, ABC Industries, 1234 Aveny Road, 567 890 Town City, +1-919-555-1234'".
                ", array( 'altrep' => ".
                "'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle)' )");
$e->setProperty( 'Contact'
               , 'Jim Dolittle, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle)' ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'comment', "3: 'Jim Dolittle, ABC Industries, +1-919-555-1234'".
                ", array( 'altrep' => ".
                  "'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle)' )");
$e->setProperty( 'contact'
               , 'Jim Dolittle, ABC Industries, +1-919-555-1234'
               , array( 'altrep' => 'ldap://host.com:6666/o=3DABC%20Industries, c=3DUS??(cn=3DBJim%20Dolittle)' ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'comment', "4: setLanguage( 'no' )".
                "'Jim Dolittle, ABC Industries, +1-919-555-1234'".
                ", array( 'altrep'    => 'CID=<part3.msg970930T083000SILVER@host.com>'".
                        ", 'language' => 'da'".
                        ", 'x-Key'    => 'x-Value' )");
$e->setProperty( 'contact'
               , 'Jim Dolittle, ABC Industries, +1-919-555-1234'
               , array( 'altrep'   => 'CID=<part3.msg970930T083000SILVER@host.com>'
                      , 'language' => 'da'
                      , 'x-Key'    => 'x-Value' ));

// save calendar in file, create new calendar, parse saved file
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