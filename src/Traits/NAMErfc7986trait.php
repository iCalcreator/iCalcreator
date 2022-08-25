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
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;

/**
 * NAME property functions
 *
 * NAME may occur multiply times in Vcalendar but once in Vlocation/Vresource
 *
 * @since 2.41.55 2022-08-13
 */
trait NAMErfc7986trait
{
    /**
     * @var null|Pc[] component property NAME value
     */
    protected ? array $name = null;

    /**
     * Return formatted output for calendar component property name
     *
     * @return string
     * @since 2.41.36 2022-04-03
     */
    public function createName() : string
    {
        return MultiProps::format(
            self::NAME,
            $this->name ?? [],
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property name
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.41.36 2022-04-11
     */
    public function deleteName( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->name )) {
            unset( $this->propDelIx[self::NAME] );
            return false;
        }
        if( self::isNameSingleProp( $this->getCompType())) {
            $propDelIx = null;
        }
        return self::deletePropertyM(
            $this->name,
            self::NAME,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property name
     *
     * @param null|bool|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-11
     */
    public function getName( null|bool|int $propIx = null, ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->name )) {
            unset( $this->propIx[self::NAME] );
            return false;
        }
        $isSingleType = self::isNameSingleProp( $this->getCompType());
        if( $isSingleType ) {
            if( is_bool( $propIx )) {
                $inclParam = $propIx;
            }
            $propIx = null;
        }
        $result = self::getMvalProperty(
            $this->name,
            self::NAME,
            $this,
            $propIx,
            $inclParam
        );
        if( $isSingleType ) {
            unset( $this->propIx[self::NAME] );
        }
        return $result;
    }

    /**
     * Return array, all calendar component property name
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllName( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->name, $inclParam );
    }

    /**
     * Return bool true if NAME property may only occur once in component
     *
     * @param string $compName
     * @return bool
     * @since 2.41.36 2022-04-11
     */
    public static function isNameSingleProp( string $compName ) : bool
    {
        return ( Vcalendar::VCALENDAR !== $compName );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isNameSet() : bool
    {
        return self::isMvalSet( $this->name );
    }

    /**
     * Set calendar component property name
     *
     * @param null|string|Pc   $value
     * @param null|int|array $params
     * @param null|int         $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-11
     */
    public function setName( null|string|Pc $value = null, null|int|array $params = [], ? int $index = null ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::NAME );
            $value->setEmpty();
        }
        else {
            Util::assertString( $value->value, self::NAME );
        }
        if( self::isNameSingleProp( $this->getCompType())) {
            $index = 1;
        }
        self::setMval( $this->name, $value, $index );
        return $this;
    }
}
