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

namespace Kigkonsult\Icalcreator\Util;

use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\CalendarComponent;
use DateInterval;

use function array_slice;
use function is_null;
use function key;
use function reset;
use function strcmp;

/**
 * iCalcreator VcalendarSortHandler class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26.7 - 2018-12-02
 */
class VcalendarSortHandler
{
    /**
     * Vcalendar sort callback function
     *
     * @since 2.26 - 2018-11-10
     * @param CalendarComponent $a
     * @param CalendarComponent $b
     * @return int
     * @static
     */
    public static function cmpfcn(
        CalendarComponent $a,
        CalendarComponent $b
    ) {
        if( empty( $a )) {
            return -1;
        }
        if( empty( $b )) {
            return 1;
        }
        if( Vcalendar::VTIMEZONE == $a->compType ) {
            if( Vcalendar::VTIMEZONE != $b->compType ) {
                return -1;
            }
            elseif( $a->srtk[0] <= $b->srtk[0] ) {
                return -1;
            }
            else {
                return 1;
            }
        }
        elseif( Vcalendar::VTIMEZONE == $b->compType ) {
            return 1;
        }
        for( $k = 0; $k < 4; $k++ ) {
            if( empty( $a->srtk[$k] )) {
                return -1;
            }
            elseif( empty( $b->srtk[$k] )) {
                return 1;
            }
            $sortStat = strcmp( $a->srtk[$k], $b->srtk[$k] );
            if( 0 == $sortStat ) {
                continue;
            }
            return ( 0 < $sortStat ) ? 1 : -1;
        }
        return 0;
    }

    /**
     * Set sort arguments/parameters in component
     *
     * @since 2.26.3 - 2018-11-15
     * @param CalendarComponent $c valendar component
     * @param string            $sortArg
     * @static
     */
    public static function setSortArgs(
        CalendarComponent $c,
        $sortArg = null
    ) {
        static $INITARR = [ '0', '0', '0', '0' ];
        $c->srtk = $INITARR;
        if( Vcalendar::VTIMEZONE == $c->compType ) {
            if( false === ( $c->srtk[0] = $c->getProperty( Util::$TZID ))) {
                $c->srtk[0] = 0;
            }
            return;
        }
        elseif( ! is_null( $sortArg )) {
            if( Util::isPropInList( $sortArg, Util::$MPROPS1 )) { // all string
                $propValues = [];
                $c->getProperties( $sortArg, $propValues );
                if( ! empty( $propValues )) {
                    $c->srtk[0] = key( array_slice( $propValues, 0, 1, true ));
                }
                if( Util::$RELATED_TO == $sortArg ) {
                    $c->srtk[0] = $c->getProperty( Util::$UID );
                }
            } // end if( Util::isPropInList( $sortArg, Util::$MPROPS1 ))
            elseif( false !== ( $d = $c->getProperty( $sortArg ))) {
                $c->srtk[0] = ( Util::isArrayDate( $d )) ? self::arrDate2str( $d ) : $d;
                if( Util::$UID == $sortArg ) {
                    if( false !== ( $d = $c->getProperty( Util::$RECURRENCE_ID ))) {
                        $c->srtk[1] = self::arrDate2str( $d );
                        if( false === ( $c->srtk[2] = $c->getProperty( Util::$SEQUENCE ))) {
                            $c->srtk[2] = 0; // missing sequence equals sequence:0 in comb. with recurr.-id
                        }
                    }
                    else {
                        $c->srtk[1] = $c->srtk[2] = PHP_INT_MAX;
                    }
                } // end if( Util::$UID == $sortArg )
            } // end elseif( false !== ( $d = $c->getProperty( $sortArg )))
            return;
        } // end elseif( $sortArg )
        switch( true ) { // sortkey 0 : dtstart
            case ( false !== ( $d = $c->getProperty( UtilSelect::X_CURRENT_DTSTART ))) :
                $c->srtk[0] = self::arrDate2str( Util::strDate2ArrayDate( $d[1] ));
                break;
            case ( false !== ( $d = $c->getProperty( Util::$DTSTART ))) :
                $c->srtk[0] = self::arrDate2str( $d );
                break;
        }
        switch( true ) { // sortkey 1 : dtend/due(/duration)
            case ( false !== ( $d = $c->getProperty( UtilSelect::X_CURRENT_DTEND ))) :
                $c->srtk[1] = self::arrDate2str( Util::strDate2ArrayDate( $d[1] ));
                break;
            case ( false !== ( $d = $c->getProperty( Util::$DTEND ))) :
                $c->srtk[1] = self::arrDate2str( $d );
                break;
            case ( false !== ( $d = $c->getProperty( UtilSelect::X_CURRENT_DUE ))) :
                $c->srtk[1] = self::arrDate2str( Util::strDate2ArrayDate( $d[1] ));
                break;
            case ( false !== ( $d = $c->getProperty( Util::$DUE ))) :
                $c->srtk[1] = self::arrDate2str( $d );
                break;
            case ( false !== ( $d = $c->getProperty( Util::$DURATION, false, false, true ))) :
                $c->srtk[1] = self::arrDate2str( $d );
                break;
        }
        switch( true ) { // sortkey 2 : created/dtstamp
            case ( false !== ( $d = $c->getProperty( Util::$CREATED ))) :
                $c->srtk[2] = self::arrDate2str( $d );
                break;
            case ( false !== ( $d = $c->getProperty( Util::$DTSTAMP ))) :
                $c->srtk[2] = self::arrDate2str( $d );
                break;
        }
        // sortkey 3 : uid
        if( false === ( $c->srtk[3] = $c->getProperty( Util::$UID ))) {
            $c->srtk[3] = 0;
        }
    }

