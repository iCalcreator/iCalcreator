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
 * PRIORITY property functions
 *
 * @since 2.41.36 2022-04-03
 */
trait PRIORITYtrait
{
    /**
     * @var null|Pc component property PRIORITY value
     */
    protected ? Pc $priority = null;

    /**
     * Return formatted output for calendar component property priority
     *
     * @return string
     */
    public function createPriority() : string
    {
        if( empty( $this->priority )) {
            return self::$SP0;
        }
        if( ! isset( $this->priority->value ) ||
            ( empty( $this->priority->value ) && ! is_numeric( $this->priority->value ))) {
            return $this->createSinglePropEmpty( self::PRIORITY );
        }
        return StringFactory::createElement(
            self::PRIORITY,
            ParameterFactory::createParams( $this->priority->params ),
            (string) $this->priority->value
        );
    }

    /**
     * Delete calendar component property priority
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deletePriority() : bool
    {
        $this->priority = null;
        return true;
    }

    /**
     * Get calendar component property priority
     *
     * @param null|bool   $inclParam
     * @return bool|int|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getPriority( ? bool $inclParam = false ) : bool | int | string | Pc
    {
        if( empty( $this->priority )) {
            return false;
        }
        return $inclParam ? clone $this->priority : $this->priority->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isPrioritySet() : bool
    {
        return ( ! empty( $this->priority->value ) || ( 0 === $this->priority->value ));
    }

    /**
     * Set calendar component property priority
     *
     * @param null|int|string|Pc $value
     * @param null|mixed[]    $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setPriority( null|int|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if(( $value->value === null ) || ( $value->value === self::$SP0 )) {
            $this->assertEmptyValue( $value->value, self::PRIORITY );
            $value->setEmpty();
        }
        else {
            Util::assertInteger( $value->value, self::PRIORITY, 0, 9 );
            $value->value = (int) $value->value;
        }
        $this->priority = $value;
        return $this;
    }
}
