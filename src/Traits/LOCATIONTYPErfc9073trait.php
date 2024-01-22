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
namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use InvalidArgumentException;

/**
 * LOCATION-TYPE property functions
 *
 * @since 2.41.85 2024-01-18
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
     * @since 2.41.85 2024-01-18
     */
    public function getLocationtype( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->locationtype )) {
            return false;
        }
        return $inclParam ? clone $this->locationtype : $this->locationtype->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isLocationtypeSet() : bool
    {
        return self::isPropSet( $this->locationtype );
    }

    /**
     * Set calendar component property LOCATION-TYPE
     *
     * Values for this parameter are taken from the values defined in Section 3 of [RFC4589].
     * New location types SHOULD be registered in the manner laid down in Section 5 of [RFC4589].
     *
     * @param null|string|Pc   $value
     * @param null|mixed[] $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setLocationtype( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::LOCATION_TYPE );
            $pc->setEmpty();
        }
        else {
            $pc->setValue( StringFactory::trimTrailNL( $pcValue ));
        }
        $this->locationtype = $pc;
        return $this;
    }
}
