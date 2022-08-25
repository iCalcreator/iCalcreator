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
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Vcalendar;
use LogicException;

use function array_change_key_case;
use function array_keys;
use function array_unique;
use function checkdate;
use function count;
use function ctype_alpha;
use function date;
use function end;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function ksort;
use function mktime;
use function sprintf;
use function strcasecmp;
use function strtoupper;
use function var_export;

/**
 * iCalcreator recur support class
 *
 * @since  2.41.16 - 2022-01-31
 */
class RecurFactory
{
    /**
     * @const int  in recur2date, years to extend startYear to create an endDate, if missing
     */
    public const EXTENDYEAR = 2;

    /**
     * @var string  iCal date/time key values ( week, tz used in test)
     */
    public static string $LCYEAR  = 'year';

    /**
     * @var string
     */
    public static string $LCMONTH = 'month';

    /**
     * @var string
     */
    public static string $LCWEEK  = 'week';

    /**
     * @var string
     */
    public static string $LCDAY   = 'day';

    /**
     * @var string
     */
    public static string $LCHOUR  = 'hour';

    /**
     * @var string
     */
    public static string $LCMIN   = 'min';

    /**
     * @var string
     */
    public static string $LCSEC   = 'sec';

    /**
     * Static values for recur BYDAY
     *
     * @var string[]
     */
    public static array $DAYNAMES = [
        IcalInterface::SU,
        IcalInterface::MO,
        IcalInterface::TU,
        IcalInterface::WE,
        IcalInterface::TH,
        IcalInterface::FR,
        IcalInterface::SA
    ];

    /*
     * @var string  DateTime format keys
     */
    public static string $YMDs = '%04d%02d%02d';

    /**
     * @var string dito
     */
    public static string $HIS  = '%02d%02d%02d';

    /*
     * @var string  fullRecur2date keys
     */
    private static string $YEARCNT_UP      = 'yearcnt_up';

    /**
     * @var string
     */
    private static string $YEARCNT_DOWN    = 'yearcnt_down';

    /**
     * @var string
     */
    private static string $MONTHDAYNO_UP   = 'monthdayno_up';

    /**
     * @var string
     */
    private static string $MONTHDAYNO_DOWN = 'monthdayno_down';

    /**
     * @var string
     */
    private static string $MONTHCNT_DOWN   = 'monthcnt_down';

    /**
     * @var string
     */
    private static string $YEARDAYNO_UP    = 'yeardayno_up';

    /**
     * @var string
     */
    private static string $YEARDAYNO_DOWN  = 'yeardayno_down';

    /**
     * @var string
     */
    private static string $WEEKNO_UP       = 'weekno_up';

    /**
     * @var string
     */
    private static string $WEEKNO_DOWN     = 'weekno_down';

