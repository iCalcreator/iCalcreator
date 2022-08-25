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

/**
 * STYLED-DESCRIPTION property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait STYLED_DESCRIPTIONrfc9073trait
{
    /**
     * @var null|Pc[] component property styleddescription value
     */
    protected ? array $styleddescription = null;

    /**
     * Return formatted output for calendar component property styleddescription
     *
     * @return string
     */
    public function createStyleddescription() : string
    {
        return MultiProps::format(
            self::STYLED_DESCRIPTION,
            $this->styleddescription ?? [],
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property styleddescription
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     */
    public function deleteStyleddescription( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->styleddescription )) {
            unset( $this->propDelIx[self::STYLED_DESCRIPTION] );
            return false;
        }
        return self::deletePropertyM(
            $this->styleddescription,
            self::STYLED_DESCRIPTION,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property styleddescription
     *
     * @param null|int $propIx specific property in case of multiply occurrence
     * @param bool $inclParam
     * @return bool|string|Pc
     */
    public function getStyleddescription( int $propIx = null, bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->styleddescription )) {
            unset( $this->propIx[self::STYLED_DESCRIPTION] );
            return false;
        }
        return self::getMvalProperty(
            $this->styleddescription,
            self::STYLED_DESCRIPTION,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return array, all calendar component property styleddescription
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllStyleddescription( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->styleddescription, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isStyleddescriptionSet() : bool
    {
        return self::isMvalSet( $this->styleddescription );
    }

    /**
     * Set calendar component property styleddescription
     *
     * Set default param DERIVED to FALSE if missing (default)
     *
     * If it does appear more than once, there MUST be exactly one instance of the property
     * with no "DERIVED" parameter or DERIVED=FALSE. All others MUST have DERIVED=TRUE.
     *
     * Additionally, if there is one or more "STYLED-DESCRIPTION" property,
     * then the "DESCRIPTION" property should either be absent or have the parameter DERIVED=TRUE.
     *
     * @param null|string|Pc   $value
     * @param null|int|array $params   VALUE TEXT/URI
     * @param null|int         $index
     * @return static
     * @throws InvalidArgumentException
     */
    public function setStyleddescription(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::STYLED_DESCRIPTION );
            $value->setEmpty();
        }
        else {
            $value->value  = Util::assertString( $value->value, self::STYLED_DESCRIPTION );
            $value->addParamValue( self::TEXT, false ); // must have one
            if( ! $value->hasParamKey( self::VALUE, self::TEXT )) { // text may have but URI not...
                $value->removeParam( self::ALTREP );
                $value->removeParam( self::LANGUAGE );
            }
            if( ! $value->hasParamKey( self::DERIVED )) {
                $value->addParam( self::DERIVED, self::FALSE ); // default
            }
        }
        self::setMval( $this->styleddescription, $value, $index );
        return $this;
    }
}
