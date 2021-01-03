<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator;

use PHPUnit\Framework\TestCase;
use Kigkonsult\Icalcreator\Util\IcalvCardFactory;

include_once 'SelectComponentsTest.php';

/**
 * class IcalvCardFactoryTest
 *
 * Testing IcalvCardFactory
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-03-16
 */
class IcalvCardFactoryTest extends TestCase
{

    /**
     * IcalvCardFactoryTest provider
    /**
     * SelectComponentsTest provider
     */
    public function IcalvCardFactoryTestProvider() {

        $dataArr = [];

        $dataArr[] = [
            11,
            SelectComponentsTest::veventCalendarSubProvider(),
            null,
            false
        ];

        $dataArr[] = [
            12,
            SelectComponentsTest::veventCalendarSubProvider(),
            '3.0',
            true
        ];

        $dataArr[] = [
            12,
            SelectComponentsTest::veventCalendarSubProvider(),
            '4.0',
            null
        ];

        $dataArr[] = [
            21,
            SelectComponentsTest::vtodoCalendarSubProvider(),
            null,
            false
        ];

        $dataArr[] = [
            21,
            SelectComponentsTest::vtodoCalendarSubProvider(),
            '3.0',
            true
        ];

        $dataArr[] = [
            21,
            SelectComponentsTest::vtodoCalendarSubProvider(),
            '4.0',
            null
        ];

        return $dataArr;
    }
//        return SelectComponentsTest::SelectComponentsTestProvider();

    /**
     * Testing IcalvCardFactory::iCal2vCards (+iCal2vCard+...)
     *
     * @test
     * @dataProvider IcalvCardFactoryTestProvider'
     * @param int       $case
     * @param Vcalendar $vcalendar
     * @param string    $version
     * @param bool      $inclParams  fetch from values or include from parameters
     */
    public function IcalvCardFactoryTest( $case, Vcalendar $vcalendar, $version, $inclParams ) {
        $vCards = IcalvCardFactory::iCal2vCards( $vcalendar, $version, $inclParams, $count );

        if( ! empty( $version )) {
            $this->assertNotFalse( strpos( $vCards, $version ));
        }

        $this->assertEquals( $count, substr_count( $vCards,  'BEGIN:VCARD' ));
    }

}
