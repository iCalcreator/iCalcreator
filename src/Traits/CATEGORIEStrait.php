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

use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * CATEGORIES property functions
 *
 * @since 2.40.11 2022-01-15
 */
trait CATEGORIEStrait
{
    /**
     * @var null|mixed[] component property CATEGORIES value
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
     * @param null|mixed[]  $pValArr
     * @param bool|string   $lang   bool false on not config lang found
     * @param bool          $allowEmpty
     * @param string[]      $specPkeys
     * @return string
     * @since  2.29.13 - 2019-09-03
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
            return Util::$SP0;
        }
        $output = Util::$SP0;
        foreach( $pValArr as $valuePart ) {
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
        return CalendarComponent::deletePropertyM(
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
     * @return bool|string|mixed[]
     * @since  2.27.1 - 2018-12-12
     */
    public function getCategories( ? int $propIx = null, ? bool $inclParam = false ) : array | bool | string
    {
        if( empty( $this->categories )) {
            unset( $this->propIx[self::CATEGORIES] );
            return false;
        }
        return  CalendarComponent::getPropertyM(
            $this->categories,
            self::CATEGORIES,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property categories
     *
     * @param null|string   $value
     * @param null|mixed[]  $params
     * @param null|int      $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.29.14 2019-09-03
     */
    public function setCategories( ? string $value = null, ? array $params = [], ? int $index = null ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::CATEGORIES );
            $value  = Util::$SP0;
            $params = [];
        }
        Util::assertString( $value, self::CATEGORIES );
        CalendarComponent::setMval(
            $this->categories,
            (string) $value,
            $params,
            null,
            $index
        );
        return $this;
    }
}
