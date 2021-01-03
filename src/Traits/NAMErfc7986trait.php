<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * NAME property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.14 2019-09-03
 */
trait NAMErfc7986trait
{
    /**
     * @var array component property NAME value
     */
    protected $name = null;

    /**
     * Return formatted output for calendar component property name
     *
     * @return string
     * @since 2.29.5 2019-06-16
     */
    public function createName()
    {
        if( empty( $this->name )) {
            return null;
        }
        $output = null;
        $lang   = $this->getConfig( self::LANGUAGE );
        foreach( $this->name as $cx => $namePart ) {
            if( empty( $namePart[Util::$LCvalue] )) {
                if( $this->getConfig( self::ALLOWEMPTY )) {
                    $output .= StringFactory::createElement( self::NAME );
                }
                continue;
            }
            $output .= StringFactory::createElement(
                self::NAME,
                ParameterFactory::createParams(
                    $namePart[Util::$LCparams],
                    self::$ALTRPLANGARR,
                    $lang
                ),
                StringFactory::strrep( $namePart[Util::$LCvalue] )
            );
        }
        return $output;
    }

    /**
     * Delete calendar component property name
     *
     * @param int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.29.5 2019-06-16
     */
    public function deleteName( $propDelIx = null )
    {
        if( empty( $this->name )) {
            unset( $this->propDelIx[self::NAME] );
            return false;
        }
        return $this->deletePropertyM( $this->name, self::NAME, $propDelIx );
    }

    /**
     * Get calendar component property name
     *
     * @param int    $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|array
     * @since 2.29.5 2019-06-16
     */
    public function getName( $propIx = null, $inclParam = false )
    {
        if( empty( $this->name )) {
            unset( $this->propIx[self::NAME] );
            return false;
        }
        return $this->getPropertyM( $this->name, self::NAME, $propIx, $inclParam );
    }

    /**
     * Set calendar component property name
     *
     * @param string  $value
     * @param array   $params
     * @param integer $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.29.14 2019-09-03
     */
    public function setName( $value = null, $params = [], $index = null )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::NAME );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            Util::assertString( $value, self::NAME );
        }
        $this->setMval( $this->name, $value, $params, null, $index );
        return $this;
    }
}
