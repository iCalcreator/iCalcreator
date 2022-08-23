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
use UnexpectedValueException;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * Class ParseTest,
 *
 * Testing Vcalendar parse + eol-htab
 *
 * @since  2.41.31 - 2022-03-11
 */
class ParseTest extends DtBase
{
    /**
     * parseExceptionsTest provider
     *
     * @return mixed[]
     */
    public function parseExceptionsTestProvider() : array
    {

        $dataArr = [];

        $dataArr[] = [
            0,
            ""
        ];

        $dataArr[] = [
            1,
            []
        ];

        $dataArr[] = [
            2,
            "\r\n"
        ];

        $dataArr[] = [
            3,
            [ "\r\n" ]
        ];

        $dataArr[] = [
            4,
            "BEGIN:VCALENDAR\r\n"
        ];

        $dataArr[] = [
            5,
            [ "BEGIN:VCALENDAR\r\n" ]
        ];

        $dataArr[] = [
            6,
            "END:VCALENDAR\r\n"
        ];
        $dataArr[] = [
            7,
            [ "END:VCALENDAR\r\n" ]
        ];

        $dataArr[] = [
            8,
            "BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n"
        ];

        $dataArr[] = [
            9,
            [ "BEGIN:VCALENDAR\r\n", "END:VCALENDAR\r\n" ]
        ];

        /*   is accepted BUT content skipped
        $dataArr[] = [
            10,
            "grodan boll"
        ];
        */

        return $dataArr;
    }

    /**
     * @test
     * @dataProvider parseExceptionsTestProvider
     * @param int $case
     * @param string|string[] $value
     */
    public function parseExceptionsTest( int $case, string|array $value ) : void
    {
        $calendar = new Vcalendar();
        $ok = false;
        try {
            $calendar->parse( $value );

            echo $calendar->createCalendar() . PHP_EOL; // test ###
        }
        catch ( UnexpectedValueException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . ' error in case #' .  $case );
    }

