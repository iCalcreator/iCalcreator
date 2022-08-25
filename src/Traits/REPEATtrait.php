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

use Kigkonsult\Icalcreator\Formatter\Property\IntProperty;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

/**
 * REPEAT property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait REPEATtrait
{
    /**
     * @var null|Pc component property REPEAT value
     */
    protected ? Pc $repeat = null;

    /**
     * Return formatted output for calendar component property repeat
     *
     * @return string
     */
    public function createRepeat() : string
    {
        if( empty( $this->repeat )) {
            return self::$SP0;
        }
        return IntProperty::format(
            self::REPEAT,
            $this->repeat,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property repeat
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteRepeat() : bool
    {
        $this->repeat = null;
        return true;
    }

    /**
     * Get calendar component property repeat
     *
     * @param null|bool   $inclParam
     * @return bool|int|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getRepeat( ? bool $inclParam = false ) : bool | int | string | Pc
    {
        if( empty( $this->repeat )) {
            return false;
        }
        if( ! $this->repeat->isset()) {
            $this->repeat->value = self::$SP0;
        }
        return $inclParam ? clone $this->repeat : $this->repeat->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isRepeatSet() : bool
    {
        return ( ! empty( $this->repeat->value ) ||
            (( null !== $this->repeat ) && ( 0 === $this->repeat->value )));
    }

    /**
     * Set calendar component property repeat
     *
     * .. defines the number of times an alarm should be repeated after its initial trigger.
     * Default 0 (zero).
     *
     * @param null|int|string|Pc $value
     * @param null|array $params
     * @return static
     * @since 2.41.36 2022-04-03
     */
    public function setRepeat( null|int|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if(( $value->value === null ) || ( $value->value === self::$SP0 )) {
            $this->assertEmptyValue( $value->value, self::REPEAT );
            $value->setEmpty();
        }
        else {
            Util::assertInteger( $value->value, self::REPEAT, 0 );
            $value->value = (int) $value->value;
        }
        $this->repeat = $value;
        return $this;
    }
}
