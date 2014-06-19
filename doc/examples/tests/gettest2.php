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
$header = array( 'get components in strict physical order'
               , 'using read-next function calls' );

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
echo "<h1>$filename</h1>\n";

dispcalProp( $c );

while( $comp = $c->getComponent()) { // first/next component
  echo "<tr><td colspan='2' class='header'>from \$c->getComponent() + \$comp->getProperty(.. .) \n";
  dispProp( $comp );
}
dispFooter();
?>