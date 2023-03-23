<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2023 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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

use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\DtBase;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Vcalendar;

/**
 * class VtimezonePopulateFactoryTest
 *
 * @since  2.27.14 - 2019-02-21
 */
class VtimezonePopulateFactoryTest extends DtBase
{
    /**
     * @var string
     */
    protected static string $ERRFMT = "%s Error in case #%s, %s, exp %s, got %s";

    /**
     * @var array|string[]
     */
    private static array $STCPAR = [ 'X-Y-Z' => 'VaLuE' ];

    /**
     * Testing VtimezonePopulateFactory::process, UTC (using Vcalendar::vtimezonePopulate())
     *
     * @test
     * @throws Exception
     * @throws InvalidArgumentException;
     */
    public function processTest1() : void
    {
        $calendar1 = new Vcalendar();

        $event     = $calendar1->newVevent( DATEYmdTHis );
        $vtimezone = $calendar1->newVtimezone(); // will below force removal in vtimezonePopulate

        $calendar2 = $calendar1->vtimezonePopulate();

        $vtimezone = $calendar2->getComponent( IcalInterface::VTIMEZONE );

        $expTz     = Vcalendar::UTC;
        $vtTzid    = $vtimezone->getTzid();
        $this->assertEquals(
            $expTz,
            $vtTzid,
            sprintf( self::$ERRFMT, __FUNCTION__, 11, IcalInterface::TZID, $expTz, $vtTzid )
        );
        $this->assertFalse( $vtimezone->getComponent( IcalInterface::STANDARD ));

        $this->parseCalendarTest( 12, $calendar1 );
    }

    /**
     * Testing VtimezonePopulateFactory::process, UTC
     *
     * @test
     * @throws Exception
     * @throws InvalidArgumentException;
     */
    public function processTest2() : void
    {
        $calendar1 = new Vcalendar();

        $event     = $calendar1->newVevent()->setDtstart(
            DATEYmdTHis,
            [ IcalInterface::TZID => OFFSET ]
        );
        $calendar2 = $calendar1->vtimezonePopulate();

        $vtimezone = $calendar2->getComponent( IcalInterface::VTIMEZONE );

        $this->assertTrue(
            $vtimezone->isTzidSet(),
            sprintf( self::$ERRFMT, __FUNCTION__, 21, IcalInterface::TZID, IcalInterface::TRUE, IcalInterface::FALSE )
        );
        $expTz     = IcalInterface::UTC;
        $vtTzid    = $vtimezone->getTzid();
        $this->assertEquals(
            $expTz,
            $vtTzid,
            sprintf( self::$ERRFMT, __FUNCTION__, 22, IcalInterface::TZID, $expTz, $vtTzid )
        );
        $this->assertFalse( $vtimezone->getComponent( IcalInterface::STANDARD ));

        $this->parseCalendarTest( 23, $calendar1 );
    }

