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
namespace Kigkonsult\Icalcreator;

use Exception;

abstract class V2component extends Vcomponent
{
    /**
     * Return Participant object instance
     *
     * @param null|string $participanttype property PARTICIPANT-TYPE value
     * @param null|string $calendaraddress property CALENDAR-ADDRESS value
     * @param null|string $url property URL value
     * @return Participant
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public function newParticipant(
        ? string $participanttype = null,
        ? string $calendaraddress = null,
        ? string $url = null
    ) : Participant
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = Participant::factory(
            $this->getConfig(),
            $participanttype,
            $calendaraddress,
            $url
        );
        return $this->components[$ix];
    }
}
