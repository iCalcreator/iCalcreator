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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Attendee;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * ATTENDEE property functions
 *
 * @since 2.41.85 2024-01-18
 */
trait ATTENDEEtrait
{
    /**
     * @var null|Pc[] component property ATTENDEE value
     */
    protected ? array $attendee = null;

    /**
     * Return formatted output for calendar component property attendee
     *
     * @return string
     */
    public function createAttendee() : string
    {
        return Attendee::format(
            self::ATTENDEE,
            $this->attendee ?? [],
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property attendee
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteAttendee( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->attendee )) {
            unset( $this->propDelIx[self::ATTENDEE] );
            return false;
        }
        return self::deletePropertyM(
            $this->attendee,
            self::ATTENDEE,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property attendee
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since  2.27.1 - 2018-12-12
     */
    public function getAttendee( ? int $propIx = null, ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->attendee )) {
            unset( $this->propIx[self::ATTENDEE] );
            return false;
        }
        return self::getMvalProperty(
            $this->attendee,
            self::ATTENDEE,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return array, all calendar component property attendees
     *
     * @param null|bool $inclParam
     * @return Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllAttendee( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->attendee, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isAttendeeSet() : bool
    {
        return self::isMvalSet( $this->attendee );
    }

    /**
     * Set calendar component property attendee
     *
     * @param null|string|Pc $value
     * @param null|int|array $params
     * @param null|int       $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setAttendee(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $pc = self::marshallInputMval( $value, $params, $index );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::ATTENDEE );
            $pc->setEmpty();
        }
        else {
            $pcValue = Util::assertString( $pcValue, self::ATTENDEE );
            $pcValue = CalAddressFactory::conformCalAddress( $pcValue, true );
            CalAddressFactory::assertCalAddress( $pcValue );
            $pc->setValue( $pcValue );
            CalAddressFactory::sameValueAndEMAILparam( $pc );
            CalAddressFactory::inputPrepAttendeeParams(
                $pc,
                $this->getCompType(),
                $this->getConfig( self::LANGUAGE )
            );
        }
        self::setMval( $this->attendee, $pc, $index );
        return $this;
    }
}
