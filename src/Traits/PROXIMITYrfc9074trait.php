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

use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * PROXIMITY property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait PROXIMITYrfc9074trait
{
    /**
     * @var null|Pc   Valarm component property PROXIMITY value
     */
    protected ? Pc $proximity = null;

    /**
     * Return formatted output for calendar Valarm component property proximity
     *
     * @return string
     */
    public function createProximity() : string
    {
        return Property::format(
            self::PROXIMITY,
            $this->proximity,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar Valarm component property proximity
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteProximity() : bool
    {
        $this->proximity = null;
        return true;
    }

    /**
     * Get calendar Valarm component property proximity
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getProximity( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->proximity )) {
            return false;
        }
        return $inclParam ? clone $this->proximity : $this->proximity->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isProximitySet() : bool
    {
        return ! empty( $this->proximity->value );
    }

    /**
     * Set calendar component property proximity
     *
     * @since 2.23.12 - 2017-04-22
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setProximity( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::PROXIMITY );
            $value->setEmpty();
        }
        else {
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
        $this->proximity = $value;
        return $this;
    }
}
