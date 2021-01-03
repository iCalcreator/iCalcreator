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
 * CATEGORIES property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.14 2019-09-03
 */
trait CATEGORIEStrait
{
    /**
     * @var array component property CATEGORIES value
     */
    protected $categories = null;

    /**
     * Return formatted output for calendar component property categories
     *
     * @return string
     * @since  2.29.11 - 2019-08-30
     */
    public function createCategories()
    {
        return self::createCatRes(
            self::CATEGORIES,
            $this->categories,
            $this->getConfig( self::LANGUAGE ),
            $this->getConfig( self::ALLOWEMPTY ),
            [ self::LANGUAGE ]
        );
    }

    /**
     * Return formatted output for calendar component properties categories/resources
     *
     * @param string $propName
     * @param array  $pValArr
     * @param string $lang
     * @param bool   $allowEmpty
     * @param array  $specPkeys
     * @return string
     * @since  2.29.13 - 2019-09-03
     */
    private static function createCatRes(
        $propName,
        $pValArr,
        $lang,
        $allowEmpty,
        $specPkeys
    ) {
        if( empty( $pValArr )) {
            return null;
        }
        $output = null;
        foreach( $pValArr as $cx => $valuePart ) {
            if( empty( $valuePart[Util::$LCvalue] )) {
                if( $allowEmpty) {
                    $output .= StringFactory::createElement( $propName );
                }
                continue;
            }
            $content = StringFactory::strrep( $valuePart[Util::$LCvalue] );
            $output .= StringFactory::createElement(
                $propName,
                ParameterFactory::createParams(
                    $valuePart[Util::$LCparams],
                    $specPkeys,
                    $lang
                ),
                $content
            );
        }
        return $output;
    }

    /**
     * Delete calendar component property categories
     *
     * @param int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteCategories( $propDelIx = null )
    {
        if( empty( $this->categories )) {
            unset( $this->propDelIx[self::CATEGORIES] );
            return false;
        }
        return $this->deletePropertyM(
            $this->categories,
            self::CATEGORIES,
            $propDelIx
        );
    }

    /**
     * Get calendar component property categories
     *
     * @param int    $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getCategories( $propIx = null, $inclParam = false )
    {
        if( empty( $this->categories )) {
            unset( $this->propIx[self::CATEGORIES] );
            return false;
        }
        return $this->getPropertyM( $this->categories,
            self::CATEGORIES,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property categories
     *
     * @param mixed   $value
     * @param array   $params
     * @param integer $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.29.14 2019-09-03
     */
    public function setCategories( $value = null, $params = [], $index = null )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::CATEGORIES );
            $value  = Util::$SP0;
            $params = [];
        }
        Util::assertString( $value, self::CATEGORIES );
        $this->setMval( $this->categories, (string) $value, $params, null, $index );
        return $this;
    }
}
