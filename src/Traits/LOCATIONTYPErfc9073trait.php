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
 * LOCATION-TYPE property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait LOCATIONTYPErfc9073trait
{
    /**
     * @var null|Pc component property LOCATION-TYPE value
     */
    protected ? Pc $locationtype = null;

    /**
     * Return formatted output for calendar component property LOCATION-TYPE
     *
     * @return string
     */
    public function createLocationtype() : string
    {
        return Property::format(
            self::LOCATION_TYPE,
            $this->locationtype,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property transp
     *
     * @return bool
     */
    public function deleteLocationtype() : bool
    {
        $this->locationtype = null;
        return true;
    }

    /**
     * Get calendar component property LOCATION-TYPE
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     */
    public function getLocationtype( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->locationtype )) {
            return false;
        }
        return $inclParam ? clone $this->locationtype : $this->locationtype->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isLocationtypeSet() : bool
    {
        return ! empty( $this->locationtype->value );
    }

    /**
     * Set calendar component property LOCATION-TYPE
     *
     * Values for this parameter are taken from the values defined in Section 3 of [RFC4589].
     * New location types SHOULD be registered in the manner laid down in Section 5 of [RFC4589].
     *
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     */
    public function setLocationtype( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::LOCATION_TYPE );
            $value->setEmpty();
        }
        else {
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
        $this->locationtype = $value;
        return $this;
    }
}
