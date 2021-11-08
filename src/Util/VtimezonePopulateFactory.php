<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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

use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Vcalendar;

use function array_keys;
use function count;
use function date;
use function end;
use function is_array;
use function reset;
use function sprintf;

/**
 * VtimezonePopulateFactory class, iCalcreator instance Vtimezone populate class
 *
 * Result when timezone is 'Europe/Stockholm' and no from/to arguments
 * BEGIN:VTIMEZONE
 * TZID:Europe/Stockholm
 * BEGIN:STANDARD
 * DTSTART:20101031T020000
 * TZOFFSETFROM:+0200
 * TZOFFSETTO:+0100
 * TZNAME:CET
 * END:STANDARD
 * BEGIN:DAYLIGHT
 * DTSTART:20100328T030000
 * TZOFFSETFROM:+0100
 * TZOFFSETTO:+0200
 * TZNAME:CEST
 * END:DAYLIGHT
 * END:VTIMEZONE
 *
 * @since  2.29.16 - 2020-01-25
 *
 * Contributors :
 *   Yitzchok Lavi <icalcreator@onebigsystem.com>
 *   jpirkey
 *
 */
class VtimezonePopulateFactory
{
    /*
    * @var string  for populate method (and descendents)
    */
    private static string $ABBR    = 'abbr';
    private static string $AT      = '@';
    private static string $ISDST   = 'isdst';
    private static string $OFFSET  = 'offset';
    private static string $SECONDS = 'seconds';
    private static string $TIME    = 'time';
    private static string $TS      = 'ts';
    private static string $YMD     = 'Ymd';

    /**
     * Return calendar with timezone and standard/daylight components
     *
     * @param Vcalendar     $calendar iCalcreator calendar instance
     * @param null|string   $timezone valid timezone acceptable by PHP5 DateTimeZone
     * @param null|string[] $xProp    *[x-propName => x-propValue]
     * @param null|int|DateTimeInterface  $start    .. or unix timestamp
     * @param null|int|DateTimeInterface  $end      .. or unix timestamp
     * @return Vcalendar
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.29.16 - 2020-01-25
     */
    public static function process(
        Vcalendar $calendar,
        ? string $timezone = null,
        ? array $xProp = [],
        null|int|DateTimeInterface $start = null,
        null|int|DateTimeInterface $end = null
    ) : Vcalendar
    {
        $timezone   = self::getTimezone( $calendar, $timezone, $xProp );
        $foundTrans = [];
        if( ! DateTimeZoneFactory::isUTCtimeZone( $timezone )) {
            [ $start, $end ] =
                self::ensureStartAndEnd( $calendar, $timezone, $start, $end );
            $foundTrans = self::findTransitions( $timezone, $start, $end );
        }
        while( false !== $calendar->deleteComponent( IcalInterface::VTIMEZONE )) {
            continue;
        }
        $timezoneComp = $calendar->newVtimezone();
        $timezoneComp->setTzid( $timezone );
        if( ! empty( $xProp )) {
            foreach( $xProp as $xPropName => $xPropValue ) {
                if( StringFactory::isXprefixed( $xPropName )) {
                    $timezoneComp->setXprop( $xPropName, $xPropValue );
                }
            }
        } // end if
        foreach( $foundTrans as $trans ) {
            // create standard/daylight subcomponents
            $subComp = ( true !== $trans[self::$ISDST] )
                ? $timezoneComp->newStandard()
                : $timezoneComp->newDaylight();
            $subComp->setDtstart( $trans[self::$TIME] );
            if( ! empty( $trans[self::$ABBR] )) {
                $subComp->setTzname( $trans[self::$ABBR] );
            }
            if( isset( $trans[IcalInterface::TZOFFSETFROM] )) {
                $subComp->setTzoffsetfrom(
                    DateTimeZoneFactory::secondsToOffset( $trans[IcalInterface::TZOFFSETFROM] )
                );
            }
            $subComp->setTzoffsetto(
                DateTimeZoneFactory::secondsToOffset( $trans[self::$OFFSET] )
            );
            if( isset( $trans[IcalInterface::RDATE] )) {
                foreach( $trans[IcalInterface::RDATE] as $rDate ) {
                    // single RDATEs, each with single date
                    $subComp->setRdate( $rDate );
                }
            }
        } // end foreach
        return $calendar;
    }

