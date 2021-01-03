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
use DateTimeInterface;
use DateInterval;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Vcalendar;

use function array_keys;
use function count;
use function explode;
use function is_array;
use function reset;
use function substr;
use function usort;
use function var_export;

/**
 * iCalcreator EXDATE/RDATE support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.16 2020-01-24
 */
class RexdateFactory
{
    /**
     * @var array
     * @static
     */
    private static $DEFAULTVALUEDATETIME = [
        Vcalendar::VALUE => Vcalendar::DATE_TIME
    ];

    /**
     * @var string
     * @static
     */
    private static $REXDATEERR = 'Unknown %s value (#%d) : %s';

    /**
     * Return formatted output for calendar component property data value type recur
     *
     * @param array $exdateData
     * @param bool  $allowEmpty
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since 2.29.2 2019-06-23
     */
    public static function formatExdate( $exdateData, $allowEmpty )
    {
        static $SORTER1 = [
            'Kigkonsult\Icalcreator\Util\SortFactory',
            'sortExdate1',
        ];
        static $SORTER2 = [
            'Kigkonsult\Icalcreator\Util\SortFactory',
            'sortExdate2',
        ];
        $output  = null;
        $exdates = [];
        foreach(( array_keys( $exdateData )) as $ex ) {
            $theExdate = $exdateData[$ex];
            if( empty( $theExdate[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= StringFactory::createElement( Vcalendar::EXDATE );
                }
                continue;
            }
            if( 1 < count( $theExdate[Util::$LCvalue] )) {
                usort( $theExdate[Util::$LCvalue], $SORTER1 );
            }
            $exdates[] = $theExdate;
        } // end foreach
        if( 1 < count( $exdates )) {
            usort( $exdates, $SORTER2 );
        }
        foreach(( array_keys( $exdates )) as $ex ) {
            $theExdate   = $exdates[$ex];
            $isValueDate = ParameterFactory::isParamsValueSet(
                $theExdate,
                Vcalendar::DATE
            );
            $isLocalTime = isset( $theExdate[Util::$LCparams][Util::$ISLOCALTIME] );
            $content     = null;
            foreach(( array_keys( $theExdate[Util::$LCvalue] )) as $eix ) {
                $formatted  = DateTimeFactory::dateTime2Str(
                    $theExdate[Util::$LCvalue][$eix],
                    $isValueDate,
                    $isLocalTime
                );
                $content .= ( 0 < $eix ) ? Util::$COMMA . $formatted : $formatted;
            } // end foreach
            $output .= StringFactory::createElement(
                Vcalendar::EXDATE,
                ParameterFactory::createParams( $theExdate[Util::$LCparams] ),
                $content
            );
        } // end foreach(( array_keys( $exdates...
        return $output;
    }

    /**
     * Return prepared calendar component property exdate input
     *
     * @param string[]|DateTimeInterface[] $exdates
     * @param array $params
     * @return mixed array|bool
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since 2.29.16 2020-01-24
     */
    public static function prepInputExdate( $exdates, $params = null )
    {
        $output = [
            Util::$LCvalue  => [],
            Util::$LCparams => ParameterFactory::setParams(
                $params,
                self::$DEFAULTVALUEDATETIME
            )
        ];
        $isValueDate = ParameterFactory::isParamsValueSet( $output, Vcalendar::DATE );
        $paramTZid   = ParameterFactory::getParamTzid( $output );
        $forceUTC    = ( Vcalendar::UTC == $paramTZid );
        $isLocalTime = false;
        if( ! empty( $paramTZid )) {
            if( DateTimeZoneFactory::hasOffset( $paramTZid )) {
                $paramTZid = DateTimeZoneFactory::getTimeZoneNameFromOffset( $paramTZid );
            }
            else {
                DateTimeZoneFactory::assertDateTimeZone( $paramTZid );
            }
        }
        foreach(( array_keys( $exdates )) as $eix ) {
            $theExdate = DateTimeFactory::cnvrtDateTimeInterface( $exdates[$eix] );
            $wDate     = null;
            switch( true ) {
                case ( $theExdate instanceof DateTime ) :
                    $wDate = DateTimeFactory::conformDateTime(
                        $theExdate,
                        $isValueDate,
                        $forceUTC,
                        $paramTZid
                    );
                    break;
                case ( DateTimeFactory::isStringAndDate( $theExdate )) : // ex. 2006-08-03 10:12:18
                    $wDate = DateTimeFactory::conformStringDate(
                        $theExdate,
                        $isValueDate,
                        $forceUTC,
                        $isLocalTime,
                        $paramTZid
                    );
                    break;
                default:
                    throw new InvalidArgumentException(
                        sprintf(
                            self::$REXDATEERR,
                            Vcalendar::EXDATE,
                            $eix,
                            var_export( $theExdate, true )
                        )
                    );
                    break;
            } // end switch
            $output[Util::$LCvalue][] = $wDate;
        } // end foreach(( array_keys( $exdates...
        if( 0 >= count( $output[Util::$LCvalue] )) {
            return false;
        }
        DateTimeFactory::conformDateTimeParams(
            $output[Util::$LCparams], $isValueDate, $isLocalTime, $paramTZid
        );
        return $output;
    }

    /**
     * Return formatted output for calendar component property rdate
     *
     * @param array  $rdateData
     * @param bool   $allowEmpty
     * @param string $compType
     * @return string
     * @static
     * @throws Exception
     * @since  2.29.2 - 2019-06-27
     */
    public static function formatRdate( $rdateData, $allowEmpty, $compType )
    {
        static $SORTER1 = [
            'Kigkonsult\Icalcreator\Util\SortFactory',
            'sortRdate1',
        ];
        static $SORTER2 = [
            'Kigkonsult\Icalcreator\Util\SortFactory',
            'sortRdate2',
        ];
        $utcTime     = Util::isCompInList( $compType, Vcalendar::$TZCOMPS );
        $output      = null;
        $rDates      = [];
        foreach(( array_keys( $rdateData )) as $rpix ) {
            $theRdate    = $rdateData[$rpix];
            if( empty( $theRdate[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= StringFactory::createElement( Vcalendar::RDATE );
                }
                continue;
            }
            if( $utcTime ) {
                unset( $theRdate[Util::$LCparams][Vcalendar::TZID] );
            }
            if( 1 < count( $theRdate[Util::$LCvalue] )) {
                usort( $theRdate[Util::$LCvalue], $SORTER1 );
            }
            $rDates[] = $theRdate;
        } // end foreach
        if( 1 < count( $rDates )) {
            usort( $rDates, $SORTER2 );
        }
        foreach(( array_keys( $rDates )) as $rpix ) {
            $theRdate    = $rDates[$rpix];
            $isValueDate = ParameterFactory::isParamsValueSet(
                $theRdate,
                Vcalendar::DATE
            );
            $isLocalTime = isset( $theRdate[Util::$LCparams][Util::$ISLOCALTIME] );
            $attributes  = ParameterFactory::createParams( $theRdate[Util::$LCparams] );
            $cnt         = count( $theRdate[Util::$LCvalue] );
            $content     = null;
            $rno         = 1;
            foreach(( array_keys( $theRdate[Util::$LCvalue] )) as $rix ) {
                $rdatePart   = $theRdate[Util::$LCvalue][$rix];
                $contentPart = null;
                if( is_array( $rdatePart ) &&
                    ParameterFactory::isParamsValueSet( $theRdate, Vcalendar::PERIOD )) {
                    // PERIOD part 1
                    $contentPart  = DateTimeFactory::dateTime2Str(
                        $rdatePart[0],
                        $isValueDate,
                        $isLocalTime
                    );
                    $contentPart .= '/';
                    // PERIOD part 2
                    if( DateIntervalFactory::isDateIntervalArrayInvertSet( $rdatePart[1] )) { // fix pre 7.0.5 bug
                        try {
                            $dateInterval =
                                DateIntervalFactory::DateIntervalArr2DateInterval(
                                    $rdatePart[1]
                                );
                        }
                        catch( Exception $e ) {
                            throw $e;
                        }
                        $contentPart .= DateIntervalFactory::dateInterval2String(
                            $dateInterval
                        );
                    }
                    else { // date-time
                        $contentPart .=
                            DateTimeFactory::dateTime2Str(
                                $rdatePart[1],
                                $isValueDate,
                                $isLocalTime
                            );
                    }

                } // PERIOD end
                else { // SINGLE date start
                    $contentPart = DateTimeFactory::dateTime2Str(
                        $rdatePart,
                        $isValueDate,
                        $isLocalTime
                    );
                }
                $content .= $contentPart;
                if( $rno < $cnt ) {
                    $content .= Util::$COMMA;
                }
                $rno++;
            } // end foreach(( array_keys( $theRdate[Util::$LCvalue]...
            $output .= StringFactory::createElement(
                Vcalendar::RDATE,
                $attributes,
                $content
            );
        } // foreach(( array_keys( $rDates...
        return $output;
    }

    /**
     * Return value and parameters from parsed row and propAttr
     *
     * @param string $row
     * @param array $propAttr
     * @return array
     * @since  2.27.11 - 2019-01-04
     */
    public static function parseRexdate( $row, array $propAttr )
    {
        static $SS = '/';
        if( empty( $row )) {
            return [ null, $propAttr ];
        }
        $values = explode( Util::$COMMA, $row );
        foreach( $values as $vix => $value ) {
            if( false === strpos( $value, $SS )) {
                continue;
            }
            $value2 = explode( $SS, $value );
            if( 1 < count( $value2 )) {
                $values[$vix] = $value2;
            }
        } // end foreach
        return [ $values, $propAttr ];
    }

    /**
     * Return prepared calendar component property rdate input
     *
     * @param array  $rDates
     * @param array  $params
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     * @static
     * @since 2.29.16 2020-01-24
     */
    public static function prepInputRdate( array $rDates, $params=null )
    {
        $output    = [
            Util::$LCparams => ParameterFactory::setParams(
                $params,
                self::$DEFAULTVALUEDATETIME
            )
        ];
        $isValuePeriod = ParameterFactory::isParamsValueSet(
            $output,
            Vcalendar::PERIOD
        );
        $isValueDate   = ParameterFactory::isParamsValueSet(
            $output,
            Vcalendar::DATE
        );
        $isLocalTime   = isset( $params[Util::$ISLOCALTIME] );
        if( $isLocalTime ) {
            $isValuePeriod = $isValueDate = false;
            $paramTZid = Vcalendar::UTC;
        }
        else {
            $paramTZid = ParameterFactory::getParamTzid( $output );
            if( ! empty( $paramTZid )) {
                if( DateTimeZoneFactory::hasOffset( $paramTZid )) {
                    $paramTZid =
                        DateTimeZoneFactory::getTimeZoneNameFromOffset( $paramTZid );
                }
                else {
                    DateTimeZoneFactory::assertDateTimeZone( $paramTZid );
                }
            }
        }
        $forceUTC = ( Vcalendar::UTC == $paramTZid );
        foreach( $rDates as $rpix => $theRdate ) {
            switch( true ) {
                case $isValuePeriod : // PERIOD
                    list( $wDate, $paramTZid ) = self::getPeriod(
                        $theRdate,
                        $rpix,
                        $isValueDate,
                        $paramTZid,
                        $isLocalTime
                    );
                    $output[Util::$LCvalue][] = $wDate;
                    break;
                case ( $theRdate instanceof DateTimeInterface ) : // SINGLE DateTime
                    $theRdate = DateTimeFactory::cnvrtDateTimeInterface( $theRdate );
                    $output[Util::$LCvalue][] = DateTimeFactory::conformDateTime(
                        $theRdate, $isValueDate, $forceUTC, $paramTZid
                    );
                    break;
                case ( DateTimeFactory::isStringAndDate( $theRdate )) : // SINGLE string date(time)
                    $output[Util::$LCvalue][] = DateTimeFactory::conformStringDate(
                        $theRdate,
                        $isValueDate,
                        $forceUTC,
                        $isLocalTime,
                        $paramTZid
                    );
                    break;
                default :
                    throw new InvalidArgumentException(
                        sprintf(
                            self::$REXDATEERR,
                            Vcalendar::RDATE, $rpix,
                            var_export( $theRdate, true )
                        )
                    );
            } // end switch
        } // end foreach( $rDates as $rpix => $theRdate )
        DateTimeFactory::conformDateTimeParams(
            $output[Util::$LCparams], $isValueDate, $isLocalTime, $paramTZid
        );
        return $output;
    }

    /**
     * Return managed period (dateTime/dateTime or dateTime/dateInterval)
     *
     * @param array  $period
     * @param int    $rpix
     * @param bool   $isValueDate
     * @param string $paramTZid
     * @param bool   $isLocalTime
     * @return array
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since 2.29.16 2020-01-24
     */
    private static function getPeriod(
        array $period,
        $rpix,
        $isValueDate,
        & $paramTZid,
        & $isLocalTime
    ) {
        $forceUTC = ( Vcalendar::UTC == $paramTZid );
        $wDate    = [];
        $perX     = -1;
        foreach( $period as $rix => $rPeriod ) {
            $perX += 1;
            if( $rPeriod instanceof DateInterval ) {
                $wDate[$perX] = (array) $rPeriod; // fix pre 7.0.5 bug
                continue;
            }
            if( is_array( $rPeriod ) && ( 1 == count( $rPeriod )) &&
                DateTimeFactory::isStringAndDate( reset( $rPeriod ))) { // text-date
                $rPeriod = reset( $rPeriod );
            }
            switch( true ) {
                case ( $rPeriod instanceof DateTimeInterface ) :
                    $rPeriod = DateTimeFactory::cnvrtDateTimeInterface( $rPeriod );
                    $wDate[$perX] = DateTimeFactory::conformDateTime(
                        $rPeriod,
                        $isValueDate,
                        $forceUTC,
                        $paramTZid
                    );
                    if( empty( $paramTZid ) && ! $isLocalTime ) {
                        $paramTZid = $wDate[$perX]->getTimezone()->getName();
                    }
                    break;
                case DateIntervalFactory::isStringAndDuration( $rPeriod ) :  // string format duration
                    if( DateIntervalFactory::$P != $rPeriod[0] ) {
                        $rPeriod = substr( $rPeriod, 1 );
                    }
                    try {
                        $wDate[$perX] =
                            (array) DateIntervalFactory::conformDateInterval(
                                new DateInterval( $rPeriod )
                            );
                    }
                    catch( Exception $e ) {
                        throw $e;
                    }
                    continue 2;
                    break;
                case ( DateTimeFactory::isStringAndDate( $rPeriod )) : // text date ex. 2006-08-03 10:12:18
                    $wDate[$perX] = DateTimeFactory::conformStringDate(
                        $rPeriod,
                        $isValueDate,
                        $forceUTC,
                        $isLocalTime,
                        $paramTZid
                    );
                    break;
                default :
                    throw new InvalidArgumentException(
                        sprintf(
                            self::$REXDATEERR,
                            Vcalendar::RDATE,
                            $rpix,
                            var_export( $rPeriod, true )
                        )
                    );
                    break;
            } // end switch
        } // end foreach( $theRdate as $rix => $rPeriod )
        return [ $wDate, $paramTZid ];
    }
}
