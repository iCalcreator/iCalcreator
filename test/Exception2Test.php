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
namespace Kigkonsult\Icalcreator;

use Exception;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * class Exception1Test
 *
 * Testing exception when dtstart+dtend and dtstart+due are not in order
 *
 * @since  2.41.24 - 2022-04-07
 */
class Exception2Test extends TestCase
{
    /**
     * Testing dtstart and dtend NOT in order
     *
     * @test
     */
    public function dtstartDtendTest() : void
    {
        $calendar  = new Vcalendar();
        $start     = new DateTime();
        $end       = ( clone $start )->modify( '-1 day' );
        $ok = false;
        try {
            $event = $calendar->newVevent( $start, $end );
        }
        catch ( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #1, dtstart/dtend' );
    }

    /**
     * Testing dtstart and due NOT in order
     *
     * @test
     */
    public function dtstartDueTest() : void
    {
        $calendar = new Vcalendar();
        $start    = new DateTime();
        $due      = ( clone $start )->modify( '-1 day' );
        $ok = false;
        try {
            $todo = $calendar->newVtodo( $start, $due );
        }
        catch ( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #1, dtstart/dtend' );
    }
}