    /**
     * parseCalendarTest provider
     *
     * @return mixed[]
     */
    public function parseCalendarTestProvider() : array
    {

        $dataArr = [];

        $dataArr[] = [
            601,
            "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:-//ShopReply Inc//CalReply 1.0//EN\r\n" .
            "METHOD:REFRESH\r\n" .
            "SOURCE;x-a=first;VALUE=uri:message://https://www.masked.de/account/subscripti\r\n" .
            " on/delivery/8878/%3Fweek=2021-W03\r\n" .
            "X-WR-CALNAME:ESPN Daily Calendar\r\n" .
            "X-WR-RELCALID:657d63b8-df1d-e611-8b88-06bb54d48d13\r\n" .
            "X-PUBLISH-TTL:P1D\r\n" .
            "X-TEST:601\r\n" .
            "BEGIN:VTIMEZONE\r\n" .
            "TZID:America/New_York\r\n" .
            "TZURL;x-a=first;VALUE=uri:message//:https://www.masked.de/account/subscriptio\r\n" .
            " n/delivery/8878/%3Fweek=2021-W03\r\n" .
            "BEGIN:STANDARD\r\n" .
            "DTSTART:20070101T020000\r\n" .
            "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU;\r\n" .
            "TZOFFSETFROM:-0400\r\n" .
            "TZOFFSETTO:-0500\r\n" .
            "END:STANDARD\r\n" .
            "BEGIN:DAYLIGHT\r\n" .
            "DTSTART:20070101T020000\r\n" .
            "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU;\r\n" .
            "TZOFFSETFROM:-0500\r\n" .
            "TZOFFSETTO:-0400\r\n" .
            "END:DAYLIGHT\r\n" .
            "END:VTIMEZONE\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:e2317772-f3a2-42cf-a5ac-e639fb6b2af0\r\n" .
            "CLASS:PUBLIC\r\n" .
            "TRANSP:TRANSPARENT\r\n" .
            "SUMMARY:⚽ English FA Cup on ESPN+\r\n" .
            "DTSTART;TZID=\"America/New_York\":20190316T081500\r\n" .
            "DTEND;TZID=\"America/New_York\":20190316T091500\r\n" .
            'DESCRIPTION:Watch live: http://bit.ly/FACuponEPlus\n\nNot an ESPN+ subscrib' . "\r\n\t" .
            'er? Start your free trial here: http://bit.ly/ESPNPlusSignup\n\nShare - http:' . "\r\n\t" .
            '//calrep.ly/2pLaM0n\n\nYou may unsubscribe by following - https://espn.calrep' . "\r\n\t" .
            'lyapp.com/unsubscribe/9bba908612a34be1881bc5098e8adbda\n\nPowered by CalReply' . "\r\n\t" .
            " - http://calrep.ly/poweredby\r\n" .
            'LOCATION:England\'s biggest soccer competition continues.\n\n• Watford vs. C' . "\r\n\t" .
            'rystal Palace (8:15 a.m. ET)\n• Swansea City vs. Manchester City (1:20 p.m.)\\' . "\r\n\t" .
            'n• Wolverhampton vs. Manchester United (3:55 p.m.)\n\nWatch live: http://bit.' . "\r\n\t" .
            "ly/FACuponEPlus\r\n" .
            "DTSTAMP:20190315T211012Z\r\n" .
            "LAST-MODIFIED:20190315T211012Z\r\n" .
            "SEQUENCE:1\r\n" .
            "URL;x-a=first;VALUE=uri:message//:https://www.masked.de/account/subscription/\r\n" .
            " delivery/8878/%3Fweek=2021-W03\r\n" .
            "BEGIN:VALARM\r\n" .
            "ACTION:DISPLAY\r\n" .
            "DESCRIPTION:Reminder\r\n" .
            "TRIGGER:-PT15M\r\n" .
            "END:VALARM\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n",
            "UID:e2317772-f3a2-42cf-a5ac-e639fb6b2af0"
        ];

        // rfc 9073 8.1.  Example 1, extended with VLOCATION inside PARTICIPANT
        $dataArr[] = [
            611,
            "BEGIN:VCALENDAR\r\n" .
            "X-TEST:611\r\n" .
            "BEGIN:VEVENT\r\n" .
            "CREATED:20200215T145739Z\r\n" .
            "DESCRIPTION: Piano Sonata No 3\n\r\n" .
            " Piano Sonata No 30\r\n" .
            "DTSTAMP:20200215T145739Z\r\n" .
            "DTSTART;TZID=America/New_York:20200315T150000Z\r\n" .
            "DTEND;TZID=America/New_York:20200315T163000Z\r\n" .
            "LAST-MODIFIED:20200216T145739Z\r\n" .
            "SUMMARY:Beethoven Piano Sonatas\r\n" .
            "UID:123456\r\n" .
            "IMAGE;VALUE=URI;DISPLAY=BADGE;FMTTYPE=image/png:h\r\n" .
            " ttp://example.com/images/concert.png\r\n" .
            "BEGIN:PARTICIPANT\r\n" .
            "PARTICIPANT-TYPE:SPONSOR\r\n" .
            "UID:dG9tQGZvb2Jhci5xlLmNvbQ\r\n" .
            "STRUCTURED-DATA;VALUE=URI:http://example.com/vevent.participant1.sponsor.vcf\r\n" .
            "END:PARTICIPANT\r\n" .
            "BEGIN:PARTICIPANT\r\n" .
            "PARTICIPANT-TYPE:PERFORMER:\r\n" .
            "UID:em9lQGZvb2GFtcGxlLmNvbQ\r\n" .
            "STRUCTURED-DATA;VALUE=URI:http://www.example.com/vevent.participant2/johndoe.vcf\r\n" .
            "BEGIN:VLOCATION\r\n" .
            "UID:123456-abcdef-123456780\r\n" .
            "NAME:The curators office\r\n" .
            "STRUCTURED-DATA;VALUE=URI:http://dir.example.com/vevent.participant2.vlocation1/office.vcf\r\n" .
            "END:VLOCATION\r\n" .
            "END:PARTICIPANT\r\n" .
            "BEGIN:VLOCATION\r\n" .
            "UID:123456-abcdef-98765432\r\n" .
            "NAME:The venue\r\n" .
            "STRUCTURED-DATA;VALUE=URI:http://dir.example.com/vevent.vlocation1/big-hall.vcf\r\n" .
            "END:VLOCATION\r\n" .
            "BEGIN:VLOCATION\r\n" .
            "UID:123456-abcdef-87654321\r\n" .
            "NAME:Parking for the venue\r\n" .
            "STRUCTURED-DATA;VALUE=URI:http://dir.example.com/vevent.vlocation2/parking.vcf\r\n" .
            "END:VLOCATION\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n",
            'STRUCTURED-DATA;VALUE=URI:http://dir.example.com/vevent.vlocation2/parking.vcf'
        ];

        // rfc 9073 8.2.  Example 2
        $dataArr[] = [
            621,
            "BEGIN:VCALENDAR\r\n" .
            "X-TEST:621\r\n" .
            "BEGIN:VEVENT\r\n" .
            "CREATED:20200215T145739Z\r\n" .
            "DTSTAMP:20200215T145739Z\r\n" .
            "DTSTART;TZID=America/New_York:20200315T150000Z\r\n" .
            "DTEND;TZID=America/New_York:20200315T163000Z\r\n" .
            "LAST-MODIFIED:20200216T145739Z\r\n" .
            "SUMMARY:Conference planning\r\n" .
            "UID:123456\r\n" .
            "ORGANIZER:mailto:a@example.com\r\n" .
            "ATTENDEE;PARTSTAT=ACCEPTED;CN=A:mailto:a@example1.com\r\n" .
            "ATTENDEE;RSVP=TRUE;CN=B:mailto:b@example2.com\r\n" .
            "X-TEST:621\r\n" .
            "BEGIN:PARTICIPANT\r\n" .
            "PARTICIPANT-TYPE:ACTIVE:\r\n" .
            "UID:v39lQGZvb2GFtcGxlLmNvbQ\r\n" .
            "STRUCTURED-DATA;VALUE=URI:http://www.example.com/people/b.vcf\r\n" .
            "LOCATION:At home\r\n" .
            "END:PARTICIPANT\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n",
            'STRUCTURED-DATA;VALUE=URI:http://www.example.com/people/b.vcf'
        ];

        // rfc 9073 6.6.  Structured-Data
        $dataArr[] = [
            631,
            "BEGIN:VCALENDAR\r\n" .
            "X-TEST:631\r\n" .
            "BEGIN:VEVENT\r\n" .
            "CREATED:20200215T145739Z\r\n" .
            "DTSTAMP:20200215T145739Z\r\n" .
            "DTSTART;TZID=America/New_York:20200315T150000Z\r\n" .
            "DTEND;TZID=America/New_York:20200315T163000Z\r\n" .
            "LAST-MODIFIED:20200216T145739Z\r\n" .
            "SUMMARY:Conference planning\r\n" .
            "UID:123456\r\n" .
            "BEGIN:PARTICIPANT\r\n" .
            "PARTICIPANT-TYPE:ACTIVE:\r\n" .
            "UID:v39lQGZvb2GFtcGxlLmNvbQ\r\n" .

            "STRUCTURED-DATA;VALUE=TEXT;FMTTYPE=application/ld+json;SCHEMA=\"https://schema.org/Sp\r\n" .
            " ortsEvent\":{\n                                                      \r\n" .
            " \"@context\": \"http://schema.org\"\\,\n                                          \r\n" .
            " \"@type\": \"SportsEvent\"\\,\n                                                   \r\n" .
            " \"homeTeam\": \"Pittsburgh Pirates\"\\,\n                                         \r\n" .
            " \"awayTeam\": \"San Francisco Giants\"\n                                         \r\n" .
            " }\n\r\n" .

            "END:PARTICIPANT\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n",
            'SUMMARY:Conference planning'
        ];

        // rfc 9074 7.2.  Example   VALARM "snoozing", "re-snoozing", and dismissal of an alarm
        // all in one but VEVENTs/VALARMs with different UIDs
        $dataArr[] = [
            641,
            "BEGIN:VCALENDAR\r\n" .
            "X-TEST:641\r\n" .

            "BEGIN:VEVENT\r\n" .
            "CREATED:20210302T151004Z\r\n" .
            "UID:AC67C078-CED3-4BF5-9726-832C3749F621\r\n" .
            "DTSTAMP:20210302T151516Z\r\n" .
            "DTSTART;TZID=America/New_York:20210302T103000\r\n" .
            "DTEND;TZID=America/New_York:20210302T113000\r\n" .
            "SUMMARY:Meeting\r\n" .
            "BEGIN:VALARM\r\n" .
            "UID:8297C37D-BA2D-4476-91AE-C1EAA364F8E1\r\n" .
            "TRIGGER:-PT15M\r\n" .
            "DESCRIPTION:Event reminder\r\n" .
            "ACTION:DISPLAY\r\n" .
            "ACKNOWLEDGED:20210302T151514Z\r\n" .
            "END:VALARM\r\n" .
            "BEGIN:VALARM\r\n" .
            "UID:DE7B5C34-83FF-47FE-BE9E-FF41AE6DD097\r\n" .
            "TRIGGER;VALUE=DATE-TIME:20210302T152000Z\r\n" .
            "RELATED-TO;RELTYPE=SNOOZE:8297C37D-BA2D-4476-91AE-C1EAA364F8E1\r\n" .
            "DESCRIPTION:Event reminder\r\n" .
            "ACTION:DISPLAY\r\n" .
            "END:VALARM\r\n" .
            "END:VEVENT\r\n" .

            "BEGIN:VEVENT\r\n" .
            "CREATED:20210302T151004Z\r\n" .
            "UID:AC67C078-CED3-4BF5-9726-832C3749F622\r\n" .
            "DTSTAMP:20210302T152026Z\r\n" .
            "DTSTART;TZID=America/New_York:20210302T103000\r\n" .
            "DTEND;TZID=America/New_York:20210302T113000\r\n" .
            "SUMMARY:Meeting\r\n" .
            "BEGIN:VALARM\r\n" .
            "UID:8297C37D-BA2D-4476-91AE-C1EAA364F8E2\r\n" .
            "TRIGGER:-PT15M\r\n" .
            "DESCRIPTION:Event reminder\r\n" .
            "ACTION:DISPLAY\r\n" .
            "ACKNOWLEDGED:20210302T152024Z\r\n" .
            "END:VALARM\r\n" .
            "BEGIN:VALARM\r\n" .
            "UID:87D690A7-B5E8-4EB4-8500-491F50AFE394\r\n" .
            "TRIGGER;VALUE=DATE-TIME:20210302T152500Z\r\n" .
            "RELATED-TO;RELTYPE=SNOOZE:8297C37D-BA2D-4476-91AE-C1EAA364F8E2\r\n" .
            "DESCRIPTION:Event reminder\r\n" .
            "ACTION:DISPLAY\r\n" .
            "END:VALARM\r\n" .
            "END:VEVENT\r\n" .

            "BEGIN:VEVENT\r\n" .
            "CREATED:20210302T151004Z\r\n" .
            "UID:AC67C078-CED3-4BF5-9726-832C3749F623\r\n" .
            "DTSTAMP:20210302T152508Z\r\n" .
            "DTSTART;TZID=America/New_York:20210302T103000\r\n" .
            "DTEND;TZID=America/New_York:20210302T113000\r\n" .
            "SUMMARY:Meeting\r\n" .
            "BEGIN:VALARM\r\n" .
            "UID:8297C37D-BA2D-4476-91AE-C1EAA364F8E3\r\n" .
            "TRIGGER:-PT15M\r\n" .
            "DESCRIPTION:Event reminder\r\n" .
            "ACTION:DISPLAY\r\n" .
            "ACKNOWLEDGED:20210302T152507Z\r\n" .
            "END:VALARM\r\n" .
            "BEGIN:VALARM\r\n" .
            "UID:87D690A7-B5E8-4EB4-8500-491F50AFE394\r\n" .
            "TRIGGER;VALUE=DATE-TIME:20210302T152500Z\r\n" .
            "RELATED-TO;RELTYPE=SNOOZE:8297C37D-BA2D-4476-91AE-C1EAA364F8E3\r\n" .
            "DESCRIPTION:Event reminder\r\n" .
            "ACTION:DISPLAY\r\n" .
            "ACKNOWLEDGED:20210302T152507Z\r\n" .
            "END:VALARM\r\n" .
            "END:VEVENT\r\n" .

            "END:VCALENDAR\r\n",
            'RELATED-TO;RELTYPE=SNOOZE:8297C37D-BA2D-4476-91AE-C1EAA364F8E3'
        ];

        // rfc 9074 8.2.  Example   VALARM with PROXIMITY and VLOCATION
        $dataArr[] = [
            641,
            "BEGIN:VCALENDAR\r\n" .
            "X-TEST:641\r\n" .
            "BEGIN:VEVENT\r\n" .
            "CREATED:20200215T145739Z\r\n" .
            "DTSTAMP:20200215T145739Z\r\n" .
            "DTSTART;TZID=America/New_York:20200315T150000Z\r\n" .
            "DTEND;TZID=America/New_York:20200315T163000Z\r\n" .
            "LAST-MODIFIED:20200216T145739Z\r\n" .
            "SUMMARY:Conference planning\r\n" .
            "UID:123456\r\n" .
            "BEGIN:VALARM\r\n" .
            "UID:77D80D14-906B-4257-963F-85B1E734DBB6\r\n" .
            "ACTION:DISPLAY\r\n" .
            "TRIGGER;VALUE=DATE-TIME:19760401T005545Z\r\n" .
            "DESCRIPTION:Remember to buy milk\r\n" .
            "PROXIMITY:DEPART\r\n" .
            "BEGIN:VLOCATION\r\n" .
            "UID:123456-abcdef-98765432\r\n" .
            "NAME:Office1\r\n" .
            "URL:geo:40.443,-79.945;u=10\r\n" .
            "END:VLOCATION\r\n" .
            "BEGIN:VLOCATION\r\n" .
            "UID:987654-ghijkl-1234567890\r\n" .
            "NAME:Office2\r\n" .
            "URL:geo:40.443,-79.945;u=10\r\n" .
            "END:VLOCATION\r\n" .
            "END:VALARM\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n",
            'URL:geo:40.443,-79.945;u=10'
        ];

        return $dataArr;
    }

