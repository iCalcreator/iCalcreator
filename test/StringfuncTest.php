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

use Kigkonsult\Icalcreator\Util\StringFactory;
use PHPUnit\Framework\TestCase;

class StringfuncTest extends TestCase
{
    /**
     * @test
     */
    public function afterTest() : void
    {
        $this->assertSame(
            '',
            StringFactory::after( 'needle', 'haystack' )
        );
        $this->assertSame(
            'after',
            StringFactory::after( 'needle', 'haystackneedleafter' )
        );
    }

    /**
     * @test
     */
    public function afterLastTest() : void
    {
        $this->assertSame(
            '',
            StringFactory::afterLast( 'needle', 'haystack' )
        );
        $this->assertSame(
            'after2',
            StringFactory::afterLast( 'needle', 'haystackneedleafter1needleafter2' )
        );
    }

    /**
     * @test
     */
    public function beforeTest() : void
    {
        $this->assertSame(
            '',
            StringFactory::before( 'needle', 'haystack' )
        );
        $this->assertSame(
            'haystackbefore',
            StringFactory::before( 'needle', 'haystackbeforeneedle' )
        );
    }

    /**
     * @test
     */
    public function beforeLastTest() : void
    {
        $this->assertSame(
            '',
            StringFactory::beforeLast( 'needle', 'haystack' )
        );
        $this->assertSame(
            'haystackbeforeneedlebefore',
            StringFactory::beforeLast( 'needle', 'haystackbeforeneedlebeforeneedle' )
        );
    }

    /**
     * @test
     */
    public function betweenTest() : void
    {
        // If no needles found in haystack, '' is returned
        $this->assertSame(
            '',
            StringFactory::between( 'needle1', 'needle2', 'haystack' )
        );
        // If only needle1 found, substring after is returned
        $this->assertSame(
            'betweenneedle3',
            StringFactory::between( 'needle1', 'needle2', 'haystackneedle1betweenneedle3' )
        );
        //If only needle2 found, substring before is returned
        $this->assertSame(
            'haystackneedle0between',
            StringFactory::between( 'needle1', 'needle2', 'haystackneedle0betweenneedle2' )
        );
        // and both needles found
        $this->assertSame(
            'between',
            StringFactory::between( 'needle1', 'needle2', 'haystackneedle1betweenneedle2' )
        );
        $this->assertSame(
            'between1',
            StringFactory::between(
                'needle1',
                'needle2',
                'haystackneedle1between1needle2needle1between2needle2'
            )
        );
    }

    /**
     * @test
     */
    public function betweenLastTest() : void
    {
        // If no needles found in haystack, '' is returned
        $this->assertSame(
            '',
            StringFactory::betweenLast(
                'needle1',
                'needle2',
                'haystack'
            )
        );
        // If only needle1 found, after(last) is returned
        $this->assertSame(
            'betweenLastneedle3',
            StringFactory::betweenLast(
                'needle1',
                'needle2',
                'haystackneedle1betweenLastneedle3'
            )
        );
        // If only needle2 found, before(last) is returned
        $this->assertSame(
            'haystackneedle0betweenLast',
            StringFactory::betweenLast(
                'needle1',
                'needle2',
                'haystackneedle0betweenLastneedle2'
            )
        );
        // and both needles found
        $this->assertSame(
            'betweenLast',
            StringFactory::betweenLast(
                'needle1',
                'needle2',
                'haystackneedle1betweenLastneedle2'
            )
        );
        $this->assertSame(
            'betweenLast2',
            StringFactory::betweenLast(
                'needle1',
                'needle2',
                'haystackneedle1betweenLast1needle2needle1betweenLast2needle2'
            )
        );
    }
}