    /**
     * Return timezone from 1. tz arg, 2. xProps arg 3. calendar/vtimezone X-prop X_WR_TIMEZONE/X-LIC-LOCATION, 4. UTC
     *
     * @param Vcalendar     $calendar iCalcreator calendar instance
     * @param null|string   $timezone valid timezone acceptable by PHP5 DateTimeZone
     * @param null|string[] $xProp    *[x-propName => x-propValue]
     * @return string
     * @since  2.29.22 - 2019-08-26
     */
    private static function getTimezone(
        Vcalendar $calendar,
        ? string $timezone = null,
        ? array $xProp = []
    ) : string
    {
        $xProp2 = $calendar->getXprop( IcalInterface::X_WR_TIMEZONE );
        switch( true ) {
            case ( ! empty( $timezone )) :
                break;
            case Util::issetAndNotEmpty( $xProp, IcalInterface::X_WR_TIMEZONE ) :
                $timezone = $xProp[IcalInterface::X_WR_TIMEZONE];
                break;
            case Util::issetAndNotEmpty( $xProp, IcalInterface::X_LIC_LOCATION ) :
                $timezone = $xProp[IcalInterface::X_LIC_LOCATION];
                break;
            case ( false !== $xProp2 ) :
                $timezone = $xProp2[1];
                break;
            case ( false !==
                ( $comp = $calendar->getComponent( IcalInterface::VTIMEZONE ))) :
                $calendar->reset();
                if( false !== ( $xProp3 = $comp->getXprop( IcalInterface::X_LIC_LOCATION ))) {
                    $timezone = $xProp3[1];
                    break;
                }
                // fall through
            default :
                return IcalInterface::UTC;
        } // end switch
        DateTimeZoneFactory::assertDateTimeZone( $timezone );
        return $timezone;
    }

    /**
     * Return valid (ymd-)from/tom
     *
     * @param Vcalendar     $calendar
     * @param string        $timezone  valid timezone acceptable by PHP5 DateTimeZone
     * @param null|int|DateTimeInterface  $start    .. or unix timestamp
     * @param null|int|DateTimeInterface  $end      .. or unix timestamp
     * @return int[]
     * @throws  InvalidArgumentException
     * @throws  Exception
     * @since  2.27.15 - 2019-03-21
     */
    private static function ensureStartAndEnd(
        Vcalendar $calendar,
        string $timezone,
        null|int|DateTimeInterface $start = null,
        null|int|DateTimeInterface $end = null
    ) : array
    {
        static $NUMBEROFDAYSBEFORE = 365;
        static $FMTBEFORE          = '-%d days';
        static $NUMBEROFDAYSAFTER  = 548;
        static $FMTAFTER           = '+%d days';
        static $ERRMSG = 'Date are not in order: %d - %d';
        switch( true ) {
            case empty( $start ) :
                break;
            case ( $start instanceof DateTimeInterface ) :
                $start = $start->getTimestamp();
                break;
            default :
                Util::assertInteger( $start, __METHOD__ );
                $start = (int) $start;
                break;
        } // end switch
        switch( true ) {
            case empty( $end ) :
                break;
            case ( $end instanceof DateTimeInterface ) :
                $end = $end->getTimestamp();
                break;
            default :
                Util::assertInteger( $end, __METHOD__ );
                $end = (int) $end;
                break;
        } // end switch
        switch( true ) {
            case ( ! empty( $start ) && ! empty( $end )) :
                break;
            case ( ! empty( $start )) : //  set to = +18 month (i.e 548 days)
                $end = (int) $start + ( 3600 * 24 * $NUMBEROFDAYSAFTER );
                break;
            case ( ! empty( $end )) :  // set from = -12 month (i.e 365 days)
                $start = (int) $end - ( 3600 * 24 * $NUMBEROFDAYSBEFORE );
                break;
            default :
                $dtstarts = array_keys( $calendar->getProperty( IcalInterface::DTSTART ));
                switch( true ) {
                    case ( empty( $dtstarts )) :
                        $start = DateTimeFactory::factory( null, $timezone );
                        $end   = ( clone $start );
                        break;
                    case ( 1 === count( $dtstarts )) :
                        $start = DateTimeFactory::factory(
                            (string) reset( $dtstarts ),
                            $timezone
                        );
                        $end   = ( clone $start );
                        break;
                    default :
                        $start = DateTimeFactory::factory(
                            (string) reset( $dtstarts ),
                            $timezone
                        );
                        $end   = DateTimeFactory::factory(
                            (string) end( $dtstarts ),
                            $timezone
                        );
                        break;
                } // end switch
                $start = $start->modify( sprintf( $FMTBEFORE, $NUMBEROFDAYSBEFORE ))
                    ->getTimestamp();
                $end   = $end->modify( sprintf( $FMTAFTER, $NUMBEROFDAYSAFTER ))
                    ->getTimestamp();
                break;
        } // end switch
        if( $start > $end ) {
            throw new InvalidArgumentException( sprintf( $ERRMSG, $start, $end ));
        }
        return [ $start, $end ];
    }

