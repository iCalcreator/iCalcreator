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
 * STYLED-DESCRIPTION property functions
 *
 * @since 2.41.3 2022-01-17
 */
trait STYLED_DESCRIPTIONrfc9073trait
{
    /**
     * @var null|mixed[] component property styleddescription value
     */
    protected ? array $styleddescription = null;

    /**
     * Return formatted output for calendar component property styleddescription
     *
     * @return string
     */
    public function createStyleddescription() : string
    {
        if( empty( $this->styleddescription )) {
            return Util::$SP0;
        }
        $output  = Util::$SP0;
        $txtLang = $this->getConfig( self::LANGUAGE );
        foreach( $this->styleddescription as $part ) {
            if( empty( $part[Util::$LCvalue] )) {
                if( $this->getConfig( self::ALLOWEMPTY )) {
                    $output .= StringFactory::createElement( self::STYLED_DESCRIPTION );
                }
                continue;
            }
            if( self::TEXT === $part[Util::$LCparams][self::VALUE] ) {
                $ctrKeys = self::$ALTRPLANGARR;
                $lang    = $txtLang;
            }
            else {
                $ctrKeys = [];
                $lang    = null;
            }
            $output .= StringFactory::createElement(
                self::STYLED_DESCRIPTION,
                ParameterFactory::createParams( $part[Util::$LCparams], $ctrKeys, $lang ),
                StringFactory::strrep( $part[Util::$LCvalue] )
            );
        } // end foreach
        return $output;
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
        return  self::deletePropertyM(
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
     * @return bool|string|mixed[]
     */
    public function getStyleddescription( int $propIx = null, bool $inclParam = false ) : bool | array | string
    {
        if( empty( $this->styleddescription )) {
            unset( $this->propIx[self::STYLED_DESCRIPTION] );
            return false;
        }
        return self::getPropertyM(
            $this->styleddescription,
            self::STYLED_DESCRIPTION,
            $this,
            $propIx,
            $inclParam
        );
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
     * @param null|string   $value
     * @param null|mixed[]  $params   VALUE TEXT/URI
     * @param null|int      $index
     * @return static
     * @throws InvalidArgumentException
     */
    public function setStyleddescription( ? string $value = null, ? array $params = [], ? int $index = null ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::STYLED_DESCRIPTION );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            $params = array_change_key_case( $params ?? [], CASE_UPPER );
            if( ! isset( $params[self::VALUE] )) {
                $params[self::VALUE] = self::TEXT; // must have one
            }
            if( self::TEXT !== $params[self::VALUE] ) { // text may have but URI not
                unset( $params[self::ALTREP], $params[self::LANGUAGE] );
            }
            if( ! isset( $params[self::DERIVED] )) {
                $params[self::DERIVED] = self::FALSE; // default
            }
        }
        $value  = Util::assertString( $value, self::STYLED_DESCRIPTION );
        self::setMval( $this->styleddescription, $value, $params, [], $index );
        return $this;
    }
}
