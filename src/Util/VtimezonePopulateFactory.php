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

use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Vtimezone;

use function array_keys;
use function count;
use function date;
use function end;
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
 * @since 2.41.10 2022-01-26
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
    private static string $ISDST   = 'isdst';
    private static string $OFFSET  = 'offset';
    private static string $SECONDS = 'seconds';
    private static string $TIME    = 'time';
    private static string $TS      = 'ts';
    private static string $YMD     = 'Ymd';

    /**
     * If missing start/end or fetched from Dtstart, number if days before/after (now/dtstart) to use
     *
     * @var int
     */
    private static int $NUMBEROFDAYSBEFORE = 365;
    private static int $NUMBEROFDAYSAFTER  = 548;

    /**
     * Return calendar with Vtimezone(s) and Standard/Daylight components
     *
     * Used timezone from 1. xProps arg 2-3. calendar/vtimezone X-prop X_WR_TIMEZONE/X-LIC-LOCATION, 4. UTC
     * start/end : 1. start/end args  2. first/last found DTSTART and recalculated using vars above
     * Will remove any previously set Vtimezones
     *
     * @param Vcalendar             $calendar iCalcreator calendar instance
     * @param null|string|string[]  $timezone valid timezone(s) acceptable by PHP DateTimeZone
     * @param null|string[]         $xProp    *[x-propName => x-propValue]
     * @param null|int|DateTimeInterface  $start    .. or unix timestamp
     * @param null|int|DateTimeInterface  $end      .. or unix timestamp
     * @return Vcalendar
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.10 2022-01-26
     */
    public static function process(
        Vcalendar $calendar,
        null | string| array $timezone = null,
        ? array $xProp = [],
        null|int|DateTimeInterface $start = null,
        null|int|DateTimeInterface $end = null
    ) : Vcalendar
    {
        $timezone = empty( $timezone )
            ? [ self::getTimezone( $calendar, $xProp ) ]
            : (array) $timezone;
        while( false !== $calendar->deleteComponent( Vcalendar::VTIMEZONE )) {} // remove all Vtimezones
        foreach( $timezone as $theTimezone ) {
            DateTimeZoneFactory::assertDateTimeZone( $theTimezone );
            self::processSingleTimezone( $calendar, $theTimezone, $xProp, $start, $end );
        }
        return $calendar;
    }

    /**
     * Add VTimezone to calendar for single timezone
     *
     * @param Vcalendar $calendar
     * @param string $timezone
     * @param null|string[] $xProps
     * @param int|DateTimeInterface|null $start
     * @param int|DateTimeInterface|null $end
     * @return void
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.10 2022-01-26
     */
    public static function processSingleTimezone(
        Vcalendar                  $calendar,
        string                     $timezone,
        ? array                    $xProps = [],
        null|int|DateTimeInterface $start = null,
        null|int|DateTimeInterface $end = null
    ) : void
    {
        $foundTrans = [];
        if( ! DateTimeZoneFactory::isUTCtimeZone( $timezone )) {
            [ $start, $end ] = self::ensureStartAndEnd( $calendar, $timezone, $start, $end );
            $foundTrans      = self::findTransitions( $timezone, $start, $end );
        }
        $timezoneComp = self::getTimezoneComp( $calendar, $timezone, $xProps );
        foreach( $foundTrans as $trans ) {
            if(( 1 === count( $trans )) && isset( $trans[self::$TIME] )) {
                // last, contains DateTime for 'next' transition, i.e. transtion valid until
                $timezoneComp->setTzuntil( $trans[self::$TIME] );
                break;
            }
            // create standard/daylight subcomponents
            $subComp = ( true !== $trans[self::$ISDST] )
                ? $timezoneComp->newStandard()
                : $timezoneComp->newDaylight();
            $subComp->setDtstart( $trans[self::$TIME] );
            if( ! empty( $trans[self::$ABBR] )) {
                $subComp->setTzname( $trans[self::$ABBR] );
            }
            if( isset( $trans[Vcalendar::TZOFFSETFROM] )) {
                $subComp->setTzoffsetfrom( DateTimeZoneFactory::secondsToOffset( $trans[Vcalendar::TZOFFSETFROM] ));
            }
            $subComp->setTzoffsetto( DateTimeZoneFactory::secondsToOffset( $trans[self::$OFFSET] ));
            if( isset( $trans[Vcalendar::RDATE] )) {
                foreach( $trans[Vcalendar::RDATE] as $rDate ) {
                    // RDATEs, each with s single date
                    $subComp->setRdate( $rDate );
                }
            }
        } // end foreach
    }

    /**
     * @param Vcalendar $calendar
     * @param string $timezone
     * @param null|string[] $xProps
     * @return Vtimezone
     */
    private static function getTimezoneComp( Vcalendar $calendar, string $timezone, ? array $xProps = [] ) : Vtimezone
    {
        $timezoneComp = $calendar->newVtimezone( $timezone );
        if( ! empty( $xProps )) {
            foreach( $xProps as $xPropName => $xPropValue ) {
                if( StringFactory::isXprefixed( $xPropName )) {
                    $timezoneComp->setXprop( $xPropName, $xPropValue );
                }
            }
        } // end if
        return $timezoneComp;
    }

    /**
     * Return timezone from 1. xProps arg 2-3. calendar/vtimezone X-prop X_WR_TIMEZONE/X-LIC-LOCATION, 4. UTC
     *
     * @param Vcalendar     $calendar iCalcreator calendar instance
     * @param null|string[] $xProps    *[x-propName => x-propValue]
     * @return string
     * @since 2.47.68 2022-09-25
     */
    private static function getTimezone( Vcalendar $calendar, ? array $xProps = [] ) : string
    {
        switch( true ) {
            case Util::issetAndNotEmpty( $xProps, Vcalendar::X_WR_TIMEZONE ) :
                $timezone = $xProps[Vcalendar::X_WR_TIMEZONE];
                break;
            case Util::issetAndNotEmpty( $xProps, Vcalendar::X_LIC_LOCATION ) :
                $timezone = $xProps[Vcalendar::X_LIC_LOCATION];
                break;
            case $calendar->isXpropSet( Vcalendar::X_WR_TIMEZONE ) :
                $timezone = $calendar->getXprop( Vcalendar::X_WR_TIMEZONE )[1];
                break;
            case ( false !== ( $comp = $calendar->getComponent( Vcalendar::VTIMEZONE ))) :
                $calendar->resetCompCounter();
                if( false !== ( $xProp3 = $comp->getXprop( Vcalendar::X_LIC_LOCATION ))) {
                    $timezone = $xProp3[1];
                    break;
                }
                // fall through
            default :
                return Vcalendar::UTC;
        } // end switch
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
        static $ERRMSG             = 'Date are not in order: %d - %d';
        $startTs = self::getArgInSeconds( $start);
        $endTs   = self::getArgInSeconds( $end );
        switch( true ) {
            case ( ! empty( $startTs ) && ! empty( $endTs )) :
                break;
            case ( ! empty( $startTs )) : //  set to = +18 month (i.e 548 days)
                $endTs = (int) $startTs + ( 3600 * 24 * self::$NUMBEROFDAYSAFTER );
                break;
            case ( ! empty( $endTs )) :  // set from = -12 month (i.e 365 days)
                $startTs = (int) $endTs - ( 3600 * 24 * self::$NUMBEROFDAYSBEFORE );
                break;
            default :
                [ $startTs, $endTs ] = self::getStartEndFromDtstarts( $calendar, $timezone );
                break;
        } // end switch
        if( $startTs > $endTs ) {
            throw new InvalidArgumentException( sprintf( $ERRMSG, $start, $end ));
        }
        return [ $startTs, $endTs ];
    }

    /**
     * @param int|DateTimeInterface|null $arg
     * @return null|int
     */
    private static function getArgInSeconds( null|int|DateTimeInterface $arg = null ) : ? int
    {
        switch( true ) {
            case empty( $arg ) :
                return null;
            case ( $arg instanceof DateTimeInterface ) :
                return $arg->getTimestamp();
            default :
                Util::assertInteger( $arg, __METHOD__ );
                return (int) $arg;
        } // end switch
    }

    /**
     * @param Vcalendar $calendar
     * @param string $timezone
     * @return int[]
     * @throws Exception
     */
    private static function getStartEndFromDtstarts( Vcalendar $calendar, string $timezone ) : array
    {
        static $FMTBEFORE          = '-%d days';
        static $FMTAFTER           = '+%d days';
        $dtstartArr = array_keys( $calendar->getProperty( Vcalendar::DTSTART ));
        switch( true ) {
            case ( empty( $dtstartArr )) :
                $start = DateTimeFactory::factory( null, $timezone );
                $end   = ( clone $start );
                break;
            case ( 1 === count( $dtstartArr )) :
                $start = DateTimeFactory::factory((string) reset( $dtstartArr ), $timezone );
                $end   = ( clone $start );
                break;
            default :
                $start = DateTimeFactory::factory((string) reset( $dtstartArr ), $timezone );
                $end   = DateTimeFactory::factory((string) end( $dtstartArr ), $timezone );
                break;
        } // end switch
        return [
            $start->modify( sprintf( $FMTBEFORE, self::$NUMBEROFDAYSBEFORE ))->getTimestamp(),
            $end->modify(   sprintf( $FMTAFTER,  self::$NUMBEROFDAYSAFTER ))->getTimestamp()
        ];
    }

    /**
     * Return (prep'd) datetimezone transitions within start and end plus one for TZUNIL
     *
     * @param string $timezone
     * @param int    $start
     * @param int    $end
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.1 2022-01-15
     */
    private static function findTransitions( string $timezone, int $start, int $end ) : array
    {
        static $Y       = 'Y';
        $foundTrans     = [];
        $prevOffsetFrom = 0;
        $stdIx          = $dlghtIx = -1;
        $backupTrans    = [];
        $dateFromYmd    = self::getYmdFromTimestamp( $start, $timezone );
        $dateToYmd      = self::getYmdFromTimestamp( $end, $timezone );
        // extend search-args to assure we start/end at daylight shifts
        $start -= ( 3600 * 24 * 275 );
        $end   += ( 3600 * 24 * ( 185 * 2 )); // need one after end to get TZUNTIL
        $transitions    = DateTimeZoneFactory::getDateTimeZoneTransitions( $timezone, $start, $end );
        // all transitions in date-time order!!
        foreach( $transitions as $tix => $trans ) {
            if( 0 > (int) date( $Y, $trans[self::$TS] )) {
                // skip negative year... but save offset
                $prevOffsetFrom = $trans[self::$OFFSET];
                // previous trans offset will be 'next' trans offsetFrom
                continue;
            } // end if
            $transDate    = DateTimeFactory::factory( DateTimeFactory::$AT . $trans[self::$TS] );
            $transDateYmd = $transDate->format( self::$YMD );
            if( $transDateYmd < $dateFromYmd ) {
                // previous trans offset will be 'next' trans offsetFrom
                $prevOffsetFrom = $trans[self::$OFFSET];
                // we save it in case we don't find any match
                $backupTrans    = $trans;
                $backupTrans[Vcalendar::TZOFFSETFROM] =
                    ( 0 < $tix ) ? $transitions[$tix - 1][self::$OFFSET] : 0;
                continue;
            } // end if
            if(( $transDateYmd > $dateToYmd ) && ( -1 < ( $stdIx + $dlghtIx ))) {
                // loop always breaks here with, at least, one standard/daylight
                // now, save first UTC DateTime after end as TZUNTIL
                $foundTrans[] = [ self::$TIME => $transDate ];
                break;
            }
            if( ! empty( $prevOffsetFrom ) || ( 0 == $prevOffsetFrom )) {
                // set previous offsetto as offsetFrom
                $trans[Vcalendar::TZOFFSETFROM] = $prevOffsetFrom;
                // convert utc time to local time
                $transDate->modify( $trans[Vcalendar::TZOFFSETFROM] . self::$SECONDS );
                $trans[self::$TIME] = $transDate;
            } // end if
            $prevOffsetFrom = $trans[self::$OFFSET];
            if( true !== $trans[self::$ISDST] ) {
                // standard timezone, build RDATEs (in date order)
                if(( -1 < $stdIx ) && self::matchTrans( $foundTrans[$stdIx], $trans )) {
                    $foundTrans[$stdIx][Vcalendar::RDATE][] = clone $trans[self::$TIME];
                    continue;
                }
                $stdIx = $tix;
            } // end if, standard timezone
            else {
                // daylight timezone, build RDATEs (in date order)
                if(( -1 < $dlghtIx ) && self::matchTrans( $foundTrans[$dlghtIx], $trans )) {
                    $foundTrans[$dlghtIx][Vcalendar::RDATE][] = clone $trans[self::$TIME];
                    continue;
                }
                $dlghtIx = $tix;
            } // end else, daylight timezone
            $foundTrans[$tix] = $trans;
        } // end foreach( $transitions as $tix => $trans )
        if( empty( $foundTrans )) {
            $foundTrans[0] = self::buildTrans( $backupTrans, $timezone );
        }
        return $foundTrans;
    }

    /**
     * Return string Ymd from timestamp and timezone
     *
     * @param int $timestamp
     * @param string $timezone
     * @return string
     * @throws Exception
     */
    private static function getYmdFromTimestamp( int $timestamp, string $timezone ) : string
    {
        return DateTimeFactory::setDateTimeTimeZone(
            DateTimeFactory::factory( DateTimeFactory::$AT . $timestamp ),
            $timezone
        )
            ->format( DateTimeFactory::$Ymd );
    }

    /**
     * Return bool true if foundTrans matches trans
     *
     * @param array $foundTrans
     * @param array $trans
     * @return bool
     * @since  2.27.15 - 2019-02-23
     */
    private static function matchTrans( array $foundTrans, array $trans ) : bool
    {
        return
            ( isset( $foundTrans[Vcalendar::TZOFFSETFROM] ) &&
                ( $foundTrans[self::$ABBR]   === $trans[self::$ABBR] ) &&
                ( $foundTrans[Vcalendar::TZOFFSETFROM] === $trans[Vcalendar::TZOFFSETFROM] ) &&
                ( $foundTrans[self::$OFFSET] === $trans[self::$OFFSET] )
            );
    }

    /**
     * Return (array) build 'found'-trans
     *
     * @param array $backupTrans
     * @param string   $timezone
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.27.15 - 2019-02-23
     */
    private static function buildTrans( array $backupTrans, string $timezone ) : array
    {
        static $NOW = 'now';
        if( ! empty( $backupTrans )) {
            // we use the last transition (i.e. before startdate) for the tz info
            $prevDate = DateTimeFactory::factory( DateTimeFactory::$AT . $backupTrans[self::$TS] );
            // convert utc date to 'local' date
            $prevDate->modify( $backupTrans[Vcalendar::TZOFFSETFROM] . self::$SECONDS );
            $backupTrans[self::$TIME] = $prevDate;
        } // end if( $backupTrans )
        else {
            // or we use the timezone identifier to BUILD the standard tz info (?)
            $prevDate    = DateTimeFactory::factory( $NOW, $timezone );
            $backupTrans = [
                self::$TIME             => $prevDate,
                self::$OFFSET           => $prevDate->format( Vcalendar::Z ),
                Vcalendar::TZOFFSETFROM => $prevDate->format( Vcalendar::Z ),
                self::$ISDST            => false,
            ];
        }
        return $backupTrans;
    }
}
