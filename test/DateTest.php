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

use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class DateTest, testing VALUE DATE, also empty value, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
 *
 * @since  2.27.14 - 2019-01-24
 */
class DateTest extends DtBase
{
    private static array $compsProps = [
        IcalInterface::VEVENT => [
            IcalInterface::DTSTART,
            IcalInterface::DTEND,
            IcalInterface::RECURRENCE_ID,
            IcalInterface::EXDATE,
            IcalInterface::RDATE
        ],
        IcalInterface::VTODO         => [ IcalInterface::DTSTART, IcalInterface::DUE, IcalInterface::RECURRENCE_ID ],
        IcalInterface::VJOURNAL      => [ IcalInterface::DTSTART, IcalInterface::RECURRENCE_ID ],
//          IcalInterface::VAVAILABILITY => [ IcalInterface::DTSTART, IcalInterface::DTEND ], datetime required
    ];
    private static array $compsProps2 = [
        IcalInterface::VEVENT        => [ IcalInterface::EXDATE, IcalInterface::RDATE ],
        IcalInterface::AVAILABLE     => [ IcalInterface::EXDATE, IcalInterface::RDATE ]
    ];

    private static array $compsProps3 = [
        IcalInterface::VEVENT        => [ IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
        IcalInterface::VTODO         => [ IcalInterface::LAST_MODIFIED, IcalInterface::CREATED, IcalInterface::COMPLETED ],
    ];

    /**
     * set and restore local timezone from const
     */
    public static ?string $oldTimeZone = null;

    /**
     * @return void
     */
    public static function setUpBeforeClass() : void
    {
        self::$oldTimeZone = date_default_timezone_get();
        date_default_timezone_set( LTZ );
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass() : void
    {
        date_default_timezone_set( self::$oldTimeZone );
    }

    /**
     * Test date isPropSet Methods and properties with no-args set-method (i.e. 'now')
     *
     * @test
     * @throws Exception
     */
    public function isPropSetTest1() : void
    {
        foreach( self::$compsProps as $theComp => $propNames ) {
            $this->isPropSetTest2( 1, $theComp, $propNames );
        }
        foreach( self::$compsProps2 as $theComp => $propNames ) {
            $this->isPropSetTest2( 2, $theComp, $propNames );
        }
        foreach( self::$compsProps3 as $theComp => $propNames ) {
            $vcalendar = new Vcalendar();
            $comp      = $vcalendar->{'new' . $theComp}();
            foreach( $propNames as $propName ) {
                $isPropSetMethod = StringFactory::getIsMethodSetName( $propName );
                $setMethod       = StringFactory::getSetMethodName( $propName );
                $this->assertFalse(
                    $comp->{$isPropSetMethod}(),
                    'error case 3-1 ' . $comp->getCompType() . '::' . $propName . ', expected false'
                );
                if(( IcalInterface::VTODO === $theComp ) && ( IcalInterface::COMPLETED === $propName )) {
                    $comp->{$setMethod}( new dateTime());
                }
                else {
                    $comp->{$setMethod}();
                }
                $this->assertTrue(
                    $comp->{$isPropSetMethod}(),
                    'error case 3-2 ' . $comp->getCompType() . '::' . $propName . ', expected true'
                );
            }
        }
    }

    /**
     * @param int $case
     * @param string $theComp
     * @param array $propNames
     */
    private function isPropSetTest2( int $case, string $theComp, array $propNames ) : void
    {
        $case += 10;
        $vcalendar = new Vcalendar();
        $newMethod = 'new' . $theComp;
        $comp = match ( true ) {
            IcalInterface::PARTICIPANT === $theComp => $vcalendar->newVevent()->{$newMethod}(),
            IcalInterface::AVAILABLE === $theComp   => $vcalendar->newVavailability()->{$newMethod}(),
            default                                 => $vcalendar->{$newMethod}(),
        };
        $this->assertTrue(
            $comp->isUidSet(),
            'error case ' . $case . '-1 ' . $theComp . '::dtstart, expected true'
        );
        $this->assertTrue(
            $comp->isDtstampSet(),
            'error case ' . $case . '-2 ' . $theComp . '::dtstart, expected true'
        );
        $this->isPropSetTest3( $case, $comp, $propNames );
    }

    /**
     * @param int $case
     * @param CalendarComponent $comp
     * @param array $propNames
     */
    private function isPropSetTest3( int $case, CalendarComponent $comp, array $propNames ) : void
    {
        $case    += 100;
        $dateTime = new DateTime();
        foreach( $propNames as $propName ) {
            $isPropSetMethod = StringFactory::getIsMethodSetName( $propName );
            $setMethod       = StringFactory::getSetMethodName( $propName );
            $this->assertFalse(
                $comp->{$isPropSetMethod}(),
                'error case ' . $case . '-11 ' . $comp->getCompType() . '::' . $propName . ', expected false'
            );
            $comp->{$setMethod}( $dateTime );
            $this->assertTrue(
                $comp->{$isPropSetMethod}(),
                'error case ' . $case . '-12 ' . $comp->getCompType() . '::' . $propName . ', expected true'
            );
        }
    }

    /**
     * testDATE provider
     *
     * @return mixed[]
     * @throws Exception
     */
    public function DATEProvider() : array
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dataArr[] = [ // test set #100 empty
            100,
            null,
            null,
            Pc::factory()->setEmpty(),
            ':'
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmd );
        $dataArr[] = [ // test set #101 DateTime
            101,
            $dateTime,
            [ IcalInterface::VALUE => IcalInterface::DATE ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::VALUE => IcalInterface::DATE ]
            ),
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis );
        $dataArr[] = [ // test set #102 DateTime
            102,
            $dateTime,
            [ IcalInterface::VALUE => IcalInterface::DATE ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::VALUE => IcalInterface::DATE ]
            ),
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dataArr[] = [ // test set #103 DateTime
            103,
            $dateTime,
            [],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => LTZ ]
            ),
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ)
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd );
        $dataArr[] = [ // test set #114 string
            114,
            DATEYmd,
            [ IcalInterface::VALUE => IcalInterface::DATE ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::VALUE => IcalInterface::DATE ]
            ),
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmdTHis );
        $dataArr[] = [ // test set #115 string
            115,
            DATEYmdTHis,
            [ IcalInterface::VALUE => IcalInterface::DATE ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::VALUE => IcalInterface::DATE ]
            ),
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, IcalInterface::UTC );
        $dataArr[] = [ // test set #116 string
            116,
            DATEYmdTHis,
            [],
            Pc::factory(
                $dateTime,
                []
            ),
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmdTHis, IcalInterface::UTC );
        $dataArr[] = [ // test set #117 string
            117,
            DATEYmdTHis . 'Z',
            [ IcalInterface::VALUE => IcalInterface::DATE ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::VALUE => IcalInterface::DATE ]
            ),
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, IcalInterface::UTC );
        $dataArr[] = [ // test set #118 string
            118,
            DATEYmdTHis . 'Z',
            [],
            Pc::factory(
                $dateTime,
                []
            ),
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( '20160305' );
        for( $x = 1; $x < 10; $x++ ) {
            $dataArr[] = [ // test set #101 DateTime
                200 + $x,
                $dateTime->format( 'Ymd' ),
                [ IcalInterface::VALUE => IcalInterface::DATE ],
                Pc::factory(
                    clone $dateTime,
                    [ IcalInterface::VALUE => IcalInterface::DATE ]
                ),
                $this->getDateTimeAsCreateShortString( $dateTime )
            ];
            $dateTime->modify( '-1 day ' );
        } // end for

        return $dataArr;
    }

    /**
     * Testing VALUE DATE, also empty value, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DATEProvider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testDATE(
        int    $case,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
//      echo __FUNCTION__ . ' start #' . $case . ' value : ' . var_export( $value, true ) . PHP_EOL; // test ###

        $this->theTestMethod( $case, self::$compsProps, $value, $params, $expectedGet, $expectedString );
        $this->theTestMethod1b( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
    }
}
