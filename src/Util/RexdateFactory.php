<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2024 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Pc;

use function count;
use function is_array;
use function reset;
use function substr;
use function var_export;

/**
 * iCalcreator EXDATE/RDATE support class
 *
 * @since 2.41.88 - 2024-01-18
 */
class RexdateFactory
{
    /**
     * @var string
     */
    public static string $REXDATEERR = 'Unknown %s value (#%d) : %s';

    /**
     * Return prepared calendar component property exdate input
     *
     * @param Pc $pc
     * @return Pc
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 - 2024-01-18
     */
    public static function prepInputExdate( Pc $pc ) : Pc
    {
        $exdates     = $pc->getValue();
        $output      = ( clone $pc )->setValue( [] );
        $output->addParam( IcalInterface::VALUE, IcalInterface::DATE_TIME, false );
        $isValueDate = $output->hasParamValue( IcalInterface::DATE );
        $paramTZid   = $output->getParams( IcalInterface::TZID ) ?? StringFactory::$SP0;
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
        $output2 = [];
        foreach( $exdates as $eix => $theExdate ) {
            $wDate     = match( true ) {
                $theExdate instanceof DateTimeInterface => DateTimeFactory::conformDateTime(
                    DateTimeFactory::toDateTime( $theExdate ),
                    $isValueDate,
                    $forceUTC,
                    $paramTZid
                ),
                DateTimeFactory::isStringAndDate( $theExdate ) =>
                    DateTimeFactory::conformStringDate(
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
            }; // end match
            $output2[] = $wDate;
        } // end foreach(( array_keys( $exdates...
        $output->setValue( $output2 );
        if( 0 < count( $output2 )) {
            DateTimeFactory::conformDateTimeParams( $output, $isValueDate, $isLocalTime, $paramTZid );
        }
        return $output;
    }

    /**
     * Return prepared calendar component property rdate input
     *
     * @param Pc $input
     * @return Pc
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.88 - 2024-01-18
     */
    public static function prepInputRdate( Pc $input ) : Pc
    {
        $rDates = $input->getValue();
        $output = $input->setValue( [] );
        $output->addParam( IcalInterface::VALUE, IcalInterface::DATE_TIME, false );
        $isValuePeriod = $output->hasParamValue( IcalInterface::PERIOD );
        $isValueDate   = $output->hasParamValue( IcalInterface::DATE );
        $isLocalTime   = $output->hasParamIsLocalTime();
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
        $output2  = [];
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
                    $output2[] = $wDate;
                    break;
                case ( $theRdate instanceof DateTimeInterface ) : // SINGLE DateTime
                    $output2[] = DateTimeFactory::conformDateTime(
                        DateTimeFactory::toDateTime( $theRdate ),
                        $isValueDate,
                        $forceUTC,
                        $paramTZid
                    );
                    break;
                case DateTimeFactory::isStringAndDate( $theRdate ) : // SINGLE string date(time)
                    $output2[] = DateTimeFactory::conformStringDate(
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
        $output->setValue( $output2 );
        DateTimeFactory::conformDateTimeParams( $output, $isValueDate, $isLocalTime, $paramTZid );
        return $output;
    }

    /**
     * Return processed period (dateTime/dateTime or dateTime/dateInterval)
     *
     * @param array  $period
     * @param int    $rpix
     * @param bool   $isValueDate
     * @param string $paramTZid
     * @param bool   $isLocalTime
     * @return array
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.57 2022-08-18
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
            if( is_array( $rPeriod ) &&
                ( 1 === count( $rPeriod )) &&
                DateTimeFactory::isStringAndDate( reset( $rPeriod ))) { // text date ex. 2006-08-03 10:12:18
                $rPeriod = reset( $rPeriod );
            }
            $wDate[$perX] = match( true ) {
                ( $rPeriod instanceof DateTimeInterface ) =>
                    self::getPeriodDateTime(
                        $rPeriod,
                        $isValueDate,
                        $forceUTC,
                        $isLocalTime,
                        $paramTZid
                    ),
                DateIntervalFactory::isStringAndDuration( $rPeriod ) =>  // string format duration
                    self::getPeriodStringDuration( $rPeriod ),
                DateTimeFactory::isStringAndDate( $rPeriod ) => // text date ex. 2006-08-03 10:12:18
                    DateTimeFactory::conformStringDate(
                        $rPeriod,
                        $isValueDate,
                        $forceUTC,
                        $isLocalTime,
                        $paramTZid
                    ),
                default =>
                    throw new InvalidArgumentException(
                        sprintf( self::$REXDATEERR, IcalInterface::RDATE, $rpix, var_export( $rPeriod, true ))
                    )
            }; // end match
        } // end foreach( $theRdate as $rix => $rPeriod )
        return [ $wDate, $paramTZid ];
    }

    /**
     * @param DateTimeInterface $rPeriod
     * @param bool     $isValueDate
     * @param bool     $forceUTC
     * @param bool     $isLocalTime
     * @param string   $paramTZid
     * @return DateTimeInterface
     * @throws Exception
     */
    private static function getPeriodDateTime(
        DateTimeInterface $rPeriod,
        bool $isValueDate,
        bool $forceUTC,
        bool $isLocalTime,
        string & $paramTZid
    ) : DateTimeInterface
    {
        $output = DateTimeFactory::conformDateTime(
            DateTimeFactory::toDateTime( $rPeriod ),
            $isValueDate,
            $forceUTC,
            $paramTZid
        );
        if( empty( $paramTZid ) && ! $isLocalTime ) {
            $paramTZid = $output->getTimezone()->getName();
        }
        return $output;
    }

    /**
     * @param string $rPeriod
     * @return DateInterval
     * @throws Exception
     */
    private static function getPeriodStringDuration( string $rPeriod ) : DateInterval
    {
        if( DateIntervalFactory::$P !== $rPeriod[0] ) {
            $rPeriod = substr( $rPeriod, 1 );
        }
        return DateIntervalFactory::conformDateInterval( new DateInterval( $rPeriod ));
    }
}
