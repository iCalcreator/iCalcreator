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
use DateInterval;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Pc;
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
 * @since 2.29.16 2020-01-24
 */
class RexdateFactory
{
    /**
     * @var string
     */
    private static string $REXDATEERR = 'Unknown %s value (#%d) : %s';

    /**
     * Return formatted output for calendar component property data value type recur
     *
     * @param Pc[] $exdateData
     * @param bool    $allowEmpty
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.2 2019-06-23
     */
    public static function formatExdate( array $exdateData, bool $allowEmpty ) : string
    {
        static $SORTER1 = [ SortFactory::class, 'sortExdate1', ];
        static $SORTER2 = [ SortFactory::class, 'sortExdate2', ];
        $output  = Util::$SP0;
        $exdates = [];
        foreach(( array_keys( $exdateData )) as $ex ) {
            $theExdate = $exdateData[$ex]; // Pc
            if( empty( $theExdate->value )) {
                if( $allowEmpty ) {
                    $output .= StringFactory::createElement( IcalInterface::EXDATE );
                }
                continue;
            }
            if( 1 < count( $theExdate->value )) {
                usort( $theExdate->value, $SORTER1 );
            }
            $exdates[] = $theExdate;
        } // end foreach
        if( 1 < count( $exdates )) {
            usort( $exdates, $SORTER2 );
        }
        foreach(( array_keys( $exdates )) as $ex ) {
            $theExdate   = $exdates[$ex]; // Pc
            $content     = Util::$SP0;
            foreach(( array_keys( $theExdate->value )) as $eix ) {
                $formatted  = DateTimeFactory::dateTime2Str(
                    $theExdate->value[$eix],
                    $theExdate->hasParamValue(IcalInterface::DATE ),
                    $theExdate->hasParamKey( Util::$ISLOCALTIME )
                );
                $content .= ( 0 < $eix ) ? Util::$COMMA . $formatted : $formatted;
            } // end foreach
            $output .= StringFactory::createElement(
                IcalInterface::EXDATE,
                ParameterFactory::createParams( $theExdate->params ),
                $content
            );
        } // end foreach(( array_keys( $exdates...
        return $output;
    }

