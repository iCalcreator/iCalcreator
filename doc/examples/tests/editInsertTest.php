<?php // editInsertest.php
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
$header = array( 'update via ordno and uid'
               , 'insert new component (replacement for old function appendCalendar)');

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
if( FALSE === $c->parse()) // filename for file to parse !!!
  echo "FALSE from parse function<br />\n"; // test ###
// $c->returnCalendar();
dispcalProp( $c );

$types = $c->getConfig( 'compsinfo'); // get type info about each component
echo "<tr><td colspan='2'><h2>\$types = \$c->getComponentsType(), display all before update</h2>\n";
foreach( $types as $component ) {
  echo "<tr><td colspan='2' class='header'>from \$types array\n";
  dispType( $component );
  echo "<tr><td colspan='2' class='header'>Component\n";
  $comp = $c->getComponent( $component['ordno'] ); // get component with order number
  dispProp( $comp );
}
/**/
wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>Update and save component ordno 1, 2 subcomponent</h2>\n";
$o  = $c->getComponent( 1 ); // fetch 1st component
$subOrdno = 2;
if( $a1   = $o->getComponent( $subOrdno )) { // fetch $subOrdno subcomponent, if it exists
  $action = $a1->getProperty( 'action' );
  $attach = $a1->getProperty( 'attach', false, true ); // fetch both value and params
}
else {
  $subOrdno = null; // make sure the new alarm is enserted last (not executed)
  $action = 'AUDIO';
  $attach = array( 'value' => 'ftp://host.com/pub/sounds/bell-02.aud'
                 , 'params' => array( 'FMTTYPE' => 'audio/basic' ));
}
$a12 = new valarm(); // create new alarm and replace
$a12->setProperty( 'action', $action );
$a12->setProperty( 'attach', $attach['value'], $attach['params'] );
$a12->setProperty( 'DURATION', 'PT2H' );
$a12->setProperty( 'REPEAT', 2 );
$a12->setProperty( 'trigger', '-PT22M' );
$a12->setProperty( 'X-new-1', "new $subOrdno alarm subcomponent, created ".date('Y:m:d H.i.s') );
$a12->setProperty( 'X-new-2', "replaces alarm component no 2" );
$o->setComponent( $a12, $subOrdno ); // replace/insert subcomponent
/**/
wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>Create and insert new alarm subcomponent</h2>\n";
$subOrdno = 3;
if( $a1   = $o->getComponent( $subOrdno )) { // fetch $subOrdno subcomponent, if it exists (it don't exist !!!)
  $action = $a1->getProperty( 'action' );
  $attach = $a1->getProperty( 'attach', false, true ); // fetch both value and params
}
else {
  $subOrdno = null; // make sure the new alarm is enserted last
  $action = 'AUDIO';
  $attach = array( 'value' => 'ftp://host.com/pub/sounds/bell-03.aud'
                 , 'params' => array( 'FMTTYPE' => 'audio/basic' ));
}
$a13 = new valarm(); // create new alarm and replace
$a13->setProperty( 'action', $action );
$a13->setProperty( 'attach', $attach['value'], $attach['params'] );
$a13->setProperty( 'DURATION', 'PT2H' );
$a13->setProperty( 'REPEAT', 2 );
$a13->setProperty( 'trigger', '-PT22M' );
$a13->setProperty( 'X-new-3', "new $subOrdno alarm subcomponent, created ".date('Y:m:d H.i.s') );
$o->setComponent( $a13, $subOrdno ); // replace/insert subcomponent

$o->setProperty( 'X-update'
               , 'This is a new property, inserted '.date('Y:m:d H.i.s') );
$c->setComponent( $o, 1  );

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>Update and save component ordno 5</h2>\n";
$o = $c->getComponent( 5 );
$o->setProperty( 'comment'
               , 'This is an update made '.date('Y:m:d H.i.s') );
$o->setProperty( 'last-modified' );
$c->setComponent( $o, 5 );

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>Update and save 2nd vevent component</h2>\n";
$o = $c->getComponent( 'vevent', 2 );
$o->setProperty( 'comment'
               , 'This is an update made '.date('Y:m:d H.i.s') );
$c->setComponent( $o, 'vevent', 2 );

wait( 1 ); // wait 1 second
$ordno = 6;
$uid = $types[$ordno]['uid']; // get some UID
$ordno++;
echo "<tr><td colspan='2'><h2>Replace component with uid=$uid (actually ordno $ordno), but keep the old uid</h2>\n";
$o = new vevent();
$o->setProperty( 'CLASS', 'CONFIDENTIAL', array( 'xparam1', 'xparamKey' => 'xparamValue' ));
$o->setProperty( 'comment'
               , 'This is a new vevent component, created '.date('Y:m:d H.i.s') );
$o->setProperty( 'dtstart'
               , '2011-11-11 11:11:11' );
$o->setProperty( 'duration'
               , 0, 0, 11 );
$o->setProperty( 'geo', 11.1111, -22.2222 );
$o->setProperty( 'priority'
               , 1
               , array( 'priority' => 'HIGH', 'Important' ));
$o->setProperty( 'uid', $uid );
$o->setProperty( 'X-uid', 'keep the UID from the old component' );
$c->setComponent( $o, $uid );

wait( 1 ); // wait 1 second
echo "<tr><td colspan='2'><h2>Add new component (used to replace old function appendCalendar)</h2>\n";
$o = new vevent();
$o->setProperty( 'comment'
               , 'This is a new vevent component, created '.date('Y:m:d H.i.s').' inserted last in chain' );
$o->setProperty( 'dtstart'
               , '2012-12-12 12:12:12' );
$o->setProperty( 'duration'
               , 0, 0, 22 );
$o->setProperty( 'geo', 12.1212, -24.2424 );
$c->setComponent( $o );

$types = $c->getConfig( 'compsinfo'); // get type info about each component
echo "<tr><td colspan='2'><h2>\$types = \$c->getComponentsType(), display all after delete, in order</h2>\n";
foreach( $types as $comptype ) {
  echo "<tr><td colspan='2' class='header'>from \$types array\n";
  dispType( $comptype );
  echo "<tr><td colspan='2' class='header'>Component\n";
  $comp = $c->getComponent( $comptype['ordno'] ); // get component with order number
  dispProp( $comp );
}
dispFooter();
?>