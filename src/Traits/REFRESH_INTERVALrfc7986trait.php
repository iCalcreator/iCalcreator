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
namespace Kigkonsult\Icalcreator\Traits;

use DateInterval;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\DurDates;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;

/**
 * REFRESH_INTERVAL property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait REFRESH_INTERVALrfc7986trait
{
    /**
     * @var null|Pc component property REFRESH_INTERVAL value
     */
    protected ? Pc $refreshinterval = null;

    /**
     * Return formatted output for calendar component property refresh_interval
     *
     * @return string
     * @throws Exception
     * @since 2.41.55 2022-08-13
     */
    public function createRefreshinterval() : string
    {
        return DurDates::format(
            self::REFRESH_INTERVAL,
            $this->refreshinterval,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property refresh_interval
     *
     * @return bool
     */
    public function deleteRefreshinterval() : bool
    {
        $this->refreshinterval = null;
        return true;
    }

    /**
     * Get calendar component property refresh_interval
     *
     * @param null|bool   $inclParam
     * @return bool|string|DateInterval|Pc
     * @throws Exception
     * @since 2.41.48 2022-04-29
     */
    public function getRefreshinterval( ? bool $inclParam = false ) : DateInterval | bool | string | Pc
    {
        if( empty( $this->refreshinterval )) {
            return false;
        }
        return $inclParam ? clone $this->refreshinterval : $this->refreshinterval->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.48 2022-04-29
     */
    public function isRefreshintervalSet() : bool
    {
        return ! empty( $this->refreshinterval->value );
    }

    /**
     * Set calendar component property refresh_interval, VALUE DURATION required
     *
     * @param null|string|DateInterval|Pc   $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.56 2022-08-14
     */
    public function setRefreshinterval( null|string|DateInterval|Pc $value = null, ? array $params = [] ) : static
    {
        static $FMTERR = 'Invalid %s value';
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params, [ self::VALUE => self::DURATION ] ));
        switch( true ) {
            case ( empty( $value->value )) :
                $this->assertEmptyValue( $value->value, self::REFRESH_INTERVAL );
                $this->refreshinterval = $value->setEmpty();
                return $this;
            case( $value->value instanceof DateInterval ) :
                $value->value = DateIntervalFactory::conformDateInterval( $value->value );
                break;
            case DateIntervalFactory::isStringAndDuration( $value->value ) :
                $value2 = StringFactory::trimTrailNL( $value->value );
                $value2 = DateIntervalFactory::removePlusMinusPrefix( $value2 ); // can only be positive
                try {
                    $dateInterval = new DateInterval( $value2 );
                    $value->value =
                        DateIntervalFactory::conformDateInterval( $dateInterval );
                }
                catch( Exception $e ) {
                    throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
                }
                break;
            default :
                throw new InvalidArgumentException(
                    sprintf( $FMTERR, self::REFRESH_INTERVAL )
                );
        } // end switch
        $this->refreshinterval = $value->addParam( self::VALUE, self::DURATION );
        return $this;
    }
}