    /**
     * Return formatted string from (array) date/datetime
     *
     * @param array $aDate
     * @return string
     * @access private
     * @static
     */
    private static function arrDate2str( array $aDate ) {
        $str = Util::getYMDString( $aDate );
        if( isset( $aDate[Util::$LCHOUR] )) {
            $str .= Util::getHisString( $aDate );
        }
        if( isset( $aDate[Util::$LCtz] ) && ! empty( $aDate[Util::$LCtz] )) {
            $str .= $aDate[Util::$LCtz];
        }
        return $str;
    }

    /**
     * Sort callback function for exdate
     *
     * @param array $a
     * @param array $b
     * @return int
     * @static
     */
    public static function sortExdate1( array $a, array $b ) {
        $as  = Util::getYMDString( $a );
        $as .= ( isset( $a[Util::$LCHOUR] )) ? Util::getHisString( $a ) : null;
        $bs  = Util::getYMDString( $b );
        $bs .= ( isset( $b[Util::$LCHOUR] )) ? Util::getHisString( $b ) : null;
        return strcmp( $as, $bs );
    }

    /**
     * Sort callback function for exdate
     *
     * @param array $a
     * @param array $b
     * @return int
     * @static
     */
    public static function sortExdate2( array $a, array $b ) {
        $val = reset( $a[Util::$LCvalue] );
        $as  = Util::getYMDString( $val );
        $as .= ( isset( $val[Util::$LCHOUR] )) ? Util::getHisString( $val ) : null;
        $val = reset( $b[Util::$LCvalue] );
        $bs  = Util::getYMDString( $val );
        $bs .= ( isset( $val[Util::$LCHOUR] )) ? Util::getHisString( $val ) : null;
        return strcmp( $as, $bs );
    }

    /**
     * Sort callback function for freebusy and rdate, sort single property (inside values)
     *
     * @param array|DateInterval $a
     * @param array|DateInterval $b
     * @return int
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-03
     */
    public static function sortRdate1( $a, $b ) {
        $as = null;
        if( $a instanceof DateInterval ) {
            $as = UtilDuration::dateInterval2String( $a, true );
        }
        elseif( isset( $a[Util::$LCYEAR] )) {
            $as = self::formatdatePart( $a );
        }
        elseif( isset( $a[0][Util::$LCYEAR] )) {
            $as  = self::formatdatePart( $a[0] );
            if( isset( $a[1] )) {
                $as .= self::formatdatePart( $a[1] );
            }
        }
        else {
            return 1;
        }
        $bs = null;
        if( $b instanceof DateInterval ) {
            $bs = UtilDuration::dateInterval2String( $b, true );
        }
        elseif( isset( $b[Util::$LCYEAR] )) {
            $bs = self::formatdatePart( $b );
        }
        elseif( isset( $b[0][Util::$LCYEAR] )) {
            $bs  = self::formatdatePart( $b[0] );
            if( isset( $b[1] )) {
                $bs .= self::formatdatePart( $b[1] );
            }
        }
        else {
            return -1;
        }
        return strcmp( $as, $bs );
    }

    /**
     * Sort callback function for rdate, sort multiple RDATEs in order (after 1st datetime/date/period)
     *
     * @param array|DateInterval $a
     * @param array|DateInterval $b
     * @return int
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-03
     */
    public static function sortRdate2( $a, $b ) {
        $as = null;
        if( $a instanceof DateInterval ) {
            $as  = UtilDuration::dateInterval2String( $a, true );
        }
        elseif( isset( $a[Util::$LCvalue][0][Util::$LCYEAR] )) {
            $as  = self::formatdatePart( $a[Util::$LCvalue][0] );
        }
        elseif( isset( $a[Util::$LCvalue][0][0][Util::$LCYEAR] )) {
            $as  = self::formatdatePart( $a[Util::$LCvalue][0][0] );
            if( isset( $a[Util::$LCvalue][0][1] )) {
                $as .= self::formatdatePart( $a[Util::$LCvalue][0][1] );
            }
        }
        else {
            return 1;
        }
        $bs = null;
        if( $b instanceof DateInterval ) {
            $bs  = UtilDuration::dateInterval2String( $b, true );
        }
        elseif( isset( $b[Util::$LCvalue][0][Util::$LCYEAR] )) {
            $bs  = self::formatdatePart( $b[Util::$LCvalue][0] );
        }
        elseif( isset( $a[Util::$LCvalue][0][0][Util::$LCYEAR] )) {
            $bs  = self::formatdatePart( $b[Util::$LCvalue][0][0] );
            if( isset( $b[Util::$LCvalue][0][1] )) {
                $bs .= self::formatdatePart( $b[Util::$LCvalue][0][1] );
            }
        }
        else {
            return -1;
        }
        return strcmp( $as, $bs );
    }

    /**
     * Format date
     *
     * @param array|DateInterval $part
     * @return string
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-29
     */
    private static function formatdatePart( $part ) {
        if( $part instanceof DateInterval ) {
            $str = UtilDuration::dateInterval2String( $part, true );
        }
        elseif( isset( $part[Util::$LCYEAR] )) {
            $str  = Util::getYMDString( $part );
            $str .= ( isset( $part[Util::$LCHOUR] )) ? Util::getHisString( $part ) : null;
        }
        else {
            $str = UtilDuration::duration2str( $part );
        }
        return $str;
    }
}
