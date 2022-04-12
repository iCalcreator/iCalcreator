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

use Kigkonsult\Icalcreator\Vcalendar;

use function in_array;

trait Participants2AttendeesTrait
{
    /**
     * Set subComponent Participants (calendaraddress) as Attendees, skip if set
     *
     * Participant UID set as X-param x-participantid
     * Used in V3component (Vevent, Vtodo ), VFreebusy, Vjournal
     *
     * @return static
     * @since 2.41.34 - 2022-03-28
     */
    public function participants2Attendees() : static
    {
        if( ! in_array( $this->getCompType(), Vcalendar::$VCOMBS )) {
            return $this;
        }
        [ $participants, $lcArr ] = $this->getSubCompsDetailType(
            self::PARTICIPANT,
            self::CALENDAR_ADDRESS,
            self::X_PARTICIPANTID,
            self::PARTICIPANT_TYPE,
            [ self::X_PARTICIPANT_TYPE, self::ROLE ]
        );
        if( ! empty( $participants )) {
            $this->comPropUpdFromSub( self::ATTENDEE, true, $participants, $lcArr );
        }
        return $this;
    }
}
