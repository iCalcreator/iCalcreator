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
use InvalidArgumentException;

/**
 * PRIORITY property functions
 *
 * @since 2.41.85 2024-01-18
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
        return IntProperty::format(
            self::PRIORITY,
            $this->priority,
            $this->getConfig( self::ALLOWEMPTY )
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
     * @since 2.41.85 2024-01-18
     */
    public function getPriority( ? bool $inclParam = false ) : bool | int | string | Pc
    {
        if( empty( $this->priority )) {
            return false;
        }
        return $inclParam ? clone $this->priority : $this->priority->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isPrioritySet() : bool
    {
        return self::isIntPropSet( $this->priority );
    }

    /**
     * Set calendar component property priority
     *
     * .. an integer in the range 0 to 9.
     * A value of 0 specifies an undefined priority.
     * A value of 1 is the highest priority.
     * A value of 2 is the second highest priority.
     * Subsequent numbers specify a decreasing ordinal priority.
     * A value of 9 is the lowest priority.
     *
     * @param null|int|string|Pc $value
     * @param null|mixed[] $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setPriority( null|int|string|Pc $value = null, ? array $params = [] ) : static
    {
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue() ?: null;
        if(( null === $pcValue ) || ( StringFactory::$SP0 === $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::PRIORITY );
            $pc->setEmpty();
        }
        else {
            Util::assertInteger( $pcValue, self::PRIORITY, 0, 9 );
            $pc->setValue((int) $pcValue );
        }
        $this->priority = $pc;
        return $this;
    }
}
