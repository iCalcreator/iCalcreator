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

use DateInterval;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * REFRESH_INTERVAL property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.5 2019-06-29
 */
trait REFRESH_INTERVALrfc7986trait
{
    /**
     * @var array component property REFRESH_INTERVAL value
     */
    protected $refreshinterval = null;

    /**
     * Return formatted output for calendar component property refresh_interval
     *
     * @return string
     * @throws Exception
     */
    public function createRefreshinterval()
    {
        if( empty( $this->refreshinterval )) {
            return null;
        }
        if( empty( $this->refreshinterval[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::REFRESH_INTERVAL )
                : null;
        }
        if( DateIntervalFactory::isDateIntervalArrayInvertSet(
            $this->refreshinterval[Util::$LCvalue] )
        ) {
            try { // fix pre 7.0.5 bug
                $dateInterval =
                    DateIntervalFactory::DateIntervalArr2DateInterval(
                        $this->refreshinterval[Util::$LCvalue]
                    );
            }
            catch( Exception $e ) {
                throw $e;
            }
        }
        else {
            $dateInterval = $this->refreshinterval[Util::$LCvalue];
        }
        return StringFactory::createElement(
            self::REFRESH_INTERVAL,
            ParameterFactory::createParams( $this->refreshinterval[Util::$LCparams] ),
            DateIntervalFactory::dateInterval2String( $dateInterval )
        );
    }

    /**
     * Delete calendar component property refresh_interval
     *
     * @return bool
     */
    public function deleteRefreshinterval()
    {
        $this->refreshinterval = null;
        return true;
    }

    /**
     * Get calendar component property refresh_interval
     *
     * @param bool   $inclParam
     * @return bool|array
     * @throws Exception
     */
    public function getRefreshinterval( $inclParam = false )
    {
        if( empty( $this->refreshinterval )) {
            return false;
        }
        if( empty( $this->refreshinterval[Util::$LCvalue] )) {
            return ( $inclParam )
                ? $this->refreshinterval
                : $this->refreshinterval[Util::$LCvalue];
        }
        $refreshinterval = $this->refreshinterval;
        if( DateIntervalFactory::isDateIntervalArrayInvertSet(
            $refreshinterval[Util::$LCvalue] )
        ) {
            try { // fix pre 7.0.5 bug
                $refreshinterval[Util::$LCvalue] =
                    DateIntervalFactory::DateIntervalArr2DateInterval(
                        $this->refreshinterval[Util::$LCvalue]
                    );
            }
            catch( Exception $e ) {
                throw $e;
            }
        }
        return ( $inclParam ) ? $refreshinterval : $refreshinterval[Util::$LCvalue];
    }

    /**
     * Set calendar component property refresh_interval
     *
     * @param mixed $value
     * @param array $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function setRefreshinterval( $value  = null, $params = [] )
    {
        static $FMTERR = 'Invalid %s value';
        switch( true ) {
            case ( empty( $value )) :
                $this->assertEmptyValue( $value, self::REFRESH_INTERVAL );
                $this->refreshinterval = [
                    Util::$LCvalue  => Util::$SP0,
                    Util::$LCparams => []
                ];
                return $this;
                break;
            case( $value instanceof DateInterval ) :
                $value = DateIntervalFactory::conformDateInterval( $value );
                break;
            case DateIntervalFactory::isStringAndDuration( $value ) :
                $value = StringFactory::trimTrailNL( $value );
                $value = DateIntervalFactory::removePlusMinusPrefix( $value ); // can only be positive
                try {
                    $dateInterval = new DateInterval( $value );
                    $value        =
                        DateIntervalFactory::conformDateInterval( $dateInterval );
                }
                catch( Exception $e ) {
                    throw new InvalidArgumentException( $e->getMessage(), null, $e );
                }
                break;
            default :
                throw new InvalidArgumentException(
                    sprintf( $FMTERR, self::REFRESH_INTERVAL )
                );
                break;
        } // end switch
        $this->refreshinterval = [
            Util::$LCvalue  => (array) $value,  // fix pre 7.0.5 bug
            Util::$LCparams => ParameterFactory::setParams(
                $params,
                [ self::VALUE => self::DURATION ] // required
            ),
        ];
        return $this;
    }
}