    /**
     * Testing Vcalendar parse eol-htab, also test of empty unique_id, parse input as string
     *
     * @test
     * @dataProvider parseCalendarTestProvider
     * @param int $case
     * @param string $value
     * @param string $expectedvalue
     * @throws Exception
     */
    public function parseCalendarTest1( int $case, string $value, string $expectedvalue ) : void
    {

        $calendar = new Vcalendar();
        $calendar->parse( $value );

        $this->parseCalendarTest( $case, $calendar, $expectedvalue );
    }

    /**
     * Testing Vcalendar parse eol-htab, also test of empty unique_id, parse input as array
     *
     * @test
     * @dataProvider parseCalendarTestProvider
     * @param int $case
     * @param string $value
     * @param string $expectedvalue
     * @throws Exception
     */
    public function parseCalendarTest2( int $case, string $value, string $expectedvalue ) : void
    {
        $calendar = new Vcalendar();
        $calendar->parse( explode( Util::$CRLF, $value ));

        $this->parseCalendarTest( $case, $calendar, $expectedvalue );
    }

    /**
     * Testing Vcalendar OLD Vcalendar->createCalendar() and NEW Formatter\Vcalendar::format()
     *
     * @test
     * @dataProvider parseCalendarTestProvider
     * @param int $case
     * @param string $value
     * @param string $expectedvalue
     * @throws Exception
     */
    public function parseCalendarTest3( int $case, string $value, string $expectedvalue ) : void
    {
        $calendar = new Vcalendar();
        $calendar->parse( explode( Util::$CRLF, $value ));

        $this->assertSame(
            $calendar->createCalendar(),
            \Kigkonsult\Icalcreator\Formatter\Vcalendar::format( $calendar )
        );
    }

