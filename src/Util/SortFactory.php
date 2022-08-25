<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software iCalcreator.
 *            The above copyright, link, package and version notices,
 *            this licence notice and the invariant [rfc5545] PRODID result use
 *            as implemented and invoked in iCalcreator shall be included in
 *            all copies or substantial portions of the iCalcreator.
 *
 *            iCalcreator is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            iCalcreator is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator\Util;

use DateTime;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Vcalendar;

use function array_slice;
use function ctype_digit;
use function in_array;
use function is_null;
use function key;
use function method_exists;
use function strcmp;

/**
 * iCalcreator SortFactory class
 *
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
     * @since 2.29.8 2019-07-23
     */
    public static function cmpfcn( CalendarComponent $a, CalendarComponent $b ) : int
    {
        if( IcalInterface::VTIMEZONE === $a->getCompType()) {
            if( IcalInterface::VTIMEZONE !== $b->getCompType()) {
                return -1;
            }
            if( $a->srtk[0] <= $b->srtk[0] ) {
                return -1;
            }
            return 1;
        }
        if( IcalInterface::VTIMEZONE === $b->getCompType()) {
            return 1;
        }
        for( $k = 0; $k < 4; $k++ ) {
            if( empty( $a->srtk[$k] )) {
                return -1;
            }
            if( empty( $b->srtk[$k] )) {
                return 1;
            }
            $aKey = ctype_digit( $a->srtk[$k] )
                ? str_pad((string) $a->srtk[$k], 20, '0', STR_PAD_LEFT )
                : (string) $a->srtk[$k];
            $bKey = ctype_digit( $b->srtk[$k] )
                ? str_pad((string) $b->srtk[$k], 20, '0', STR_PAD_LEFT )
                : (string)$b->srtk[$k];
            $sortStat = strcmp( $aKey, $bKey );
            if( 0 === $sortStat ) {
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
     * @param null|string       $sortArg
     * @since 2.41.13 2022-02-01
     */
    public static function setSortArgs( CalendarComponent $c, ? string $sortArg = null ) : void
    {
        static $INITARR = [ '0', '0', '0', '0' ];
        static $GETRECURRIDMETHOD = null;
        static $GETSEQMETHOD      = null;
        $c->srtk        = $INITARR;
        $compType = $c->getCompType();
        if( IcalInterface::VTIMEZONE === $compType ) {
            $c->srtk[0] = $c->cno; // set order
            return;
        }
        if( is_null( $sortArg )) {
            self::setSortDefaultArgs( $c );
            return;
        }
        if( in_array( $sortArg, Vcalendar::$MPROPS1, true )) { // all string
            $propValues = [];
            $c->getProperties( $sortArg, $propValues );
            if( ! empty( $propValues )) {
                $c->srtk[0] = key( array_slice( $propValues, 0, 1, true ));
            }
            if( IcalInterface::RELATED_TO === $sortArg ) {
                $c->srtk[0] = $c->getUid();
            }
            return;
        } // end if
        $method = StringFactory::getGetMethodName( $sortArg );
        if( method_exists( $c, $method ) && ( false !== ( $d = $c->{$method}()))) {
            $c->srtk[0] = ( $d instanceof DateTime ) ? $d->getTimestamp() : $d;
            if( IcalInterface::UID === $sortArg ) {
                if( null === $GETRECURRIDMETHOD ) {
                    $GETRECURRIDMETHOD = StringFactory::getGetMethodName( IcalInterface::RECURRENCE_ID );
                    $GETSEQMETHOD      = StringFactory::getGetMethodName( IcalInterface::SEQUENCE );
                }
                if( method_exists( $c, $GETRECURRIDMETHOD ) && ( false !== ( $d = $c->getRecurrenceid()))) {
                    $c->srtk[1] = $d->getTimestamp();
                    if( method_exists( $c, $GETSEQMETHOD ) && ( false === ( $c->srtk[2] = $c->getSequence()))) {
                        $c->srtk[2] = 0; // missing sequence equals sequence:0 in comb. with recurr.-id
                    }
                }
                else {
                    $c->srtk[1] = $c->srtk[2] = PHP_INT_MAX;
                }
            } // end if( Vcalendar::UID == $sortArg )
        } // end if
    }

    /**
     * Set default (date-related/uid) sort arguments/parameters in component
     *
     * @param CalendarComponent $c valendar component
     * @since 2.41.53 2022-08-11
     */
    private static function setSortDefaultArgs( CalendarComponent $c ) : void
    {
        static $DTENDCOMPS = [ IcalInterface::VEVENT, IcalInterface::VFREEBUSY, IcalInterface::VAVAILABILITY ];
        static $DURCOMPS   = [
            IcalInterface::VAVAILABILITY,
            IcalInterface::VEVENT,
            IcalInterface::VFREEBUSY,
            IcalInterface::VTODO
        ];
        $compType = $c->getCompType();
        // sortkey 0 : dtstart
        if( false !== ( $d = $c->getXprop( IcalInterface::X_CURRENT_DTSTART ))) {
            $c->srtk[0] = $d[1];
        }
        elseif( false !== ( $d = $c->getDtstart())) {
                $c->srtk[0] = $d->getTimestamp();
        }
        switch( true ) { // sortkey 1 : dtend/due(/duration)
            case ( false !== ( $d = $c->getXprop( IcalInterface::X_CURRENT_DTEND ))) :
                $c->srtk[1] = $d[1];
                break;
            case( in_array( $compType, $DTENDCOMPS, true ) ) && ( false !== ( $d = $c->getDtend())) :
                $c->srtk[1] = $d->getTimestamp();
                break;
            case ( false !== ( $d = $c->getXprop( IcalInterface::X_CURRENT_DUE ))) :
                $c->srtk[1] = $d[1];
                break;
            case (( IcalInterface::VTODO === $compType  ) && ( false !== ( $d = $c->getDue()))) :
                $c->srtk[1] = $d->getTimestamp();
                break;
            case ( in_array( $compType, $DURCOMPS, true ) && ( false !== ( $d = $c->getDuration( null, true )))) :
                $c->srtk[1] = $d->getTimestamp();
                break;
        } // end switch
        // sortkey 2 : created/dtstamp
        $c->srtk[2] = (( IcalInterface::VFREEBUSY !== $compType  ) &&
            ( false !== ( $d = $c->getCreated())))
            ? $d->getTimestamp()
            : $c->getDtstamp()->getTimestamp();
        // sortkey 3 : uid
        $c->srtk[3] = $c->getUid();
    }

    /**
     * Sort callback function for freebusy and rdate, sort single property
     *
     * @param array|DateTime $a
     * @param array|DateTime $b
     * @return int
     * @since 2.29.2 2019-06-23
     */
    public static function sortRdate1( array | DateTime $a, array | DateTime $b ) : int
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
     * @param Pc $a
     * @param Pc $b
     * @return int
     * @since 2.41.36 2022-04-03
     */
    public static function sortRdate2( Pc $a, Pc $b ) : int
    {
        return strcmp(
            self::sortRdate2GetValue( $a->value ),
            self::sortRdate2GetValue( $b->value )
        );
    }

    /**
     * Return sortValue from RDATE value
     *
     * @param array|DateTime $v
     * @return string
     * @since 2.29.2 2019-06-23
     */
    private static function sortRdate2GetValue( array | DateTime $v ) : string
    {
        if( $v instanceof DateTime ) {
            return $v->format( DateTimeFactory::$YmdTHis);
        }
        if( is_array( $v ) && ( $v[0] instanceof DateTime )) {
            return $v[0]->format( DateTimeFactory::$YmdTHis);
        }
        if( is_array( $v[0] ) && ( $v[0][0] instanceof DateTime )) {
            return $v[0][0]->format( DateTimeFactory::$YmdTHis);
        }
        return Util::$SP0;
    }
}
