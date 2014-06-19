<?php // gettest2.php
/**
 * iCalcreator class v2.10
 * copyright (c) 2007-2011 Kjell-Inge Gustafsson, kigkonsult
 * www.kigkonsult.se/iCalcreator/index.php
 * ical@kigkonsult.se
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
$header = array( 'components with specific uid and recurrence-id' );

require_once '../iCalcreator.class.php';
if( isset( $_REQUEST['f']))
  $filename = urldecode( $_REQUEST['f'] );
else {
  require_once 'createComponents.php';
  $filename = 'test.ics';
}
require_once 'displayProperties.php';

dispHeader( $header );
/* create new calendar, parse saved file */
$c = new vcalendar( array( 'filename' => $filename )); // filename for file to parse !!!
if( FALSE === $c->parse())
  echo "FALSE from parse function<br />\n"; // test ###
// $c->returnCalendar();

/* fix a new component with specific uid and set recurrence-id, last in chain */
$comp = $c->getComponent( '19960401T080045Z-4000F192713-0052@host1.com' );
$c = new vcalendar();
$c->setComponent( $comp );

while( FALSE !== $comp->deleteProperty( 'attendee' ))
  continue;
while( FALSE !== $comp->deleteProperty( 'comment' ))
  continue;
while( FALSE !== $comp->deleteProperty( 'X-PROP' ))
  continue;
$comp->setProperty( 'Recurrence-id'
                   , $comp->getProperty( 'dtstart' )
                   , array( 'VALUE' => 'DATE-TIME'
                          , 'RANGE' => 'THISANDFUTURE' ));
$comp->setProperty( 'X-order', '2nd component, 2nd event, created '.date('Y:m:d H.i.s').' and with recurrence-id 1' );
$c->setComponent( $comp );
$comp->setProperty( 'X-order', '2nd component, 2nd event, created '.date('Y:m:d H.i.s').' and with recurrence-id 2' );
$c->setComponent( $comp );
$c->setConfig( 'filename', $filename );

echo "<h1>$filename</h1>\n";

dispcalProp( $c );

while( $comp = $c->getComponent( array( 'uid' => '19960401T080045Z-4000F192713-0052@host1.com', 'recurrence-id' => '20110401' ))) { // first/next uid
  echo "<tr><td colspan='2' class='header'>from \$comp->getComponent( 'uid' ) + \$comp->getProperty(.. .) \n";
  dispProp( $comp );
}
dispFooter();
?>