    /**
     * parseCompTest provider
     *
     * @return mixed[]
     */
    public function parseCompTestProvider() : array
    {
        $dataArr = [];

        $dataArr[] = [
            701,
            "DTSTAMP:19970324T120035Z"
        ];
        $dataArr[] = [
            703,
            "SEQUENCE:0"
        ];
        $dataArr[] = [
            705,
            "ORGANIZER:mailto:jdoe@host1.com"
        ];
        $dataArr[] = [
            707,
            [
                "ATTENDEE;RSVP=TRUE:mailto:jsmith@host1.com",
                "ATTENDEE;RSVP=TRUE:mailto:jsmith@host2.com",
                "ATTENDEE;RSVP=TRUE:mailto:jsmith@host3.com",
                "ATTENDEE;RSVP=TRUE:mailto:jsmith@host4.com"
            ]
        ];
        $dataArr[] = [
            709,
            "DTSTART:19970324T123000Z"
        ];
        $dataArr[] = [
            711,
            "DTEND:19970324T210000Z"
        ];
        $dataArr[] = [
            713,
            "CATEGORIES:MEETING,PROJECT"
        ];
        $dataArr[] = [
            715,
            "CLASS:PUBLIC"
        ];
        $dataArr[] = [
            717,
            "SUMMARY:Calendaring Interoperability Planning Meeting"
        ];
        $dataArr[] = [
            719,
            "STATUS:TENTATIVE"
        ];
        $dataArr[] = [
            721,
            'DESCRIPTION:Project xyz Review Meeting Minutes\n' .
            ' Agenda\n' .
            ' 1. Review of project version 1.0 requirements.\n' .
            ' 2. Definition of project processes.\n' .
            ' 3. Review of project schedule.\n' .
            ' Participants: John Smith, Jane Doe, Jim Dandy\n' .
            ' - It was decided that the requirements need to be signed off by product marketing.\n' .
            ' - Project processes were accepted.\n' .
            ' - Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.\n' .
            ' - New schedule will be distributed by Friday.\n' .
            ' - Next weeks meeting is cancelled. No meeting until 3/23.'
        ];
        $dataArr[] = [
            723,
            [
            'COMMENT:Project xyz Review Meeting Minutes\n' .
            ' Agenda\n' .
            ' 1. Review of project version 1.0 requirements.\n' .
            ' 2. Definition of project processes.\n' .
            ' 3. Review of project schedule.\n' .
            ' Participants: John Smith, Jane Doe, Jim Dandy\n' .
            ' - It was decided that the requirements need to be signed off by product marketing.\n' .
            ' - Project processes were accepted.\n' .
            ' - Project schedule needs to account for scheduled holidays and employee vacation time. Check with HR for specific dates.\n' .
            ' - New schedule will be distributed by Friday.\n' .
            ' - Next weeks meeting is cancelled. No meeting until 3/23.'
            ]
        ];
        $dataArr[] = [
            725,
            'LOCATION:LDB Lobby'
        ];
        $dataArr[] = [
            727,
            "ATTACH;FMTTYPE=application/postscript:ftp://xyz.com/pub/conf/bkgrnd.ps"
        ];
        $dataArr[] = [
            729,
            [
                "BEGIN:VALARM",
                "ACTION:AUDIO",
                "TRIGGER;VALUE=DATE-TIME:19970224T070000Z",
                "ATTACH;FMTTYPE=audio/basic:http://host.com/pub/audio-files/ssbanner.aud",
                "REPEAT:4",
                "DURATION:PT1H",
                "X-alarm:non-standard ALARM property", "END:VALARM"
            ]
        ];
        $dataArr[] = [
            731,
            "X-XOMMENT:non-standard property will be displayed, comma escaped"
        ];
        $dataArr[] = [
            733,
            'STRUCTURED-DATA;VALUE=TEXT;FMTTYPE=application/ld+json;SCHEMA="https://schema.org/Sp' .
            'ortsEvent":{\n' .
            '\'@type\': \'SportsEvent\',\n' .
            '\'homeTeam\': \'Pittsburgh Pirates\',\n' .
            '\'awayTeam\': \'San Francisco Giants\'\n' .
            '}'
        ];
        $dataArr[] = [
            735,
            'DESCRIPTION;ALTREP="https://username:password@hostname.domin.com:9090/path?arg=value#anchor":Description here...'
        ];

        return $dataArr;
    }

