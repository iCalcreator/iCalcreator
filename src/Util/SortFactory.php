<?php
/**
  * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
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

use DateTime;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\Vcalendar;

use function array_slice;
use function ctype_digit;
use function is_null;
use function key;
use function method_exists;
use function reset;
use function strcmp;

/**
 * iCalcreator SortFactory class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.17 2020-01-25
 */
class SortFactory
{
    /**
     * Vcalendar sort callback function
     *
     * @param CalendarComponent $a
     * @param CalendarComponent $b
     * @return int
     * @static
     * @since 2.29.8 2019-07-23
     */
    public static function cmpfcn( CalendarComponent $a, CalendarComponent $b )
    {
        if( empty( $a )) {
            return -1;
        }
        if( empty( $b )) {
            return 1;
        }
        if( Vcalendar::VTIMEZONE == $a->getCompType()) {
            if( Vcalendar::VTIMEZONE != $b->getCompType()) {
                return -1;
            }
            elseif( $a->srtk[0] <= $b->srtk[0] ) {
                return -1;
            }
            else {
                return 1;
            }
        }
        elseif( Vcalendar::VTIMEZONE == $b->getCompType()) {
            return 1;
        }
        for( $k = 0; $k < 4; $k++ ) {
            if( empty( $a->srtk[$k] )) {
                return -1;
            }
            elseif( empty( $b->srtk[$k] )) {
                return 1;
            }
            $aKey = ctype_digit( $a->srtk[$k] )
                ? str_pad( $a->srtk[$k], 20, '0', STR_PAD_LEFT )
                : $a->srtk[$k];
            $bKey = ctype_digit( $b->srtk[$k] )
                ? str_pad( $b->srtk[$k], 20, '0', STR_PAD_LEFT )
                : $b->srtk[$k];
            $sortStat = strcmp( $aKey, $bKey );
            if( 0 == $sortStat ) {
                continue;
            }
            return ( 0 < $sortStat ) ? 1 : -1;
        } // end for
        return 0;
    }

    /**
     * Set sort arguments/parameters in component
     *
     * @param CalendarComponent $c valendar component
     * @param string            $sortArg
     * @static
     * @since 2.29.17 2020-01-25
     */
    public static function setSortArgs( CalendarComponent $c, $sortArg = null )
    {
        static $INITARR = [ '0', '0', '0', '0' ];
        $c->srtk  = $INITARR;
        $compType = $c->getCompType();
        if( Vcalendar::VTIMEZONE == $compType ) {
            $c->srtk[0] = $c->cno; // set order
            return;
        }
        elseif( ! is_null( $sortArg )) {
            if( Util::isPropInList( $sortArg, Vcalendar::$MPROPS1 )) { // all string
                $propValues = [];
                $c->getProperties( $sortArg, $propValues );
                if( ! empty( $propValues )) {
                    $c->srtk[0] = key( array_slice( $propValues, 0, 1, true ));
                }
                if( Vcalendar::RELATED_TO == $sortArg ) {
                    $c->srtk[0] = $c->getUid();
                }
            } // end if( Util::isPropInList( $sortArg, Util::$MPROPS1 ))
            else {
                $method = Vcalendar::getGetMethodName( $sortArg );
                if( method_exists( $c, $method ) && ( false !== ( $d = $c->{$method}()))) {
                    $c->srtk[0] = ( $d instanceof DateTime ) ? $d->getTimestamp() : $d;
                    if( Vcalendar::UID == $sortArg ) {
                        if(( Vcalendar::VFREEBUSY != $compType  ) &&
                            ( false !== ( $d = $c->getRecurrenceid()))) {
                            $c->srtk[1] = $d->getTimestamp();
                            if( false === ( $c->srtk[2] = $c->getSequence())) {
                                $c->srtk[2] = 0; // missing sequence equals sequence:0 in comb. with recurr.-id
                            }
                        }
                        else {
                            $c->srtk[1] = $c->srtk[2] = PHP_INT_MAX;
                        }
                    } // end if( Vcalendar::UID == $sortArg )
                } // end if
            } // end elseif( false !== ( $d = $c->getProperty( $sortArg )))
            return;
        } // end elseif( $sortArg )
        switch( true ) { // sortkey 0 : dtstart
            case ( false !== ( $d = $c->getXprop( Vcalendar::X_CURRENT_DTSTART ))) :
                $c->srtk[0] = $d[1];
                break;
            case ( false !== ( $d = $c->getDtstart())) :
                $c->srtk[0] = $d->getTimestamp();
                break;
        } // end switch
        switch( true ) { // sortkey 1 : dtend/due(/duration)
            case ( false !== ( $d = $c->getXprop( Vcalendar::X_CURRENT_DTEND ))) :
                $c->srtk[1] = $d[1];
                break;
            case ((( Vcalendar::VEVENT == $compType ) ||
                   ( Vcalendar::VFREEBUSY == $compType  )) &&
                ( false !== ( $d = $c->getDtend()))) :
                $c->srtk[1] = $d->getTimestamp();
                break;
            case ( false !== ( $d = $c->getXprop( Vcalendar::X_CURRENT_DUE ))) :
                $c->srtk[1] = $d[1];
                break;
            case (( Vcalendar::VTODO == $compType  ) && ( false !== ( $d = $c->getDue()))) :
                $c->srtk[1] = $d->getTimestamp();
                break;
            case ((( Vcalendar::VEVENT == $compType  ) ||
                    ( Vcalendar::VTODO == $compType )) &&
                ( false !== ( $d = $c->getDuration( null, true )))) :
                $c->srtk[1] = $d->getTimestamp();
                break;
        } // end switch
        switch( true ) { // sortkey 2 : created/dtstamp
            case (( Vcalendar::VFREEBUSY != $compType  ) &&
                ( false !== ( $d = $c->getCreated()))) :
                $c->srtk[2] = $d->getTimestamp();
                break;
            case ( false !== ( $d = $c->getDtstamp())) :
                $c->srtk[2] = $d->getTimestamp();
                break;
        } // end switch
        // sortkey 3 : uid
        if( false === ( $c->srtk[3] = $c->getUid())) {
            $c->srtk[3] = 0;
        }
    }