    /**
     * processTest3 provider
     *
     * @return mixed[]
     * @throws Exception
     */
    public function processTest3Provider() : array
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
     * Testing VtimezonePopulateFactory::process, new TZUNTIL (and include TZID_ALIAS_OF)
     *
     * @test
     * @dataProvider processTest3Provider
     * @param int $case
     * @param mixed $xParamTz
     * @param mixed $mParamTz
     * @param int|null $from
     * @param int|null $to
     * @param mixed[] $dtstarts
     * @throws Exception
     */
    public function processTest3( int $case, mixed $xParamTz, mixed $mParamTz, null|int $from, null|int $to, array $dtstarts ) : void
    {
        $calendar1 = new Vcalendar();

        if( ! empty( $xParamTz )) {
            $calendar1->setXprop( IcalInterface::X_WR_TIMEZONE, $xParamTz );
        }
        $params = ['X-case' => $case ] + self::$STCPAR;
        foreach( $params as $k => $v ) {
            $calendar1->setXprop( $k, $v );
        }

        foreach( $dtstarts as $dtstartValue ) {
//          $e = $calendar1->newVevent( $dtstartValue );
            $vav = $calendar1->newVavailability( $dtstartValue );
        }

        $c2 = VtimezonePopulateFactory::process( // with TZUNTIL set!!
            $calendar1,
            $mParamTz ?: null,
            $params,
            $from ?: null,
            $to ?: null
        );

        $vtimezone = $c2->getComponent( IcalInterface::VTIMEZONE );

        $expTz  = ( ! empty( $mParamTz )) ? $mParamTz : $xParamTz;
        $vtTzid = $vtimezone->getTzid();
        $this->assertEquals(
            $expTz,
            $vtTzid,
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-1', IcalInterface::TZID, $expTz, $vtTzid )
        );

        // test get/set of TZID-ALIAS-OF in Vtimezone (with TZID value)
        $this->assertFalse(
            $vtimezone->isTzidaliasofSet(),
                sprintf( self::$ERRFMT, __FUNCTION__, $case . '-2a', IcalInterface::TZID_ALIAS_OF, IcalInterface::FALSE, IcalInterface::TRUE )
        );
        $vtimezone->setTzidaliasof( $vtTzid . 1 );
        $this->assertTrue(
            $vtimezone->isTzidaliasofSet(),
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-2b', IcalInterface::TZID_ALIAS_OF, IcalInterface::TRUE, IcalInterface::FALSE )
        );
        $vtimezone->setTzidaliasof( $vtTzid . 2 );
        foreach( [ 1, 2 ] as $x ) {
            $this->assertEquals(
                $vtTzid . $x,
                $vtimezone->getTzidaliasof(),
                sprintf( self::$ERRFMT, __FUNCTION__, $case . '-2c-' . $x, IcalInterface::TZID_ALIAS_OF, $expTz, $vtTzid )
            );
        }

        $calendar1->replaceComponent( $vtimezone ); // assure TZID_ALIAS_OF is set in calendars Vtimezone

        // test isset TZUNTIL
        $this->assertTrue(
            $vtimezone->isTzuntilSet(),
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-3a', IcalInterface::TZUNTIL, IcalInterface::TRUE, IcalInterface::FALSE )
        );
        $tzUntil = $vtimezone->getTzuntil();
        $this->assertInstanceOf(
            DateTime::class,
            $tzUntil,
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-3c', IcalInterface::TZUNTIL, $expTz, $vtTzid )
        );

        $standard = $vtimezone->getComponent( IcalInterface::STANDARD );
        $this->assertNotFalse(
            $standard,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-4',
                IcalInterface::STANDARD,
                IcalInterface::STANDARD,
                'false'
            )
        );

