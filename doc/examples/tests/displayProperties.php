<?php // displayProperties.php
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
$k = 1;
$version = ICALCREATOR_VERSION;
function getmicrotime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
}
function info() {
  global $time_start;
  if( !$time_start )
    $time_start = getmicrotime(true);
  else {
    $time_end   = getmicrotime(true);
    $time       = $time_end - $time_start;
  }
  echo "<tr><td colspan='2'>\n<table width='100%'>\n<tr class='r0'>";
  echo "<td width='25%'><a class='param' title='home page' href='http://www.kigkonsult.se/iCalcreator' target='_blank'>www.kigkonsult.se/iCalcreator</a>";
  if( isset( $time ) && ( 0.04 < $time ))
    echo "<td align='center' class='param'>exec&nbsp;time&nbsp;".number_format( $time, 4 ).'&nbsp;';
  echo "<td align='center'><a class='param' title='Forum / bug reports at Sourceforge.net' href='http://sourceforge.net/projects/icalcreator/' target='_blank'>Forum / bug reporting</a>";
  echo "<td width='30%' align='right'><a class='param' title='rfc2445 in HTML format' href='http://www.kigkonsult.se/iCalDictionary/index.html' target='_blank'>rfc2445</a>";
  echo "</table>\n";
}
function dispHeader( $header=FALSE ) {
  global $version;
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'."\n";
  echo "<html>\n<head>\n<title>iCalcreator $version testing edit/delete/get/select functions</title>\n";
  echo '<meta name="author"      content="Kjell-Inge Gustafsson - kigkonsult" />'."\n";
  echo '<meta name="copyright"   content="2007-2011 Kjell-Inge Gustafsson - kigkonsult" />'."\n";
  echo '<meta name="keywords"    content="ical, calendar, calender, xcal, xml, icalender, rfc2445, vcalender, php, create" />'."\n";
  echo '<meta name="description" content="iCalcreator" />'."\n";
  echo '<link href="../images/favicon.ico" rel="shortcut icon" />'."\n";
  echo '<style type="text/css">'."\n";
  echo ".label {font-family: monospace}\n";
  echo ".header {font-style: italic}\n";
  echo ".r0 {background-color: #ffffff}\n";
  echo ".r1 {background-color: #ffeeff}\n";
  echo "h1 {font-size: x-large}\n";
  echo "h2 {font-size: medium}\n";
  echo "table {border-collapse: collapse}\n";
  echo "td {vertical-align: top}\n";
  echo "tr {background-color: #dfdfdf}\n";
  echo ".param {font-family: arial; font-size: x-small}\n";
  echo "</style>\n";
  echo "</head>\n<body>\n<table>\n";
  info();
  echo "<tr class='r0'><td colspan='2'><h1>";
  if( $header ) {
    if( is_array( $header ))
      $header = implode( '<br />', $header );
    echo $header;
  }
  echo "</h1>\n";
}
function dispFooter() {
  info();
  echo "</table>\n</body>\n</html>\n";
}
function wait( $secs ) {
  echo "<tr><td colspan='2'><h2>wait $secs sec.. .";

  $temp=gettimeofday();
  $start=(int)$temp["sec"];
  while(1) {
    $temp=gettimeofday();
    $stop=(int)$temp["sec"];
    if ($stop-$start >= $secs) break;
  }
}
function dispType( $comp ) {
  GLOBAL $k;
  foreach( $comp as $key => $value ) {
    if( is_array( $value ) && ( 0 < count( $value ))) {
      $k = 1 - $k;
      echo "<tr class='r$k'><td class='label'>$key<td>";
      if( 'sub' != $key ) {
        foreach( $value as $key2 => $value2 )
          echo "$key2 => $value2<br />";
      }
      else {
        echo "\n<table>";
        foreach( $value as $key2 => $value2 )
          dispType( $value2 );
        echo "</table>\n";
      }
      echo "</td></tr>\n";
    }
    elseif( !empty( $value ))
     { $k = 1 - $k; echo "<tr class='r$k'><td class='label'>$key</td><td>$value</td></tr>\n"; }
  }
}
function dispcalProp( $comp ) {
  GLOBAL $k;
  $k = 1 - $k; echo "<tr class='r$k'><td class='label' colspan='2'>calendar properties";
/* displays all calendar properties */
  if   ( $prop = $comp->getProperty( 'version' ))
    { $k = 1 - $k; echo "<tr class='r$k'><td class='label'>version</td><td>$prop</td></tr>\n"; }
  if   ( $prop = $comp->getProperty( 'prodid' ))
    { $k = 1 - $k; echo "<tr class='r$k'><td class='label'>prodid</td><td>$prop</td></tr>\n"; }
  if   ( $prop = $comp->getProperty( 'calscale' ))
    { $k = 1 - $k; echo "<tr class='r$k'><td class='label'>calscale</td><td>$prop</td></tr>\n"; }
  if   ( $prop = $comp->getProperty( 'method' ))
    { $k = 1 - $k; echo "<tr class='r$k'><td class='label'>method</td><td>$prop</td></tr>\n"; }
  while( $prop = $comp->getProperty( FALSE, FALSE, TRUE )) dispXValue( $prop );
}
function dispProp( & $comp ) {
  require_once '../iCalUtilityFunctions.class.php';
  GLOBAL $k;
  $k = 1 - $k; echo "<tr class='r$k'><td class='label'>component type<td>".$comp->objName;
/* displays all component properties */
  if   ( $prop = $comp->getProperty( 'uid',             FALSE, TRUE )) dispValue( 'uid',          $prop );
  if   ( $prop = $comp->getProperty( 'dtstamp',         FALSE, TRUE )) dispValue( 'dtstamp',      $prop, TRUE );
  if   ( $prop = $comp->getProperty( 'dtstart',         FALSE, TRUE )) dispValue( 'dtstart',      $prop, TRUE );
  $dtstart = ( isset($prop['value'] )) ? $prop['value'] : FALSE;
  if   ( $prop = $comp->getProperty( 'dtend',           FALSE, TRUE )) dispValue( 'dtend',        $prop, TRUE );
  if   ( $prop = $comp->getProperty( 'due',             FALSE, TRUE )) dispValue( 'due',          $prop, TRUE );
  if   ( $prop = $comp->getProperty( 'duration',        FALSE, TRUE )) {
                                                                       dispValue( 'duration',     $prop, TRUE );
                                    if( FALSE !== ( $date = iCalUtilityFunctions::_duration2date( $dtstart, $prop['value'] ))) {
                                      $convprop = ( 'vtodo' != $comp->objName ) ? 'enddate' : 'due';
                                      dispValue( "duration (->$convprop)", array('value' => $date), TRUE );
                                    }
  }
  while( $prop = $comp->getProperty( 'rdate',            FALSE, TRUE )) {
    $k = 1 - $k; echo "<tr class='r$k'><td class='label'>rdate</td><td>";
    foreach( $prop['value'] as $rix => $rdatePart ) {
      if( $rix )
        echo "<br />\n";
      if( is_array( $rdatePart ) &&
        ( 2 == count( $rdatePart )) &&
          array_key_exists( '0', $rdatePart ) &&
          array_key_exists( '1', $rdatePart )) { // PERIOD
        foreach( $rdatePart[0] as $key => $value )
          echo "$key=>$value ";
        echo ' / ';
        foreach( $rdatePart[1] as $key => $value )
          echo "$key=>$value ";
      }
      else {
        foreach( $rdatePart as $key => $value )
          echo "$key=>$value ";
      }
    }
    dispParams( $prop['params'] );
    echo "</td></tr>\n";
  }
  while( $prop = $comp->getProperty( 'rrule',            FALSE, TRUE )) disprexruleValue( 'rrule', $prop );
  while( $prop = $comp->getProperty( 'exdate',           FALSE, TRUE )) {
    $k = 1 - $k; echo "<tr class='r$k'><td class='label'>exdate</td><td>";
    foreach( $prop['value'] as $key => $value ) {
      if( $key )
        echo "<br />\n";
      foreach( $value as $key2 => $value2 )
        echo "$key2=>$value2 ";
    }
    dispParams( $prop['params'] );
    echo "</td></tr>\n";
  }
  while( $prop = $comp->getProperty( 'exrule',           FALSE, TRUE )) disprexruleValue('exrule',$prop );
  if   ( $prop = $comp->getProperty( 'created',          FALSE, TRUE )) dispValue( 'created',     $prop, TRUE );
  if   ( $prop = $comp->getProperty( 'last-modified',    FALSE, TRUE )) dispValue( 'last-modified',$prop, TRUE );
  if   ( $prop = $comp->getProperty( 'summary',          FALSE, TRUE )) dispValue( 'summary',     $prop );
  while( $prop = $comp->getProperty( 'description',      FALSE, TRUE )) dispValue( 'description', $prop );
  if   ( $prop = $comp->getProperty( 'location',         FALSE, TRUE )) dispValue( 'location',    $prop );

  if   ( $prop = $comp->getProperty( 'action',           FALSE, TRUE )) dispValue( 'action',      $prop );
  while( $prop = $comp->getProperty( 'attach',           FALSE, TRUE )) dispValue( 'attach',      $prop );
  while( $prop = $comp->getProperty( 'attendee',         FALSE, TRUE )) dispValue( 'attendee',    $prop );
  while( $prop = $comp->getProperty( 'categories',       FALSE, TRUE )) dispValue( 'categories',  $prop );
  if   ( $prop = $comp->getProperty( 'class',            FALSE, TRUE )) dispValue( 'class',       $prop );
  while( $prop = $comp->getProperty( 'comment',          FALSE, TRUE )) dispValue( 'comment',     $prop );
  if   ( $prop = $comp->getProperty( 'completed',        FALSE, TRUE )) dispValue( 'completed',   $prop, TRUE );
  while( $prop = $comp->getProperty( 'contact',          FALSE, TRUE )) dispValue( 'contact',     $prop );
  while( $prop = $comp->getProperty( 'freebusy',         FALSE, TRUE )) {
    $k = 1 - $k;
    echo "<tr class='r$k'><td class='label'>freebusy</td><td>";
    echo $prop['value']['fbtype'];
    foreach( $prop['value'] as $periodix => $freebusyPeriod ) {
      if( 'fbtype' == $periodix )
        continue;
      echo "<br />\n";
      foreach( $freebusyPeriod[0] as $key => $value )
        echo "$key=>$value ";
      echo ' / ';
      foreach( $freebusyPeriod[1] as $key => $value )
        echo "$key=>$value ";
    }
    dispParams( $prop['params'] );
    echo "</td></tr>\n";
  }
  if   ( $prop = $comp->getProperty( 'geo',              FALSE, TRUE )) {
    $k = 1 - $k;
    echo "<tr class='r$k'><td class='label'>geo</td><td>";
    echo number_format( (float) $prop['value']['latitude'], 6, '.', '');
    echo ';';
    echo number_format( (float) $prop['value']['longitude'], 6, '.', '');
    dispParams( $prop['params'] );
    echo "</td></tr>\n";
  }
  if   ( $prop = $comp->getProperty( 'organizer',        FALSE, TRUE )) dispValue( 'organizer',     $prop );
  if   ( $prop = $comp->getProperty( 'percent-complete', FALSE, TRUE )) dispValue( 'percent-complete', $prop );
  if   ( $prop = $comp->getProperty( 'priority',         FALSE, TRUE )) dispValue( 'priority',      $prop );
  if   ( $prop = $comp->getProperty( 'recurrence-id',    FALSE, TRUE )) dispValue( 'recurrence-id', $prop, TRUE );
  while( $prop = $comp->getProperty( 'related-to',       FALSE, TRUE )) dispValue( 'related-to',    $prop );
  if   ( $prop = $comp->getProperty( 'repeat',           FALSE, TRUE )) dispValue( 'repeat',        $prop );
  while( $prop = $comp->getProperty( 'request-status',   FALSE, TRUE )) {
    $k = 1 - $k;
    echo "<tr class='r$k'><td class='label'>request-status</td><td>".$prop['value']['statcode'];
    echo "<br />\n".$prop['value']['text'];
    if( !empty( $prop['value']['extdata'] ))
      echo "<br />\n".$prop['value']['extdata'];
    dispParams( $prop['params'] );
    echo "</td></tr>\n";
  }
  while( $prop = $comp->getProperty( 'resources',        FALSE, TRUE )) dispValue( 'resources',     $prop );
  if   ( $prop = $comp->getProperty( 'sequence',         FALSE, TRUE )) dispValue( 'sequence',      $prop );
  if   ( $prop = $comp->getProperty( 'status',           FALSE, TRUE )) dispValue( 'status',        $prop );
  if   ( $prop = $comp->getProperty( 'transp',           FALSE, TRUE )) dispValue( 'transp',        $prop );
  if   ( $prop = $comp->getProperty( 'trigger',          FALSE, TRUE )) dispValue( 'trigger',       $prop, TRUE );
  if   ( $prop = $comp->getProperty( 'tzid',             FALSE, TRUE )) dispValue( 'tzid',          $prop );
  while( $prop = $comp->getProperty( 'tzname',           FALSE, TRUE )) dispValue( 'tzname',        $prop );
  if   ( $prop = $comp->getProperty( 'tzoffsetfrom',     FALSE, TRUE )) dispValue( 'tzoffsetfrom',  $prop );
  if   ( $prop = $comp->getProperty( 'tzoffsetto',       FALSE, TRUE )) dispValue( 'tzoffsetto',    $prop );
  if   ( $prop = $comp->getProperty( 'tzurl',            FALSE, TRUE )) dispValue( 'tzurl',         $prop );
  if   ( $prop = $comp->getProperty( 'url',              FALSE, TRUE )) dispValue( 'url',           $prop );
  while( $prop = $comp->getProperty( FALSE,              FALSE, TRUE )) dispXValue(                 $prop );
  while( $cmp2 = $comp->getComponent()) {
    echo "<tr><td colspan='2' class='header'>subComponent\n";
    dispProp( $cmp2 );
  }
}
function disprexruleValue( $propname, & $prop ) {
  GLOBAL $k;
  $k = 1 - $k; echo "<tr class='r$k'><td class='label'>$propname</td><td>";
  $nl = null;
  foreach( $prop['value'] as $key => $value ) {
    echo "$nl$key : ";
    if( empty( $nl ))
      $nl = "<br />\n";
    if( 'UNTIL' == $key ) {
      foreach( $value as $key2 => $value2 )
        echo "$key2=>$value2 ";
    }
    elseif( 'BYDAY' != $key ) {
      if( is_array( $value ))
        $value = implode( ',', $value );
      echo $value;
    }
    else { // 'BYDAY' == $key
      $bydaycnt = 0;
      $content2 = null;
      foreach( $value as $vix => $part ) {
        $content21 = $content22 = null;
        if( is_array( $part )) {
          $content2 .= ( $bydaycnt ) ? ',' : null;
          foreach( $part as $vix2 => $part2 ) {
            if( 'DAY' != strtoupper( $vix2 ))
              $content21 .= $part2;
            else
              $content22 .= $part2;
          }
          $content2 .= $content21.$content22;
          $bydaycnt++;
        }
        else {
          $content2 .= ( $bydaycnt ) ? ',' : null;
          if( 'DAY' != strtoupper( $vix ))
            $content21 .= $part;
          else {
            $content22 .= $part;
            $bydaycnt++;
          }
          $content2 .= $content21.$content22;
        }
      }
      echo $content2;
    }
  }
  dispParams( $prop['params'] );
  echo "</td></tr>\n";
}
function dispValue( $propname, $prop, $datedur=FALSE) {
  GLOBAL $k;
  $k = 1 - $k;
  echo "<tr class='r$k'><td class='label'>$propname</td><td>";
  if( is_array( $prop['value'] )) {
    if( $datedur ) {
      $str = '';
      foreach( $prop['value'] as $key => $value ) {
         if( !isset( $prop['value']['year'] )) { // duration
           if( 0 != $value )
             $str .= "$key=$value ";
           continue;
         }
         if( in_array( $key, array('hour', 'min', 'sec' ))) {
           if( isset( $prop['params']['VALUE'] ) &&  ( 'DATE' == ( $prop['params']['VALUE'] )))
             continue;
           elseif( 'hour' == $key )
             $str .= ' ';
           else
             $str .= ':';
         }
         elseif( 'tz' == $key )
           $str .= ' ';
         elseif( 'year' != $key )
           $str .= '-';
         $str .= $value;
//         echo "$key=$value ";
      }
      echo $str;
    }
    else
      echo implode( ', ', $prop['value'] );
  }
  else
    echo $prop['value'];
  if( !empty( $prop['params'] ))
    dispParams( $prop['params'] );
/*
  elseif( isset($prop['params'] )) echo "<br /><b>1. empty params propid=$propname</b>"; // test ###
  else                             echo "<br /><b>2. missing params 2 propid=$propname</b>"; // test ###
*/
  echo "</td></tr>\n";
}
function dispParams( $propParams ) {
  if( !is_array( $propParams ))
    return;
  foreach( $propParams as $key => $value ) {
    if( is_array( $value ))
      $value = implode( ', ', $value );
    echo "<br />\n<span class='param'>$key=$value</span>";
  }
}
function dispXValue( $prop ) {
  GLOBAL $k;
  $k = 1 - $k;
  if( is_array( $prop[1]['value'] ))
    $prop[1]['value']  = implode( ', ', $prop[1]['value'] );
  echo "<tr class='r$k'><td class='label'>{$prop[0]}</td><td>{$prop[1]['value']}";
  dispParams( $prop[1]['params'] );
  echo "</td></tr>\n";
}
function vtimezoneshow( & $component ) {
  GLOBAL $k;
  echo "<tr class='r$k'><td colspan='2'><hr />\n";
  $k = 1;
  echo "<tr class='r$k'><td colspan='2'><table>";
  dispProp( $component );
  echo "<tr class='r0'><td colspan='2'><hr />\n";
}
function selshow( & $selectedComponents ) {
  GLOBAL $k;
  $totcnt = $daycnt = 0;
  if( !empty( $selectedComponents )) {
    foreach( $selectedComponents as $year => $yeararr ) {
      foreach( $yeararr as $month => $montharr ) {
        foreach( $montharr as $day => $components ) {
          $weekdayWeek  = date( 'D', mktime( 0, 0, 0, $month, $day, $year )).' week(MO start)=';
          $weekdayWeek .= date( 'W', mktime( 0, 0, 0, $month, $day, $year )).' week(SU start)=';
          $weekdayWeek .= date( 'W', mktime( 0, 0, 0, $month, ( $day + 1 ), $year ));
          $daycnt = 0;
          foreach( $components as $cix => $comp ) {
            $totcnt += 1;
            $daycnt += 1;
            echo "<tr class='r$k'><td colspan='2'><hr />\n";
            $k = 1;
            echo "<tr class='r$k'><td colspan='2'><table>";
            echo "<tr><td>$year<td>-<td>$month<td>-<td>$day<td>&nbsp;$weekdayWeek</table></tr>\n";
            dispProp( $comp );
          }
          echo "<tr class='r0'><td colspan='2'><hr />\n";
          echo "<tr><td colspan='2' class='header'>daycount: $daycnt components $year-$month-$day\n";
          echo "<tr class='r0'><td colspan='2'><hr />\n";
        }
      }
    }
  }
  echo "<tr class='r0'><td colspan='2'><hr />\n";
  echo "<tr><td colspan='2' class='header'>count: $totcnt components\n";
  echo "<tr class='r0'><td colspan='2'><hr />\n";
}
function selsortshow( & $selectedComponents ) {
  $c = new vcalendar();
  if ( !empty( $selectedComponents )) {
    foreach( $selectedComponents as $year => $yeararr ) {
      foreach( $yeararr as $month => $montharr ) {
        foreach( $montharr as $day => $components ) {
          foreach( $components as $cix => $comp )
            $c->setComponent( $comp );
        }
      }
    }
  }
  $c->sort();
  $totcnt = 0;
  $types = $c->getConfig( 'compsinfo' ); // get type info about each component
  foreach( $types as $component ) {
    $totcnt += 1;
    echo "<tr><td colspan='2' class='header'>from \$types array\n";
    dispType( $component );

    echo "\n<tr><td colspan='2' class='header'>from \$c->getComponent(".$component['ordno'].") + \$comp->getProperty(.. .) \n";
    $comp = $c->getComponent( (string) $component['ordno'] ); // get component with order number
                                                            // string?, testing.. .
    dispProp( $comp );
  }
  echo "<tr class='r1'><td colspan='2'><hr />\n";
  echo "<tr><td colspan='2' class='header'>count: $totcnt components\n";
  echo "<tr class='r1'><td colspan='2'><hr />\n";
}
?>