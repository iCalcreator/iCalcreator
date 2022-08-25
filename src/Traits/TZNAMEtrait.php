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
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
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
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * TZNAME property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait TZNAMEtrait
{
    /**
     * @var null|Pc[] component property TZNAME value
     */
    protected ? array $tzname = null;

    /**
     * Return formatted output for calendar component property tzname
     *
     * @return string
     */
    public function createTzname() : string
    {
        return MultiProps::format(
            self::TZNAME,
            $this->tzname ?? [],
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property tzname
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteTzname( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->tzname )) {
            unset( $this->propDelIx[self::TZNAME] );
            return false;
        }
        return  self::deletePropertyM(
            $this->tzname,
            self::TZNAME,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property tzname
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getTzname( ? int $propIx = null, ? bool $inclParam = false ) : bool | string |Pc
    {
        if( empty( $this->tzname )) {
            unset( $this->propIx[self::TZNAME] );
            return false;
        }
        return self::getMvalProperty(
            $this->tzname,
            self::TZNAME,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return array, all calendar component property tzname
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllTzname( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->tzname, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isTznameSet() : bool
    {
        return self::isMvalSet( $this->tzname );
    }

    /**
     * Set calendar component property tzname
     *
     * @param null|string|Pc  $value
     * @param null|int|array $params
     * @param null|int        $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-09
     */
    public function setTzname(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::TZNAME );
            $value->setEmpty();
        }
        else {
            Util::assertString( $value->value, self::TZNAME );
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
         self::setMval( $this->tzname, $value, $index );
        return $this;
    }
}
