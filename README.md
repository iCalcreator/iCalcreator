# iCalcreator v2.14

iCalcreator v2.14

copyright (c) 2007-2012 Kjell-Inge Gustafsson, kigkonsult

[kigkonsult.se iCalcreator](http://kigkonsult.se/iCalcreator/index.php)

[kigkonsult.se contact](http://kigkonsult.se/contact/index.php)

iCalcreator is a _PHP_ class package managing iCal files, supporting
(non-)**calendar** systems and applications to process and communicate
**calendar** information like events, agendas, tasks, reports, totos and
journaling information.

This is a **short summary** how to use iCalcreator; create, parse, edit,
select and output functionality.

The iCalcreator package, built of a **calendar** class with support of a
function class and helper functions, are **calendar** component property
oriented. Development environment is _PHP_ version 5.x but coding is done to
meet 4.x backward compatibility and may work. Some functions requires _PHP_ >=
5.2.0.

The iCalcreator main class, utility class and helper functions are included in
the "iCalcreator.class.php" file.

More iCalcreator supplementary, usage and "howto" information will be found at
kigkonsult.se iCalcreator [coding and
test](http://kigkonsult.se/test/index.php) pages. A strong recommendation is
to have the document [user's
manual](http://kigkonsult.se/iCalcreator/docs/using.html) open in parallel
when exploiting the link.

#### iCal

A short iCal description is found at
[Wikipedia](http://en.wikipedia.org/wiki/ICalendar#Core_object). If You are
not familiar with iCal, read this first!

Knowledge of **calendar** protocol rfc5545/rfc5546 is to recommend;

[rfc5545](http://kigkonsult.se/downloads/dl.php?f=rfc5545)     Internet
Calendaring and Scheduling Core Object Specification (iCalendar)     obsoletes
[rfc2445](http://kigkonsult.se/downloads/dl.php?f=rfc2445)
[rfc5546](http://kigkonsult.se/downloads/dl.php?f=rfc5546)     iCalendar
Transport-Independent Interoperability Protocol (iTIP) Scheduling Events,
BusyTime, To-dos and Journal Entries     obsoletes
[rfc2446](http://kigkonsult.se/downloads/dl.php?f=rfc2446).

#### xCal

iCalcreator also supports xCal (iCal xml).

[rfc6321](http://kigkonsult.se/downloads/dl.php?f=rfc6321)     xCal: The XML
Format for **iCalendar**

#### SUPPORT

The main support channel is using iCalcreator
[Sourceforge](http://sourceforge.net/projects/icalcreator/forums/) forum.

kigkonsult offer services for software support, design and development of
customizations and adaptations of _PHP_/_MySQL_ solutions with a special focus
on software long term utility and reliability, supported through our agile
acquire/design/transition process model.

#### DONATE

You can show your appreciation for our free software, and can support future
development by making a donation to the kigkonsult GPL/LGPL projects.

Make a donation of any size by clicking
[here](http://kigkonsult.se/contact/index.php#Donate). Thanks in advance!

#### Contact

Use the [contact page](http://kigkonsult.se/contact/index.php) for queries,
improvement/development issues or professional support and development. Please
note that paid support or consulting service has the highest priority.

#### Downloads and usage examples

At [kigkonsult.se](http://kigkonsult.se/iCalcreator/index.php) you can
download the [complete
manual](http://kigkonsult.se/downloads/index.php#iCalcreator) and review and
explore the iCalcreator [coding and test](http://kigkonsult.se/test/index.php)
pages.

#### INSTALL

Unpack to any folder     add this folder to your include-path     or unpack to
your application-(include)-folder

Add

    
    <?php
    .. .
    require_once './iCalcreator.class.php';
    .. .
    ?>
    

to your php-script.

When creating a new calendar(/component) object instance, review config
settings.

To really boost performance, visit kigkonsult.se contact
[_page_](http://kigkonsult.se/contact/index.php) for information.

## CREATE

    
    <?php
    .. .
    require_once( "iCalcreator.class.php" );
    $config = array( "unique_id" => "kigkonsult.se" );         // set Your unique id, 
    .. .                                                       // required if any component UID is missing
    $v = new vcalendar( $config );                             // create a new calendar object instance
    $tz = "Europe/Stockholm";                                  // define time zone
    
    $v->setProperty( "method", "PUBLISH" );                    // required of some **calendar** software
    $v->setProperty( "x-wr-calname", "Calendar Sample" );      // required of some **calendar** software
    $v->setProperty( "X-WR-CALDESC", "Calendar Description" ); // required of some **calendar** software
    $v->setProperty( "X-WR-TIMEZONE", $tz );                   // required of some **calendar** software
    .. .
    $xprops = array( "X-LIC-LOCATION" => $tz );                // required of some **calendar** software
    iCalUtilityFunctions::createTimezone( $v, $tz, $xprops );  // create timezone component(-s) **opt. 1**
    .. .                                                       // based on present date
    .. .
    $vevent = & $v->newComponent( "vevent" );                  // create an event **calendar** component
    $vevent->setProperty( "dtstart", array( "year"  => 2007
                                          , "month" =>    4
                                          , "day"   =>    1
                                          , "hour"  =>   19
                                          , "min"   =>    0
                                          , "sec"   =>    0 ));
    $vevent->setProperty( "dtend",   array( "year"  => 2007
                                          , "month" =>    4
                                          , "day"   =>    1
                                          , "hour"  =>   22
                                          , "min"   =>   30
                                          , "sec"   =>    0 ));
    $vevent->setProperty( "LOCATION", "Central Placa" );       // property name - case independent
    $vevent->setProperty( "summary", "PHP summit" );
    $vevent->setProperty( "description", "This is a description" );
    $vevent->setProperty( "comment", "This is a comment" );
    $vevent->setProperty( "attendee", "attendee1@icaldomain.net" );
    .. .
    $valarm = & $vevent->newComponent( "valarm" );             // create an event alarm
    $valarm->setProperty("action", "DISPLAY" );
    $valarm->setProperty("description", $vevent->getProperty( "description" );
    .. .                                                       // reuse the event description
    $d = sprintf( '%04d%02d%02d %02d%02d%02d', 2007, 3, 31, 15, 0, 0 );
    iCalUtilityFunctions::transformDateTime( $d, $tz, "UTC", "Ymd\THis\Z");
    $valarm->setProperty( "trigger", $d );                     // create alarm trigger (in UTC datetime)
    .. .
    $vevent = & $v->newComponent( "vevent" );                  // create next event calendar component
    $vevent->setProperty( "dtstart", "20070401", array("VALUE" => "DATE"));// alt. date format,
                                                               //  now for an all-day event
    $vevent->setProperty( "organizer" , "boss@icaldomain.com" );
    $vevent->setProperty( "summary", "ALL-DAY event" );
    $vevent->setProperty( "description", "This is a description for an all-day event" );
    $vevent->setProperty( "resources", "COMPUTER PROJECTOR" );
    $vevent->setProperty( "rrule", array( "FREQ" => "WEEKLY", "count" => 4));// weekly, four occasions
    $vevent->parse( "LOCATION:1CP Conference Room 4350" );     // supporting parse of
                                                               //  strict rfc5545 formatted text
    .. .
    .. .// all calendar components are described in [rfc5545](http://kigkonsult.se/downloads/dl.php?f=rfc5545)
    .. .// a complete iCalcreator function list (ex. setProperty) in [iCalcreator manual](http://kigkonsult.se/downloads/index.php#iCalcreator)
    .. .
    iCalUtilityFunctions::createTimezone( $v, $tz, $xprops);   // create timezone component(-s) **opt. 2**
                                                               // based on all start dates in events
                                                               // (i.e. dtstart)
    .. .
    ?>
    

## PARSE

#### iCal, rfc5545 / rfc2445

##### create iCalcreator object instance

    
    <?php
    .. .
    require_once( "iCalcreator.class.php" );
    $config = array( "unique_id" => "kigkonsult.se" );         // set Your unique id, 
    .. .                                                       // required if any component UID is missing
    $v = new vcalendar( $config );                             // create a new **calendar** object instance
    .. .
    

##### when parse a local iCal file

    
    .. .
    $config = array( "directory" => "calendar", "filename" => "file.ics" );
    $v->setConfig( $config );                                  // set directory and file name
    $v->parse();
    .. .                                                       // continue process (edit, parse,select)
    .. .                                                       //  the iCalcreator object instance
    .. .
    ?>
    

##### or parse a remote iCal file (resource)

    
    .. .
    $v->setConfig( "url", "http://www.aDomain.net/file.ics" ); // supporting parse of remote files
    $v->parse();
    .. .
    $v->sort();                                                // ensure start date order (opt.)
    .. .
    .. .                                                       // continue process (edit, parse,select)
    .. .                                                       //  the iCalcreator object instance
    .. .
    ?>
    

On error, the parse method returns FALSE.

#### xCal, rfc6321 (XML)

    
    <?php
    .. .
    require_once( "iCalcreator.class.php" );
    $config = array( "unique_id" => "kigkonsult.se" );         // set Your unique id, 
    .. .                                                       // required if any component UID is missing
    .. .
    $filename = 'xmlfile.xml';                                 // use a local xCal file
    // $filename = 'http://kigkonsult.se/xcal.php?a=1&b=2&c=3';//  or a remote xCal resource
    if( FALSE === ( $v = XMLfile2iCal(  $filename, $config ))) // convert the XML resource
      exit( "Error when parsing $filename" );                  //  to an iCalcreator object instance
    .. .                                                       // continue process (edit, parse,select)
    .. .                                                       //  the iCalcreator object instance
    .. .
    ?>
    

## EDIT

    
    <?php
    .. .
    require_once( "iCalcreator.class.php" );
    $config = array( "unique_id" => "kigkonsult.se"            // set Your unique id,
                   , "directory" => "calendar"                 // import directory
                   , "filename" => "file.ics" );               //  and file name
                                                               
    $v = new vcalendar( $config );                             // create a new calendar object instance
    
    $v->parse();
    
    $v->setProperty( "method", "PUBLISH" );                    // required of some **calendar** software
    $v->setProperty( "x-wr-calname", "Calendar Sample" );      // required of some **calendar** software
    $v->setProperty( "X-WR-CALDESC", "Calendar Description" ); // required of some **calendar** software
    $v->setProperty( "X-WR-TIMEZONE", "Europe/Stockholm" );    // required of some **calendar** software
    
    while( $vevent = $v->getComponent( "vevent" )) {           // read events, one by one
      $uid = $vevent->getProperty( "uid" );                    // uid required, one occurrence
      .. .                                                     //   (unique id/key for component)
      $dtstart = $vevent->getProperty( "dtstart" );            // dtstart required, one occurrence
      .. .
      if( $description = $vevent->getProperty( "description", 1 )) { // opt. description, 1st occurrence
        .. .                                                   // edit the description
        $vevent->setProperty( "description", $description, FALSE, 1 ); // update/replace the description
      }
      while( $comment = $vevent->getProperty( "comment" )) {   // optional comments
        .. .                                                   // manage comments
      }
      .. .
      while( $vevent->deleteProperty( "attendee" ))
        continue;                                              // remove all ATTENDEE properties .. .
      .. .
      $v->setComponent ( $vevent, $uid );                      // update/replace event in calendar
                                                               //  with **UID** as key
    }
    .. .
    .. .// a complete iCalcreator function list (ex. getProperty, deleteProperty) in [iCalcreator manual](http://kigkonsult.se/downloads/index.php#iCalcreator)
    .. .
    ?>
    

  
  

## SELECT

    
    <?php
    .. .
    require_once( "iCalcreator.class.php" );
    $config = array( "unique_id" => "kigkonsult.se" );         // set Your unique id
    $v = new vcalendar( $config );                             // create a new **calendar** object instance
    
    $v->setConfig( "url", "http://www.aDomain.net/file.ics" ); // iCalcreator also support remote files
    $v->parse();
    $v->sort();                                                // ensure start date order
    
    $v->setProperty( "method", "PUBLISH" );                    // required of some **calendar** software
    $v->setProperty( "x-wr-calname", "Calendar Sample" );      // required of some **calendar** software
    $v->setProperty( "X-WR-CALDESC", "Calendar Description" ); // required of some **calendar** software
    $v->setProperty( "X-WR-TIMEZONE", "Europe/Stockholm" );    // required of some **calendar** software
    .. .
    ?>
    

#### Date based select

    
    <?php
    .. .
    $eventArray = $v->selectComponents();                      // select components occurring **today**
                                                               // (including components
                                                               // with recurrence pattern)
    foreach( $eventArray as $year => $yearArray) {
     foreach( $yearArray as $month => $monthArray ) {
      foreach( $monthArray as $day => $dailyEventsArray ) {
       foreach( $dailyEventsArray as $vevent ) {
        $currddate = $event->getProperty( "x-current-dtstart" );
                                                               // if member of a recurrence set
                                                               // (2nd occurrence etc)
                                                               // returns array( 
                                                               //     "x-current-dtstart"
                                                               //   , (string) date(
                                                               //     "Y-m-d [H:i:s][timezone/UTC offset]"))
        $dtstart = $vevent->getProperty( "dtstart" );          // dtstart required, one occurrence,
                                                               //  (orig. start date)
        $summary = $vevent->getProperty( "summary" );
        $description = $vevent->getProperty( "description" );
        .. .
        .. .
       }
      }
     }
    }
    .. .
    ?>
    

#### Select specific property values

    
    <?php
    .. .
    $valueOccur = $v->getProperty( "RESOURCES" );              // fetch specific property
                                                               // (unique) values and occurrences
                                                               // ATTENDEE, CATEGORIES, CONTACT,
                                                               // DTSTART, LOCATION, ORGANIZER,
                                                               // PRIORITY, RESOURCES, STATUS,
                                                               // SUMMARY, UID, URL,
                                                               // GEOLOCATION* 
    foreach( $valueOccur as $uniqueValue => $occurCnt ) {
      echo "The RESOURCES value <b>$uniqueValue</b> occurs <b>$occurCnt</b> times<br />";
      .. .
    }
    .. .
    ?>
    

*) Using the non-standard directive "GEOLOCATION", iCalcreator returns output supporting ISO6709 "Standard representation of geographic point location by coordinates", by combining the "LOCATION" and "GEO" property values (only if "GEO" is set). 

#### Select components based on specific property value

    
    <?php
    .. .
    $selectSpec = array( "CATEGORIES" => "course1" );
    $specComps = $v->selectComponents( $selectSpec );          // selects components
                                                               // based on specific property value(-s)
                                                               // ATTENDEE, CATEGORIES, CONTACT,
                                                               // LOCATION, ORGANIZER,
                                                               // PRIORITY, RESOURCES, STATUS,
                                                               // SUMMARY, URL, UID
    foreach( $specComps as $comp ) {
     .. .
    }
    .. .
    ?>
    

  
  

## OUTPUT

##### create iCalcreator object instance

    
    <?php
    .. .
    require_once( "iCalcreator.class.php" );
    $config = array( "unique_id" => "kigkonsult.se" );         // set Your unique id
    $v = new vcalendar( $config );                             // create a new calendar object instance
    
    $v->setProperty( "method", "PUBLISH" );                    // required of some **calendar** software
    $v->setProperty( "x-wr-calname", "Calendar Sample" );      // required of some **calendar** software
    $v->setProperty( "X-WR-CALDESC", "Calendar Description" ); // required of some **calendar** software
    $v->setProperty( "X-WR-TIMEZONE", "Europe/Stockholm" );    // required of some **calendar** software
    .. .
    .. .                                                       // continue process (edit, parse,select)
    .. .                                                       //  the iCalcreator object instance
    .. .
    ?>
    

##### opt 1

    
    <?php
    .. .
    $v->returnCalendar();                                      // redirect calendar file to browser
    ?>
    

##### opt 2

    
    <?php
    .. .
    $config = array( "directory" => "depot", "filename" => "calendar.ics" );
    $v->setConfig( $config );                                  // set output directory and file name
    $v->saveCalendar();                                        // save calendar to (local) file
    .. .
    ?>
    

##### opt 3, xCal

    
    <?php
    .. .
    $mlstr = iCal2XML( $v );                                   // create well-formed XML, rfc6321
    .. .
    ?>
    

  
  

## COPYRIGHT AND LICENSE

#### Copyright

iCalcreator v2.14

copyright (c) 2007-2012 Kjell-Inge Gustafsson, kigkonsult

[kigkonsult.se iCalcreator](http://kigkonsult.se/iCalcreator/index.php)

[kigkonsult.se contact](http://kigkonsult.se/contact/index.php)

#### License

This library is free software; you can redistribute it and/or modify it under
the terms of the GNU Lesser General Public License as published by the Free
Software Foundation; either version 2.1 of the License, or (at your option)
any later version.

This library is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
details.

You should have received a copy of the GNU Lesser General Public License along
with this library; if not, write to the Free Software Foundation, Inc., 59
Temple Place, Suite 330, Boston, MA 02111-1307 USA or download it
[here](http://kigkonsult.se/downloads/dl.php?f=LGPL).