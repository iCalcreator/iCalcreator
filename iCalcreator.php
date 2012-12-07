<?php
/*********************************************************************************/
/**
 * iCalcreator v2.16
 * copyright (c) 2007-2012 Kjell-Inge Gustafsson kigkonsult
 * kigkonsult.se/iCalcreator/index.php
 * ical@kigkonsult.se
 *
 * Description:
 * This file is a PHP implementation of rfc2445/rfc5545.
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
/*********************************************************************************/
/*********************************************************************************/
/*         A little setup                                                        */
/*********************************************************************************/
            /* your local language code */
// define( 'ICAL_LANG', 'sv' );
            // alt. autosetting
/*
$langstr     = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$pos         = strpos( $langstr, ';' );
if ($pos   !== false) {
  $langstr   = substr( $langstr, 0, $pos );
  $pos       = strpos( $langstr, ',' );
  if ($pos !== false) {
    $pos     = strpos( $langstr, ',' );
    $langstr = substr( $langstr, 0, $pos );
  }
  define( 'ICAL_LANG', $langstr );
}
*/
/*********************************************************************************/
/*         version, do NOT remove!!                                              */
define( 'ICALCREATOR_VERSION', 'iCalcreator 2.16' );

include_once('iCalCreator/VCalendar/vcalendar.class.php');
include_once('iCalCreator/Component/calendarComponent.class.php');
include_once('iCalCreator/Component/vevent.class.php');
include_once('iCalCreator/Component/vtodo.class.php');
include_once('iCalCreator/Component/vjournal.class.php');
include_once('iCalCreator/Component/vfreebusy.class.php');
include_once('iCalCreator/Component/valarm.class.php');
include_once('iCalCreator/Component/vtimezone.class.php');
include_once('iCalCreator/iCalUtilityfunctions.class.php');
include_once('iCalCreator/vcardHelper.php');
include_once('iCalCreator/xmlHelper.php');
include_once('iCalCreator/vtimezoneHelper.php');

?>
