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
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * DURATION property functions
 *
 * @since 2.40.11 2022-01-15
 */
trait DURATIONtrait
{
    /**
     * @var null|mixed[] component property DURATION value
     */
    protected ? array $duration = null;

    /**
     * Return formatted output for calendar component property duration
     *
     * @return string
     * @throws Exception
     * @since  2.40 - 2021-10-04
     */
    public function createDuration() : string
    {
        if( empty( $this->duration )) {
            return Util::$SP0;
        }
        if( empty( $this->duration[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::DURATION )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::DURATION,
            ParameterFactory::createParams( $this->duration[Util::$LCparams] ),
            DateIntervalFactory::dateInterval2String( $this->duration[Util::$LCvalue] )
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
     * @return bool|string|DateInterval|DateTime|mixed[]
     * @throws Exception
     * @since  2.40 - 2021-10-04
     */
    public function getDuration(
        ? bool $inclParam = false,
        ? bool $specform = false
    ) : DateInterval | DateTime | bool | string | array
    {
        if( empty( $this->duration )) {
            return false;
        }
        if( empty( $this->duration[Util::$LCvalue] )) {
            return $inclParam ? $this->duration : $this->duration[Util::$LCvalue];
        }
        $value  = $this->duration[Util::$LCvalue];
        $params = $this->duration[Util::$LCparams];
        if( $specform && ! empty( $this->dtstart )) {
            $dtStart = $this->dtstart;
            $dtValue = clone $dtStart[Util::$LCvalue];
            DateIntervalFactory::modifyDateTimeFromDateInterval( $dtValue, $value );
            $value   = $dtValue;
            if( $inclParam && isset( $dtStart[Util::$LCparams][self::TZID] )) {
                $params = array_merge( $params, $dtStart[Util::$LCparams] );
            }
        }
        return $inclParam
            ? [ Util::$LCvalue  => $value, Util::$LCparams => (array) $params, ]
            : $value;
    }

    /**
     * Set calendar component property duration
     *
     * @param null|string|DateInterval $value
     * @param null|mixed[]  $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.40 - 2021-10-04
     * @todo "When the "DURATION" property relates to a
     *        "DTSTART" property that is specified as a DATE value, then the
     *        "DURATION" property MUST be specified as a "dur-day" or "dur-week"
     *        value."
     */
    public function setDuration( null|string|DateInterval $value = null, ? array $params = [] ) : static
    {
        switch( true ) {
            case empty( $value ) :
                $this->assertEmptyValue( $value, self::DURATION );
                $this->duration = [
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
                    $value        = DateIntervalFactory::conformDateInterval( $dateInterval );
                }
                catch( Exception $e ) {
                    throw new InvalidArgumentException( $e->getMessage(), $e->getCode(), $e );
                }
                break;
            default :
                throw new InvalidArgumentException(
                    sprintf(
                        self::$FMTERRPROPFMT,
                        self::DURATION,
                        var_export( $value, true )
                    )
                );
        } // end switch
        $this->duration = [
            Util::$LCvalue  => $value,
            Util::$LCparams => ParameterFactory::setParams( $params ),
        ];
        return $this;
    }
}
