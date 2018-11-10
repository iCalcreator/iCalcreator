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

use Kigkonsult\Icalcreator\Util\UtilSelect;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * iCalcreator VcalendarSortHandler class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
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
            $sortStat = \strcmp( $a->srtk[$k], $b->srtk[$k] );
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
     * @since 2.26 - 2018-11-10
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
        elseif( ! \is_null( $sortArg )) {
            if( Util::isPropInList( $sortArg, Util::$MPROPS1 )) { // all string
                $propValues = [];
                $c->getProperties( $sortArg, $propValues );
                if( ! empty( $propValues )) {
                    $c->srtk[0] = key( \array_slice( $propValues, 0, 1, true ));
                    if( Util::$RELATED_TO == $sortArg ) {
                        $c->srtk[0] .= $c->getProperty( Util::$UID );
                    }
                }
                elseif( Util::$RELATED_TO == $sortArg ) {
                    $c->srtk[0] = $c->getProperty( Util::$UID );
                }
            } // end if( Util::isPropInList( $sortArg, Util::$MPROPS1 ))
            elseif( false !== ( $d = $c->getProperty( $sortArg ))) {
                $c->srtk[0] = ( Util::isArrayDate( $d )) ? self::arrDate2str( $d ) : $d;
                if( Util::$UID == $sortArg ) {
                    if( false !== ( $d = $c->getProperty( Util::$RECURRENCE_ID ))) {
                        $c->srtk[1] = self::arrDate2str( $d );
                        if( false === ( $c->srtk[2] = $c->getProperty( Util::$SEQUENCE ))) {
                            $c->srtk[2] = PHP_INT_MAX;
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
        $val = \reset( $a[Util::$LCvalue] );
        $as  = Util::getYMDString( $val );
        $as .= ( isset( $val[Util::$LCHOUR] )) ? Util::getHisString( $val ) : null;
        $val = \reset( $b[Util::$LCvalue] );
        $bs  = Util::getYMDString( $val );
        $bs .= ( isset( $val[Util::$LCHOUR] )) ? Util::getHisString( $val ) : null;
        return strcmp( $as, $bs );
    }

    /**
     * Sort callback function for freebusy and rdate, sort single property (inside values)
     *
     * @param array $a
     * @param array $b
     * @return int
     * @static
     */
    public static function sortRdate1( array $a, array $b ) {
        $as = null;
        if( isset( $a[Util::$LCYEAR] )) {
            $as = self::formatdatePart( $a );
        }
        elseif( isset( $a[0][Util::$LCYEAR] )) {
            $as  = self::formatdatePart( $a[0] );
            $as .= self::formatdatePart( $a[1] );
        }
        else {
            return 1;
        }
        $bs = null;
        if( isset( $b[Util::$LCYEAR] )) {
            $bs = self::formatdatePart( $b );
        }
        elseif( isset( $b[0][Util::$LCYEAR] )) {
            $bs  = self::formatdatePart( $b[0] );
            $bs .= self::formatdatePart( $b[1] );
        }
        else {
            return -1;
        }
        return \strcmp( $as, $bs );
    }

    /**
     * Sort callback function for rdate, sort multiple RDATEs in order (after 1st datetime/date/period)
     *
     * @param array $a
     * @param array $b
     * @return int
     * @static
     */
    public static function sortRdate2( array $a, array $b ) {
        $as = null;
        if( isset( $a[Util::$LCvalue][0][Util::$LCYEAR] )) {
            $as = self::formatdatePart( $a[Util::$LCvalue][0] );
        }
        elseif( isset( $a[Util::$LCvalue][0][0][Util::$LCYEAR] )) {
            $as  = self::formatdatePart( $a[Util::$LCvalue][0][0] );
            $as .= self::formatdatePart( $a[Util::$LCvalue][0][1] );
        }
        else {
            return 1;
        }
        $bs = null;
        if( isset( $b[Util::$LCvalue][0][Util::$LCYEAR] )) {
            $bs = self::formatdatePart( $b[Util::$LCvalue][0] );
        }
        elseif( isset( $a[Util::$LCvalue][0][0][Util::$LCYEAR] )) {
            $bs  = self::formatdatePart( $b[Util::$LCvalue][0][0] );
            $bs .= self::formatdatePart( $b[Util::$LCvalue][0][1] );
        }
        else {
            return -1;
        }
        return \strcmp( $as, $bs );
    }

    /**
     * Format date
     *
     * @param array $part
     * @return string
     */
    private static function formatdatePart( array $part ) {
        if( isset( $part[Util::$LCYEAR] )) {
            $str  = Util::getYMDString( $part );
            $str .= ( isset( $part[Util::$LCHOUR] )) ? Util::getHisString( $part ) : null;
        }
        else {
            $str = Util::duration2str( $part );
        }
        return $str;
    }
}
