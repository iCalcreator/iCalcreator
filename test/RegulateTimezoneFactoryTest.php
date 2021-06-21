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

use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\DtBase;
use Kigkonsult\Icalcreator\Vcalendar;
use UnexpectedValueException;

/**
 * class RegulateTimezoneFactoryTest
 *
 * @covers \Kigkonsult\Icalcreator\Util\RegulateTimezoneFactory
 *
 * @since  2.27.14 - 2019-02-27
 */
class RegulateTimezoneFactoryTest extends DtBase
{
    /**
     * TzTest1 provider
     */
    public function TzTest1Provider()
    {
        $dataArr = [];

        $case = 100;
        foreach( RegulateTimezoneFactory::$MStimezoneToOffset as $otherTimezone => $offset ) {
            $dataArr[] = [
                ++$case,
                $otherTimezone,
                1
            ];
        }

        $case = 200;
        $timezoneIdentifiers = DateTimeZone::listIdentifiers();
        foreach( $timezoneIdentifiers as $tix => $timezoneIdentifier ) {
            if( 0 != ( $tix % 10 )) {
                continue;
            }
            $dataArr[] = [
                ++$case,
                $timezoneIdentifier,
                10
            ];
        } // end foreach

        return $dataArr;
    }


    /**
     * Test all ms timezones, none found after conversion,
     * Test (every 10th) PHP timezones (but no UTC), 2 found after conversion (ie no conversion),
     *
     * @test
     * @dataProvider TzTest1Provider
     * @param int    $case
     * @param string $otherTimezone
     * @param int    $hitsAfterProcess
     * @throws Exception
     */
    public function TzTest1( $case, $otherTimezone, $hitsAfterProcess )
    {
        $case += 1000;
        static $CALFMT =
            'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//kigkonsult.se//NONSGML kigkonsult.se iCalcreator 2.27.15//
X-TZID;%1$s
BEGIN:VTIMEZONE
TZID:%1$s
X-COMMENT:utan andra params
END:VTIMEZONE
BEGIN:VTIMEZONE
TZID;X-PROP1=XVAL1:%1$s
X-COMMENT:en param
END:VTIMEZONE
BEGIN:VEVENT
UID:20190312T204257CET-1111111111@kigkonsult.se
DTSTART;TZID=%1$s:20190421T090000
X-COMMENT:utan andra params
END:VEVENT
BEGIN:VEVENT
UID:20190312T204257CET-1111111111@kigkonsult.se
DTSTART;X-PROP1=XVAL1;TZID=%1$s:20190421T090000
X-COMMENT:en param före
END:VEVENT
BEGIN:VEVENT
UID:20190312T204257CET-1111111111@kigkonsult.se
DTSTART;TZID=%1$s;X-PROP3=XVAL3:20190421T090000
X-COMMENT:en param after
END:VEVENT
BEGIN:VEVENT
UID:20190312T204257CET-1111111111@kigkonsult.se
DTSTART;X-PROP1=XVAL1;TZID=%1$s;X-PROP2=XVAL2:20190421T090000
X-COMMENT:params både före och efter
EXDATE;VALUE=DATE:20010101,20010102
EXDATE:20020202T020202Z
EXDATE;TZID=%1$s:20030303T030303
RDATE;VALUE=DATE:20040401,20040402
RDATE:20050505T050505Z
RDATE;TZID=%1$s:20060606T060606
RDATE;VALUE=PERIOD:20070707T070701Z/20070707T070702Z,20070707T070703Z/PT7H
RDATE;TZID=%1$s;VALUE=PERIOD:20080808T080801/20080808T080802,20080808T080803/PT8H
END:VEVENT
END:VCALENDAR
';
        $calendar  = sprintf( $CALFMT, $otherTimezone );
        $calendar2 = RegulateTimezoneFactory::process( $calendar );

        if( 'UTC' == $otherTimezone ) {
            $hitsAfterProcess = 3;
        }
        $this->assertEquals(
            $hitsAfterProcess,
            substr_count( $calendar2, $otherTimezone ),
            'error in case #' . $case . ', timezone : ' . $otherTimezone . PHP_EOL . $calendar2
        );

        $this->parseCalendarTest(
            $case,
            Vcalendar::factory()->parse( $calendar2 )
        );
    }

