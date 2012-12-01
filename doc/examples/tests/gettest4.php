<?php // gettest4.php
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
$header = array( 'get component type info in array $types'
               , 'get components in random order with uid as key' );

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
dispcalProp( $c );

$types = $c->getConfig( 'compsinfo' ); // get type info about each component
$outputcnt = array();
while( count( $types ) > count( $outputcnt )) {
  $cix = mt_rand (0, count( $types ) - 1);
  if( isset( $outputcnt[$cix] ))
    continue;
  $outputcnt[$cix] = $cix;
  echo "<tr><td colspan='2'><h2>Component number ".($cix + 1)."</h1>";
  echo "<tr><td colspan='2' class='header'>from \$c->getComponentsType()\n";
  dispType( $types[$cix] );

  if( !empty( $types[$cix]['uid'] )) {
    $comp = $c->getComponent( $types[$cix]['uid'] ); // get component with uid as key
    echo "<tr><td colspan='2' class='header'>from \$c->getComponent('".$types[$cix]['uid']."') + \$comp->getProperty(.. .) \n";
    dispProp( $comp );
  }
  else
    echo "<tr><td colspan='2' class='header'>Component alarm/timezone (+standard/daylight) has no UID<br />&nbsp;\n";
}
dispFooter();
?>