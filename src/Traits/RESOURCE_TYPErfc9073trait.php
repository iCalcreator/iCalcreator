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
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

/**
 * RESOURCE_TYPE property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait RESOURCE_TYPErfc9073trait
{
    /**
     * @var null|Pc component property resourcetype value
     */
    protected ? Pc $resourcetype = null;

    /**
     * Return formatted output for calendar component property resourcetype
     *
     * @return string
     */
    public function createResourcetype() : string
    {
        return Property::format(
            self::RESOURCE_TYPE,
            $this->resourcetype,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property resourcetype
     *
     * @return bool
     */
    public function deleteResourcetype() : bool
    {
        $this->resourcetype = null;
        return true;
    }

    /**
     * Get calendar component property resourcetype
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     */
    public function getResourcetype( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->resourcetype )) {
            return false;
        }
        return $inclParam ? clone $this->resourcetype : $this->resourcetype->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isResourcetypeSet() : bool
    {
        return ! empty( $this->resourcetype->value );
    }

    /**
     * Set calendar component property resourcetype
     *
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     */
    public function setResourcetype( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::RESOURCE_TYPE );
            $value->setEmpty();
        }
        else {
            Util::assertString( $value->value, self::RESOURCE_TYPE );
        }
        $this->resourcetype = $value;
        return $this;
    }
}
