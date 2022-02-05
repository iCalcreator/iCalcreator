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

/**
 * TZID-ALIAS-OF property functions
 *
 * @since 2.41.1 2022-01-15
 */
trait TZID_ALIAS_OFrfc7808trait
{
    /**
     * @var null|mixed[] component property SUMMARY value
     */
    protected ? array $tzidaliasof = null;

    /**
     * Return formatted output for calendar component property TZID-ALIAS-OF
     *
     * @return string
     * @since 2.41.1 2022-01-15
     */
    public function createTzidaliasof() : string
    {
        if( empty( $this->tzidaliasof )) {
            return Util::$SP0;
        }
        $output = Util::$SP0;
        foreach( $this->tzidaliasof as $tzidaliasofPart ) {
            if( empty( $tzidaliasofPart[Util::$LCvalue] )) {
                if( $this->getConfig( self::ALLOWEMPTY )) {
                    $output .= StringFactory::createElement( self::TZID_ALIAS_OF );
                }
                continue;
            }
            $output .= StringFactory::createElement(
                self::TZID_ALIAS_OF,
                ParameterFactory::createParams( $tzidaliasofPart[Util::$LCparams] ),
                StringFactory::strrep( $tzidaliasofPart[Util::$LCvalue] )
            );
        } // end foreach
        return $output;
    }

    /**
     * Delete calendar component property TZID-ALIAS-OF
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.41.1 2022-01-15
     */
    public function deleteTzidaliasof( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->tzidaliasof )) {
            unset( $this->propDelIx[self::TZID_ALIAS_OF] );
            return false;
        }
        return  self::deletePropertyM(
            $this->tzidaliasof,
            self::TZID_ALIAS_OF,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property TZID-ALIAS-OF
     *
     * @param null|int $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|string|mixed[]
     * @since 2.41.1 2022-01-15
     */
    public function getTzidaliasof( int $propIx = null, bool $inclParam = false ) : array | bool | string
    {
        if( empty( $this->tzidaliasof )) {
            unset( $this->propIx[self::TZID_ALIAS_OF] );
            return false;
        }
        return self::getPropertyM(
            $this->tzidaliasof,
            self::TZID_ALIAS_OF,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property TZID-ALIAS-OF
     *
     * @param null|string   $value
     * @param null|mixed[]  $params
     * @param null|int      $index
     * @return static
     * @since 2.41.1 2022-01-15
     */
    public function setTzidaliasof( ? string $value = null, ? array $params = [], ? int $index = null ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::TZID_ALIAS_OF );
            $value  = Util::$SP0;
            $params = [];
        }
        Util::assertString( $value, self::TZID_ALIAS_OF );
        self::setMval( $this->tzidaliasof, $value, $params, null, $index );
        return $this;
    }
}