    /**
     * Convert input format for EXRULE and RRULE to internal format
     *
     * "The value of the UNTIL rule part MUST have the same value type as the "DTSTART" property."
     * "If specified as a DATE-TIME value, then it MUST be specified in a UTC time format."
     * @param Pc $rexrule   params merged with dtstart params
     * @return Pc
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws LogicException
     * @since 2.41.57 2022-08-17
     * @todo "The BYSECOND, BYMINUTE and BYHOUR rule parts MUST NOT be specified
     *        when the associated "DTSTART" property has a DATE value type."
     */
    public static function setRexrule( Pc $rexrule ) : Pc
    {
        static $ERR    = 'Invalid input date \'%s\'';
        if( empty( $rexrule->value )) {
            return $rexrule;
        }
        $input          = [];
        $isValueDate    = $rexrule->hasParamValue( IcalInterface::DATE );
        $paramTZid      = $rexrule->getParams( IcalInterface::TZID );
        $rexrule->value = array_change_key_case( $rexrule->value, CASE_UPPER );
        foreach( $rexrule->value as $ruleLabel => $ruleValue ) {
            switch( true ) {
                case ( IcalInterface::UNTIL !== $ruleLabel ) :
                    $input[$ruleLabel] = $ruleValue;
                    break;
                case ( $ruleValue instanceof DateTimeInterface ) :
                    $input[$ruleLabel] =
                        DateTimeFactory::setDateTimeTimeZone(
                            DateTimeFactory::toDateTime( $ruleValue ),
                            IcalInterface::UTC
                        );
                    $rexrule->removeParam( IcalInterface::TZID ); // if exists
                    break;
                case DateTimeFactory::isStringAndDate( $ruleValue ) :
                    [ $dateStr, $timezonePart ] =
                        DateTimeFactory::splitIntoDateStrAndTimezone( $ruleValue );
                    $isLocalTime = ( empty( $timezonePart ) && empty( $paramTZid ));
                    $dateTime = DateTimeFactory::getDateTimeWithTimezoneFromString(
                        $dateStr,
                        $isLocalTime ? null : $timezonePart,
                        $isLocalTime ? IcalInterface::UTC : $paramTZid,
                        true
                    );
                    if( ! $isValueDate ) {
                        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, IcalInterface::UTC );
                    }
                    $rexrule->removeParam( IcalInterface::TZID ); // if exists
                    $input[$ruleLabel] = $dateTime;
                    break;
                default :
                    throw new InvalidArgumentException(
                        sprintf( $ERR, var_export( $ruleValue, true ))
                    );
            } // end switch
        } // end foreach( $rexrule as $ruleLabel => $ruleValue )
        $output = self::orderRRuleKeys( $input );
        if( ! isset( $output[IcalInterface::UNTIL] )) {
            $rexrule->removeParam( IcalInterface::TZID ); // if exists
        }
        try {
            RecurFactory2::assertRecur( $output );
        }
        catch( LogicException $e ) {
            throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
        }
        return $rexrule->setValue( $output );
    }

    /**
     * @param array $input
     * @return array
     * @since  2.41.16 - 2022-01-31
     */
    private static function orderRRuleKeys( array $input ) : array
    {
        static $RKEYS1 = [
            IcalInterface::FREQ,
            IcalInterface::UNTIL,
            IcalInterface::COUNT,
            IcalInterface::INTERVAL,
            IcalInterface::BYSECOND,
            IcalInterface::BYMINUTE,
            IcalInterface::BYHOUR
        ];
        static $RKEYS2 = [
            IcalInterface::BYMONTHDAY,
            IcalInterface::BYYEARDAY,
            IcalInterface::BYWEEKNO,
            IcalInterface::BYMONTH,
            IcalInterface::BYSETPOS,
            IcalInterface::WKST
        ];
        static $RKEYS3 = [
            IcalInterface::BYSECOND,
            IcalInterface::BYMINUTE,
            IcalInterface::BYHOUR,
            IcalInterface::BYMONTHDAY,
            IcalInterface::BYYEARDAY,
            IcalInterface::BYWEEKNO,
            IcalInterface::BYMONTH,
            IcalInterface::BYSETPOS,
        ];
        /* set recurrence rule specification in rfc2445 order */
        $output = [];
        if( isset( $input[IcalInterface::RSCALE] )) { // rfc7529 - first
            $output[IcalInterface::RSCALE] = strtoupper( $input[IcalInterface::RSCALE] );
        }
        if( isset( $input[IcalInterface::FREQ] )) {
            $input[IcalInterface::FREQ] = strtoupper( $input[IcalInterface::FREQ] );
        }
        foreach( $RKEYS1 as $rKey1 ) {
            if( isset( $input[$rKey1] )) {
                $output[$rKey1] = $input[$rKey1];
            }
        }
        if( isset( $input[IcalInterface::BYDAY] )) {
            self::orderRRuleBydayKey( $input, $output );
        }
        foreach( $RKEYS2 as $rKey2 ) {
            if( isset( $input[$rKey2] )) {
                $output[$rKey2] = $input[$rKey2];
            }
        }
        foreach( $RKEYS3 as $rKey3 ) {
            if( ! isset( $output[$rKey3] )) {
                continue;
            }
            if( is_string( $output[$rKey3] )) {
                $temp = explode( Util::$COMMA, $output[$rKey3] );
                if( 1 === count( $temp )) {
                    $output[$rKey3] = reset( $temp );
                }
                else {
                    sort( $temp );
                    $output[$rKey3] = array_unique( $temp );
                }
            }
            elseif( is_array( $output[$rKey3] )) {
                sort( $output[$rKey3] );
                $output[$rKey3] = array_unique( $output[$rKey3] );
            }
        } // end foreach
        if( isset( $input[IcalInterface::SKIP] )) { // rfc7529 - last
            $output[IcalInterface::SKIP] = strtoupper( $input[IcalInterface::SKIP] );
        }
        return $output;
    }

    /**
     * Ensure RRULE BYDAY array and upper case.. .
     *
     * @param array $input
     * @param array $output
     * @return void
     * @since  2.29.27 - 2020-09-19
     */
    private static function orderRRuleBydayKey( array $input, array & $output ) : void
    {
        if( empty( $input[IcalInterface::BYDAY] )) {
            // results in error
            $output[IcalInterface::BYDAY] = [];
            return;
        }
        if( ! is_array( $input[IcalInterface::BYDAY] )) {
            // single day
            $output[IcalInterface::BYDAY] = [
                IcalInterface::DAY => strtoupper( $input[IcalInterface::BYDAY] ),
            ];
            return;
        }
        $cntStr = $cntNum = 0;
        foreach( $input[IcalInterface::BYDAY] as $BYDAYv ) {
            if( is_array( $BYDAYv )) {
                break;
            }
            if( is_string( $BYDAYv ) && ctype_alpha( $BYDAYv )) {
                ++$cntStr;
                continue;
            }
            if( empty( $BYDAYv )) {
                $input[IcalInterface::BYDAY] = [ Util::$SP0 ];
                ++$cntStr;
                continue;
            }
            ++$cntNum;
        } // end foreach
        if(( 1 === $cntStr ) || ( 1 < $cntNum )) { // single day OR invalid format...
            $input[IcalInterface::BYDAY] = [ $input[IcalInterface::BYDAY] ];
        }
        elseif( 1 < $cntStr ) { // split (single) days
            $days = [];
            foreach( $input[IcalInterface::BYDAY] as $BYDAYv ) {
                $days[] = [ IcalInterface::DAY => $BYDAYv ];
            }
            $input[IcalInterface::BYDAY] = $days;
        }
        foreach( $input[IcalInterface::BYDAY] as $BYDAYx => $BYDAYv ) {
            $nIx = 0;
            foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                switch( true ) {
                    case ( is_string( $BYDAYx2 ) &&
                        ( 0 === strcasecmp( IcalInterface::DAY, $BYDAYx2 ))) :
                        // day abbr with key
                        $output[IcalInterface::BYDAY][$BYDAYx][$BYDAYx2] = strtoupper( $BYDAYv2 );
                        break;
                    case ( is_string( $BYDAYv2 ) && ctype_alpha( $BYDAYv2 )) :
                        // day abbr without key, set key
                        $output[IcalInterface::BYDAY][$BYDAYx][IcalInterface::DAY] = strtoupper( $BYDAYv2 );
                        break;
                    default :
                        // rel pos day number. force key from 0 (1++ results in error)
                        $output[IcalInterface::BYDAY][$BYDAYx][$nIx++] = $BYDAYv2;
                        break;
                } // end switch
            } // end foreach
            ksort( $output[IcalInterface::BYDAY][$BYDAYx], SORT_NATURAL );
        } // end foreach
        ksort( $output[IcalInterface::BYDAY], SORT_NATURAL );
    }

    /**
     * Return UID[] where RRULE(/EXRULE) RECUR RSCALE exists and is NOT GREGORIAN (or similar)
     *
     * Return UID[] contains UID to skip, rfc7529 6. Compatibility, option 2
     * For all (rfc7529) calendar systems, see
     *   (http://www.unicode.org/repos/cldr/tags/latest/common/bcp47/calendar.xml, redirected to)
     *   https://github.com/unicode-org/cldr/blob/latest/common/bcp47/calendar.xml
     *
     * @param Vcalendar  $calendar
     * @param string[]   $compTypes  component types to accept
     * @return string[]
     * @since 2.41.16 - 2022-02-01
     */
    public static function rruleRscaleCheck( Vcalendar $calendar, array $compTypes ) : array
    {
        static $ACCEPTED   = [ 'GREGORY', IcalInterface::GREGORIAN, 'ISO8601' ];
        static $RRULEPROPS = [ IcalInterface::EXRULE, IcalInterface::RRULE ];
        $foundUids = [];
        $calendar->resetCompCounter();
        while( $component = $calendar->getComponent()) {
            if( ! in_array( $component->getCompType(), $compTypes, true )) {
                continue;
            }
            foreach( $RRULEPROPS as $rruleProp ) {
                $getMethod = StringFactory::getGetMethodName( $rruleProp );
                if( false === ( $propValue = $component->{$getMethod}( true ))) {
                    continue;
                }
                if( isset( $propValue->value[IcalInterface::RSCALE] ) &&
                    ! in_array( $propValue->value[IcalInterface::RSCALE], $ACCEPTED, true )) {
                    $foundUids[] = $component->getUID();
                }
            } // end foreach
        } // end while
        return $foundUids;
    }

    /**
     * Update array $result with dates based on a recur pattern
     *
     * If missing, UNTIL is set 1 year from startdate (emergency break)
     *
     * @param array $result      array to update, array([Y-m-d] => bool)
     * @param array $recur       pattern for recurrency (only value part, params ignored)
     * @param string|DateTime $wDateIn     component start date
     * @param string|DateTime $fcnStartIn  start date
     * @param string|DateTime $fcnEndIn    end date
     * @return void
     * @throws Exception
     * @since  2.29.24 - 2020-08-29
     * @todo   BYHOUR, BYMINUTE, BYSECOND, WEEKLY at year end/start OR not at all
     */
    public static function recur2date(
        array & $result,
        array $recur,
        string | DateTime $wDateIn,
        string | DateTime $fcnStartIn,
        string | DateTime $fcnEndIn
    ) : void
    {
        if( ! isset( $recur[IcalInterface::FREQ] )) { // "MUST be specified.. ." ??
            $recur[IcalInterface::FREQ] = IcalInterface::DAILY;
        }
        $recur[IcalInterface::INTERVAL] = isset( $recur[IcalInterface::INTERVAL] )
            ? (int) $recur[IcalInterface::INTERVAL]
            : 1;
        switch( true ) {
            case RecurFactory2::isRecurDaily1( $recur ) :
                foreach( RecurFactory2::recurDaily1( $recur, $wDateIn, $fcnStartIn, $fcnEndIn )
                         as $ymd => $v ) {
                    $result[$ymd] = $v;
                }
                ksort( $result, SORT_NUMERIC );
                break;
            case RecurFactory2::isRecurDaily2( $recur ) :
                foreach( RecurFactory2::recurDaily2( $recur, $wDateIn, $fcnStartIn, $fcnEndIn )
                         as $ymd => $v ) {
                    $result[$ymd] = $v;
                }
                ksort( $result, SORT_NUMERIC );
                break;
            case RecurFactory2::isRecurMonthly1( $recur ) :
                foreach( RecurFactory2::recurMonthly1( $recur, $wDateIn, $fcnStartIn, $fcnEndIn )
                         as $ymd => $v ) {
                    $result[$ymd] = $v;
                }
                ksort( $result, SORT_NUMERIC );
                break;
            case RecurFactory2::isRecurMonthly2( $recur ) :
                foreach( RecurFactory2::recurMonthlyYearly3( $recur, $wDateIn, $fcnStartIn, $fcnEndIn )
                         as $ymd => $v ) {
                    $result[$ymd] = $v;
                }
                ksort( $result, SORT_NUMERIC );
                break;
            case RecurFactory2::isRecurWeekly1( $recur ) :
                foreach( RecurFactory2::recurWeekly1( $recur, $wDateIn, $fcnStartIn, $fcnEndIn )
                         as $ymd => $v ) {
                    $result[$ymd] = $v;
                }
                ksort( $result, SORT_NUMERIC );
                break;
            case RecurFactory2::isRecurWeekly2( $recur ) :
                foreach( RecurFactory2::recurWeekly2( $recur, $wDateIn, $fcnStartIn, $fcnEndIn )
                         as $ymd => $v ) {
                    $result[$ymd] = $v;
                }
                ksort( $result, SORT_NUMERIC );
                break;
            case RecurFactory2::isRecurYearly1( $recur ) :
                foreach( RecurFactory2::recurYearly1( $recur, $wDateIn, $fcnStartIn, $fcnEndIn )
                         as $ymd => $v ) {
                    $result[$ymd] = $v;
                }
                ksort( $result, SORT_NUMERIC );
                break;
            case RecurFactory2::isRecurYearly2( $recur ) :
                foreach( RecurFactory2::recurMonthlyYearly3( $recur, $wDateIn, $fcnStartIn, $fcnEndIn )
                         as $ymd => $v ) {
                    $result[$ymd] = $v;
                }
                ksort( $result, SORT_NUMERIC );
                break;
            default :
                self::fullRecur2date(
                    $result,
                    $recur,
                    $wDateIn,
                    $fcnStartIn,
                    $fcnEndIn
                );
        } // end switch
    }

    /**
     * Update array $result with dates based on a recur pattern
     *
     * If missing, UNTIL is set 1 year from startdate (emergency break)
     *
     * @param array $result              array to update, array([Y-m-d] => bool)
     * @param array $recur               pattern for recurrency (only value part, params ignored)
     * @param string|DateTime $wDateIn     component start date
     * @param string|DateTime $fcnStartIn  start date
     * @param string|DateTime $fcnEndIn    end date
     * @throws Exception
     * @since  2.26 - 2018-11-10
     * @todo   BYHOUR, BYMINUTE, BYSECOND, WEEKLY at year end/start OR not at all
     */
    public static function fullRecur2date(
        array & $result,
        array $recur,
        string | DateTime $wDateIn,
        string | DateTime $fcnStartIn,
        string | DateTime $fcnEndIn
    ) : void
    {
        static $YEAR2DAYARR = [ 'YEARLY', 'MONTHLY', 'WEEKLY', 'DAILY' ];
        if( ! isset( $recur[IcalInterface::FREQ] )) { // "MUST be specified.. ."
            $recur[IcalInterface::FREQ] = IcalInterface::DAILY;
        } // ??
        $recur[IcalInterface::INTERVAL] =
            isset( $recur[IcalInterface::INTERVAL] )
                ? (int) $recur[IcalInterface::INTERVAL]
                : 1;
        $wDate     = self::reFormatDate( $wDateIn );
        $wDateYMD  = sprintf(
            self::$YMDs,
            $wDate[self::$LCYEAR],
            $wDate[self::$LCMONTH],
            $wDate[self::$LCDAY]
        );
        $wDateHis    = sprintf(
            self::$HIS,
            $wDate[self::$LCHOUR],
            $wDate[self::$LCMIN],
            $wDate[self::$LCSEC]
        );
        $untilHis    = $wDateHis;
        $fcnStart    = self::reFormatDate( $fcnStartIn );
        $fcnStartYMD = sprintf(
            self::$YMDs,
            $fcnStart[self::$LCYEAR],
            $fcnStart[self::$LCMONTH],
            $fcnStart[self::$LCDAY]
        );
        if( ! empty( $fcnEndIn )) {
            $fcnEnd = self::reFormatDate( $fcnEndIn );
        }
        else {
            $fcnEnd                = $fcnStart;
            $fcnEnd[self::$LCYEAR] += self::EXTENDYEAR;
        }
        $fcnEndYMD   = sprintf(
            self::$YMDs,
            $fcnEnd[self::$LCYEAR],
            $fcnEnd[self::$LCMONTH],
            $fcnEnd[self::$LCDAY]
        );
        if( ! isset( $recur[IcalInterface::COUNT] ) && ! isset( $recur[IcalInterface::UNTIL] )) {
            $recur[IcalInterface::UNTIL] = $fcnEnd; // ??
        } // create break
        if( isset( $recur[IcalInterface::UNTIL] )) {
            $recur[IcalInterface::UNTIL] = self::reFormatDate( $recur[IcalInterface::UNTIL] );
            if( $fcnEnd > $recur[IcalInterface::UNTIL] ) {
                $fcnEnd    = $recur[IcalInterface::UNTIL]; // emergency break
                $fcnEndYMD = sprintf(
                    self::$YMDs,
                    $fcnEnd[self::$LCYEAR],
                    $fcnEnd[self::$LCMONTH],
                    $fcnEnd[self::$LCDAY]
                );
            }
            if( isset( $recur[IcalInterface::UNTIL][self::$LCHOUR] )) {
                $untilHis = sprintf(
                    self::$HIS,
                    $recur[IcalInterface::UNTIL][self::$LCHOUR],
                    $recur[IcalInterface::UNTIL][self::$LCMIN],
                    $recur[IcalInterface::UNTIL][self::$LCSEC]
                );
            }
            else {
                $untilHis = sprintf( self::$HIS, 23, 59, 59 );
            }
        } // end if( isset( $recur[Vcalendar::UNTIL] ))
        if( $wDateYMD > $fcnEndYMD ) {
            return; // nothing to do.. .
        }
        $recurFreqIsYearly  = ( IcalInterface::YEARLY  === $recur[IcalInterface::FREQ] );
        $recurFreqIsMonthly = ( IcalInterface::MONTHLY === $recur[IcalInterface::FREQ] );
        $recurFreqIsWeekly  = ( IcalInterface::WEEKLY  === $recur[IcalInterface::FREQ] );
        $recurFreqIsDaily   = ( IcalInterface::DAILY   === $recur[IcalInterface::FREQ] );
        $wkst = ( Util::issetKeyAndEquals( $recur, IcalInterface::WKST, IcalInterface::SU ))
            ? 24 * 60 * 60
            : 0; // ??
        $recurCount = ( isset( $recur[IcalInterface::BYSETPOS] )) ? 0 : 1; // DTSTART counts as the first occurrence
        /* find out how to step up dates and set index for interval \count */
        $step = [];
        if( $recurFreqIsYearly ) {
            $step[self::$LCYEAR] = 1;
        }
        elseif( $recurFreqIsMonthly ) {
            $step[self::$LCMONTH] = 1;
        }
        elseif( $recurFreqIsWeekly ) {
            $step[self::$LCDAY] = 7;
        }
        else {
            $step[self::$LCDAY] = 1;
        }
        if( isset( $step[self::$LCYEAR], $recur[IcalInterface::BYMONTH] )) {
            $step = [ self::$LCMONTH => 1 ];
        }
        if( empty( $step ) && isset( $recur[IcalInterface::BYWEEKNO] )) { // ??
            $step = [ self::$LCDAY => 7 ];
        }
        if( isset( $recur[IcalInterface::BYYEARDAY] ) ||
            isset( $recur[IcalInterface::BYMONTHDAY] ) ||
            isset( $recur[IcalInterface::BYDAY] )) {
            $step = [ self::$LCDAY => 1 ];
        }
        $intervalArr = [];
        if( 1 < $recur[IcalInterface::INTERVAL] ) {
            $intervalIx  = self::recurIntervalIx(
                $recur[IcalInterface::FREQ],
                $wDate,
                $wkst
            );
            $intervalArr = [ $intervalIx => 0 ];
        }
        if( isset( $recur[IcalInterface::BYSETPOS] )) { // save start date + weekno
            $bysetPosymd1 = $bysetPosymd2 = $bysetPosw1 = $bysetPosw2 = [];
            if( is_array( $recur[IcalInterface::BYSETPOS] )) {
                RecurFactory2::assureIntArray( $recur[IcalInterface::BYSETPOS] );
            }
            else {
                $recur[IcalInterface::BYSETPOS] = [ (int) $recur[IcalInterface::BYSETPOS] ];
            }
            if( $recurFreqIsYearly ) {
                // start from beginning of year
                $wDate[self::$LCMONTH] = $wDate[self::$LCDAY] = 1;
                $wDateYMD              = sprintf(
                    self::$YMDs,
                    $wDate[self::$LCYEAR],
                    $wDate[self::$LCMONTH],
                    $wDate[self::$LCDAY]
                );
                // make sure to count last year
                self::stepDate( $fcnEnd, $fcnEndYMD, [ self::$LCYEAR => 1 ] );
            }
            elseif( $recurFreqIsMonthly ) {
                // start from beginning of month
                $wDate[self::$LCDAY] = 1;
                $wDateYMD            = sprintf(
                    self::$YMDs,
                    $wDate[self::$LCYEAR],
                    $wDate[self::$LCMONTH],
                    $wDate[self::$LCDAY]
                );
                // make sure to count last month
                self::stepDate( $fcnEnd, $fcnEndYMD, [ self::$LCMONTH => 1 ] );
            }
            else {
                self::stepDate( $fcnEnd, $fcnEndYMD, $step );
            } // make sure to count whole last period
            $bysetPosWold = self::getWeekNumber(
                $wkst,
                $wDate[self::$LCMONTH],
                $wDate[self::$LCDAY],
                $wDate[self::$LCYEAR]
            );
            $bysetPosYold = $wDate[self::$LCYEAR];
            $bysetPosMold = $wDate[self::$LCMONTH];
            $bysetPosDold = $wDate[self::$LCDAY];
        } // end if( isset( $recur[Vcalendar::BYSETPOS] ))
        else {
            self::stepDate( $wDate, $wDateYMD, $step );
        }
        $yearOld = -1;
        $dayCnts  = [];
        /* MAIN LOOP */
        while( true ) {
            if( $wDateYMD . $wDateHis > $fcnEndYMD . $untilHis ) {
                break;
            }
            if( isset( $recur[IcalInterface::COUNT] ) &&
                ( $recurCount > (int) $recur[IcalInterface::COUNT] )) {
                break;
            }
            if( $yearOld != $wDate[self::$LCYEAR] ) { // $yearOld=-1  1:st time
                $yearOld  = $wDate[self::$LCYEAR];
                $dayCnts  = self::initDayCnts( $wDate, $recur, $wkst );
            }
            /* check interval */
            if( 1 < $recur[IcalInterface::INTERVAL] ) {
                /* create interval index */
                $intervalIx = self::recurIntervalIx(
                    $recur[IcalInterface::FREQ],
                    $wDate,
                    $wkst
                );
                /* check interval */
                $currentKey = array_keys( $intervalArr );
                $currentKey = end( $currentKey ); // get last index
                if( $currentKey != $intervalIx ) {
                    $intervalArr = [ $intervalIx => ( $intervalArr[$currentKey] + 1 ) ];
                }
                if(( $recur[IcalInterface::INTERVAL] != $intervalArr[$intervalIx] ) &&
                    ( 0 != $intervalArr[$intervalIx] )) {
                    /* step up date */
                    self::stepDate( $wDate, $wDateYMD, $step );
                    continue; // while
                }
                // continue within the selected interval
                $intervalArr[$intervalIx] = 0;
            } // endif( 1 < $recur['INTERVAL'] )
            $updateOK = true;
            if( isset( $recur[IcalInterface::BYMONTH] ) ) {
                $updateOK = self::recurBYcntcheck(
                    $recur[IcalInterface::BYMONTH],
                    $wDate[self::$LCMONTH],
                    ( $wDate[self::$LCMONTH] - 13 )
                );
            }
            if( $updateOK && isset( $recur[IcalInterface::BYWEEKNO] )) {
                $updateOK = self::recurBYcntcheck(
                    $recur[IcalInterface::BYWEEKNO],
                    $dayCnts[$wDate[self::$LCMONTH]][$wDate[self::$LCDAY]][self::$WEEKNO_UP],
                    $dayCnts[$wDate[self::$LCMONTH]][$wDate[self::$LCDAY]][self::$WEEKNO_DOWN]
                );
            }
            if( $updateOK && isset( $recur[IcalInterface::BYYEARDAY] )) {
                $updateOK = self::recurBYcntcheck(
                    $recur[IcalInterface::BYYEARDAY],
                    $dayCnts[$wDate[self::$LCMONTH]][$wDate[self::$LCDAY]][self::$YEARCNT_UP],
                    $dayCnts[$wDate[self::$LCMONTH]][$wDate[self::$LCDAY]][self::$YEARCNT_DOWN]
                );
            }
            if( $updateOK && isset( $recur[IcalInterface::BYMONTHDAY] )) {
                $updateOK = self::recurBYcntcheck(
                    $recur[IcalInterface::BYMONTHDAY],
                    $wDate[self::$LCDAY],
                    $dayCnts[$wDate[self::$LCMONTH]][$wDate[self::$LCDAY]][self::$MONTHCNT_DOWN]
                );
            }
            if( $updateOK && isset( $recur[IcalInterface::BYDAY] )) {
                $updateOK = false;
                $m        = $wDate[self::$LCMONTH];
                $d        = $wDate[self::$LCDAY];
                if( isset( $recur[IcalInterface::BYDAY][IcalInterface::DAY] )) { // single day, opt with year/month day order no
                    $dayNumberExists = $dayNumberSw = $dayNameSw = false;
                    if( $recur[IcalInterface::BYDAY][IcalInterface::DAY] ==
                            $dayCnts[$m][$d][IcalInterface::DAY] ) {
                        $dayNameSw = true;
                    }
                    if( isset( $recur[IcalInterface::BYDAY][0] )) {
                        $dayNumberExists = true;
                        if( $recurFreqIsMonthly || isset( $recur[IcalInterface::BYMONTH] )) {
                            $dayNumberSw = self::recurBYcntcheck(
                                $recur[IcalInterface::BYDAY][0],
                                $dayCnts[$m][$d][self::$MONTHDAYNO_UP],
                                $dayCnts[$m][$d][self::$MONTHDAYNO_DOWN]
                            );
                        }
                        elseif( $recurFreqIsYearly ) {
                            $dayNumberSw = self::recurBYcntcheck(
                                $recur[IcalInterface::BYDAY][0],
                                $dayCnts[$m][$d][self::$YEARDAYNO_UP],
                                $dayCnts[$m][$d][self::$YEARDAYNO_DOWN]
                            );
                        }
                    }
                    if(( $dayNumberExists && $dayNumberSw && $dayNameSw ) ||
                        ( ! $dayNumberExists && ! $dayNumberSw && $dayNameSw )) {
                        $updateOK = true;
                    }
                } // end if( isset( $recur[Vcalendar::BYDAY][Vcalendar::DAY] ))
                else {  // multiple days
                    foreach( $recur[IcalInterface::BYDAY] as $byDayValue ) {
                        $dayNumberExists = $dayNumberSw = $dayNameSw = false;
                        if( isset( $byDayValue[IcalInterface::DAY] ) &&
                            ( $byDayValue[IcalInterface::DAY] ==
                                $dayCnts[$m][$d][IcalInterface::DAY] )) {
                            $dayNameSw = true;
                        }
                        if( isset( $byDayValue[0] )) {
                            $dayNumberExists = true;
                            if( $recurFreqIsMonthly ||
                                isset( $recur[IcalInterface::BYMONTH] )) {
                                $dayNumberSw = self::recurBYcntcheck(
                                    $byDayValue[Util::$ZERO],
                                    $dayCnts[$m][$d][self::$MONTHDAYNO_UP],
                                    $dayCnts[$m][$d][self::$MONTHDAYNO_DOWN]
                                );
                            }
                            elseif( $recurFreqIsYearly ) {
                                $dayNumberSw = self::recurBYcntcheck(
                                    $byDayValue[Util::$ZERO],
                                    $dayCnts[$m][$d][self::$YEARDAYNO_UP],
                                    $dayCnts[$m][$d][self::$YEARDAYNO_DOWN]
                                );
                            }
                        } // end if( isset( $byDayValue[0] ))
                        if(( $dayNumberExists && $dayNumberSw && $dayNameSw ) ||
                            ( ! $dayNumberExists && ! $dayNumberSw && $dayNameSw )) {
                            $updateOK = true;
                            break;
                        }
                    } // end foreach( $recur[Vcalendar::BYDAY] as $byDayValue )
                } // end else
            } // end if( $updateOK && isset( $recur[Vcalendar::BYDAY] ))
            /* check BYSETPOS */
            if( $updateOK ) {
                if(      isset( $recur[IcalInterface::BYSETPOS] ) &&
                    ( in_array( $recur[IcalInterface::FREQ], $YEAR2DAYARR, true ) )) {
                    if( $recurFreqIsWeekly ) {
                        if( $bysetPosWold ==
                            $dayCnts[$wDate[self::$LCMONTH]][$wDate[self::$LCDAY]][self::$WEEKNO_UP] ) {
                            $bysetPosw1[] = $wDateYMD;
                        }
                        else {
                            $bysetPosw2[] = $wDateYMD;
                        }
                    } // end if
                    elseif(( $recurFreqIsYearly &&
                            ( $bysetPosYold == $wDate[self::$LCYEAR] )) ||
                        ( $recurFreqIsMonthly &&
                            (( $bysetPosYold == $wDate[self::$LCYEAR] ) &&
                             ( $bysetPosMold == $wDate[self::$LCMONTH] ))) ||
                        ( $recurFreqIsDaily &&
                            (( $bysetPosYold == $wDate[self::$LCYEAR] ) &&
                             ( $bysetPosMold == $wDate[self::$LCMONTH] ) &&
                             ( $bysetPosDold == $wDate[self::$LCDAY] )))) {
                        $bysetPosymd1[] = $wDateYMD;
                    } // end elseif
                    else {
                        $bysetPosymd2[] = $wDateYMD;
                    } // end else
                }
                else { // ! isset( $recur[Vcalendar::BYSETPOS] )
                    if( checkdate(
                        (int) $wDate[self::$LCMONTH],
                        (int) $wDate[self::$LCDAY],
                        (int) $wDate[self::$LCYEAR] )) {
                        /* update result array if BYSETPOS is not set */
                        $recurCount++;
                        if( $fcnStartYMD <= $wDateYMD ) { // only output within period
                            $result[$wDateYMD] = true;
                        }
                    }
                    $updateOK = false;
                }
            }
            /* step up date */
            self::stepDate( $wDate, $wDateYMD, $step );
            /* check if BYSETPOS is set for updating result array */
            if( $updateOK && isset( $recur[IcalInterface::BYSETPOS] )) {
                $bysetPos = false;
                if( $recurFreqIsYearly &&
                    ( $bysetPosYold != $wDate[self::$LCYEAR] )) {
                    $bysetPos     = true;
                    $bysetPosYold = $wDate[self::$LCYEAR];
                }
                elseif( $recurFreqIsMonthly &&
                        (( $bysetPosYold != $wDate[self::$LCYEAR] ) ||
                         ( $bysetPosMold != $wDate[self::$LCMONTH] ))) {
                    $bysetPos     = true;
                    $bysetPosYold = $wDate[self::$LCYEAR];
                    $bysetPosMold = $wDate[self::$LCMONTH];
                }
                elseif( $recurFreqIsWeekly ) {
                    $weekNo = self::getWeekNumber(
                        $wkst,
                        $wDate[self::$LCMONTH],
                        $wDate[self::$LCDAY],
                        $wDate[self::$LCYEAR]
                    );
                    if( $bysetPosWold != $weekNo ) {
                        $bysetPosWold = $weekNo;
                        $bysetPos     = true;
                    }
                }
                elseif( $recurFreqIsDaily &&
                    (( $bysetPosYold != $wDate[self::$LCYEAR] ) ||
                     ( $bysetPosMold != $wDate[self::$LCMONTH] ) ||
                     ( $bysetPosDold != $wDate[self::$LCDAY] ))) {
                    $bysetPos     = true;
                    $bysetPosYold = $wDate[self::$LCYEAR];
                    $bysetPosMold = $wDate[self::$LCMONTH];
                    $bysetPosDold = $wDate[self::$LCDAY];
                }
                if( $bysetPos ) {
                    if( isset( $recur[IcalInterface::BYWEEKNO] )) {
                        $bysetPosArr1 = &$bysetPosw1;
                        $bysetPosArr2 = &$bysetPosw2;
                    }
                    else {
                        $bysetPosArr1 = &$bysetPosymd1;
                        $bysetPosArr2 = &$bysetPosymd2;
                    }

                    foreach( $recur[IcalInterface::BYSETPOS] as $ix ) {
                        if( 0 > $ix ) { // both positive and negative BYSETPOS allowed
                            $ix = ( count( $bysetPosArr1 ) + $ix + 1 );
                        }
                        $ix--;
                        if( isset( $bysetPosArr1[$ix] )) {
                            if( $fcnStartYMD <= $bysetPosArr1[$ix] ) { // only output within period
                                $result[$bysetPosArr1[$ix]] = true;
                            }
                            $recurCount++;
                        }
                        if( isset( $recur[IcalInterface::COUNT] ) &&
                            ( $recurCount >= $recur[IcalInterface::COUNT] )) {
                            break;
                        }
                    }
                    $bysetPosArr1 = $bysetPosArr2;
                    $bysetPosArr2 = [];
                } // end if( $bysetPos )
            } // end if( $updateOK && isset( $recur['BYSETPOS'] ))
        } // end while( true )
        ksort( $result );
        if( isset( $recur[IcalInterface::COUNT] ) &&
            ( count( $result ) >= $recur[IcalInterface::COUNT] )) {
            $max    = $recur[IcalInterface::COUNT] - 1;
            $result = array_slice( $result, 0, $max, true );
        }
    }

    /**
     * Checking BYDAY (etc) hits, recur2date help function
     *
     * @param int|string|array $BYvalue
     * @param int $upValue
     * @param int   $downValue
     * @return bool
     *@since  2.6.12 - 2011-01-03
     */
    private static function recurBYcntcheck(
        int | string | array $BYvalue,
        int $upValue,
        int $downValue
    ) : bool
    {
        if( is_array( $BYvalue ) &&
            ( in_array( $upValue, $BYvalue ) || in_array( $downValue, $BYvalue )) // no third arg tue
        ) {
            return true;
        }
        return (( $BYvalue == $upValue ) || ( $BYvalue == $downValue ));  // no third arg tue
    }

    /**
     * (re-)Calculate internal index, recur2date help function
     *
     * @param string  $freq
     * @param array $date
     * @param int     $wkst
     * @return string
     * @since  2.26 - 2018-11-10
     */
    private static function recurIntervalIx( string $freq, array $date, int $wkst ) : string
    {
        /* create interval index */
        $intervalIx = match( $freq ) {
            IcalInterface::YEARLY  => $date[self::$LCYEAR],
            IcalInterface::MONTHLY => $date[self::$LCYEAR] . Util::$MINUS . $date[self::$LCMONTH],
            IcalInterface::WEEKLY  => self::getWeekNumber(
                $wkst,
                $date[self::$LCMONTH],
                $date[self::$LCDAY],
                $date[self::$LCYEAR]
            ),
            default => $date[self::$LCYEAR] .
                Util::$MINUS .
                $date[self::$LCMONTH] .
                Util::$MINUS .
                $date[self::$LCDAY],
        }; // end switch
        return (string) $intervalIx;
    }

    /**
     * Return updated date, array and timpstamp
     *
     * @param array $date     date to step
     * @param string       $dateYMD  date YMD
     * @param null|array $step     default array( Util::$LCDAY => 1 )
     * @return void
     */
    private static function stepDate( array & $date, string & $dateYMD, ? array $step = null ) : void
    {
        if( empty( $step )) {
            $step = [ self::$LCDAY => 1 ];
        }
        if( ! isset( $date[self::$LCHOUR] )) {
            $date[self::$LCHOUR] = 0;
        }
        if( ! isset( $date[self::$LCMIN] )) {
            $date[self::$LCMIN] = 0;
        }
        if( ! isset( $date[self::$LCSEC] )) {
            $date[self::$LCSEC] = 0;
        }
        if( isset( $step[self::$LCDAY] )) {
            $daysInMonth = self::getDaysInMonth(
                $date[self::$LCHOUR],
                $date[self::$LCMIN],
                $date[self::$LCSEC],
                $date[self::$LCMONTH],
                $date[self::$LCDAY],
                $date[self::$LCYEAR]
            );
        }
        RecurFactory2::assureIntArray( $step, false );
        RecurFactory2::assureIntArray( $date, false );
        foreach( $step as $stepix => $stepvalue ) {
            $date[$stepix] += $stepvalue;
        }
        if( isset( $step[self::$LCMONTH] )) {
            if( 12 < $date[self::$LCMONTH] ) {
                ++$date[self::$LCYEAR];
                $date[self::$LCMONTH] -= 12;
            }
        }
        elseif( isset( $step[self::$LCDAY] ) &&
            ( $daysInMonth < $date[self::$LCDAY] )) {
            $date[self::$LCDAY] -= $daysInMonth;
            ++$date[self::$LCMONTH];
            if( 12 < $date[self::$LCMONTH] ) {
                ++$date[self::$LCYEAR];
                $date[self::$LCMONTH] -= 12;
            }
        }
        $dateYMD = sprintf(
            self::$YMDs,
            $date[self::$LCYEAR],
            $date[self::$LCMONTH],
            $date[self::$LCDAY]
        );
    }

    /**
     * Return initiated $dayCnts
     *
     * @param array $wDate
     * @param array $recur
     * @param int     $wkst
     * @return array
     */
    private static function initDayCnts( array $wDate, array $recur, int $wkst ) : array
    {
        $dayCnts    = [];
        $yearDayCnt = [];
        $yearDays   = 0;
        foreach( self::$DAYNAMES as $dn ) {
            $yearDayCnt[$dn] = 0;
        }
        for( $m = 1; $m <= 12; $m++ ) { // count up and update up-counters
            $dayCnts[$m] = [];
            $weekDayCnt  = [];
            foreach( self::$DAYNAMES as $dn ) {
                $weekDayCnt[$dn] = 0;
            }
            $daysInMonth = self::getDaysInMonth( 0, 0, 0, $m, 1, $wDate[self::$LCYEAR] );
            for( $d = 1; $d <= $daysInMonth; $d++ ) {
                $dayCnts[$m][$d] = [];
                if( isset( $recur[IcalInterface::BYYEARDAY] )) {
                    $yearDays++;
                    $dayCnts[$m][$d][self::$YEARCNT_UP] = $yearDays;
                }
                if( isset( $recur[IcalInterface::BYDAY] )) {
                    $day = self::getDayInWeek( $m, $d, $wDate[self::$LCYEAR] );
                    $dayCnts[$m][$d][IcalInterface::DAY] = $day;
                    $weekDayCnt[$day]++;
                    $dayCnts[$m][$d][self::$MONTHDAYNO_UP] = $weekDayCnt[$day];
                    $yearDayCnt[$day]++;
                    $dayCnts[$m][$d][self::$YEARDAYNO_UP] = $yearDayCnt[$day];
                }
                if( isset( $recur[IcalInterface::BYWEEKNO] ) ||
                    ( $recur[IcalInterface::FREQ] === IcalInterface::WEEKLY )) {
                    $dayCnts[$m][$d][self::$WEEKNO_UP] =
                        self::getWeekNumber($wkst, $m, $d, $wDate[self::$LCYEAR] );
                }
            } // end for( $d   = 1; $d <= $daysInMonth; $d++ )
        } // end for( $m = 1; $m <= 12; $m++ )
        $daycnt     = 0;
        $yearDayCnt = [];
        if( isset( $recur[IcalInterface::BYWEEKNO] ) ||
            ( $recur[IcalInterface::FREQ] === IcalInterface::WEEKLY )) {
            $weekNo = 0;
            for( $d = 31; $d > 25; $d-- ) { // get last weekno for year
                if( empty( $weekNo )) {
                    $weekNo = $dayCnts[12][$d][self::$WEEKNO_UP];
                }
                elseif( $weekNo < $dayCnts[12][$d][self::$WEEKNO_UP] ) {
                    $weekNo = $dayCnts[12][$d][self::$WEEKNO_UP];
                    break;
                }
            } // end for
        }
        for( $m = 12; $m > 0; $m-- ) { // count down and update down-counters
            $weekDayCnt = [];
            foreach( self::$DAYNAMES as $dn ) {
                $yearDayCnt[$dn] = $weekDayCnt[$dn] = 0;
            }
            $monthCnt    = 0;
            $daysInMonth = self::getDaysInMonth( 0, 0, 0, $m, 1, $wDate[self::$LCYEAR] );
            for( $d = $daysInMonth; $d > 0; $d-- ) {
                if( isset( $recur[IcalInterface::BYYEARDAY] )) {
                    --$daycnt;
                    $dayCnts[$m][$d][self::$YEARCNT_DOWN] = $daycnt;
                }
                if( isset( $recur[IcalInterface::BYMONTHDAY] )) {
                    --$monthCnt;
                    $dayCnts[$m][$d][self::$MONTHCNT_DOWN] = $monthCnt;
                }
                if( isset( $recur[IcalInterface::BYDAY] )) {
                    $day                                     = $dayCnts[$m][$d][IcalInterface::DAY];
                    --$weekDayCnt[$day];
                    $dayCnts[$m][$d][self::$MONTHDAYNO_DOWN] = $weekDayCnt[$day];
                    --$yearDayCnt[$day];
                    $dayCnts[$m][$d][self::$YEARDAYNO_DOWN]  = $yearDayCnt[$day];
                }
                if( isset( $recur[IcalInterface::BYWEEKNO] ) ||
                    ( $recur[IcalInterface::FREQ] === IcalInterface::WEEKLY )) {
                    $dayCnts[$m][$d][self::$WEEKNO_DOWN] =
                        ((int) $dayCnts[$m][$d][self::$WEEKNO_UP] - (int) $weekNo - 1 );
                }
            } // end for( $d = $daysInMonth; $d > 0; $d-- )
        } // end for( $m = 12; $m > 0; $m-- )
        return $dayCnts;
    }

    /**
     * Return a reformatted input date
     *
     * @param string|array|DateTime $inputDate
     * @return int[]
     * @throws Exception
     * @since  2.29.21 - 2020-01-31
     */
    private static function reFormatDate( string | array | DateTime $inputDate ) : array
    {
        static $Y = 'Y';
        static $M = 'm';
        static $D = 'd';
        static $H = 'H';
        static $I = 'i';
        static $S = 'i';
        if( is_array( $inputDate )) {
            RecurFactory2::assureIntArray( $inputDate, false );
            return $inputDate;
        }
        if( ! $inputDate instanceof DateTime ) {
            $inputDate = DateTimeFactory::factory( $inputDate );
        }
        return [
            self::$LCYEAR  => (int) $inputDate->format( $Y ),
            self::$LCMONTH => (int) $inputDate->format( $M ),
            self::$LCDAY   => (int) $inputDate->format( $D ),
            self::$LCHOUR  => (int) $inputDate->format( $H ),
            self::$LCMIN   => (int) $inputDate->format( $I ),
            self::$LCSEC   => (int) $inputDate->format( $S ),
        ];
    }

    /**
     * Return week number
     *
     * @param int $sec
     * @param int $month
     * @param int $day
     * @param int $year
     * @return int
     */
    private static function getWeekNumber(
        int $sec,
        int $month,
        int $day,
        int $year
    ) : int
    {
        static $UCW  = 'W'; // week number
        return (int) date( $UCW, (int) mktime( 0, 0, $sec, $month, $day, $year ));
    }

    /**
     * Return number of days in month
     *
     * @param int $hour
     * @param int $min
     * @param int $sec
     * @param int $month
     * @param int $day
     * @param int $year
     * @return int
     */
    private static function getDaysInMonth(
        int $hour,
        int $min,
        int $sec,
        int $month,
        int $day,
        int $year
    ) : int
    {
        static $LCT  = 't'; // number of days in month
        return (int) date( $LCT, (int) mktime( $hour, $min, $sec, $month, $day, $year ));
    }

    /**
     * Return (string) 2-pos day in week
     *
     * @param int $month
     * @param int $day
     * @param int $year
     * @return string
     */
    private static function getDayInWeek(
        int $month,
        int $day,
        int $year
    ) : string
    {
        static $LCW  = 'w'; // day of week number
        $dayNo = (int) date(
            $LCW,
            (int) mktime( 0, 0, 0, $month, $day, $year )
        );
        return self::$DAYNAMES[$dayNo];
    }
}
