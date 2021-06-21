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
namespace Kigkonsult\Icalcreator\Util;

use Kigkonsult\Icalcreator\DtBase;
use Kigkonsult\Icalcreator\Vcalendar;
use InvalidArgumentException;
use Exception;

/**
 * class VtimezonePopulateFactoryTest
 *
 * @since  2.27.14 - 2019-02-21
 */
class VtimezonePopulateFactoryTest extends DtBase
{
    private static $ERRFMT = "%s Error in case #%s, %s, exp %s, got %s";
    private static $STCPAR = [ 'X-Y-Z' => 'VaLuE' ];

    /**
     * set and restore local timezone from const
     */
    public static $oldTimeZone = null;

    public static function setUpBeforeClass()
    {
        self::$oldTimeZone = date_default_timezone_get();
        date_default_timezone_set( LTZ );
    }

    public static function tearDownAfterClass()
    {
        date_default_timezone_set( self::$oldTimeZone );
    }

    /**
     * Testing VtimezonePopulateFactory::process, UTC
     *
     * @test
     * @throws Exception
     * @throws InvalidArgumentException;
     */
    public function processTest1()
    {
        $calendar1 = new Vcalendar();

        $event     = $calendar1->newVevent()->setDtstart( DATEYmdTHis );
        $vtimezone = $calendar1->newVtimezone();

        $calendar2 = VtimezonePopulateFactory::process( $calendar1 );

        $vtimezone = $calendar2->getComponent( Vcalendar::VTIMEZONE );

        $expTz     = Vcalendar::UTC;
        $vtTzid    = $vtimezone->getTzid();
        $this->assertEquals(
            $expTz,
            $vtTzid,
            sprintf( self::$ERRFMT, __FUNCTION__, 11, Vcalendar::TZID, $expTz, $vtTzid )
        );
        $this->assertFalse( $vtimezone->getComponent( Vcalendar::STANDARD ));

        $this->parseCalendarTest( 12, $calendar1 );
    }

    /**
     * Testing VtimezonePopulateFactory::process, UTC
     *
     * @test
     * @throws Exception
     * @throws InvalidArgumentException;
     */
    public function processTest2()
    {
        $calendar1 = new Vcalendar();

        $event     = $calendar1->newVevent()->setDtstart(
            DATEYmdTHis,
            [ Vcalendar::TZID => OFFSET ]
        );
        $calendar2 = $calendar1->vtimezonePopulate();

        $vtimezone = $calendar2->getComponent( Vcalendar::VTIMEZONE );

        $expTz     = Vcalendar::UTC;
        $vtTzid    = $vtimezone->getTzid();
        $this->assertEquals(
            $expTz,
            $vtTzid,
            sprintf( self::$ERRFMT, __FUNCTION__, 21, Vcalendar::TZID, $expTz, $vtTzid )
        );
        $this->assertFalse( $vtimezone->getComponent( Vcalendar::STANDARD ));

        $this->parseCalendarTest( 21, $calendar1 );
    }

    /**
     * processTest3 provider
     */
    public function processTest3Provider()
    {
        $dataArr = [];

        $timezone = 'Europe/Stockholm';

        $dataArr[] = [ // param timezone in X-prop X_WR_TIMEZONE and NO DTSTART
            1,
            $timezone,
            null,
            null, null,  // from/to
            []
        ];

        $dataArr[] = [ // param timezone in X-prop X_WR_TIMEZONE and ONE DTSTART
            2,
            $timezone,
            null,
            null, null,  // from/to
            [ '20170312' ]
        ];

        $dataArr[] = [ // param timezone in X-prop X_WR_TIMEZONE and TWO DTSTARTs
            3,
            $timezone,
            null,
            null, null,  // from/to
            [ '20160912', '20181113' ]
        ];

        $dataArr[] = [ // method arg timezone and ONE DTSTART
            3,
            null,
            $timezone,
            null, null,  // from/to
            [ '20170312' ]
        ];

        $dataArr[] = [ // method arg timezone and NO DTSTART
            4,
            null,
            $timezone,
            null, null,  // from/to
            [ '20170312' ]
        ];

        $dataArr[] = [ // method arg timezone and TWO DTSTARTs
            5,
            null,
            $timezone,
            null, null,  // from/to
            [ '20160912', '20181113' ]
        ];


        $from = DateTimeFactory::factory( '20161001', $timezone )->getTimestamp();
        $dataArr[] = [ // param timezone in X-prop X_WR_TIMEZONE and from
            6,
            $timezone,
            null,
            $from, null,  // from/to
            []
        ];

        $to = DateTimeFactory::factory( '20170312', $timezone )->getTimestamp();
        $dataArr[] = [ // method arg timezone and from
            7,
            $timezone,
            null,
            null, $to,  // from/to
            []
        ];

        $from = DateTimeFactory::factory( '20170312', $timezone )->modify( '-7 month' )->getTimestamp();
        $to   = DateTimeFactory::factory( '20170312', $timezone )->modify( '+18 month' )->getTimestamp();
        $dataArr[] = [ // param timezone in X-prop X_WR_TIMEZONE and from/to
            8,
            $timezone,
            null,
            $from, $to,  // from/to
            []
        ];


        $from = DateTimeFactory::factory( '20161001', $timezone )->getTimestamp();
        $dataArr[] = [ // param timezone in X-prop X_WR_TIMEZONE and from
            9,
            null,
            $timezone,
            $from, null,  // from/to
            []
        ];

//        $to = DateTimeFactory::factory( '20170312', $timezone )->getTimestamp();
        $to = DateTimeFactory::factory( '20170606', $timezone )->getTimestamp();
        $dataArr[] = [ // method arg timezone and from
            10,
            null,
            $timezone,
            null, $to,  // from/to
            []
        ];

        $from = DateTimeFactory::factory( '20170312', $timezone )->modify( '-7 month' )->getTimestamp();
        $to   = DateTimeFactory::factory( '20170312', $timezone )->modify( '+18 month' )->getTimestamp();
        $dataArr[] = [ // param timezone in X-prop X_WR_TIMEZONE and from/to
            11,
            null,
            $timezone,
            $from, $to,  // from/to
            []
        ];

        return $dataArr;
    }

