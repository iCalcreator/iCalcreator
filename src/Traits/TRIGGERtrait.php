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

namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Util\Util;

/**
 * TRIGGER property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-05
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
     */
    public function createTrigger() {
        static $RELATEDSTART = 'relatedStart';
        static $BEFORE       = 'before';
        static $RELATED_END  = 'RELATED=END';
        if( empty( $this->trigger )) {
            return null;
        }
        if( empty( $this->trigger[Util::$LCvalue] )) {
            return ( $this->getConfig( Util::$ALLOWEMPTY )) ? Util::createElement( Util::$TRIGGER ) : null;
        }
        $content = $attributes = null;
        if( isset( $this->trigger[Util::$LCvalue][Util::$LCYEAR] ) &&
            isset( $this->trigger[Util::$LCvalue][Util::$LCMONTH] ) &&
            isset( $this->trigger[Util::$LCvalue][Util::$LCDAY] )) {
            $content .= Util::date2strdate( $this->trigger[Util::$LCvalue] );
        }
        else {
            if( true !== $this->trigger[Util::$LCvalue][$RELATEDSTART] ) {
                $attributes .= Util::$SEMIC . $RELATED_END;
            }
            if( $this->trigger[Util::$LCvalue][$BEFORE] ) {
                $content .= Util::$MINUS;
            }
            $content .= Util::duration2str( $this->trigger[Util::$LCvalue] );
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
        static $PREFIXARR    = [ 'P', '+', '-' ];
        static $P            = 'P';
        static $RELATEDSTART = 'relatedStart';
        static $BEFORE       = 'before';
        static $RELATED      = 'RELATED';
        static $END          = 'END';
        if( empty( $year ) &&
            ( empty( $month ) || \is_array( $month )) &&
            empty( $day ) && empty( $week ) && empty( $hour ) && empty( $min ) && empty( $sec )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $this->trigger = [
                    Util::$LCvalue  => Util::$EMPTYPROPERTY,
                    Util::$LCparams => Util::setParams( $month ),
                ];
                return true;
            }
            else {
                return false;
            }
        }
        if( \is_null( $relatedStart )) {
            $relatedStart = true;
        }
        if( \is_null( $before )) {
            $before = true;
        }
        switch( true ) {
            case( Util::isArrayTimestampDate( $year )) : // timestamp UTC
                $params = Util::setParams( $month );
                $date   = Util::timestamp2date( $year, 7 );
                foreach( $date as $k => $v ) {
                    $$k = $v;
                }
                break;
            case( \is_array( $year ) && ( \is_array( $month ) || empty( $month ))) :
                $params = Util::setParams( $month );
                if( ! ( \array_key_exists( Util::$LCYEAR, $year ) &&   // exclude date-time
                        \array_key_exists( Util::$LCMONTH, $year ) &&
                        \array_key_exists( Util::$LCDAY, $year ))) {  // when this must be a duration
                    if( isset( $params[$RELATED] ) && ( 0 == \strcasecmp( $END, $params[$RELATED] ))) {
                        $relatedStart = false;
                    }
                    else {
                        $relatedStart = ( \array_key_exists( $RELATEDSTART, $year ) &&
                                            ( true !== $year[$RELATEDSTART] )) ? false : true;
                    }
                    $before = ( \array_key_exists( $BEFORE, $year ) &&
                        ( true !== $year[$BEFORE] )) ? false : true;
                }
                $SSYY  = ( \array_key_exists( Util::$LCYEAR,  $year )) ? $year[Util::$LCYEAR]  : null;
                $month = ( \array_key_exists( Util::$LCMONTH, $year )) ? $year[Util::$LCMONTH] : null;
                $day   = ( \array_key_exists( Util::$LCDAY,   $year )) ? $year[Util::$LCDAY]   : null;
                $week  = ( \array_key_exists( Util::$LCWEEK,  $year )) ? $year[Util::$LCWEEK]  : null;
                $hour  = ( \array_key_exists( Util::$LCHOUR,  $year )) ? $year[Util::$LCHOUR]  : 0; //null;
                $min   = ( \array_key_exists( Util::$LCMIN,   $year )) ? $year[Util::$LCMIN]   : 0; //null;
                $sec   = ( \array_key_exists( Util::$LCSEC,   $year )) ? $year[Util::$LCSEC]   : 0; //null;
                $year  = $SSYY;
                break;
            case( \is_string( $year ) && ( \is_array( $month ) || empty( $month ))) :  // duration or date in a string
                $params = Util::setParams( $month );
                if( \in_array( $year{0}, $PREFIXARR )) { // duration
                    $relatedStart = ( isset( $params[$RELATED] ) && ( 0 == \strcasecmp( $END, $params[$RELATED] ))) ? false : true;
                    $before       = ( Util::$MINUS == $year[0] ) ? true : false;
                    if( $P != $year[0] ) {
                        $year = \substr( $year, 1 );
                    }
                    $date = Util::durationStr2arr( $year );
                }
                else   // date
                {
                    $date = Util::strDate2ArrayDate( $year, 7 );
                }
                unset( $year, $month, $day, $date[Util::$UNPARSEDTEXT] );
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
        if( ! empty( $year ) && ! empty( $month ) && ! empty( $day )) { // date
            $params[Util::$VALUE]          = Util::$DATE_TIME;
            $hour                          = ( $hour ) ? $hour : 0;
            $min                           = ( $min )  ? $min  : 0;
            $sec                           = ( $sec ) ?  $sec  : 0;
            $this->trigger                 = [ Util::$LCparams => $params ];
            $this->trigger[Util::$LCvalue] = [
                Util::$LCYEAR  => $year,
                Util::$LCMONTH => $month,
                Util::$LCDAY   => $day,
                Util::$LCHOUR  => $hour,
                Util::$LCMIN   => $min,
                Util::$LCSEC   => $sec,
                Util::$LCtz    => Util::$Z,
            ];
            return true;
        }
        elseif(( empty( $year ) && empty( $month )) &&    // duration
            (( ! empty( $week ) || ( 0 == $week )) ||
             ( ! empty( $day )  || ( 0 == $day ))  ||
             ( ! empty( $hour ) || ( 0 == $hour )) ||
             ( ! empty( $min )  || ( 0 == $min ))  ||
             ( ! empty( $sec )  || ( 0 == $sec )))) {
            unset( $params[$RELATED] );     // set at output creation (END only)
            unset( $params[Util::$VALUE] ); // Util::$DURATION default
            $this->trigger                 = [ Util::$LCparams => $params ];
            $this->trigger[Util::$LCvalue] = [];
            if( ! empty( $week )) {
                $this->trigger[Util::$LCvalue][Util::$LCWEEK] = $week;
            }
            if( ! empty( $day )) {
                $this->trigger[Util::$LCvalue][Util::$LCDAY] = $day;
            }
            if( ! empty( $hour )) {
                $this->trigger[Util::$LCvalue][Util::$LCHOUR] = $hour;
            }
            if( ! empty( $min )) {
                $this->trigger[Util::$LCvalue][Util::$LCMIN] = $min;
            }
            if( ! empty( $sec )) {
                $this->trigger[Util::$LCvalue][Util::$LCSEC] = $sec;
            }
            if( empty( $this->trigger[Util::$LCvalue] )) {
                $this->trigger[Util::$LCvalue][Util::$LCSEC] = 0;
                $before                                      = false;
            }
            else {
                $this->trigger[Util::$LCvalue] = Util::duration2arr( $this->trigger[Util::$LCvalue] );
            }
            $relatedStart = ( false !== $relatedStart ) ? true : false;
            $this->trigger[Util::$LCvalue][$RELATEDSTART] = $relatedStart;
            $before       = ( false !== $before ) ? true : false;
            $this->trigger[Util::$LCvalue][$BEFORE]       = $before;
            return true;
        }
        return false;
    }
}
