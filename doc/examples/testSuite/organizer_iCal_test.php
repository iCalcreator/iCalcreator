<?php // organizer_iCal_text.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "1: 'jsmith@host1.com'" );
$e->setProperty( 'organizer', 'jsmith@host1.com' );

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'Comment'
               , "2: 'MAILTO:jsmith@host1.com', array( 'xparamKey' => 'xparamValue', 'yParam' )" );
$e->setProperty( 'Organizer'
               , 'MAILTO:jsmith@host1.com'
               , array( 'xparamKey' => 'xparamValue', 'yParam' ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'comment', "3: 'jsmith@host1.com', array( 'CN' => 'John Smith', 'xparamKey' => 'xparamValue', 'yParam' )" );
$e->setProperty( 'organizer'
               , 'jsmith@host1.com'
               , array( 'CN' => 'John Smith'
                      , 'xparamKey' => 'xparamValue'
                      , 'yParam' ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'comment', "4: 'jsmith@host1.com', array( 'language' => 'se', 'CN' => 'John Smith', 'SENT-BY' => ".'"MAILTO:info@host1.com"'." )" );
$e->setProperty( 'ORGANIZER'
               , 'jsmith@host1.com'
               , array( 'language' => 'se'
                      , 'CN' => 'John Smith'
                      , 'SENT-BY' => '"MAILTO:info@host1.com"' ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'comment', "5: 'jsmith@host1.com', array( 'language' => 'se', 'CN' => 'John Smith', 'DIR' => 'ldap://host.com:6666/o=3DDC%20Associates,c=3DUS??(cn=3DJohn%20Smith)', 'SENT-BY' => 'info1@host1.com', 'xparamKey' => 'xparamValue', 'yParam' )" );
$e->setProperty( 'organizer'
               , 'jsmith@host1.com'
               , array( 'language'  => 'se'
                      , 'CN'        => 'John Smith'
                      , 'DIR'       => 'ldap://host.com:6666/o=3DDC%20Associates,c=3DUS??(cn=3DJohn%20Smith)'
                      , 'SENT-BY'   => 'info1@host1.com'
                      , 'xparamKey' => 'xparamValue'
                      ,                'yParam' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "6: 'foo.bar@abc.com'");
$e->setProperty( 'organizer', 'foo.bar@abc.com');

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "7: 'organizer:foo.bar@abc.com'");
$e->parse( 'organizer:foo.bar@abc.com');

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment', "8: 'organizer;SENT-BY=info1@host1.com:httpd://www.foo.bar@abc.com'");
$e->parse( 'organizer;SENT-BY=info1@host1.com:httpd://www.foo.bar@abc.com');

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