    /**
     * Sort callback function for exdate
     *
     * @param DateTime $a
     * @param DateTime $b
     * @return int
     * @static
     * @since 2.29.2 2019-06-23
     */
    public static function sortExdate1( DateTime $a, DateTime $b )
    {
        return strcmp(
            $a->format( DateTimeFactory::$YmdTHis ),
            $b->format( DateTimeFactory::$YmdTHis )
        );
    }

    /**
     * Sort callback function for exdate
     *
     * @param array $a
     * @param array $b
     * @return int
     * @static
     * @since 2.29.2 2019-06-23
     */
    public static function sortExdate2( array $a, array $b )
    {
        $a1 = reset( $a[Util::$LCvalue] );
        $b1 = reset( $b[Util::$LCvalue] );
        return strcmp(
            $a1->format( DateTimeFactory::$YmdTHis ),
            $b1->format( DateTimeFactory::$YmdTHis )
        );
    }

    /**
     * Sort callback function for freebusy and rdate, sort single property
     *
     * @param array|DateTime $a
     * @param array|DateTime $b
     * @return int
     * @static
     * @since 2.29.2 2019-06-23
     */
    public static function sortRdate1( $a, $b )
    {
        $as = $bs = null;
        if( $a instanceof DateTime ) {
            $as = $a->format( DateTimeFactory::$YmdTHis);
        }
        elseif( is_array( $a ) && ( $a[0] instanceof DateTime )) {
            $as = $a[0]->format( DateTimeFactory::$YmdTHis);
        }
        if( $b instanceof DateTime ) {
            $bs = $b->format( DateTimeFactory::$YmdTHis);
        }
        elseif( is_array( $b ) && ( $b[0] instanceof DateTime )) {
            $bs = $b[0]->format( DateTimeFactory::$YmdTHis);
        }
        return strcmp( $as, $bs );
    }

    /**
     * Sort callback function for rdate, sort multiple RDATEs in order (after 1st datetime/date/period)
     *
     * @param array|DateTime $a
     * @param array|DateTime $b
     * @return int
     * @static
     * @since 2.29.11 2019-08-29
     */
    public static function sortRdate2( $a, $b )
    {
        return strcmp(
            self::sortRdate2GetValue( $a[Util::$LCvalue] ),
            self::sortRdate2GetValue( $b[Util::$LCvalue] )
        );
    }

    /**
     * Return sortValue from RDATE value
     *
     * @param array|DateTime $v
     * @return string
     * @static
     * @since 2.29.2 2019-06-23
     */
    private static function sortRdate2GetValue( $v )
    {
        if( $v instanceof DateTime ) {
            return $v->format( DateTimeFactory::$YmdTHis);
        }
        elseif( is_array( $v ) && ( $v[0] instanceof DateTime )) {
            return $v[0]->format( DateTimeFactory::$YmdTHis);
        }
        elseif( is_array( $v[0] ) && ( $v[0][0] instanceof DateTime )) {
            return $v[0][0]->format( DateTimeFactory::$YmdTHis);
        }
        return null;
    }
}