    /**
     * Return (prep'd) datetimezone transitions
     *
     * @param string $timezone
     * @param int    $start
     * @param int    $end
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.27.15 - 2019-02-23
     */
    private static function findTransitions(
        string $timezone,
        int $start,
        int $end
    ) : array
    {
        static $Y       = 'Y';
        $foundTrans     = [];
        $prevOffsetFrom = 0;
        $stdIx          = $dlghtIx = -1;
        $backupTrans    = false;
        $dateFromYmd    = DateTimeFactory::setDateTimeTimeZone(
            DateTimeFactory::factory( self::$AT . $start ),
            $timezone
        )
            ->format( DateTimeFactory::$Ymd );
        $dateToYmd      = DateTimeFactory::setDateTimeTimeZone(
            DateTimeFactory::factory( self::$AT . $end ), $timezone )
            ->format( DateTimeFactory::$Ymd );
        // extend search-args to assure we start/end at daylight shifts
        $start -= ( 3600 * 24 * 275 );
        $end   += ( 3600 * 24 * 185 );
        $transitions    =
            DateTimeZoneFactory::getDateTimeZoneTransitions( $timezone, $start, $end );
        // all transitions in date-time order!!
        foreach( $transitions as $tix => $trans ) {
            if( 0 > (int) date( $Y, $trans[self::$TS] )) {
                // skip negative year... but save offset
                $prevOffsetFrom = $trans[self::$OFFSET];
                // previous trans offset will be 'next' trans offsetFrom
                continue;
            } // end if
            $transDate = DateTimeFactory::factory( self::$AT . $trans[self::$TS] );
            $transDateYmd = $transDate->format( self::$YMD );
            if( $transDateYmd < $dateFromYmd ) {
                // previous trans offset will be 'next' trans offsetFrom
                $prevOffsetFrom = $trans[self::$OFFSET];
                // we save it in case we don't find any match
                $backupTrans = $trans;
                $backupTrans[IcalInterface::TZOFFSETFROM] =
                    ( 0 < $tix ) ? $transitions[$tix - 1][self::$OFFSET] : 0;
                continue;
            } // end if
            if(( $transDateYmd > $dateToYmd ) && ( -1 < ( $stdIx + $dlghtIx ))) {
                // loop always (?) breaks here with, at least, one standard/daylight
                break;
            }
            if( ! empty( $prevOffsetFrom ) || ( 0 == $prevOffsetFrom )) {
                // set previous offsetto as offsetFrom
                $trans[IcalInterface::TZOFFSETFROM] = $prevOffsetFrom;
                // convert utc time to local time
                $transDate->modify( $trans[IcalInterface::TZOFFSETFROM] . self::$SECONDS );
                $trans[self::$TIME]             = $transDate;
            } // end if
            $prevOffsetFrom = $trans[self::$OFFSET];
            if( true !== $trans[self::$ISDST] ) {
                // standard timezone, build RDATEs (in date order)
                if(( -1 < $stdIx ) &&
                    self::matchTrans( $foundTrans[$stdIx], $trans )) {
                    $foundTrans[$stdIx][IcalInterface::RDATE][] = clone $trans[self::$TIME];
                    continue;
                }
                $stdIx = $tix;
            } // end standard timezone
            else {
                // daylight timezone, build RDATEs (in date order)
                if(( -1 < $dlghtIx ) &&
                    self::matchTrans( $foundTrans[$dlghtIx], $trans )) {
                    $foundTrans[$dlghtIx][IcalInterface::RDATE][] = clone $trans[self::$TIME];
                    continue;
                }
                $dlghtIx = $tix;
            } // end daylight timezone
            $foundTrans[$tix] = $trans;
        } // end foreach( $transitions as $tix => $trans )
        if( empty( $foundTrans )) {
            $foundTrans[0] = self::buildTrans( $backupTrans, $timezone );
        }
        return $foundTrans;
    }

    /**
     * return bool true if foundTrans matches trans
     *
     * @param array $foundTrans
     * @param array $trans
     * @return bool
     * @since  2.27.15 - 2019-02-23
     */
    private static function matchTrans( array $foundTrans, array $trans ) : bool
    {
        return
            ((  isset( $foundTrans[IcalInterface::TZOFFSETFROM] )) &&
                ( $foundTrans[self::$ABBR]   === $trans[self::$ABBR] ) &&
                ( $foundTrans[IcalInterface::TZOFFSETFROM]
                    === $trans[IcalInterface::TZOFFSETFROM] ) &&
                ( $foundTrans[self::$OFFSET] === $trans[self::$OFFSET] )
            );
    }

    /**
     * return (array build 'found'-trans
     *
     * @param array|bool $backupTrans
     * @param string     $timezone
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.27.15 - 2019-02-23
     */
    private static function buildTrans( array | bool $backupTrans, string $timezone ) : array
    {
        static $NOW = 'now';
        if( is_array( $backupTrans )) {
            // we use the last transition (i.e. before startdate) for the tz info
            $prevDate = DateTimeFactory::factory( self::$AT . $backupTrans[self::$TS] );
            // convert utc date to 'local' date
            $prevDate->modify( $backupTrans[IcalInterface::TZOFFSETFROM] . self::$SECONDS );
            $backupTrans[self::$TIME] = $prevDate;
        } // end if( $backupTrans )
        else {
            // or we use the timezone identifier to BUILD the standard tz info (?)
            $prevDate    = DateTimeFactory::factory( $NOW, $timezone );
            $backupTrans = [
                self::$TIME             => $prevDate,
                self::$OFFSET           => $prevDate->format( IcalInterface::Z ),
                IcalInterface::TZOFFSETFROM => $prevDate->format( IcalInterface::Z ),
                self::$ISDST            => false,
            ];
        }
        return $backupTrans;
    }
}