    /**
     * Return prepared calendar component property exdate input
     *
     * @param Pc $pc
     * @return Pc
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public static function prepInputExdate( Pc $pc ) : Pc
    {
        $exdates     = $pc->value;
        $output      = ( clone $pc )->setValue( [] );
        $output->addParam( IcalInterface::VALUE, IcalInterface::DATE_TIME, false );
        $isValueDate = $output->hasParamValue( IcalInterface::DATE );
        $paramTZid   = $output->getParams( IcalInterface::TZID ) ?? '';
        $forceUTC    = ( IcalInterface::UTC === $paramTZid );
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
            $theExdate = $exdates[$eix];
            $wDate     = match ( true ) {
                $theExdate instanceof DateTimeInterface => DateTimeFactory::conformDateTime(
                    DateTimeFactory::toDateTime( $theExdate ),
                    $isValueDate,
                    $forceUTC,
                    $paramTZid
                ),
                DateTimeFactory::isStringAndDate( $theExdate ) => DateTimeFactory::conformStringDate(
                    $theExdate,
                    $isValueDate,
                    $forceUTC,
                    $isLocalTime,
                    $paramTZid
                ),
                default => throw new InvalidArgumentException(
                    sprintf(
                        self::$REXDATEERR,
                        IcalInterface::EXDATE,
                        $eix,
                        var_export( $theExdate, true )
                    )
                ),
            }; // end switch
            $output->value[] = $wDate;
        } // end foreach(( array_keys( $exdates...
        if( 0 < count( $output->value )) {
            DateTimeFactory::conformDateTimeParams( $output, $isValueDate, $isLocalTime, $paramTZid );
        }
        return $output;
    }

    /**
     * Return formatted output for calendar component property rdate
     *
     * @param Pc[]   $rdateData
     * @param bool   $allowEmpty
     * @param string $compType
     * @return string
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public static function formatRdate( array $rdateData, bool $allowEmpty, string $compType ) : string
    {
        static $SORTER1 = [ SortFactory::class, 'sortRdate1' ];
        static $SORTER2 = [ SortFactory::class, 'sortRdate2' ];
        $utcTime     = Util::isCompInList( $compType, Vcalendar::$TZCOMPS );
        $output      = Util::$SP0;
        $rDates      = [];
        foreach(( array_keys( $rdateData )) as $rpix ) {
            $theRdate    = $rdateData[$rpix]; // Pc
            if( empty( $theRdate->value )) {
                if( $allowEmpty ) {
                    $output .= StringFactory::createElement( IcalInterface::RDATE );
                }
                continue;
            }
            if( $utcTime ) {
                $theRdate->removeParam( IcalInterface::TZID );
            }
            if( 1 < count( $theRdate->value )) {
                usort( $theRdate->value, $SORTER1 );
            }
            $rDates[] = $theRdate;
        } // end foreach
        if( 1 < count( $rDates )) {
            usort( $rDates, $SORTER2 );
        }
        foreach(( array_keys( $rDates )) as $rpix ) {
            $theRdate    = $rDates[$rpix]; // Pc
            $isValueDate = $theRdate->hasParamValue( IcalInterface::DATE );
            $isLocalTime = $theRdate->hasParamKey( Util::$ISLOCALTIME );
            $attributes  = ParameterFactory::createParams( $theRdate->params );
            $cnt         = count( $theRdate->value );
            $content     = Util::$SP0;
            $rno         = 1;
            foreach(( array_keys( $theRdate->value )) as $rix ) {
                $rdatePart = $theRdate->value[$rix];
                if( is_array( $rdatePart ) && $theRdate->hasParamValue( IcalInterface::PERIOD )) {
                    // PERIOD part 1
                    $contentPart  = DateTimeFactory::dateTime2Str( $rdatePart[0], $isValueDate, $isLocalTime );
                    $contentPart .= '/';
                    // PERIOD part 2
                    if( $rdatePart[1] instanceof DateInterval ) {
                        $contentPart .= DateIntervalFactory::dateInterval2String( $rdatePart[1] );
                    }
                    else { // date-time
                        $contentPart .= DateTimeFactory::dateTime2Str( $rdatePart[1], $isValueDate, $isLocalTime );
                    }

                } // PERIOD end
                else { // SINGLE date start
                    $contentPart = DateTimeFactory::dateTime2Str( $rdatePart, $isValueDate, $isLocalTime );
                }
                $content .= $contentPart;
                if( $rno < $cnt ) {
                    $content .= Util::$COMMA;
                }
                $rno++;
            } // end foreach(( array_keys( $theRdate[Util::$LCvalue]...
            $output .= StringFactory::createElement(
                IcalInterface::RDATE,
                $attributes,
                $content
            );
        } // foreach(( array_keys( $rDates...
        return $output;
    }

    /**
     * Return value and parameters from parsed row and propAttr
     *
     * @param string  $row
     * @param mixed[] $propAttr
     * @return mixed[]
     * @since  2.27.11 - 2019-01-04
     */
    public static function parseRexdate( string $row, array $propAttr ) : array
    {
        static $SS = '/';
        if( empty( $row )) {
            return [ null, $propAttr ];
        }
        $values = explode( Util::$COMMA, $row );
        foreach( $values as $vix => $value ) {
            if( ! str_contains( $value, $SS ) ) {
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
     * @param Pc $input
     * @return Pc
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public static function prepInputRdate( Pc $input ) : Pc
    {
        $rDates = $input->value;
        $output = $input->setValue( [] );
        $output->addParam( IcalInterface::VALUE, IcalInterface::DATE_TIME, false );
        $isValuePeriod = $output->hasParamValue( IcalInterface::PERIOD );
        $isValueDate   = $output->hasParamValue( IcalInterface::DATE );
        $isLocalTime   = $output->hasParamKey( Util::$ISLOCALTIME );
        if( $isLocalTime ) {
            $isValuePeriod = $isValueDate = false;
            $paramTZid = IcalInterface::UTC;
        }
        else {
            $paramTZid = $output->getParams( IcalInterface::TZID ) ?? '';
            if( ! empty( $paramTZid )) {
                if( DateTimeZoneFactory::hasOffset( $paramTZid )) {
                    $paramTZid = DateTimeZoneFactory::getTimeZoneNameFromOffset( $paramTZid );
                }
                else {
                    DateTimeZoneFactory::assertDateTimeZone( $paramTZid );
                }
            }
        }
        $forceUTC = ( IcalInterface::UTC === $paramTZid );
        foreach( $rDates as $rpix => $theRdate ) {
            switch( true ) {
                case $isValuePeriod : // PERIOD
                    [ $wDate, $paramTZid ] = self::getPeriod(
                        $theRdate,
                        $rpix,
                        $isValueDate,
                        $paramTZid,
                        $isLocalTime
                    );
                    $output->value[] = $wDate;
                    break;
                case ( $theRdate instanceof DateTimeInterface ) : // SINGLE DateTime
                    $output->value[] = DateTimeFactory::conformDateTime(
                        DateTimeFactory::toDateTime( $theRdate ),
                        $isValueDate,
                        $forceUTC,
                        $paramTZid
                    );
                    break;
                case ( DateTimeFactory::isStringAndDate( $theRdate )) : // SINGLE string date(time)
                    $output->value[] = DateTimeFactory::conformStringDate(
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
                            IcalInterface::RDATE, $rpix,
                            var_export( $theRdate, true )
                        )
                    );
            } // end switch
        } // end foreach( $rDates as $rpix => $theRdate )
        DateTimeFactory::conformDateTimeParams( $output, $isValueDate, $isLocalTime, $paramTZid );
        return $output;
    }

    /**
     * Return managed period (dateTime/dateTime or dateTime/dateInterval)
     *
     * @param mixed[] $period
     * @param int     $rpix
     * @param bool    $isValueDate
     * @param string  $paramTZid
     * @param bool    $isLocalTime
     * @return mixed[]
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.40 2021-10-04
     */
    private static function getPeriod(
        array $period,
        int $rpix,
        bool $isValueDate,
        string & $paramTZid,
        bool & $isLocalTime
    ) : array
    {
        $forceUTC = ( IcalInterface::UTC === $paramTZid );
        $wDate    = [];
        $perX     = -1;
        foreach( $period as $rPeriod ) {
            ++$perX;
            if( $rPeriod instanceof DateInterval ) {
                $wDate[$perX] = $rPeriod;
                continue;
            }
            if( is_array( $rPeriod ) && ( 1 === count( $rPeriod )) &&
                DateTimeFactory::isStringAndDate( reset( $rPeriod ))) { // text-date
                $rPeriod = reset( $rPeriod );
            }
            switch( true ) {
                case ( $rPeriod instanceof DateTimeInterface ) :
                    $wDate[$perX] = DateTimeFactory::conformDateTime(
                        DateTimeFactory::toDateTime( $rPeriod ),
                        $isValueDate,
                        $forceUTC,
                        $paramTZid
                    );
                    if( empty( $paramTZid ) && ! $isLocalTime ) {
                        $paramTZid = $wDate[$perX]->getTimezone()->getName();
                    }
                    break;
                case DateIntervalFactory::isStringAndDuration( $rPeriod ) :  // string format duration
                    if( DateIntervalFactory::$P !== $rPeriod[0] ) {
                        $rPeriod = substr( $rPeriod, 1 );
                    }
                    $wDate[$perX] =
                        DateIntervalFactory::conformDateInterval(
                            new DateInterval( $rPeriod )
                        );
                    continue 2;
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
                            IcalInterface::RDATE,
                            $rpix,
                            var_export( $rPeriod, true )
                        )
                    );
            } // end switch
        } // end foreach( $theRdate as $rix => $rPeriod )
        return [ $wDate, $paramTZid ];
    }
}
