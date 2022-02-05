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
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * REFRESH_INTERVAL property functions
 *
 * @since 2.29.5 2019-06-29
 */
trait REFRESH_INTERVALrfc7986trait
{
    /**
     * @var null|mixed[] component property REFRESH_INTERVAL value
     */
    protected ? array $refreshinterval = null;

    /**
     * Return formatted output for calendar component property refresh_interval
     *
     * @return string
     * @throws Exception
     * @since 2.40 2021-10-04
     */
    public function createRefreshinterval() : string
    {
        if( empty( $this->refreshinterval )) {
            return Util::$SP0;
        }
        if( empty( $this->refreshinterval[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::REFRESH_INTERVAL )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::REFRESH_INTERVAL,
            ParameterFactory::createParams( $this->refreshinterval[Util::$LCparams] ),
            DateIntervalFactory::dateInterval2String( $this->refreshinterval[Util::$LCvalue] )
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
     * @return bool|string|DateInterval|mixed[]
     * @throws Exception
     * @since 2.43 2021-10-30
     */
    public function getRefreshinterval( ? bool $inclParam = false ) : DateInterval | bool | string | array
    {
        if( empty( $this->refreshinterval )) {
            return false;
        }
        return $inclParam
            ? $this->refreshinterval
            : $this->refreshinterval[Util::$LCvalue];
    }

    /**
     * Set calendar component property refresh_interval
     *
     * @param null|string|DateInterval   $value
     * @param null|mixed[]  $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.40 2021-10-04
     */
    public function setRefreshinterval( null|string|DateInterval $value = null, ? array $params = [] ) : static
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
                    throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
                }
                break;
            default :
                throw new InvalidArgumentException(
                    sprintf( $FMTERR, self::REFRESH_INTERVAL )
                );
        } // end switch
        $this->refreshinterval = [
            Util::$LCvalue  => $value,
            Util::$LCparams => ParameterFactory::setParams(
                $params,
                [ self::VALUE => self::DURATION ] // required
            ),
        ];
        return $this;
    }
}
