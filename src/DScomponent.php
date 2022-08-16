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

/**
 * iCalcreator (Vtimezone) Daylight/Standard base component class
 *
 * @since 2.41.55 2022-08-13
 */
abstract class DScomponent extends CalendarComponent
{
    use Traits\COMMENTtrait;
    use Traits\DTSTARTtrait;
    use Traits\RDATEtrait;
    use Traits\RRULEtrait;
    use Traits\TZNAMEtrait;
    use Traits\TZOFFSETFROMtrait;
    use Traits\TZOFFSETTOtrait;

    /**
     * @var string
     */
    protected static string $compSgn = 'ds';

    /**
     * Destructor
     *
     * @since  2.29.11 - 2019-08-30
     */
    public function __destruct() {
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->config,
            $this->propIx,
            $this->compix,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->comment,
            $this->dtstart,
            $this->rdate,
            $this->rrule,
            $this->tzname,
            $this->tzoffsetfrom,
            $this->tzoffsetto
        );
    }
}
