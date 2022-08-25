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

use Kigkonsult\Icalcreator\Formatter\Property\MultiProps;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;

/**
 * LOCATION property functions
 *
 * LOCATION may occur multiply times i Participant, once otherwise
 *
 * @since 2.41.55 2022-08-13
 */
trait LOCATIONtrait
{
    /**
     * @var null|Pc[] component property LOCATION value
     */
    protected ? array $location = null;

    /**
     * Return formatted output for calendar component property location
     *
     * @return string
     */
    public function createLocation() : string
    {
        return MultiProps::format(
            self::LOCATION,
            $this->location ?? [],
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property location
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.41.36 2022-04-11
     */
    public function deleteLocation( ? int $propDelIx = null ) : bool
    {
        $this->location = null;
        if( empty( $this->location )) {
            unset( $this->propDelIx[self::LOCATION] );
            return false;
        }
        if( self::isLocationSingleProp( $this->getCompType())) {
            $propDelIx = null;
        }
        return self::deletePropertyM(
            $this->location,
            self::LOCATION,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property location
     *
     * @param null|bool|int   $propIx specific property in case of multiply occurrence
     * @param null|bool  $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-11
     */
    public function getLocation( null|bool|int $propIx = null, ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->location )) {
            unset( $this->propIx[self::LOCATION] );
            return false;
        }
        $isSingleProp = self::isLocationSingleProp( $this->getCompType());
        if( $isSingleProp ) {
            if( is_bool( $propIx )) {
                $inclParam = $propIx;
            }
            $propIx = null;
        }
        $result = self::getMvalProperty(
            $this->location,
            self::LOCATION,
            $this,
            $propIx,
            $inclParam
        );
        if( $isSingleProp ) {
            unset( $this->propIx[self::LOCATION] );
        }
        return $result;
    }

    /**
     * Return array, all calendar component property location
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllLocation( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->location, $inclParam );
    }

    /**
     * Return bool true if LOCATION property may only occur once in component
     *
     * @param string $compName
     * @return bool
     * @since 2.41.36 2022-04-11
     */
    public static function isLocationSingleProp( string $compName ) : bool
    {
        return ( self::PARTICIPANT !== $compName );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isLocationSet() : bool
    {
        return self::isMvalSet( $this->location );
    }

    /**
     * Set calendar component property location
     *
     * @param null|string|Pc    $value
     * @param null|int|array $params
     * @param null|int          $index  if NOT comp PARTICIPANT : 1
     * @return static
     * @since 2.41.36 2022-04-11
     */
    public function setLocation(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::LOCATION );
            $value->setEmpty();
        }
        else {
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
        if( self::isLocationSingleProp( $this->getCompType())) {
            $index = 1;
        }
        self::setMval( $this->location, $value, $index );
        return $this;
    }
}