    /**
     * Testing VtimezonePopulateFactory::process
     *
     * @test
     * @dataProvider processTest3Provider
     * @param int    $case
     * @param string $xParamTz
     * @param string $mParamTz
     * @param int    $from
     * @param int    $to
     * @param array  $dtstarts
     * @throws Exception
     * @throws InvalidArgumentException;
     */
    public function processTest3( $case, $xParamTz, $mParamTz, $from, $to, $dtstarts )
    {
        $calendar1 = new Vcalendar();

        if( ! empty( $xParamTz )) {
            $calendar1->setXprop( Vcalendar::X_WR_TIMEZONE, $xParamTz );
        }
        $params = ['X-case' => $case ] + self::$STCPAR;
        foreach( $params as $k => $v ) {
            $calendar1->setXprop( $k, $v );
        }

        foreach( $dtstarts as $dtstartValue ) {
            $e = $calendar1->newVevent()->setDtstart( $dtstartValue );
        }

        $c2 = VtimezonePopulateFactory::process(
            $calendar1,
            $mParamTz ?: null,
            $params,
            $from ?: null,
            $to ?: null
        );

        $vtimezone = $c2->getComponent( Vcalendar::VTIMEZONE );

        $expTz  = ( ! empty( $mParamTz )) ? $mParamTz : $xParamTz;
        $vtTzid = $vtimezone->getTzid();
        $this->assertEquals(
            $expTz,
            $vtTzid,
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-1', Vcalendar::TZID, $expTz, $vtTzid )
        );

        $standard = $vtimezone->getComponent( Vcalendar::STANDARD );
        $this->assertNotFalse(
            $standard,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-2',
                Vcalendar::STANDARD,
                Vcalendar::STANDARD,
                'false'
            )
        );

        $getValue = $standard->getTzoffsetfrom();
        $this->assertEquals(
            '+0200',
            $getValue,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-3',
                Vcalendar::STANDARD . '::' . Vcalendar::TZOFFSETFROM,
                '+0200',
                $getValue
            )
        );

        $getValue = $standard->getTzoffsetTo();
        $this->assertEquals(
            '+0100',
            $getValue,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-4',
                Vcalendar::STANDARD . '::' . Vcalendar::TZOFFSETTO,
                '+0100',
                $getValue
            )
        );

        $getValue = $standard->getRdate( 1 );
        $this->assertTrue(
             ( false == $getValue ) ||
            (( 10 == $getValue[0]->format( 'm' )) &&
             (  3 == $getValue[0]->format( 'H' )) &&
             (  0 == $getValue[0]->format( 'i' ) )),
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-5',
                Vcalendar::STANDARD . '::' . Vcalendar::RDATE,
                '20xx-10-xx-03-00-00',
                var_export( $getValue[0], true )
            )
        );

        $daylight = $vtimezone->getComponent( Vcalendar::DAYLIGHT );
        $this->assertNotFalse(
            $daylight,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-6',
                Vcalendar::DAYLIGHT,
                Vcalendar::DAYLIGHT,
                'false'
            )
        );

        $getValue = $daylight->getTzoffsetfrom();
        $this->assertEquals(
            '+0100',
            $getValue,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-7',
                Vcalendar::DAYLIGHT . '::' . Vcalendar::TZOFFSETFROM,
                '+0100',
                $getValue
            )
        );

        $getValue = $daylight->getTzoffsetTo();
        $this->assertEquals(
            '+0200',
            $getValue,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-8',
                Vcalendar::DAYLIGHT . '::' . Vcalendar::TZOFFSETTO,
                '+0200',
                $getValue
            )
        );

        $getValue = $daylight->getRdate( 1 );
        $this->assertTrue(
            ( ( false == $getValue ) ||
            (( 3 == $getValue[0]->format( 'm' )) &&
             ( 2 == $getValue[0]->format( 'H' )) &&
             ( 0 == $getValue[0]->format( 'i' ) ))),
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-9',
                Vcalendar::DAYLIGHT . '::' . Vcalendar::RDATE,
                '20xx-03-xx-02-00-00',
                var_export( $getValue[0], true )
            )
        );

        $this->parseCalendarTest( 1, $calendar1 );
        $calendar1Str = $calendar1->createCalendar();

        // fetch all components
        $compArr = [];
        while( $comp = $vtimezone->getComponent()) {
            $compArr[] = $comp;
        }
        $x = 1;
        while( $vtimezone->deleteComponent( $x )) {
            $x += 1;
        }
        $this->assertTrue(
            ( 0 == $vtimezone->countComponents()),
            'deleteComponent-error ' . $case . '-10, has ' . $vtimezone->countComponents()
        );
        // check components are set
        foreach( $compArr as $comp ) {
            $vtimezone->setComponent( $comp );
        }
        // check number of components
        $this->assertTrue(
            ( count( $compArr ) == $vtimezone->countComponents()),
            'setComponent-error ' . $case . '-11, has ' . $vtimezone->countComponents()
        );

        $vtimezone2 = $calendar1->getComponent( Vcalendar::VTIMEZONE, 1 );

        $calendar1->replaceComponent( $vtimezone2 );

        $this->assertEquals(
            $calendar1Str,
            $calendar1->createCalendar(),
            'calendar compare error ' . $case . '-12'
        );
    }
}
