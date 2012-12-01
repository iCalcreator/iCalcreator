<?php // gettest3.php
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
$header = array( 'get components in reverse order'
               , 'get all event Components in order'
               , 'get all vtodo Components in order'
               , 'get event Component number 2 and remove some properties' );

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
echo "<h2>$filename</h2>\n";

dispcalProp( $c );

echo "<tr><td colspan='2' ><h2>get Components in reverse order</h1>\n";
for( $cix = count( $c->getConfig( 'compsinfo' )); $cix > 0; $cix-- ) {
  $comp = $c->getComponent( $cix ); // get component with order number
  echo "<tr><td colspan='2' class='header'>from \$c->getComponent($cix) + \$comp->getProperty(.. .) \n";
  dispProp( $comp );
}

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2' ><h2>get all event Components in order</h1>\n";
while( $comp = $c->getComponent( 'vevent' )) {
  echo "<tr><td colspan='2' class='header'>from \$c->getComponent('vevent') + \$comp->getProperty(.. .) \n";
  dispProp( $comp );
}

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2' ><h2>get all vtodo Components in order</h1>\n";
while( $comp = $c->getComponent( 'vtodo' )) {
  echo "<tr><td colspan='2' class='header'>from \$c->getComponent('vtodo') + \$comp->getProperty(.. .) \n";
  dispProp( $comp );
}

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2' ><h2>get event Component number 2</h1>\n";
$comp = $c->getComponent( 'vevent', 2 );
echo "<tr><td colspan='2' ><h2>remove all attendees, 2 last comments, url and all X-prop </h1>\n";
while( TRUE === $comp->deleteProperty( 'attendee'))
  continue;
$comp->deleteProperty( 'attendee');
$comp->deleteProperty( 'comment', 2);
$comp->deleteProperty( 'comment', 3);
$comp->deleteProperty( 'url');
while( FALSE !== $comp->deleteProperty( 'x-prop' ))
  continue;
echo "<tr><td colspan='2' class='header'>from \$c->getComponent('vevent', 2) + \$comp->getProperty(.. .) \n";
echo "\n<tr><td class='label'>component type<td>".$comp->objName; // ???
dispProp( $comp );

dispFooter();
?>