    /**
     * Testing CalendarCompomponent parse, rfc5545 rendered properties
     *
     * @test
     * @dataProvider parseCompTestProvider
     * @param int $case
     * @param string|array $value
     * @throws Exception
     */
    public function parseCompTest( int $case, string|array $value ) : void
    {
        $calendar = new Vcalendar();

        if( is_array( $value )) {
            $vevent = $calendar->newVevent();
            $vevent->parse( $value );
            $this->parseCalendarTest( $case . '-a', $calendar, $value[1] ?? $value[0] );

            $vevent = $calendar->newVevent();
            $vevent->parse( implode( Util::$CRLF, $value ));
            $this->parseCalendarTest( $case . '-s', $calendar, $value[1] ?? $value[0] );
        }
        else {
            $vevent = $calendar->newVevent();
            $vevent->parse( explode( Util::$CRLF, $value ));
            $this->parseCalendarTest( $case . '-a', $calendar, $value );

            $vevent = $calendar->newVevent();
            $vevent->parse( $value );
            $this->parseCalendarTest( $case . '-s', $calendar, $value );

        }
    }

    /**
     * parseCompPortnrTest provider
     *
     * @return mixed[]
     */
    public function parseCompPortnrTestProvider() : array
    {
        $dataArr = [];

        // quoted ALTREP value
        $dataArr[] = [
            811,
            'DESCRIPTION;ALTREP="https://811username:password@hostname.domin.com:9090/path?arg=value#anchor":Description here...',
            null
        ];

        // quoted ALTREP value AND other param
        $dataArr[] = [
            812,
            'DESCRIPTION;ALTREP="https://812username:password@hostname.domin.com:9090/path?arg=value#anchor";LANGUAGE=EN:Description here...',
            null
        ];

        // unquoted ALTREP value
        /*
        $dataArr[] = [
            821,
            'DESCRIPTION;ALTREP=https://821username:password@hostname.domain.com:9090/path?arg=value#anchor:Description here...',
            'DESCRIPTION;ALTREP="https://821username:password@hostname.domin.com:9090/path?arg=value#anchor":Description here...'
        ];

        // unquoted ALTREP value AND other param
        $dataArr[] = [
            831,
            'DESCRIPTION;ALTREP=https://831username:password@hostname.domain.com:9090/path?arg=value#anchor;LANGUAGE=EN:Description here...',
            'DESCRIPTION;ALTREP="https://831username:password@hostname.domin.com:9090/path?arg=value#anchor";LANGUAGE=EN:Description here...'
        ];
        */

        return $dataArr;

    }

    /**
     * Testing CalendarCompomponent parse, rfc5545 rendered property with portnr
     *
     * Known bug here: property parse with param ALTREP (etc?) with unquoted url with ..>user.passwd@<.. before hostname
     *
     * @test
     * @dataProvider parseCompPortnrTestProvider
     * @param int $case
     * @param string $value
     * @param string|null $expected
     * @throws Exception
     */
    public function parseCompPortnrTest( int $case, string $value, ? string $expected = null ) : void
    {
        $calendar = new Vcalendar();
        $calendar->newVevent()
            ->parse( $value );
        $this->parseCalendarTest( $case, $calendar, $expected ?? $value );
    }
}
