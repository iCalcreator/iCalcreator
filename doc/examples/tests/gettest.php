<?php // gettest.php
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
$header = array( 'get component type info from array $types'
               , 'display components in (physical) $types order'
               , 'then sort file and display after sort' );

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

$types = $c->getConfig( 'compsinfo' ); // get type info about each component
echo "<tr><td colspan='2' class='header'>from 'compsinfo' array before sort, displayed in create order, notify DTSTART values<br />\n";
foreach( $types as $component ) {
  echo "<tr><td colspan='2' class='header'>from \$types array\n";
  dispType( $component );

  echo "\n<tr><td colspan='2' class='header'>from \$c->getComponent(".$component['ordno'].") + \$comp->getProperty(.. .) \n";
  $comp = $c->getComponent( (string) $component['ordno'] ); // get component with order number
                                                            // string?, testing.. .
  dispProp( $comp );
}

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>sort calendar file</h2>\n";
$c->sort();

$types = $c->getConfig( 'compsinfo' ); // get type info about each component
foreach( $types as $component ) {
  echo "<tr><td colspan='2' class='header'>from \$types array\n";
  dispType( $component );

  echo "\n<tr><td colspan='2' class='header'>from \$c->getComponent(".$component['ordno'].") + \$comp->getProperty(.. .) \n";
  $comp = $c->getComponent( (string) $component['ordno'] ); // get component with order number
                                                            // string?, testing.. .
  dispProp( $comp );
}
dispFooter();
?>