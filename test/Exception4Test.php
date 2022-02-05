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
     *
     * @return mixed[]
     */
    public function integerTestProvider() : array
    {
        $dataArr = [];

        $dataArr[] = [
            11,
            [
                IcalInterface::SEQUENCE         => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ],
            ],
            'NaN',
        ];

        $dataArr[] = [
            12,
            [
                IcalInterface::SEQUENCE         => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ],
            ],
            -1,
        ];

        $dataArr[] = [
            21,
            [
                IcalInterface::PERCENT_COMPLETE => [ IcalInterface::VTODO ],
            ],
            'NaN',
        ];

        $dataArr[] = [
            22,
            [
                IcalInterface::PERCENT_COMPLETE => [ IcalInterface::VTODO ],
            ],
            -1,
        ];

        $dataArr[] = [
            23,
            [
                IcalInterface::PERCENT_COMPLETE => [ IcalInterface::VTODO ],
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
     * @param int     $case
     * @param mixed[] $propComps
     * @param mixed   $value
     */
    public function integerTest( int $case, array $propComps, mixed $value ) : void
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
