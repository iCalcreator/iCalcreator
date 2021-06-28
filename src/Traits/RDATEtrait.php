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
namespace Kigkonsult\Icalcreator\Traits;

use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\RexdateFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;

use function count;
use function is_array;
use function reset;

/**
 * RDATE property functions
 *
 * @since 2.29.2 2019-06-23
 */
trait RDATEtrait
{
    /**
     * @var array component property RDATE value
     */
    protected $rdate = null;

    /**
     * Return formatted output for calendar component property rdate
     *
     * @return string
     * @throws Exception
     */
    public function createRdate() : string
    {
        if( empty( $this->rdate )) {
            return Util::$SP0;
        }
        try {
            $res = RexdateFactory::formatRdate(
                $this->rdate,
                $this->getConfig( self::ALLOWEMPTY ),
                $this->getCompType()
            );
        }
        catch( Exception $e ) {
            throw $e;
        }
        return $res;
    }

    /**
     * Delete calendar component property rdate
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteRdate( $propDelIx = null ) : bool
    {
        if( empty( $this->rdate )) {
            unset( $this->propDelIx[self::RDATE] );
            return false;
        }
        return  self::deletePropertyM(
            $this->rdate,
            self::RDATE,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property rdate
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|array
     * @throws Exception
     * @since 2.29.2 2019-06-23
     */
    public function getRdate( $propIx = null, $inclParam = false )
    {
        if( empty( $this->rdate )) {
            unset( $this->propIx[self::RDATE] );
            return false;
        }
        $output =  self::getPropertyM(
            $this->rdate,
            self::RDATE,
            $this,
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
            foreach( $output[Util::$LCvalue] as $rIx => $rdatePart ) {
                if( is_array( $rdatePart ) && isset( $rdatePart[1] ) &&
                    DateIntervalFactory::isDateIntervalArrayInvertSet( $rdatePart[1] )) {
                    try {// fix pre 7.0.5 bug
                        $output[Util::$LCvalue][$rIx][1] =
                            DateIntervalFactory::DateIntervalArr2DateInterval( $rdatePart[1] );
                    }
                    catch( Exception $e ) {
                        throw $e;
                    }
                }
            } // end foreach
        } // end if
        else {
            foreach( $output as $rIx => $rdatePart ) {
                if( is_array( $rdatePart ) && isset( $rdatePart[1] ) &&
                    DateIntervalFactory::isDateIntervalArrayInvertSet( $rdatePart[1] )) {
                    try { // fix pre 7.0.5 bug
                        $output[$rIx][1] =
                            DateIntervalFactory::DateIntervalArr2DateInterval( $rdatePart[1] );
                    }
                    catch( Exception $e ) {
                        throw $e;
                    }
                }
            } // end foreach
        } // end else
        return $output;
    }

    /**
     * Set calendar component property rdate
     *
     * @param null|array   $value
     * @param null|array   $params
     * @param null|integer $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.2 2019-06-23
     */
    public function setRdate( $value = null, $params = [], $index = null ) : self
    {
        if( empty( $value ) ||
            ( is_array( $value) && ( 1 == count( $value )) && empty( reset( $value )))
        ) {
            $this->assertEmptyValue( $value, self::RDATE );
             self::setMval( $this->rdate, Util::$SP0, [], null, $index );
            return $this;
        }
        $value = self::checkSingleRdates(
            $value,
            ParameterFactory::isParamsValueSet(
                [ Util::$LCparams => $params ],
                self::PERIOD
            )
        );
        if( Util::isCompInList( $this->getCompType(), Vcalendar::$TZCOMPS )) {
            $params[Util::$ISLOCALTIME] = true;
        }
        try {
            $input = RexdateFactory::prepInputRdate( $value, $params );
        }
        catch( Exception $e ) {
            throw $e;
        }
         self::setMval(
            $this->rdate,
            $input[Util::$LCvalue],
            $input[Util::$LCparams],
            null,
            $index
        );
        return $this;
    }

    /**
     * Return Rdates is single input
     *
     * @param string|array|DateTimeInterface $rDates
     * @param bool $isPeriod
     * @return array
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    private static function checkSingleRdates( $rDates, bool $isPeriod ) : array
    {
        if( $rDates instanceof DateTimeInterface ) {
            return [ DateTimeFactory::toDateTime( $rDates ) ];
        }
        if( DateTimeFactory::isStringAndDate( $rDates )) {
            return [ $rDates ];
        }
        if( $isPeriod && is_array( $rDates ) && ( 2 == count( $rDates ))) {
            $first = reset( $rDates );
            if( $first instanceof DateTimeInterface ) {
                return [ $rDates ];
            }
            if( DateTimeFactory::isStringAndDate( $first )) {
                return [ $rDates ];
            }
        }
        return $rDates;
    }
}
