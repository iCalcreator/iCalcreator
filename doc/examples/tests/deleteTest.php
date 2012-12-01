<?php // deleteTest.php
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
$header = array( 'delete via ordno and uid' );

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
$c = new vcalendar( array( 'filename' => $filename ));
if( FALSE === $c->parse())
  echo "FALSE from parse function<br />\n"; // test ###
// $c->returnCalendar();
dispcalProp( $c );

$types = $c->getConfig( 'compsinfo'); // get type info about each component
echo "<tr><td colspan='2'><h2>\$types = \$c->getComponentsType(), display before delete</h2>\n";
foreach( $types as $component ) {
  echo "<tr><td colspan='2' class='header'>from \$types\n";
  dispType( $component );
  echo "\n<tr><td colspan='2' class='header'>from \$c->getComponent(".$component['ordno'].") + \$comp->getProperty(.. .) \n";
  $comp = $c->getComponent( $component['ordno'] ); // get component with order number
  dispProp( $comp );
}
wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>Delete 2nd component</h2>\n";
$c->deleteComponent( 2 );

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>Delete component with uid=".$types[0]['uid']." (1st component)</h2>\n";
$c->deleteComponent( $types[0]['uid'] );

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>Delete 2nd vtodo component</h2>\n";
$c->deleteComponent( 'vtodo', 2 );

$types = $c->getConfig( 'compsinfo'); // get type info about each component
echo "<tr><td colspan='2'><h2>\$types = \$c->getComponentsType(), display after delete</h2>\n";
foreach( $types as $component ) {
  echo "<tr><td colspan='2' class='header'>from \$types array, ordno:".$component['ordno']."\n";
  dispType( $component );
  echo "\n<tr><td colspan='2' class='header'>from \$c->getComponent(".$component['ordno'].") + \$comp->getProperty(.. .) \n";
  $comp = $c->getComponent( $component['ordno'] ); // get component with order number
  dispProp( $comp );
}
dispFooter();
?>