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

use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\HttpFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

/**
 * TZURL property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait TZURLtrait
{
    /**
     * @var null|Pc component property TZURL value
     */
    protected ? Pc $tzurl = null;

    /**
     * Return formatted output for calendar component property tzurl
     *
     * @return string
     */
    public function createTzurl() : string
    {
        return Property::format(
            self::TZURL,
            $this->tzurl,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property tzurl
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteTzurl() : bool
    {
        $this->tzurl = null;
        return true;
    }

    /**
     * Get calendar component property tzurl
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getTzurl( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->tzurl )) {
            return false;
        }
        return $inclParam ? clone $this->tzurl : $this->tzurl->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isTzurlSet() : bool
    {
        return ! empty( $this->tzurl->value );
    }

    /**
     * Set calendar component property tzurl
     *
     * Note, "TZURL" values SHOULD NOT be specified as a file URI type.
     * This URI form can be useful within an organization, but is problematic
     * in the Internet.
     *
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     * @since 2.41.36 2022-04-03
     */
    public function setTzurl( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::TZURL );
            $this->tzurl = $value->setEmpty();
        }
        else {
            Util::assertString( $value->value, self::TZURL );
            HttpFactory::urlSet( $this->tzurl, $value );
        }
        return $this;
    }
}
