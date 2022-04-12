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

use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * CATEGORIES property functions
 *
 * @since 2.41.36 2022-04-03
 */
trait CATEGORIEStrait
{
    /**
     * @var null|Pc[] component property CATEGORIES value
     */
    protected ? array $categories = null;

    /**
     * Return formatted output for calendar component property categories
     *
     * @return string
     * @since  2.29.11 - 2019-08-30
     */
    public function createCategories() : string
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
     * @param string        $propName
     * @param null|Pc[]  $pValArr
     * @param bool|string   $lang   bool false on not config lang found
     * @param bool          $allowEmpty
     * @param string[]      $specPkeys
     * @return string
     * @since 2.41.36 2022-04-03
     */
    private static function createCatRes(
        string $propName,
        ? array $pValArr,
        bool|string $lang,
        bool $allowEmpty,
        array $specPkeys
    ) : string
    {
        if( empty( $pValArr )) {
            return self::$SP0;
        }
        $output = self::$SP0;
        foreach( $pValArr as $valuePart ) { // Pc
            if( empty( $valuePart->value )) {
                if( $allowEmpty) {
                    $output .= StringFactory::createElement( $propName );
                }
                continue;
            }
            $content = StringFactory::strrep( $valuePart->value );
            $output .= StringFactory::createElement(
                $propName,
                ParameterFactory::createParams( $valuePart->params, $specPkeys, $lang ),
                $content
            );
        }
        return $output;
    }

    /**
     * Delete calendar component property categories
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteCategories( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->categories )) {
            unset( $this->propDelIx[self::CATEGORIES] );
            return false;
        }
        return self::deletePropertyM(
            $this->categories,
            self::CATEGORIES,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property categories
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getCategories( ? int $propIx = null, ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->categories )) {
            unset( $this->propIx[self::CATEGORIES] );
            return false;
        }
        return self::getMvalProperty(
            $this->categories,
            self::CATEGORIES,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isCategoriesSet() : bool
    {
        return self::isMvalSet( $this->categories );
    }

    /**
     * Set calendar component property categories
     *
     * @param null|string|Pc   $value
     * @param null|int|mixed[]  $params
     * @param null|int      $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-09
     */
    public function setCategories(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::CATEGORIES );
            $value->setEmpty();
        }
        else {
            $value->value = Util::assertString( $value->value, self::CATEGORIES );
        }
        self::setMval( $this->categories, $value, $index );
        return $this;
    }
}
