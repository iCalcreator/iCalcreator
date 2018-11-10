<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * This file is a part of iCalcreator.
 *
 * Copyright (c) 2007-2018 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      http://kigkonsult.se/iCalcreator/index.php
 * Package   iCalcreator
 * Version   2.26
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the [rfc5545] PRODID as implemented and
 *           invoked in iCalcreator shall be included in all copies or
 *           substantial portions of the iCalcreator.
 *           iCalcreator can be used either under the terms of
 *           a proprietary license, available from iCal_at_kigkonsult_dot_se
 *           or the GNU Affero General Public License, version 3:
 *           iCalcreator is free software: you can redistribute it and/or
 *           modify it under the terms of the GNU Affero General Public License
 *           as published by the Free Software Foundation, either version 3 of
 *           the License, or (at your option) any later version.
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *           GNU Affero General Public License for more details.
 *           You should have received a copy of the GNU Affero General Public
 *           License along with this program.
 *           If not, see <http://www.gnu.org/licenses/>.
 */

namespace Kigkonsult\Icalcreator\Util;

/**
 * iCalcreator geo support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class UtilGeo
{
    /**
     * @var string  GEO vars: output format for geo latitude and longitude (before rtrim) etc
     * @access public
     * @static
     */
    public static $geoLatFmt  = '%09.6f';
    public static $geoLongFmt = '%8.6f';
    public static $LATITUDE   = 'latitude';
    public static $LONGITUDE  = 'longitude';

    /**
     * Return formatted geo output
     *
     * @param float  $ll
     * @param string $format
     * @return string
     * @access public
     * @static
     */
    public static function geo2str2( $ll, $format ) {
        if( 0.0 < $ll ) {
            $sign = Util::$PLUS;
        }
        else {
            $sign = ( 0.0 > $ll ) ? Util::$MINUS : null;
        }
        return \rtrim( \rtrim( $sign . \sprintf( $format, abs( $ll )), Util::$ZERO ), Util::$DOT );
    }
}