        $this->assertTrue(
            $standard->isTzoffsetfromSet(),
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-5a', IcalInterface::TZOFFSETFROM, IcalInterface::TRUE, IcalInterface::FALSE )
        );
        $getValue = $standard->getTzoffsetfrom();
        $this->assertEquals(
            '+0200',
            $getValue,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-5c',
                IcalInterface::STANDARD . '::' . IcalInterface::TZOFFSETFROM,
                '+0200',
                $getValue
            )
        );

        $this->assertTrue(
            $standard->isTzoffsetfromSet(),
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-6a', IcalInterface::TZOFFSETTO, IcalInterface::TRUE, IcalInterface::FALSE )
        );
        $getValue = $standard->getTzoffsetTo();
        $this->assertEquals(
            '+0100',
            $getValue,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-6c',
                IcalInterface::STANDARD . '::' . IcalInterface::TZOFFSETTO,
                '+0100',
                $getValue
            )
        );

        $getValue = $standard->getRdate( 1 );
        $this->assertTrue(
             ( false === $getValue ) ||
            (( 10 === (int) $getValue[0]->format( 'm' )) &&
             (  3 === (int) $getValue[0]->format( 'H' )) &&
             (  0 === (int) $getValue[0]->format( 'i' ) )),
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-7',
                IcalInterface::STANDARD . '::' . IcalInterface::RDATE,
                '20xx-10-xx-03-00-00',
                var_export( $getValue, true )
            )
        );

        $daylight = $vtimezone->getComponent( IcalInterface::DAYLIGHT );
        $this->assertNotFalse(
            $daylight,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-8',
                IcalInterface::DAYLIGHT,
                IcalInterface::DAYLIGHT,
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
                $case . '-9',
                IcalInterface::DAYLIGHT . '::' . IcalInterface::TZOFFSETFROM,
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
                $case . '-10',
                IcalInterface::DAYLIGHT . '::' . IcalInterface::TZOFFSETTO,
                '+0200',
                $getValue
            )
        );

        $getValue = $daylight->getRdate( 1 );
        $this->assertTrue(
            ( ( false === $getValue ) ||
            (( 3 === (int) $getValue[0]->format( 'm' )) &&
             ( 2 === (int) $getValue[0]->format( 'H' )) &&
             ( 0 === (int) $getValue[0]->format( 'i' ) ))),
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . '-11',
                IcalInterface::DAYLIGHT . '::' . IcalInterface::RDATE,
                '20xx-03-xx-02-00-00',
                var_export( $getValue, true )
            )
        );

        $this->parseCalendarTest( $case, $calendar1, IcalInterface::TZUNTIL ); // force xml+parse
        $calendar1Str = $calendar1->createCalendar();

        // fetch all components
        $vtimezone->resetCompCounter();  // REQUIRED
        $compArr = [];
        while( $comp = $vtimezone->getComponent()) {
            $compArr[] = $comp;
        }

        $x = 1;
        while( $vtimezone->deleteComponent( $x )) {
            ++$x;
        }
        $this->assertSame(
            0,
            $vtimezone->countComponents(),
            'deleteComponent-error ' . $case . '-12, has ' . $vtimezone->countComponents()
        );
        // set components again
        foreach( $compArr as $comp ) {
            $vtimezone->setComponent( $comp );
        }
        // check number of components
        $this->assertSame(
            count( $compArr ),
            $vtimezone->countComponents(),
            'setComponent-error ' . $case . '-13, has ' . $vtimezone->countComponents()
        );

        $vtimezone2 = $calendar1->getComponent( IcalInterface::VTIMEZONE, 1 );
        // check number of components
        $this->assertSame(
            count( $compArr ),
            $vtimezone2->countComponents(),
            'setComponent-error ' . $case . '-14, has ' . $vtimezone2->countComponents()
        );

        $calendar1->replaceComponent( $vtimezone2 );

        $this->assertEquals(
            $calendar1Str,
            $calendar1->createCalendar(),
            'calendar compare error ' . $case . '-15'
        );
    }

    /**
     * Testing VtimezonePopulateFactory::process, multiple timezones as timezone arg, test twice
     *
     * @test
     * @throws Exception
     */
    public function processTest4() : void
    {
        $timezone1 = 'Europe/Stockholm';
        $timezone2 = 'America/New_York';
        $timezone3 = 'Europe/Moscow';
        $timezone4 = 'America/Los_Angeles';

        $vCalendar = new Vcalendar();

        // set two timezones into calendar 
        $vCalendar = VtimezonePopulateFactory::process(
            $vCalendar,
            [ $timezone1, $timezone2 ]
        );

        // check first 
        $this->assertNotFalse(
            $vTimezone = $vCalendar->getComponent( Vcalendar::VTIMEZONE, 1 ),
            __METHOD__ . ' 11 Vtimezone not found'
        );
        $this->assertNotFalse(
            $tzId = $vTimezone->getTzId(),
            __METHOD__ . ' 12 TZID not found'
        );
        $this->assertSame(
            $timezone1,
            $tzId,
            __METHOD__ . ' 13 expected ' . $timezone1 . ' got ' . $tzId
        );

        // check second 
        $this->assertNotFalse(
            $vTimezone = $vCalendar->getComponent( Vcalendar::VTIMEZONE, 2 ),
            __METHOD__ . ' 21 Vtimezone not found'
        );
        $this->assertNotFalse(
            $tzId = $vTimezone->getTzId(),
            __METHOD__ . ' 22 TZID not found'
        );
        $this->assertSame(
            $timezone2,
            $tzId,
            __METHOD__ . ' 23 expected ' . $timezone1 . ' got ' . $tzId
        );

        // set two other timezones into calendar
        $vCalendar = VtimezonePopulateFactory::process(
            $vCalendar,
            [ $timezone3, $timezone4 ]
        );

        // check first 
        $this->assertNotFalse(
            $vTimezone = $vCalendar->getComponent( Vcalendar::VTIMEZONE, 1 ),
            __METHOD__ . ' 31 Vtimezone not found'
        );
        $this->assertNotFalse(
            $tzId = $vTimezone->getTzId(),
            __METHOD__ . ' 32 TZID not found'
        );
        $this->assertSame(
            $timezone3,
            $tzId,
            __METHOD__ . ' 33 expected ' . $timezone3 . ' got ' . $tzId
        );

        // check second 
        $this->assertNotFalse(
            $vTimezone = $vCalendar->getComponent( Vcalendar::VTIMEZONE, 2 ),
            __METHOD__ . ' 41 Vtimezone not found'
        );
        $this->assertNotFalse(
            $tzId = $vTimezone->getTzId(),
            __METHOD__ . ' 42 TZID not found'
        );
        $this->assertSame(
            $timezone4,
            $tzId,
            __METHOD__ . ' 43 expected ' . $timezone4 . ' got ' . $tzId
        );
    }
}
