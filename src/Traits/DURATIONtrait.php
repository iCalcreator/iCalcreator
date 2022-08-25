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
use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\DurDates;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;

/**
 * DURATION property functions
 *
 * @since 2.41.56 2022-08-15
 */
trait DURATIONtrait
{
    /**
     * @var null|Pc component property DURATION value
     */
    protected ? Pc $duration = null;

    /**
     * Return formatted output for calendar component property duration
     *
     * @return string
     * @throws Exception
     * @since 2.41.55 2022-08-13
     */
    public function createDuration() : string
    {
        return DurDates::format(
            self::DURATION,
            $this->duration,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property duration
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteDuration() : bool
    {
        $this->duration = null;
        return true;
    }

    /**
     * Get calendar component property duration
     *
     * @param null|bool   $inclParam
     * @param null|bool   $specform
     * @return bool|string|DateInterval|DateTime|Pc
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public function getDuration(
        ? bool $inclParam = false,
        ? bool $specform = false
    ) : DateInterval | DateTime | bool | string | Pc
    {
        if( empty( $this->duration )) {
            return false;
        }
        if( empty( $this->duration->value )) {
            return $inclParam ? clone $this->duration : $this->duration->value;
        }
        $value  = $this->duration->value;
        $params = $this->duration->params;
        if( $specform && ! empty( $this->dtstart )) {
            $dtStart = $this->dtstart;
            $dtValue = clone $dtStart->value;
            DateIntervalFactory::modifyDateTimeFromDateInterval( $dtValue, $value );
            $value   = $dtValue;
            if( $inclParam && $dtStart->hasParamKey( self::TZID )) {
                foreach( $dtStart->params as $k =>$v ) {
                    $params[$k] = $v;
                }
            }
        }
        return $inclParam
            ? Pc::factory( $value, $params )
            : $value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isDurationSet() : bool
    {
        return ! empty( $this->duration->value );
    }

    /**
     * Set calendar component property duration
     *
     * @param null|string|Pc|DateInterval $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.56 2022-08-15
     * @todo "When the "DURATION" property relates to a
     *        "DTSTART" property that is specified as a DATE value, then the
     *        "DURATION" property MUST be specified as a "dur-day" or "dur-week"
     *        value."
     */
    public function setDuration( null|string|DateInterval|Pc $value = null, ? array $params = [] ) : static
    {
        static $FMTERRPROPFMT = 'Invalid %s input format (%s)';
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        switch( true ) {
            case empty( $value->value ) :
                $this->assertEmptyValue( $value->value, self::DURATION );
                $this->duration = $value->setEmpty();
                return $this;
            case( $value->value instanceof DateInterval ) :
                $value->value = DateIntervalFactory::conformDateInterval( $value->value );
                break;
            case DateIntervalFactory::isStringAndDuration( $value->value ) :
                $value2 = StringFactory::trimTrailNL( $value->value );
                $value2 = DateIntervalFactory::removePlusMinusPrefix( $value2 ); // can only be positive
                try {
                    $dateInterval = new DateInterval( $value2 );
                    $value->value = DateIntervalFactory::conformDateInterval( $dateInterval );
                }
                catch( Exception $e ) {
                    throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
                }
                break;
            default :
                throw new InvalidArgumentException(
                    sprintf( $FMTERRPROPFMT, self::DURATION, var_export( $value->value, true ))
                );
        } // end switch
        $this->duration = $value;
        return $this;
    }
}
