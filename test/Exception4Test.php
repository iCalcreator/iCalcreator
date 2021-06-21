<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
use Kigkonsult\Icalcreator\Util\StringFactory;
use PHPUnit\Framework\TestCase;

/**
 * class Exception4Test
 *
 * Testing SEQUENCE/PERCENT_COMPLETE integer exceptions
 *
 * @since  2.27.14 - 2019-02-27
 */
class Exception4Test extends TestCase
{
    /**
     * integerTest provider
     */
    public function integerTestProvider()
    {
        $dataArr = [];

        $dataArr[] = [
            11,
            [
                Vcalendar::SEQUENCE         => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ],
            ],
            'NaN',
        ];

        $dataArr[] = [
            12,
            [
                Vcalendar::SEQUENCE         => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ],
            ],
            -1,
        ];

        $dataArr[] = [
            21,
            [
                Vcalendar::PERCENT_COMPLETE => [ Vcalendar::VTODO ],
            ],
            'NaN',
        ];

        $dataArr[] = [
            22,
            [
                Vcalendar::PERCENT_COMPLETE => [ Vcalendar::VTODO ],
            ],
            -1,
        ];

        $dataArr[] = [
            23,
            [
                Vcalendar::PERCENT_COMPLETE => [ Vcalendar::VTODO ],
            ],
            101,
        ];

        return $dataArr;
    }

    /**
     * Testing SEQUENCE/PERCENT_COMPLETE integer exceptions
     *
     * @test
     * @dataProvider integerTestProvider
     * @param int    $case
     * @param array  $propComps
     * @param mixed  $value
     */
    public function integerTest( $case, $propComps, $value )
    {
        $calendar = new Vcalendar();
        foreach( $propComps as $propName => $theComps ) {
            $setMethod    = StringFactory::getSetMethodName( $propName );
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                $ok        = false;
                try {
                    $calendar->{$newMethod}()
                             ->{$setMethod}( $value );
                }
                catch( Exception $e ) {
                    $ok = true;
                }
                $this->assertTrue( $ok, 'error in case #' . $case );
            }
        }
    }
}
