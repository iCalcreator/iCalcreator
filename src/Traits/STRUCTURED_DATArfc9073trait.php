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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\MultiProps;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * STRUCTURED-DATA property functions
 *
 * @since 2.41.91 2024-12-17
 */
trait STRUCTURED_DATArfc9073trait
{
    /**
     * @var null|Pc[] component property structureddata value
     */
    protected ? array $structureddata = null;

    /**
     * Return formatted output for calendar component property structureddata
     *
     * @return string
     */
    public function createStructureddata() : string
    {
        return MultiProps::format(
            self::STRUCTURED_DATA,
            $this->structureddata ?? [],
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property structureddata
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     */
    public function deleteStructureddata( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->structureddata )) {
            unset( $this->propDelIx[self::STRUCTURED_DATA] );
            return false;
        }
        return self::deletePropertyM(
            $this->structureddata,
            self::STRUCTURED_DATA,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property structureddata
     *
     * @param null|int $propIx specific property in case of multiply occurrence
     * @param bool $inclParam
     * @return bool|string|Pc
     * @since 2.41.91 2024-12-17
     */
    public function getStructureddata( ? int $propIx = null, ? bool $inclParam = false ) : bool | string |Pc
    {
        if( empty( $this->structureddata )) {
            unset( $this->propIx[self::STRUCTURED_DATA] );
            return false;
        }
        return self::getMvalProperty(
            $this->structureddata,
            self::STRUCTURED_DATA,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return array, all calendar component property structureddata
     *
     * @param null|bool   $inclParam
     * @return Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllStructureddata( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->structureddata, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isStructureddataSet() : bool
    {
        return self::isMvalSet( $this->structureddata );
    }

    /**
     * Set calendar component property structureddata
     *
     * Set default param DERIVED to FALSE if missing (default)
     *
     * fmttypeparam/ schemaparam are OPTIONAL for a URI value, REQUIRED for a TEXT or BINARY value
     * and MUST NOT occur more than once
     *
     * @param null|string|Pc   $value
     * @param null|int|array $params   VALUE TEXT/URI
     * @param null|int         $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setStructureddata(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $pc      = self::marshallInputMval( $value, $params, $index );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::STRUCTURED_DATA );
            $pc->setEmpty();
        }
        else {
            $pc->setValue( Util::assertString( $pcValue, self::STRUCTURED_DATA ));
            $pc->addParamValue( self::TEXT, false ); // must have VALUE
            if( $pc->hasParamValue( self::BINARY ) &&
                ! $pc->hasParamKey( self::ENCODING )) {
                $pc->addParam( self::ENCODING, self::BASE64 );
            }
        }
        self::setMval( $this->structureddata, $pc, $index );
        return $this;
    }
}
