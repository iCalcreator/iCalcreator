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

use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use InvalidArgumentException;

/**
 * CALENDAR-ADDRESS property functions
 *
 * @since 2.41.85 2024-01-18
 */
trait CALENDAR_ADDRESSrfc9073trait
{
    /**
     * @var null|Pc component property CALENDAR-ADDRESS value
     */
    protected ? Pc $calendaraddress = null;

    /**
     * Return formatted output for calendar component property calendaraddress
     *
     * @return string
     */
    public function createCalendaraddress() : string
    {
        return Property::format(
            self::CALENDAR_ADDRESS,
            $this->calendaraddress,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property calendaraddress
     *
     * @return bool
     */
    public function deleteCalendaraddress() : bool
    {
        $this->calendaraddress = null;
        return true;
    }

    /**
     * Get calendar component property calendaraddress
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.85 2024-01-18
     */
    public function getCalendaraddress( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->calendaraddress )) {
            return false;
        }
        return $inclParam ? clone $this->calendaraddress : $this->calendaraddress->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isCalendaraddressSet() : bool
    {
        return self::isPropSet( $this->calendaraddress );
    }

    /**
     * Set calendar component property calendaraddress
     *
     * @param null|string|Pc   $value
     * @param null|mixed[] $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setCalendaraddress( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::CALENDAR_ADDRESS );
            $pc->setEmpty();
        }
        else {
            $pcValue = Util::assertString( $pcValue, self::CALENDAR_ADDRESS );
            $pcValue = CalAddressFactory::conformCalAddress( $pcValue, true );
            CalAddressFactory::assertCalAddress( $pcValue );
            $pc->setValue( $pcValue );
        }
        $this->calendaraddress = $pc;
        return $this;
    }
}
