<?php // action_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->parse('ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;CN=Birthdays & Anniversaries;X-NUM-GUESTS=0:mailto:11d2leoe9n20ufi8426kd808kg@group.calendar.google.com');

$tpl = "'someone@internet.com'
                , array( 'cutype'         => 'New York'
                       , 'member'         => array( ".'"kigsf1@sf.net", kigsf2@sf.net, kigsf3@sf.net'." )
                       , 'role'           => 'CHAIR'
                       , 'PARTSTAT'       => 'ACCEPTED'
                       , 'RSVP'           => 'TRUE'
                       , 'DELEgated-to'   => array( ".'"MAILTO:kigsf1@sf.net", "kigsf2@sf.net", mailto:kigsf3@sf.net'." )
                       , 'delegateD-FROM' => array( ".'"kigsf1@sf.net"'.", 'kigsf2@sf.net', 'http://www.sf.net' )
                       , 'SENT-BY'        => 'info@kigkonsult.se'
                       , 'CN'             => 'John Doe'
                       , 'DIR'            => 'http://www.domain.net/doc/info.doc'
                       , 'LANGUAGE'       => 'us-EN'
                       , 'hejsan'         => 'hoppsan'   // xparam
                       ,                     'tjosan'    // also xparam
                  )";
while( 0 < substr_count( $tpl, '  '))
  $tpl = str_replace( "  ", " ", $tpl );

$e->setProperty( 'description', $tpl );
$e->setProperty( 'attendee'
               , 'someone@internet.com'
               , array( 'cutype'         => 'New York'
                      , 'member'         => array('kigsf1@sf.net','kigsf2@sf.net','kigsf3@sf.net' )
                      , 'role'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'TRUE'
                      , 'DELEgated-to'   => array('"MAILTO:p1@d.to"','"p2@d.to"', 'mailto:p3@t.to' )
                      , 'delegateD-FROM' => array('"p1@d.fr"', 'p2@d.fr', 'http://www.sf.net' )
                      , 'SENT-BY'        => 'info@kigkonsult.se'
                      , 'CN'             => 'John Doe'
                      , 'DIR'            => 'http://www.domain.net/doc/info.doc'
                      , 'LANGUAGE'       => 'us-EN'
                      , 'hejsan'         => 'hoppsan'  // xparam
                      ,                     'tjosan'   // also xparam
                  ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language'
              , 'no' );
$e->setProperty( 'attendee'
                ,'someone@internet.com'
                , array( 'cutype'         => 'New York'
                       , 'member'         => array( 'kigsf1@sf.net', 'kigsf2@sf.net', 'kigsf3@sf.net' )
                       , 'role'           => 'CHAIR'
                       , 'PARTSTAT'       => 'ACCEPTED'
                       , 'RSVP'           => 'TRUE'
                       , 'DELEgated-to'   => array( 'kigsf4@sf.net' )
                       , 'delegateD-FROM' => array( 'kigsf5@sf.net' )
                       , 'SENT-BY'        => 'info@kigkonsult.se'
                       , 'CN'             => 'John Doe'
                       , 'DIR'            => 'http://www.domain.net/doc/info.doc'
                       , 'hejsan'         => 'hoppsan'   // xparam
                       ,                     'tjosan'    // also xparam
                  ));

$e = & $c->newComponent( 'vevent' );
$e->setConfig ('language', 'no' );
$e->setProperty( 'attendee'
               , 'someone@internet.com'
               , array( 'cutype'         => 'Boston'
                      , 'member'         => array( 'kigs1@sf.net' )
                      , 'role'           => 'CHAIR'
                      , 'PARTSTAT'       => 'ACCEPTED'
                      , 'RSVP'           => 'TRUE'
                      , 'DELEgated-to'   => array( 'kigsf2@sf.net' )
                      , 'delegateD-FROM' => array( 'kigsf3@sf.net' )
                      , 'SENT-BY'        => 'info@kigkonsult.se'
                      , 'DIR'            => 'http://www.domain.net/doc/info.doc'
                      , 'hejsan'         => 'hoppsan'   // xparam
                      ,                     'tjosan'    // also xparam
                  ));
$e->setProperty( 'attendee', 'someone.else@internet.com' );

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'ATTENDEE'
                ,'someone@internet.com'
                , array( 'cutype'         => 'Boston'
                       , 'member'         => array( 'kigs1@sf.net' )
                       , 'role'           => 'CHAIR'
                       , 'PARTSTAT'       => 'ACCEPTED'
                       , 'RSVP'           => 'TRUE'
                       , 'CN'             => 'John Doe'
                       , 'DELEgated-to'   => array( 'kigsf2@sf.net' )
                       , 'delegateD-FROM' => array( 'kigsf3@sf.net' )
                       , 'SENT-BY'        => 'info@kigkonsult.se'
                       , 'DIR'            => 'http://www.domain.net/doc/info.doc'
                       , 'hejsan'         => 'hoppsan'   // xparam
                       ,                     'tjosan'    // also xparam
                  ));
$e->setProperty( 'attendee', 'someone.else@internet.com' );

$t = & $c->newComponent( 'vevent' );
$t->setConfig( 'language', 'no' );
$t->setProperty( 'comment', 'removal of defaults, CUTYPE=INDIVIDUAL, ROLE=REQ-PARTICIPANT, PARTSTAT=NEEDS-ACTION, RSVP=FALSE' );
$t->setProperty( 'ATTENDEE'
                ,'someone.other@internet.com'
                , array( 'cutype'         => 'INDIVIDUAL'
                       , 'role'           => 'REQ-PARTICIPANT'
                       , 'PARTSTAT'       => 'NEEDS-ACTION'
                       , 'RSVP'           => 'FALSE'
                       )
               );

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