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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\MultiProps;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;

/**
 * RESOURCES property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait RESOURCEStrait
{
    /**
     * @var null|Pc[] component property RESOURCES value
     */
    protected ? array $resources = null;

    /**
     * Return formatted output for calendar component property resources
     *
     * @return string
     * @since 2.41.55 2022-08-13
     */
    public function createResources() : string
    {
        return MultiProps::format(
            self::RESOURCES,
            $this->resources ?? [],
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property resources
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteResources( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->resources )) {
            unset( $this->propDelIx[self::RESOURCES] );
            return false;
        }
        return self::deletePropertyM(
            $this->resources,
            self::RESOURCES,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property resources
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getResources( ? int $propIx = null, ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->resources )) {
            unset( $this->propIx[self::RESOURCES] );
            return false;
        }
        return self::getMvalProperty(
            $this->resources,
            self::RESOURCES,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return array, all calendar component property resources
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllResources( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->resources, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isResourcesSet() : bool
    {
        return self::isMvalSet( $this->resources );
    }

    /**
     * Set calendar component property resources
     *
     * @param null|string|Pc    $value
     * @param null|int|array $params
     * @param null|int          $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-09
     */
    public function setResources(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::RESOURCES );
            $value->setEmpty();
        }
        else {
            $value->value= StringFactory::trimTrailNL( $value->value );
        }
        self::setMval( $this->resources, $value, $index );
        return $this;
    }
}
