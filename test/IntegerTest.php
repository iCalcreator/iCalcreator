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

use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Exception;

/**
 * class IntegerTest, testing Integers in
 *    PERCENT-COMPLETE    VTODO
 *    PRIORITY            VEVENT and VTODO
 *    SEQUENCE            VEVENT, VTODO, or VJOURNAL
 *    REPEAT              (VEVENT) VALARM
 *
 * @since  2.27.14 - 2019-01-24
 */
class IntegerTest extends DtBase
{
    use GetPropMethodNamesTrait;

    /**
     * @var string[]
     */
    private static array  $STCPAR = [ 'X-PARAM' => 'Y-vALuE' ];

    /**
     * integerTest provider
     *
     * @return mixed[]
     */
    public function integerTestProvider() : array
    {
        $dataArr = [];

        $dataArr[] = [
            1,
            [
                IcalInterface::PERCENT_COMPLETE => [ IcalInterface::VTODO ],
                IcalInterface::PRIORITY         => [ IcalInterface::VEVENT, IcalInterface::VTODO ],
                IcalInterface::REPEAT           => [ IcalInterface::VALARM ],
            ],
            null,
            self::$STCPAR,
            Pc::factory(
                '',
                []
            ),
            ':'
        ];

        $value = null;
        $dataArr[] = [
            5,
            [
                IcalInterface::SEQUENCE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ],
            ],
            $value,
            self::$STCPAR,
            Pc::factory(
                0,
                self::$STCPAR
            ),
            Property::formatParams( self::$STCPAR ) .
            ':0'
        ];

        $value = 9;
        $dataArr[] = [
            9,
            [
                IcalInterface::PERCENT_COMPLETE => [ IcalInterface::VTODO ],
                IcalInterface::PRIORITY         => [ IcalInterface::VEVENT, IcalInterface::VTODO ],
                IcalInterface::SEQUENCE         => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ],
                IcalInterface::REPEAT           => [ IcalInterface::VALARM ],
            ],
            $value,
            self::$STCPAR,
            Pc::factory(
                $value,
                self::$STCPAR
            ),
            Property::formatParams( self::$STCPAR ) .
            ':' . $value
        ];

        $value = 19;
        $dataArr[] = [
            19,
            [
                IcalInterface::SEQUENCE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ],
            ],
            $value,
            self::$STCPAR,
            Pc::factory(
                $value,
                self::$STCPAR
            ),
            Property::formatParams( self::$STCPAR ) .
            ':' . $value
        ];

        $value = 109;
        $dataArr[] = [
            109,
            [
                IcalInterface::PERCENT_COMPLETE => [ IcalInterface::VTODO ],
            ],
            $value,
            self::$STCPAR,
            Pc::factory(
                $value,
                self::$STCPAR
            ),
            Property::formatParams( self::$STCPAR ) .
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing integers
     *
     * @test
     * @dataProvider integerTestProvider
     * @param int     $case
     * @param mixed[] $propComps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function integerTest(
        int    $case,
        array  $propComps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        $c       = new Vcalendar();
        $pcInput = false;
        foreach( $propComps as $propName => $theComps ) {
            [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                if( IcalInterface::VALARM === $theComp ) {
                    $comp      = $c->newVevent()->{$newMethod}();
                }
                else {
                    $comp      = $c->{$newMethod}();
                }
                $this->assertFalse(
                    $comp->{$isMethod}(),
                    self::getErrMsg(  '1 ', $case, __FUNCTION__, $theComp, $isMethod )
                );
                try {
                    $comp->{$setMethod}( $value, $params );
                }
                catch( Exception $e ) {
                    $ok = false;
                    if(( IcalInterface::SEQUENCE === $propName ) && ( $value > 9 )) {
                        $ok = true;
                    }
                    elseif(( IcalInterface::PERCENT_COMPLETE === $propName ) && ( $value > 100 )) {
                        $ok = true;
                    }
                    $this->assertTrue( $ok );
                    return;
                } // end catch
                $this->assertSame(
                    ((( IcalInterface::SEQUENCE === $propName ) || // empty input updates seq.
                        ( ! empty( $value ) || (( null !== $value ) && ( 0 === $value ))))),
                    $comp->{$isMethod}(),
                    self::getErrMsg(  '2 ', $case, __FUNCTION__, $theComp, $isMethod, $value )
                );
                $getValue = $comp->{$getMethod}( true );
                if(( empty( $getValue->value ) && IcalInterface::SEQUENCE === $propName )) {
                    $expectedGet->value  = 0;
                    $expectedGet->params = self::$STCPAR;
                }
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    self::getErrMsg(  '3 ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    self::getErrMsg(  '4 ', $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$isMethod}(),
                    self::getErrMsg(  '5 (after delete) ', $case, __FUNCTION__, $theComp, $isMethod )
                );
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    self::getErrMsg(  ' 6 (after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );

                if( $pcInput ) {
                    $comp->{$setMethod}( Pc::factory( $value, $params ));
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }
                $pcInput = ! $pcInput;
            } // end foreach  component
        } // end foreach,  $propName => $theComps

        $this->parseCalendarTest( $case, $c, $expectedString );
    }
}
