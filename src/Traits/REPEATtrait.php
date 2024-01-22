<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2024 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * REPEAT property functions
 *
 * @since 2.41.85 2024-01-18
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
     * @since 2.41.85 2024-01-18
     */
    public function getRepeat( ? bool $inclParam = false ) : bool | int | string | Pc
    {
        if( empty( $this->repeat )) {
            return false;
        }
        if( ! $this->repeat->isset()) {
            $this->repeat->setValue( self::$SP0 );
        }
        return $inclParam ? clone $this->repeat : $this->repeat->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isRepeatSet() : bool
    {
        return self::isIntPropSet( $this->repeat );
    }

    /**
     * Set calendar component property repeat
     *
     * defines the number of times an alarm should be repeated after its initial trigger.
     * Default 0 (zero).
     *
     * @param null|int|string|Pc $value
     * @param null|mixed[] $params
     * @return static
     * @since 2.41.85 2024-01-18
     */
    public function setRepeat( null|int|string|Pc $value = null, ? array $params = [] ) : static
    {
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue() ?: null;
        if(( null === $pcValue ) || ( StringFactory::$SP0 === $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::REPEAT );
            $pc->setEmpty();
        }
        else {
            Util::assertInteger( $pcValue, self::REPEAT, 0 );
            $pc->setValue((int) $pcValue );
        }
        $this->repeat = $pc;
        return $this;
    }
}
