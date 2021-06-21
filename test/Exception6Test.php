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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Kigkonsult\Icalcreator\Util\StringFactory;

/**
 * class Exception6Test
 *
 * Testing exceptions TZOFFSETFROM and TZOFFSETTO
 *
 * @since  2.27.14 - 2019-02-27
 */
class Exception6Test extends TestCase
{
    /**
     * DateIntervalFactoryTest provider
     */
    public function DateIntervalFactoryTestProvider()
    {
        $dataArr = [];

        $dataArr[] = [
            11,
            Vcalendar::TZOFFSETFROM,
            null,
            [ Vcalendar::ALLOWEMPTY => false ]
        ];

        $dataArr[] = [
            11,
            Vcalendar::TZOFFSETFROM,
            'abc',
            []
        ];

        $dataArr[] = [
            21,
            Vcalendar::TZOFFSETTO,
            null,
            [ Vcalendar::ALLOWEMPTY => false ]
        ];

        $dataArr[] = [
            21,
            Vcalendar::TZOFFSETTO,
            'abc',
            []
        ];

        return $dataArr;
    }

    /**
     * Testing DateInterval::factory
     *
     * @test
     * @dataProvider DateIntervalFactoryTestProvider
     * @param int    $case
     * @param string $property
     * @param string|null $value
     * @param array $config
     */
    public function DateIntervalFactoryTest(  $case, $property, $value  = null, $config = [] )
    {
        $standard = new Standard( $config );
        $method   = StringFactory::getSetMethodName( $property );
        $ok = false;
        try {
            $standard->{$method}( $value );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #' . $case );
    }
}