    /**
     * Test ms timezone 'Customized Time Zone'
     *
     * @test
     * @throws Exception
     */
    public function TzTest2()
    {
        $calendar =
'BEGIN:VCALENDAR
METHOD:PUBLISH
PRODID:Microsoft Exchange Server 2010
VERSION:2.0
X-WR-CALNAME:Kalender
BEGIN:VTIMEZONE
TZID:W. Europe Standard Time
BEGIN:STANDARD
DTSTART:16010101T030000
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:16010101T020000
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VTIMEZONE
TZID:Customized Time Zone
BEGIN:STANDARD
DTSTART:16010101T030000
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=4SU;BYMONTH=10
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:16010101T020000
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
RRULE:FREQ=YEARLY;UNTIL=20190329T230000Z;INTERVAL=1;BYMONTHDAY=30;BYMONTH=3
UID:040000008200E00074C5B7101A82E0080000000010E8CC0CF8FCD201000000000000000
 010000000AE2AC3B60D6B0040ABD8541542CF30AB
SUMMARY:Geburtstag von Sebastiano La Ferla
DTSTART;TZID=W. Europe Standard Time:20190330T010000
DTEND;TZID=W. Europe Standard Time:20190331T010000
RECURRENCE-ID:20190331T020000Z
CLASS:PUBLIC
PRIORITY:5
DTSTAMP:20190626T190359Z
TRANSP:TRANSPARENT
STATUS:CONFIRMED
SEQUENCE:0
X-MICROSOFT-CDO-APPT-SEQUENCE:0
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-MICROSOFT-CDO-INTENDEDSTATUS:BUSY
X-MICROSOFT-CDO-ALLDAYEVENT:FALSE
X-MICROSOFT-CDO-IMPORTANCE:1
X-MICROSOFT-CDO-INSTTYPE:1
X-MICROSOFT-DONOTFORWARDMEETING:FALSE
X-MICROSOFT-DISALLOW-COUNTER:FALSE
END:VEVENT
END:VCALENDAR
';
        $tzFactory = RegulateTimezoneFactory::factory();
        $this->assertFalse( $tzFactory->isInputiCalSet());

        $tzFactory->setInputiCal( $calendar );
        $this->assertTrue( $tzFactory->isInputiCalSet());

        $this->assertFalse( $tzFactory->hasOtherTzPHPtzMap( 'otherTimezone' ));
        $tzFactory->addOtherTzPhpRelation( 'otherTimezone', 'Europe/Stockholm' );
        $this->assertTrue( $tzFactory->hasOtherTzPHPtzMap( 'otherTimezone' ));

        $calendar2 = $tzFactory->processCalendar( $calendar )->getOutputiCal();

        $this->assertFalse(
            stripos( $calendar2, 'W. Europe Standard Time' )
        );
        $this->assertFalse(
            stripos( $calendar2, 'Customized Time Zone' )
        );

        $this->parseCalendarTest(
            100,
            Vcalendar::factory()->parse( $calendar2 )
        );
    }

    private static $calendar2 =
        'BEGIN:VCALENDAR
METHOD:PUBLISH
PRODID:Microsoft Exchange Server 2010
VERSION:2.0
BEGIN:VEVENT
RRULE:FREQ=YEARLY;UNTIL=20190329T230000Z;INTERVAL=1;BYMONTHDAY=30;BYMONTH=3
UID:040000008200E00074C5B7101A82E0080000000010E8CC0CF8FCD201000000000000000
 010000000AE2AC3B60D6B0040ABD8541542CF30AB
SUMMARY:Geburtstag von Sebastiano La Ferla
DTSTART;TZID=%1$s:20190330T010000
DTEND;TZID=%1$s:20190331T010000
DTSTAMP:20190626T190359Z
END:VEVENT
END:VCALENDAR
';

    /**
     * Test without Vtimezone
     *
     * @test
     * @dataProvider TzTest1Provider
     * @param int    $case
     * @param string $otherTimezone
     * @param int    $hitsAfterProcess
     * @throws Exception
     */
    public function TzTest3( $case, $otherTimezone, $hitsAfterProcess )
    {
        $case += 3000;
        $calendar = sprintf( self::$calendar2, $otherTimezone );
        $calendar2 = RegulateTimezoneFactory::process( $calendar );

        if( 3200 > $case ) {
            $this->assertFalse(
                strpos( $calendar2, $otherTimezone )
            );
        }
        else {
            $this->assertTrue(
                ( false !== strpos( $calendar2, $otherTimezone ))
            );
        }

        $this->parseCalendarTest(
            $case,
            Vcalendar::factory()->parse( $calendar2 )
        );
    }

    /**
     * Test update of (static and inner) arrays
     *
     * @test
     * @throws Exception
     */
    public function TzTest5()
    {
        RegulateTimezoneFactory::addMStimezoneToOffset( 'otherTimezone', 12345 );
        $this->assertArrayHasKey(
            'otherTimezone',
            RegulateTimezoneFactory::$MStimezoneToOffset
        );

        RegulateTimezoneFactory::addOtherTzMapToPhpTz( 'otherTimezone', 'Europe/Stockholm' );
        $this->assertArrayHasKey(
            'otherTimezone',
            RegulateTimezoneFactory::$otherTzToPhpTz
        );

        $calendar = sprintf( self::$calendar2, 'W. Europe Standard Time' );
        $otherTzToPhpTz = RegulateTimezoneFactory::$otherTzToPhpTz;
        $tzFactory = new RegulateTimezoneFactory( self::$calendar2 );
        $tzFactory->addOtherTzPhpRelation( 'otherTimezone', 'Europe/Stockholm' );
        $this->assertEquals(
            $otherTzToPhpTz + ['otherTimezone' => 'Europe/Stockholm'],
            $tzFactory->getOtherTzPhpRelations()
        );
    }

    /**
     * Test exception
     *
     * @test
     * @expectedException InvalidArgumentException
     * @throws Exception
     */
    public function TzTest6()
    {
        $calendar2 = RegulateTimezoneFactory::process(
            sprintf( self::$calendar2, '̈́Europe/Stockholm' ),
            [ 'otherTimezone' => 'phpTimezone' ]
        );
    }

    /**
     * Test exception
     *
     * @test
     * @expectedException InvalidArgumentException
     * @throws Exception
     */
    public function TzTest7()
    {
        $tzFactory = RegulateTimezoneFactory::factory();
        $this->assertFalse( $tzFactory->isInputiCalSet());

        $tzFactory->processCalendar();
    }

    /**
     * Test exception
     *
     * @test
     * @expectedException UnexpectedValueException
     * @throws Exception
     */
    public function TzTest8()
    {
        $calendar2 = RegulateTimezoneFactory::process( 'grodan boll' );
    }
}
