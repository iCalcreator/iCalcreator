<?php // parse_iCal_text.php
require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test-se' ));
$comment = <<<EOT
ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i åå>>>åå i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ää>>>ää i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i öö>>>öö i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÅÅ>>>ÅÅ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÄÄ>>>ÄÄ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÖÖ>>>ÖÖ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i üü>>>üü i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÜÜ>>>ÜÜ
EOT;
$c->setProperty( 'X-propx', "ä e $comment" );
$c->setProperty( 'X-PROPY', "str_Project xyz Review Meeting Minutes
 Agenda
1. Review of project version 1.0 requirements.
2. Definition of project processes.
3. Review of project schedule.
Participants: John Smith, Jane Doe, Jim Dandy
-It was decided that the requirements need to be signed off by product marketing.
-Project processes were accepted.
-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.
-New schedule will be distributed by Friday.
-Next weeks meeting is cancelled. No meeting until 3/23." );

$e = & $c->newComponent( 'vevent' );
$e->parse( array( 'ATTENDEE;RSVP=TRUE:MAILTO:jsmith@host1.com'
                , 'ATTENDEE;RSVP=TRUE:MAILTO:jsmith@host2.com'
                , 'ATTENDEE;RSVP=TRUE:MAILTO:jsmith@host3.com'
                , 'ATTENDEE;RSVP=TRUE:MAILTO:jsmith@host4.com' ));
$e->parse( 'DTSTAMP:19980309T231000Z' );
$e->parse( 'UID:guid-1.host1.com' );
$e->parse( 'ORGANIZER;ROLE=CHAIR:MAILTO:mrbig@host.com' );
$e->parse( 'ATTENDEE;RSVP=TRUE;ROLE=REQ-PARTICIPANT;CUTYPE=GROUP:MAILTO:employee-A@host.com' );
$e->parse( 'ATTENDEE;CUTYPE="GROUP";PARTSTAT="ORGANIZER";X-NUM-GUESTS="0";CN="Noon Duty Volunteers";MAILTO:m1kjfs4339gmg8i8h0m8qr1ioc@group.calendar.google.com' );
$e->parse( array( 'CATEGORIES:MEETING'
                , 'CLASS:PUBLIC'
                , 'CREATED:19980309T130000Z'
                , 'SUMMARY:XYZ Project Review'
                , 'DTSTART;TZID=US-Eastern:19980312T083000' ));
$e->parse( 'DTEND;TZID=US-Eastern:19980312T093000' );
$e->parse( 'LOCATION:1CP Conference Room 4350' );
$e->parse( 'STATUS:DRAFT' );
$e->parse( 'DESCRIPTION:Networld+Interop Conference '
         . 'and Exhibit Atlanta World Congress Center '
         . 'Atlanta, Georgia' );
$e->parse( array( 'BEGIN:VALARM'
                , 'TRIGGER;VALUE=DATE-TIME:19980309T080000Z'
                , 'REPEAT:4'
                , 'DURATION:PT15M'
                , 'ACTION:AUDIO'
                , 'ATTACH;FMTTYPE=audio/basic:ftp://host.com/pub/sounds/bell-01.aud'
                , 'X-alarm:non-standard alarm property'
                , 'END:VALARM' ));
// echo $e->createComponent( $x=null );
$e->parse( 'X-xomment:this non-standard property will be overwritten' );
$e->parse( 'X-xomment:this non-standard property will be displayed, with a comma escaped' );
$e->parse( "X-propx:ä e $comment" );

$e = & $c->newComponent( 'vevent' );
$e->parse( 'DTSTAMP:19970324T1200Z' );
$e->parse( 'SEQUENCE:0' );
$e->parse( 'ORGANIZER:MAILTO:jdoe@host1.com' );
$e->parse( 'DTSTART:19970324T123000Z' );
$e->parse( 'DTEND:19970324T210000Z' );
$e->parse( 'CATEGORIES:MEETING,PROJECT' );
$e->parse( 'CLASS:PUBLIC' );
$e->parse( 'SUMMARY:Calendaring Interoperability Planning Meeting' );
$e->parse( 'STATUS:DRAFT' );
$e->parse( array(
 "DESCRIPTION:array",
"Project xyz Review Meeting Minutes",
" Agenda",
"1. Review of project version 1.0 requirements.",
"2. Definition of project processes.",
"3. Review of project schedule.",
"Participants: John Smith, Jane Doe, Jim Dandy",
"-It was decided that the requirements need to be signed off by product marketing.",
"-Project processes were accepted.",
"-Project schedule needs to account for scheduled holidays",
" and employee vacation time. Check with HR for specific dates.",
"- New schedule will be distributed by Friday.",
"- Next weeks meeting is cancelled. No meeting until 3/23." ));
$e->parse( 'LOCATION:LDB Lobby' );
$e->parse( 'ATTACH;FMTTYPE=application/postscript:ftp://xyzCorp.com/pub/conf/bkgrnd.ps' );
$e->parse( array( 'BEGIN:VALARM'
                , 'ACTION:AUDIO'
                , 'TRIGGER;VALUE=DATE-TIME:19970224T070000Z'
                , 'ATTACH;FMTTYPE=audio/basic:http://host.com/pub/audio-files/ssbanner.aud'
                , 'REPEAT:4'
                , 'DURATION:PT1H'
                , 'END:VALARM' ));

$e = & $c->newComponent( 'vtodo' );
$e->parse( 'DTSTAMP:19980130T134500Z' );
$e->parse( 'SEQUENCE:2' );
$e->parse( 'UID:uid4@host1.com' );
$e->parse( 'ORGANIZER:MAILTO:unclesam@us.gov' );
$e->parse( 'ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:jqpublic@host.com' );
$e->parse( 'DUE:19980415T235959' );
$e->parse( 'STATUS:NEEDS-ACTION' );
$e->parse( 'SUMMARY:Submit Income Taxes' );
$e->parse( array(
 "DESCRIPTION:row 1 Project xyz Review Meeting Minutes",
"row 2 Agenda
 1. Review of project version 1.0 requirements.
 2. Definition of project processes.
 3. Review of project schedule.",
"row 3Participants: John Smith, Jane Doe, Jim Dandy
-It was decided that the requirements need to be signed off by product marketing.
-Project processes were accepted.
-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.
- New schedule will be distributed by Friday.
- Next weeks meeting is cancelled. No meeting until 3/23." ));
$e->parse( array(
 "COMMENT:array one row every line project xyz Review Meeting Minutes"
,"Agenda"
,"1. Review of project version 1.0 requirements."
,"2. Definition of project processes."
,"3. Review of project schedule."
,"Participants: John Smith, Jane Doe, Jim Dandy"
,"-It was decided that the requirements need to be signed off by product marketing."
,"-Project processes were accepted."
,"-Project schedule needs to account for scheduled holidays"
," and employee vacation time. Check with HR for specific dates."
,"- New schedule will be distributed by Friday."
,"- Next weeks meeting is cancelled. No meeting until 3/23." ));
$e->parse( array(
 "COMMENT:array row=bullets project xyz Review Meeting Minutes"
,"Agenda"
,"1. Review of project version 1.0 requirements."
,"2. Definition of project processes."
,"3. Review of project schedule."
,"Participants: John Smith, Jane Doe, Jim Dandy"
,"- It was decided that the requirements need to be signed off by product marketing."
,"- Project processes were accepted."
,"- Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates."
,"- New schedule will be distributed by Friday."
,"- Next weeks meeting is cancelled. No meeting until 3/23." ));

$e->setProperty('Comment', 'one string ect xyz Review Meeting Minutes
Agenda
1. Review of project version 1.0 requirements.
2. Definition of project processes.
3. Review of project schedule.
Participants: John Smith, Jane Doe, Jim Dandy
-It was decided that the requirements need to be signed off by product marketing.
-Project processes were accepted.
-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.
- New schedule will be distributed by Friday.
- Next weeks meeting is cancelled. No meeting until 3/23.' );

$c->setProperty( 'X-PROPY', 'one string  xyz Review Meeting Minutes
Agenda
1. Review of project version 1.0 requirements.
2. Definition of project processes.
3. Review of project schedule.
Participants: John Smith, Jane Doe, Jim Dandy
-It was decided that the requirements need to be signed off by product marketing.
-Project processes were accepted.
-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.
- New schedule will be distributed by Friday.
- Next weeks meeting is cancelled. No meeting until 3/23.' );

$e->parse( array( 'BEGIN:VALARM'
                , 'TRIGGER:-P3D'
                , 'REPEAT:2'
                , 'DURATION:P1D'
                , 'ACTION:DISPLAY'
                , 'DESCRIPTION:Breakfast meeting with executive'
                , ' team at 8:30 AM EST.'
                , 'END:VALARM' ));

$e = & $c->newComponent( 'vjournal' );
$e->parse( 'DTSTAMP:19970324T120000Z' );
$e->parse( 'UID:uid5@host1.com' );
$e->parse( 'ORGANIZER:MAILTO:jsmith@host.com' );
$e->parse( 'CLASS:PUBLIC' );
$e->parse( 'CATEGORIES:Project Report, XYZ, Weekly Meeting' );
$e->parse( array(
'DESCRIPTION:array line/row Project xyz Review Meeting Minutes',
' Agenda',
'1. Review of project version 1.0 requirements.',
'2.Definition of project processes.',
'3. Review of project schedule.'
,'Participants: John Smith, Jane Doe, Jim Dandy',
'-It was decided that the requirements need to be signed off by product marketing.',
'-Project processes were accepted.',
'-Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.'
,'- New schedule will be distributed by Friday.'
,'- Next weeks meeting is cancelled. No meeting until 3/23.' ));

$e = & $c->newComponent( 'vfreebusy' );
$e->parse( 'ORGANIZER:MAILTO:jsmith@host.com' );
$e->parse( 'DTSTART:19980313T141711Z' );
$e->parse( 'DTEND:19980410T141711Z' );
$e->parse( 'FREEBUSY:19980314T233000Z/19980315T003000Z' );
$e->parse( 'FREEBUSY:19980316T153000Z/19980316T163000Z' );
$e->parse( 'FREEBUSY:19980318T030000Z/19980318T040000Z' );
$e->parse( "URL:http://www.host.com/calendar/busytime/jsmith.ifb" );

// save calendar in file, get size, create new calendar, parse saved file, get size
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
// $cmd = 'diff '.$d.'/'.$f1.' '.$d.'/'.$f2;
$cmd = 'diff -b -H --side-by-side '.$d.'/'.$f1.' '.$d.'/'.$f2;
$c->saveCalendar();
$fs2 = $c->getConfig('filesize');
// $str = $c->createCalendar();
// echo $str; $a=array(); $n=chr(10); echo "$n 1 filezise=$fs1 dir/file='$df1'$n"; echo " 2 filezise=$fs2 dir/file='$df2'$n"; echo " cmd=$cmd$n"; exec($cmd, $a); echo " diff result:".implode($n,$a);

$c->returnCalendar( FALSE, TRUE );
?>