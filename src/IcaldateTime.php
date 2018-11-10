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

namespace Kigkonsult\Icalcreator;

use DateTime;
use DateTimeZone;
use Exception;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * iCalcreator::selectComponent dateTime support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.26 - 2018-11-10
 */
class IcaldateTime extends DateTime
{

    /**
     * @var string default date[-time] format
     */
    public $dateFormat = 'Y-m-d H:i:s e';

    /**
     * @var string default object instance date[-time] 'key'
     */
    public $key = null;

    /**
     * @var array date[-time] origin
     */
    public $SCbools = [];

    /**
     * @link https://php.net/manual/en/language.oop5.cloning.php#116329
     */
    public function __clone()
    {
        $object_vars = \get_object_vars( $this );

        foreach( $object_vars as $attr_name => $attr_value ) {
            if( \is_object ($this->$attr_name )) {
                $this->$attr_name = clone $this->$attr_name;
            }
            else if( \is_array( $this->$attr_name )) {
                // Note: This copies only one dimension arrays
                foreach( $this->$attr_name as &$attr_array_value ) {
                    if( \is_object( $attr_array_value )) {
                        $attr_array_value = clone $attr_array_value;
                    }
                    unset( $attr_array_value);
                }
            }
        }
    }

    /**
     * Return time (His) array
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.20 - 2017-02-07
     * @return array
     */
    public function getTime() {
        static $H_I_S = 'H:i:s';
        return \explode( Util::$COLON, $this->format( $H_I_S ));
    }

    /**
     * Return the timezone name
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.21.7 - 2015-03-07
     * @return string
     */
    public function getTimezoneName() {
        $tz = $this->getTimezone();
        return $tz->getName();
    }

    /**
     * Return formatted date
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.21.7 - 2015-03-07
     * @param string $format
     * @return string
     */
    public function format( $format = null ) {
        if( empty( $format ) && isset( $this->dateFormat )) {
            $format = $this->dateFormat;
        }
        return parent::format( $format );
    }

    /**
     * Return IcaldateTime object instance based on date array and timezone(s)
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param array  $date
     * @param array  $params
     * @param array  $tz
     * @param string $dtstartTz
     * @return IcaldateTime
     * @static
     */
    public static function factory( array $date, $params = null, $tz = null, $dtstartTz = null ) {
        static $YMDHIS = 'YmdHis';
        static $YMD    = 'Ymd';
        static $Y_M_D  = 'Y-m-d';
        if( isset( $params[Util::$TZID] ) && ! empty( $params[Util::$TZID] )) {
            $tz = ( Util::$Z == $params[Util::$TZID] ) ? Util::$UTC : $params[Util::$TZID];
        }
        elseif( isset( $tz[Util::$LCtz] ) && ! empty( $tz[Util::$LCtz] )) {
            $tz = ( Util::$Z == $tz[Util::$LCtz] ) ? Util::$UTC : $tz[Util::$LCtz];
        }
        else {
            $tz = \date_default_timezone_get();
        }
        $strdate = Util::getYMDString( $date );
        if( isset( $date[Util::$LCHOUR] )) {
            $strdate .= Util::$T;
            $strdate .= Util::getHisString( $date );
        }
        try {
            $timezone     = new DateTimeZone( $tz );
            $iCaldateTime = new IcaldateTime( $strdate, $timezone );
        }
        catch( Exception $e ) {
            $iCaldateTime = new IcaldateTime( $strdate );
        }
        if( ! empty( $dtstartTz )) {
            if( Util::$Z == $dtstartTz ) {
                $dtstartTz = Util::$UTC;
            }
            // set the same timezone as dtstart
            if( $dtstartTz != $iCaldateTime->getTimezoneName()) {
                try {
                    $timezone = new DateTimeZone( $dtstartTz );
                    $iCaldateTime->setTimezone( $timezone );
                }
                catch( Exception $e ) {
                } // ??
            }
        }
        if( Util::isParamsValueSet( [ Util::$LCparams => $params ], Util::$DATE )) {
            $iCaldateTime->dateFormat = $Y_M_D;
            $iCaldateTime->key        = $iCaldateTime->format( $YMD );
        }
        else {
            $iCaldateTime->key = $iCaldateTime->format( $YMDHIS );
        }
        return $iCaldateTime;
    }
}
