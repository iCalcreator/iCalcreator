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

use Kigkonsult\Icalcreator\Formatter\Property\SingleProps;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * TZID property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait TZIDtrait
{
    /**
     * @var null|Pc component property TZID value
     */
    protected ? Pc $tzid = null;

    /**
     * Return formatted output for calendar component property tzid
     *
     * @return string
     */
    public function createTzid() : string
    {
        return SingleProps::format(
            self::TZID,
            $this->tzid,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property tzid
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteTzid() : bool
    {
        $this->tzid = null;
        return true;
    }

    /**
     * Get calendar component property tzid
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getTzid( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->tzid )) {
            return false;
        }
        return $inclParam ? clone $this->tzid : $this->tzid->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isTzidSet() : bool
    {
        return ! empty( $this->tzid->value );
    }

    /**
     * Set calendar component property tzid
     *
     * @since 2.23.12 - 2017-04-22
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setTzid( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::TZID );
            $value->setEmpty();
        }
        else {
            Util::assertString( $value->value, self::TZID );
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
        $this->tzid = $value->setParams( ParameterFactory::setParams( $params ));
        return $this;
    }
}
