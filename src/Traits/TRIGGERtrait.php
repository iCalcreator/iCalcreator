<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.8
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\UtilDuration;
use DateTime;
use DateTimeZone;
use DateInterval;
use Exception;

use function array_key_exists;
use function checkdate;
use function in_array;
use function is_array;
use function is_null;
use function is_string;
use function strcasecmp;
use function substr;

/**
 * TRIGGER property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26.8 - 2018-12-12
 */
trait TRIGGERtrait
{
    /**
     * @var array component property TRIGGER value
     * @access protected
     */
    protected $trigger = null;

    /**
     * Return formatted output for calendar component property trigger
     *
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-02
     */
    public function createTrigger() {
        static $RELATED_END  = 'RELATED=END';
        if( empty( $this->trigger )) {
            return null;
        }
        if( empty( $this->trigger[Util::$LCvalue] )) {
            return ( $this->getConfig( Util::$ALLOWEMPTY )) ? Util::createElement( Util::$TRIGGER ) : null;
        }
        if( isset( $this->trigger[Util::$LCvalue]['invert'] )) { // fix pre 7.0.5 bug
            $dateInterval = UtilDuration::DateIntervalArr2DateInterval( $this->trigger[Util::$LCvalue] );
            return Util::createElement(
                Util::$TRIGGER,
                Util::createParams( $this->trigger[Util::$LCparams] ),
                UtilDuration::dateInterval2String( $dateInterval, true )
            );
        }
        $content = $attributes = null;
        if( isset( $this->trigger[Util::$LCvalue][Util::$LCYEAR] ) &&
            isset( $this->trigger[Util::$LCvalue][Util::$LCMONTH] ) &&
            isset( $this->trigger[Util::$LCvalue][Util::$LCDAY] )) {
            $content .= Util::date2strdate( $this->trigger[Util::$LCvalue] );
        }
        else {
            if( true !== $this->trigger[Util::$LCvalue][UtilDuration::$RELATEDSTART] ) {
                $attributes .= Util::$SEMIC . $RELATED_END;
            }
            if( $this->trigger[Util::$LCvalue][UtilDuration::$BEFORE] ) {
                $content .= Util::$MINUS;
            }
            $content .= UtilDuration::duration2str( $this->trigger[Util::$LCvalue] );
        }
        $attributes .= Util::createParams( $this->trigger[Util::$LCparams] );
        return Util::createElement( Util::$TRIGGER, $attributes, $content );
    }

