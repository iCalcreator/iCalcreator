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

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * STRUCTURED-DATA property functions
 *
 * @since 2.41.3 2022-01-17
 */
trait STRUCTURED_DATArfc9073trait
{
    /**
     * @var null|mixed[] component property structureddata value
     */
    protected ? array $structureddata = null;

    /**
     * Return formatted output for calendar component property structureddata
     *
     * @return string
     */
    public function createStructureddata() : string
    {
        if( empty( $this->structureddata )) {
            return Util::$SP0;
        }
        $output  = Util::$SP0;
        foreach( $this->structureddata as $part ) {
            if( empty( $part[Util::$LCvalue] )) {
                if( $this->getConfig( self::ALLOWEMPTY )) {
                    $output .= StringFactory::createElement( self::STRUCTURED_DATA );
                }
                continue;
            }
            $output .= StringFactory::createElement(
                self::STRUCTURED_DATA,
                ParameterFactory::createParams( $part[Util::$LCparams] ),
                StringFactory::strrep( $part[Util::$LCvalue] )
            );
        } // end foreach
        return $output;
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
        return  self::deletePropertyM(
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
     * @return bool|string|mixed[]
     */
    public function getStructureddata( int $propIx = null, bool $inclParam = false ) : bool | array | string
    {
        if( empty( $this->structureddata )) {
            unset( $this->propIx[self::STRUCTURED_DATA] );
            return false;
        }
        return self::getPropertyM(
            $this->structureddata,
            self::STRUCTURED_DATA,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property structureddata
     *
     * Set default param DERIVED to FALSE if missing (default)
     *
     * fmttypeparam/ schemaparam are OPTIONAL for a URI value, REQUIRED for a TEXT or BINARY value
     * and MUST NOT occur more than once
     *
     * @param null|string   $value
     * @param null|mixed[]  $params   VALUE TEXT/URI
     * @param null|int      $index
     * @return static
     * @throws InvalidArgumentException
     */
    public function setStructureddata( ? string $value = null, ? array $params = [], ? int $index = null ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::STRUCTURED_DATA );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            $params = array_change_key_case( $params ?? [], CASE_UPPER );
            if( ! isset( $params[self::VALUE] )) {
                $params[self::VALUE] = self::TEXT; // must have one
            }
            if(( self::BINARY === $params[self::VALUE] ) && ! isset( $params[self::ENCODING] )) {
                $params[self::ENCODING] = self::BASE64;
            }
        }
        $value  = Util::assertString( $value, self::STRUCTURED_DATA );
        self::setMval( $this->structureddata, $value, $params, [], $index );
        return $this;
    }
}
