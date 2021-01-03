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

namespace Kigkonsult\Icalcreator\Traits;

use DateTime;
use DateTimeInterface;
use DateInterval;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

use function count;
use function in_array;
use function is_array;
use function reset;
use function sprintf;
use function usort;
use function var_export;

/**
 * FREEBUSY property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @throws InvalidArgumentException
 * @since 2.29.16 2020-01-24
 */
trait FREEBUSYtrait
{
    /**
     * @var array component property FREEBUSY value
     */
    protected $freebusy = null;

    /**
     * @var array FREEBUSY param keywords
     * @static
     */
    protected static $FREEBUSYKEYS = [
        self::FREE,
        self::BUSY,
        self::BUSY_UNAVAILABLE,
        self::BUSY_TENTATIVE
    ];

    /**
     * Return formatted output for calendar component property freebusy
     *
     * @return string
     * @throws Exception
     * @since 2.29.2 2019-06-27
     */
    public function createFreebusy()
    {
        static $FMT = ';FBTYPE=%s';
        static $SORTER = [ 'Kigkonsult\Icalcreator\Util\SortFactory', 'sortRdate1' ];
        if( empty( $this->freebusy )) {
            return null;
        }
        $output = null;
        foreach( $this->freebusy as $fx => $freebusyPart ) {
            if( empty( $freebusyPart[Util::$LCvalue] ) ||
                (( 1 == count( $freebusyPart[Util::$LCvalue] )) &&
                    isset( $freebusyPart[Util::$LCvalue][self::FBTYPE] ))) {
                if( $this->getConfig( self::ALLOWEMPTY )) {
                    $output .= StringFactory::createElement( self::FREEBUSY );
                }
                continue;
            }
            $attributes = $content = null;
            if( isset( $freebusyPart[Util::$LCvalue][self::FBTYPE] )) {
                $attributes .= sprintf(
                    $FMT,
                    $freebusyPart[Util::$LCvalue][self::FBTYPE]
                );
                unset( $freebusyPart[Util::$LCvalue][self::FBTYPE] );
                $freebusyPart[Util::$LCvalue] =
                    array_values( $freebusyPart[Util::$LCvalue] );
            }
            else {
                $attributes .= sprintf( $FMT, self::BUSY );
            }
            $attributes .= ParameterFactory::createParams(
                $freebusyPart[Util::$LCparams]
            );
            $fno         = 1;
            $cnt         = count( $freebusyPart[Util::$LCvalue] );
            if( 1 < $cnt ) {
                usort( $freebusyPart[Util::$LCvalue], $SORTER );
            }
            foreach( $freebusyPart[Util::$LCvalue] as $periodix => $freebusyPeriod ) {
                $content .= DateTimeFactory::dateTime2Str( $freebusyPeriod[0] );
                $content .= Util::$SLASH;
                if( DateIntervalFactory::isDateIntervalArrayInvertSet( $freebusyPeriod[1] )) { // fix pre 7.0.5 bug
                    try {
                        $dateInterval =
                            DateIntervalFactory::DateIntervalArr2DateInterval( $freebusyPeriod[1] );
                    }
                    catch( Exception $e ) {
                        throw $e;
                    }
                        // period=  -> duration
                    $content .= DateIntervalFactory::dateInterval2String( $dateInterval );
                }
                else {  // period=  -> date-time
                    $content .= DateTimeFactory::dateTime2Str( $freebusyPeriod[1] );
                }
                if( $fno < $cnt ) {
                    $content .= Util::$COMMA;
                }
                $fno++;
            } // end foreach
            $output .= StringFactory::createElement(
                self::FREEBUSY,
                $attributes,
                $content
            );
        } // end foreach( $this->freebusy as $fx => $freebusyPart )
        return $output;
    }

    /**
     * Delete calendar component property freebusy
     *
     * @param int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteFreebusy( $propDelIx = null )
    {
        if( empty( $this->freebusy )) {
            unset( $this->propDelIx[self::FREEBUSY] );
            return false;
        }
        return $this->deletePropertyM( $this->freebusy, self::FREEBUSY, $propDelIx );
    }

    /**
     * Get calendar component property freebusy
     *
     * @param int    $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|array
     * @throws Exception
     * @since 2.29.2 2019-06-27
     */
    public function getFreebusy( $propIx = null, $inclParam = false )
    {
        if( empty( $this->freebusy )) {
            unset( $this->propIx[self::FREEBUSY] );
            return false;
        }
        $output = $this->getPropertyM(
            $this->freebusy,
            self::FREEBUSY,
            $propIx,
            $inclParam
        );
        if( empty( $output )) {
            return false;
        }
        if( empty( $output[Util::$LCvalue] )) {
            return $output;
        }
        if( isset( $output[Util::$LCvalue] )) {
            foreach( $output[Util::$LCvalue] as $perIx => $freebusyPeriod ) {
                if( DateIntervalFactory::isDateIntervalArrayInvertSet( $freebusyPeriod[1] )) { // fix pre 7.0.5 bug
                    try {
                        $output[Util::$LCvalue][$perIx][1] =
                            DateIntervalFactory::DateIntervalArr2DateInterval( $freebusyPeriod[1] );
                    }
                    catch( Exception $e ) {
                        throw $e;
                    }
                }
            } // end foreach
        }
        else {
            foreach( $output as $perIx => $freebusyPeriod ) {
                if( DateIntervalFactory::isDateIntervalArrayInvertSet( $freebusyPeriod[1] )) { // fix pre 7.0.5 bug
                    try {
                        $output[$perIx][1] =
                            DateIntervalFactory::DateIntervalArr2DateInterval( $freebusyPeriod[1] );
                    }
                    catch( Exception $e ) {
                        throw $e;
                    }
                }
            } // end foreach
        }
        return $output;
    }