    /**
     * Set calendar component property trigger
     *
     * @param mixed $year
     * @param mixed $month
     * @param int   $day
     * @param int   $week
     * @param int   $hour
     * @param int   $min
     * @param int   $sec
     * @param bool  $relatedStart
     * @param bool  $before
     * @param array $params
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-01
     */
    public function setTrigger(
        $year         = null,
        $month        = null,
        $day          = null,
        $week         = null,
        $hour         = null,
        $min          = null,
        $sec          = null,
        $relatedStart = null,
        $before       = null,
        $params       = null
    ) {
        if( empty( $year ) &&
            ( empty( $month ) || is_array( $month )) &&
            empty( $day ) && empty( $week ) && empty( $hour ) && empty( $min ) && empty( $sec )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $this->trigger = [
                    Util::$LCvalue  => Util::$SP0,
                    Util::$LCparams => Util::setParams( $month ),
                ];
                return true;
            }
            else {
                return false;
            }
        }
        if( is_null( $relatedStart )) {
            $relatedStart = true;
        }
        if( is_null( $before )) {
            $before = true;
        }
        $isArrayDate = $isArrayDuration = $isStringDate = $isStringDuration = false;
        if( is_array( $year ) && ( is_array( $month ) || empty( $month ))) {
            $params = Util::setParams( $month );
            if( array_key_exists( Util::$LCYEAR,  $year ) &&   // identify date(-time)
                array_key_exists( Util::$LCMONTH, $year ) &&
                array_key_exists( Util::$LCDAY,   $year ) &&
              ! array_key_exists( Util::$LCWEEK,  $year ) &&
                isset( $params[Util::$VALUE] ) && ( Util::$DATE_TIME == $params[Util::$VALUE] )) {
                $isArrayDate = true;
            }
            else {
                $isArrayDuration = true;
            }
        }
        elseif( is_string( $year ) && ! empty( $year ) && ( is_array( $month ) || empty( $month ))) {
            $year   = trim( $year );
            $params = Util::setParams( $month );
            if( in_array( $year[0], UtilDuration::$PREFIXARR ) &&
                ( ! isset( $params[Util::$VALUE] ) || ( Util::$DATE_TIME != $params[Util::$VALUE] ))) {
                $isStringDuration = true;
            }
            else {
                $isStringDate = true;
            }
        }
        switch( true ) {
            case ( $year instanceof DateInterval ) :
                try {
                    $dateInterval = UtilDuration::conformDateInterval( $year );
                }
                catch( Exception $e ) {
                    return false; // todo
                }
                $params = Util::setParams( $month );
                if( true != self::isDurationRelatedEnd( $params )) {
                    unset( $params[UtilDuration::$RELATED] ); // remove default
                }
                unset( $params[Util::$VALUE] ); // remove default
                $this->trigger[Util::$LCvalue]  = (array) $dateInterval;  // fix pre 7.0.5 bug
                $this->trigger[Util::$LCparams] = $params;
                return true;
                break;
            case ( $year instanceof DateTime ) :
                $params = Util::setParams( $month );
                $params[Util::$VALUE] = Util::$DATE_TIME;
                try {
                    $year->setTimezone( new DateTimeZone( Util::$UTC ) );
                }
                catch( Exception $e ) {
                    return false; // todo
                }
                $date   = Util::dateTime2Str( $year );
                Util::strDate2arr( $date );
                foreach( (array) $date as $k => $v ) { // populate all method args
                    $$k = $v;
                }
                unset( $week );
                break;
            case( $isArrayDate ) :  // populate all method args
                $params = Util::setParams( $month );
                $SSYY  = ( array_key_exists( Util::$LCYEAR,  $year )) ? $year[Util::$LCYEAR]  : null;
                $month = ( array_key_exists( Util::$LCMONTH, $year )) ? $year[Util::$LCMONTH] : null;
                $day   = ( array_key_exists( Util::$LCDAY,   $year )) ? $year[Util::$LCDAY]   : null;
                $week  = ( array_key_exists( Util::$LCWEEK,  $year )) ? $year[Util::$LCWEEK]  : null;
                $hour  = ( array_key_exists( Util::$LCHOUR,  $year )) ? $year[Util::$LCHOUR]  : 0; //null;
                $min   = ( array_key_exists( Util::$LCMIN,   $year )) ? $year[Util::$LCMIN]   : 0; //null;
                $sec   = ( array_key_exists( Util::$LCSEC,   $year )) ? $year[Util::$LCSEC]   : 0; //null;
                $year  = $SSYY;
                break;
            case( Util::isArrayTimestampDate( $year )) : // timestamp UTC
                $params = Util::setParams( $month );
                $params[Util::$VALUE] = Util::$DATE_TIME;
                $date   = Util::timestamp2date( $year, 7 );
                foreach( $date as $k => $v ) { // populate all method args
                    $$k = $v;
                }
                unset( $week );
                break;
            case ( $isArrayDuration ) :
                $before = false;
                if( array_key_exists( UtilDuration::$BEFORE, $year ) &&
                    ( false !== $year[UtilDuration::$BEFORE] )) {
                    $before = true;
                }
                try {
                    $dateInterval1 = new DateInterval(
                        $durationString = UtilDuration::duration2str(
                            UtilDuration::duration2arr( $year )
                        )
                    );
                    $dateInterval1->invert = ( $before ) ? 1 : 0;
                    $dateInterval = UtilDuration::conformDateInterval( $dateInterval1 );
                }
                catch( Exception $e ) {
                    return false; // todo
                }
                $params = Util::setParams( $month );
                if( true != ( $relatedStart = self::isDurationRelatedEnd( $params ))) {
                    $relatedStart = ( ! array_key_exists( UtilDuration::$RELATEDSTART, $year ) ||
                        ( false !== $year[UtilDuration::$RELATEDSTART] ));
                }
                if( $relatedStart ) {
                    unset( $params[UtilDuration::$RELATED] ); // remove default
                }
                else {
                    $params[UtilDuration::$RELATED] = UtilDuration::$END;
                }
                unset( $params[Util::$VALUE] ); // remove default
                $this->trigger[Util::$LCvalue]  = (array) $dateInterval; // fix pre 7.0.5 bug
                $this->trigger[Util::$LCparams] = $params;
                return true;
                break;
            case( $isStringDuration ) : // duration in a string
                $before = ( Util::$MINUS == $year[0] ) ? true : false;
                if( UtilDuration::$P != $year[0] ) {
                    $year = substr( $year, 1 );
                }
                try {
                    $dateInterval1 = new DateInterval( $year );
                    $dateInterval1->invert = ( $before ) ? 1 : 0;
                    $dateInterval = UtilDuration::conformDateInterval( $dateInterval1 );
                }
                catch( Exception $e ) {
                    return false; // todo
                }
                $params = Util::setParams( $month );
                if( true != self::isDurationRelatedEnd( $params )) {
                    unset( $params[UtilDuration::$RELATED] ); // remove default
                }
                unset( $params[Util::$VALUE] ); // remove default
                $this->trigger = [
                    Util::$LCvalue  => (array) $dateInterval, // fix pre 7.0.5 bug
                    Util::$LCparams => $params
                ];
                return true;
                break;
            case( $isStringDate ) :
                $params = Util::setParams( $month );
                $date = Util::strDate2ArrayDate( $year, 7 ); // date in a string
                unset( $year, $month, $week, $day, $date[Util::$UNPARSEDTEXT] );
                if( empty( $date )) {
                    $sec = 0;
                }
                else {
                    foreach( $date as $k => $v ) {
                        $$k = $v;
                    }
                }
                break;
            default : // single values in function input parameters
                $params = Util::setParams( $params );
                break;
        } // end switch( true )
        if( isset( $params[Util::$VALUE] ) && ( Util::$DATE_TIME == $params[Util::$VALUE] ) &&
            ! empty( $year ) && ! empty( $month ) && ! empty( $day )) { // && empty( $week )) { // date !
            if( ! checkdate( $month, $day, $year )) {
                return false; // todo
            }
            $params[Util::$VALUE]          = Util::$DATE_TIME;
            $hour                          = ( $hour ) ? $hour : 0;
            $min                           = ( $min )  ? $min  : 0;
            $sec                           = ( $sec )  ? $sec  : 0;
            $this->trigger                 = [ Util::$LCparams => $params ];
            $this->trigger[Util::$LCvalue] = [
                Util::$LCYEAR  => (int) $year,
                Util::$LCMONTH => (int) $month,
                Util::$LCDAY   => (int) $day,
                Util::$LCHOUR  => (int) $hour,
                Util::$LCMIN   => (int) $min,
                Util::$LCSEC   => (int) $sec,
                Util::$LCtz    => Util::$Z,
            ];
            return true;
        }
        else { // if( ! empty( $week ) || ( 0 == $week )) { // duration
            if( $relatedStart && ! self::isDurationRelatedEnd( $params )) {
                unset( $params[UtilDuration::$RELATED] ); // remove default
            }
            else {
                $params[UtilDuration::$RELATED] = UtilDuration::$END;
            }
            unset( $params[Util::$VALUE] );   // Util::$DURATION default
            if( ! isset( $week )) {
                $week = 0;
            }
            $duration      = self::variables2Array(
                $year, $month, $day, $week, $hour, $min, $sec
            );
            try {
                $dateInterval1 = new DateInterval(
                    $durationString = UtilDuration::duration2str(
                        UtilDuration::duration2arr( $duration )
                    )
                );
                if( $before ) {
                    $dateInterval1->invert = 1;
                }
                $dateInterval = UtilDuration::conformDateInterval( $dateInterval1 );
            }
            catch( Exception $e ) {
                return false; // todo
            }
            $this->trigger[Util::$LCvalue]  = (array) $dateInterval; // fix pre 7.0.5 bug
            $this->trigger[Util::$LCparams] = $params;
        }
        return true;
    }

    /**
     * Return array of the argument variables
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $week
     * @param int $hour
     * @param int $min
     * @param int $sec
     * @return array
     * @access private
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-30
     */
    private static function variables2Array(
        $year  = null,
        $month = null,
        $day   = null,
        $week  = null,
        $hour  = null,
        $min   = null,
        $sec   = null
    ) {
        $result = [ Util::$LCSEC => 0 ];
        if( ! empty( $year )) {
            $result[Util::$LCWEEK] = $year;
        }
        if( ! empty( $month )) {
            $result[Util::$LCWEEK] = $month;
        }
        if( ! empty( $week )) {
            $result[Util::$LCWEEK] = $week;
        }
        if( ! empty( $day )) {
            $result[Util::$LCDAY]  = $day;
        }
        if( ! empty( $hour )) {
            $result[Util::$LCHOUR] = $hour;
        }
        if( ! empty( $min )) {
            $result[Util::$LCMIN]  = $min;
        }
        if( ! empty( $sec )) {
            $result[Util::$LCSEC]  = $sec;
        }
        return $result;
    }

    /**
     * Return bool true if duration i9s related END
     *
     * @param null|array $params
     * @return bool
     * @access private
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-01
     */
    private static function isDurationRelatedEnd( $params ) {
        return ( is_array( $params ) &&
            isset( $params[UtilDuration::$RELATED] ) &&
            ( 0 == strcasecmp( UtilDuration::$END, $params[UtilDuration::$RELATED] )));
    }
}
