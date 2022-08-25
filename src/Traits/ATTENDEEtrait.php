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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Attendee;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * ATTENDEE property functions
 *
 * @since 2.41.55 - 2022-08-13
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
     * @return array|Pc[]
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
     * @since 2.41.36 2022-04-09
     */
    public function setAttendee(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::ATTENDEE );
            $value->setEmpty();
        }
        else {
            $value->value = Util::assertString( $value->value, self::ATTENDEE );
            $value->value = CalAddressFactory::conformCalAddress( $value->value, true );
            CalAddressFactory::assertCalAddress( $value->value );
            CalAddressFactory::sameValueAndEMAILparam( $value );
            $value->params = CalAddressFactory::inputPrepAttendeeParams(
                $value->params,
                $this->getCompType(),
                $this->getConfig( self::LANGUAGE )
            );
        }
        self::setMval( $this->attendee, $value, $index );
        return $this;
    }
}