    /**
     * Return type, value and parameters from parsed row and propAttr
     *
     * @param string $row
     * @param array  $propAttr
     * @return array
     * @since  2.27.11 - 2019-01-04
     */
    protected static function parseFreebusy( $row, array $propAttr )
    {
        static $SS = '/';
        $fbtype = $values = null;
        if( ! empty( $propAttr )) {
            foreach( $propAttr as $k => $v ) {
                if( 0 == strcasecmp( self::FBTYPE, $k )) {
                    $fbtype = $v;
                    unset( $propAttr[$k] );
                    break;
                }
            }
        }
        if( ! empty( $row )) {
            $values = explode( Util::$COMMA, $row );
            foreach( $values as $vix => $value ) {
                $value2 = explode( $SS, $value ); // '/'
                if( 1 < count( $value2 )) {
                    $values[$vix] = $value2;
                }
            }
        }
        return [ $fbtype, $values, $propAttr, ];
    }

    /**
     * Set calendar component property freebusy
     *
     * @param string  $fbType
     * @param string|DateTimeInterface|array $fbValues
     * @param array   $params
     * @param integer $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     * @todo Applications MUST treat x-name and iana-token values they don't recognize the same way as they would the BUSY value.
     */
    public function setFreebusy(
        $fbType = null,
        $fbValues = null,
        $params = [],
        $index = null
    ) {
        static $ERR2 = 'Unknown (%d) freebusy value (#%d/%d) : \'%s\'';
        if( empty( $fbValues )) {
            $this->assertEmptyValue( $fbValues, self::FREEBUSY );
            $this->setMval($this->freebusy, Util::$SP0, [],null, $index );
            return $this;
        }
        $fbType = ( empty( $fbType )) ? self::BUSY : strtoupper( $fbType );
        if( ! in_array( $fbType, self::$FREEBUSYKEYS ) &&
            ! StringFactory::isXprefixed( $fbType )) {
            $fbType = self::BUSY;
        }
        $input    = [ self::FBTYPE => $fbType ];
        $fbValues = self::checkSingleValues( $fbValues );
        foreach( $fbValues as $fbix1 => $fbPeriod ) {     // periods => period
            if( empty( $fbPeriod )) {
                continue;
            }
            $freebusyPeriod = [];
            foreach( $fbPeriod as $fbix2 => $fbMember ) { // pairs => singlepart
                $fbMember = DateTimeFactory::cnvrtDateTimeInterface( $fbMember );
                switch( true ) {
                    case ( $fbMember instanceof DateTime ) :     // datetime
                        $freebusyPeriod[$fbix2] =
                            DateTimeFactory::setDateTimeTimeZone( $fbMember, Vcalendar::UTC );
                        break;
                    case ( $fbMember instanceof DateInterval ) : // interval
                        $freebusyPeriod[$fbix2] = (array) $fbMember; // fix pre 7.0.5 bug
                        break;
                    case ( DateTimeFactory::isStringAndDate( $fbMember )) :   // text date ex. 2006-08-03 10:12:18
                        list( $dateStr, $timezonePart ) =
                            DateTimeFactory::splitIntoDateStrAndTimezone( $fbMember );
                        $dateTime = DateTimeFactory::getDateTimeWithTimezoneFromString(
                            $dateStr,
                            $timezonePart,
                            Vcalendar::UTC,
                            true
                        );
                        $dateTime = DateTimeFactory::setDateTimeTimeZone(
                            $dateTime, Vcalendar::UTC
                        );
                        $freebusyPeriod[$fbix2] = $dateTime;
                        break;
                    case DateIntervalFactory::isStringAndDuration( $fbMember ) : // duration string
                        $fbMember = DateIntervalFactory::removePlusMinusPrefix( $fbMember ); // can only be positive
                        try {  // fix pre 7.0.5 bug
                            $freebusyPeriod[$fbix2] =
                                (array) DateIntervalFactory::conformDateInterval(
                                    DateIntervalFactory::factory( $fbMember )
                                );
                        }
                        catch( Exception $e ) {
                            throw $e;
                        }
                        break;
                    default :
                        throw new InvalidArgumentException(
                            sprintf( $ERR2, 2, $fbix1, $fbix2, var_export( $fbMember, true ))
                        );
                        break;
                } // end switch
            } // end foreach
            $input[] = $freebusyPeriod;
        }
        $this->setMval( $this->freebusy, $input, $params, null, $index );
        return $this;
    }

    /**
     * Check for single values and , if so, put into array
     *
     * @param array $fbValues
     * @return array
     * @since 2.29.16 2020-01-24
     */
    private static function checkSingleValues( array $fbValues )
    {
        if( ! is_array( $fbValues )) {
            return $fbValues;
        }
        if( 2 != count( $fbValues )) {
            return $fbValues;
        }
        $first = reset( $fbValues );
        if( $first instanceof DateTimeInterface ) {
            return [ $fbValues ];
        }
        if( DateTimeFactory::isStringAndDate( $first )) {
            return [ $fbValues ];
        }
        return $fbValues;
    }
}
