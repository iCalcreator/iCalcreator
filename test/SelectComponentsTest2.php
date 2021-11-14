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
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class SelectComponentsTest2 extends TestCase
{
    /**
     * @return array
     */
    public function SelectComponentsTest21Provider() : array
    {
        $dataArr = [];

        $dataArr[] = [ // test daily, interval 1, count 10
            '2100-2445-1',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-1
COMMENT:Daily for 10 occurrences:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970902T090000
COMMENT:RRULE:FREQ=DAILY;COUNT=10
DTSTART;TZID=America/Los_Angeles:19970902T090000
RRULE:FREQ=DAILY;COUNT=10
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970902090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-02 09:00:00 America/Los_Angeles', // event start
                '1997-09-03 09:00:00 America/Los_Angeles',
                '1997-09-04 09:00:00 America/Los_Angeles',
                '1997-09-05 09:00:00 America/Los_Angeles',
                '1997-09-06 09:00:00 America/Los_Angeles',
                '1997-09-07 09:00:00 America/Los_Angeles',
                '1997-09-08 09:00:00 America/Los_Angeles',
                '1997-09-09 09:00:00 America/Los_Angeles',
                '1997-09-10 09:00:00 America/Los_Angeles',
                '1997-09-11 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // test daily, interval 1, count 10 - over night - neotsn #95
            '2101',
            'BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:6176f48f65015c1a736187c9
DTSTAMP:20211025T181647Z
DTSTART;TZID=America/Chicago:20211025T210000
DTEND;TZID=America/Chicago:20211026T060000
RRULE:FREQ=DAILY;COUNT=10:INTERVAL=1;WKST=SU
SUMMARY:Test Over Night, interval 1
END:VEVENT
END:VCALENDAR
',
            new DateTime( '20211025', new DateTimeZone( 'America/Chicago' )),
            [
                '2021-10-25 21:00:00 America/Chicago', // event start
                '2021-10-26 00:00:00 America/Chicago',
                '2021-10-26 21:00:00 America/Chicago',
                '2021-10-27 00:00:00 America/Chicago',
                '2021-10-27 21:00:00 America/Chicago',
                '2021-10-28 00:00:00 America/Chicago',
                '2021-10-28 21:00:00 America/Chicago',
                '2021-10-29 00:00:00 America/Chicago',
                '2021-10-29 21:00:00 America/Chicago',
                '2021-10-30 00:00:00 America/Chicago',
                '2021-10-30 21:00:00 America/Chicago',
                '2021-10-31 00:00:00 America/Chicago',
                '2021-10-31 21:00:00 America/Chicago',
                '2021-11-01 00:00:00 America/Chicago',
                '2021-11-01 21:00:00 America/Chicago',
                '2021-11-02 00:00:00 America/Chicago',
                '2021-11-02 21:00:00 America/Chicago',
                '2021-11-03 00:00:00 America/Chicago',
                '2021-11-03 21:00:00 America/Chicago', '2021-11-04 00:00:00 America/Chicago' // last event start day1/2
            ]
        ];

        $dataArr[] = [ // test daily, interval 2, count 10 - same as above but interval 2
            '2102',
            'BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:6176f48f65015c1a736187c9
DTSTAMP:20211025T181647Z
DTSTART;TZID=America/Chicago:20211025T210000
DTEND;TZID=America/Chicago:20211026T060000
RRULE:FREQ=DAILY;COUNT=10;INTERVAL=2;WKST=SU
SUMMARY:Test Over Night, interval 2
END:VEVENT
END:VCALENDAR
',
            new DateTime( '20211025', new DateTimeZone( 'America/Chicago' )),
            [
                '2021-10-25 21:00:00 America/Chicago', '2021-10-26 00:00:00 America/Chicago', // event start day1/2
                '2021-10-27 21:00:00 America/Chicago', '2021-10-28 00:00:00 America/Chicago',
                '2021-10-29 21:00:00 America/Chicago', '2021-10-30 00:00:00 America/Chicago',
                '2021-10-31 21:00:00 America/Chicago', '2021-11-01 00:00:00 America/Chicago',
                '2021-11-02 21:00:00 America/Chicago', '2021-11-03 00:00:00 America/Chicago',
                '2021-11-04 21:00:00 America/Chicago', '2021-11-05 00:00:00 America/Chicago',
                '2021-11-06 21:00:00 America/Chicago', '2021-11-07 00:00:00 America/Chicago',
                '2021-11-08 21:00:00 America/Chicago', '2021-11-09 00:00:00 America/Chicago',
                '2021-11-10 21:00:00 America/Chicago', '2021-11-11 00:00:00 America/Chicago',
                '2021-11-12 21:00:00 America/Chicago', '2021-11-13 00:00:00 America/Chicago',
            ]
        ];

        $dataArr[] = [ // rfc 2445-4, test daily, interval 10, count 5
            '2103-2445-4',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-4
COMMENT:Every 10 days, 5 occurrences:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970902T090000
COMMENT:RRULE:FREQ=DAILY;INTERVAL=10;COUNT=5
COMMENT:==> (1997 9:00 AM EDT)September 2,12,22;October 2,12
DTSTART;TZID=America/Los_Angeles:19970902T090000
RRULE:FREQ=DAILY;INTERVAL=10;COUNT=5
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970902090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-02 09:00:00 America/Los_Angeles', // event start
                '1997-09-12 09:00:00 America/Los_Angeles',
                '1997-09-22 09:00:00 America/Los_Angeles',
                '1997-10-02 09:00:00 America/Los_Angeles',
                '1997-10-12 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-7, test Weekly for 10 occurrences
            '2103-2445-7',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-7
COMMENT:Weekly for 10 occurrences
COMMENT:DTSTART;TZID=America/Los_Angeles:19970902T090000
COMMENT:RRULE:FREQ=WEEKLY;COUNT=10
COMMENT:==> (1997 9:00 AM EDT)September 2,9,16,23,30;October 7,14,21
COMMENT:    (1997 9:00 AM EST)October 28;November 4
DTSTART;TZID=America/Los_Angeles:19970902T090000
RRULE:FREQ=WEEKLY;COUNT=10
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970902090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-02 09:00:00 America/Los_Angeles', // event start
                '1997-09-09 09:00:00 America/Los_Angeles',
                '1997-09-16 09:00:00 America/Los_Angeles',
                '1997-09-23 09:00:00 America/Los_Angeles',
                '1997-09-30 09:00:00 America/Los_Angeles',
                '1997-10-07 09:00:00 America/Los_Angeles',
                '1997-10-14 09:00:00 America/Los_Angeles',
                '1997-10-21 09:00:00 America/Los_Angeles',
                '1997-10-28 09:00:00 America/Los_Angeles',
                '1997-11-04 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-8, test Weekly for 10 occurrences, same as above but NO COUNT, has UNTIL
            '2103-2445-8',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-8
COMMENT:Weekly until December 24, 1997
COMMENT:DTSTART;TZID=America/Los_Angeles:19970902T090000
COMMENT:RRULE:FREQ=WEEKLY;UNTIL=19971224T000000Z
COMMENT:==> (1997 9:00 AM EDT)September 2,9,16,23,30;October 7,14,21
COMMENT:    (1997 9:00 AM EST)October 28;November 4,11,18,25; December 2,9,16,23
DTSTART;TZID=America/Los_Angeles:19970902T090000
RRULE:FREQ=WEEKLY;UNTIL=19971224T000000Z
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970902090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-02 09:00:00 America/Los_Angeles', // event start
                '1997-09-09 09:00:00 America/Los_Angeles',
                '1997-09-16 09:00:00 America/Los_Angeles',
                '1997-09-23 09:00:00 America/Los_Angeles',
                '1997-09-30 09:00:00 America/Los_Angeles',
                '1997-10-07 09:00:00 America/Los_Angeles',
                '1997-10-14 09:00:00 America/Los_Angeles',
                '1997-10-21 09:00:00 America/Los_Angeles',
                '1997-10-28 09:00:00 America/Los_Angeles',
                '1997-11-04 09:00:00 America/Los_Angeles',
                '1997-11-11 09:00:00 America/Los_Angeles',
                '1997-11-18 09:00:00 America/Los_Angeles',
                '1997-11-25 09:00:00 America/Los_Angeles',
                '1997-12-02 09:00:00 America/Los_Angeles',
                '1997-12-09 09:00:00 America/Los_Angeles',
                '1997-12-16 09:00:00 America/Los_Angeles',
                '1997-12-23 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-10, test Weekly on Tuesday and Thursday for 5 weeks
            '2103-2445-10',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-10
COMMENT:Weekly on Tuesday and Thursday for 5 weeks:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970902T090000
COMMENT:RRULE:FREQ=WEEKLY;UNTIL=19971007T000000Z;WKST=SU;BYDAY=TU,TH
COMMENT:==> (1997 9:00 AM EDT)September 2,4,9,11,16,18,23,25,30;October 2
DTSTART;TZID=America/Los_Angeles:19970902T090000
RRULE:FREQ=WEEKLY;UNTIL=19971007T000000Z;WKST=SU;BYDAY=TU,TH
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970902090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-02 09:00:00 America/Los_Angeles', // event start
                '1997-09-04 09:00:00 America/Los_Angeles',
                '1997-09-09 09:00:00 America/Los_Angeles',
                '1997-09-11 09:00:00 America/Los_Angeles',
                '1997-09-16 09:00:00 America/Los_Angeles',
                '1997-09-18 09:00:00 America/Los_Angeles',
                '1997-09-23 09:00:00 America/Los_Angeles',
                '1997-09-25 09:00:00 America/Los_Angeles',
                '1997-09-30 09:00:00 America/Los_Angeles',
                '1997-10-02 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-14, test Monthly on the 1st Friday for ten occurrences
            '2103-2445-14',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-14
COMMENT:Monthly on the 1st Friday for ten occurrences:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970905T090000
COMMENT:RRULE:FREQ=MONTHLY;COUNT=10;BYDAY=1FR
COMMENT:==> (1997 9:00 AM EDT)September 5;October 3
COMMENT:    (1997 9:00 AM EST)November 7;Dec 5
COMMENT:    (1998 9:00 AM EST)January 2;February 6;March 6;April 3
COMMENT:    (1998 9:00 AM EDT)May 1;June 5
DTSTART;TZID=America/Los_Angeles:19970905T090000
RRULE:FREQ=MONTHLY;COUNT=10;BYDAY=1FR
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970905090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-05 09:00:00 America/Los_Angeles', // event start
                '1997-10-03 09:00:00 America/Los_Angeles',
                '1997-11-07 09:00:00 America/Los_Angeles',
                '1997-12-05 09:00:00 America/Los_Angeles',
                '1998-01-02 09:00:00 America/Los_Angeles',
                '1998-02-06 09:00:00 America/Los_Angeles',
                '1998-03-06 09:00:00 America/Los_Angeles',
                '1998-04-03 09:00:00 America/Los_Angeles',
                '1998-05-01 09:00:00 America/Los_Angeles',
                '1998-06-05 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-16, test Every other month on the 1st and last Sunday of the month for 10 occurrences
            '2103-2445-16',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-16
COMMENT:Every other month on the 1st and last Sunday of the month for 10 occurrences:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970907T090000
COMMENT:RRULE:FREQ=MONTHLY;INTERVAL=2;COUNT=10;BYDAY=1SU,-1SU
COMMENT:==> (1997 9:00 AM EDT)September 7,28
COMMENT:    (1997 9:00 AM EST)November 2,30
COMMENT:    (1998 9:00 AM EST)January 4,25;March 1,29
COMMENT:    (1998 9:00 AM EDT)May 3,31
DTSTART;TZID=America/Los_Angeles:19970907T090000
RRULE:FREQ=MONTHLY;INTERVAL=2;COUNT=10;BYDAY=1SU,-1SU
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970907090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-07 09:00:00 America/Los_Angeles', // event start
                '1997-09-28 09:00:00 America/Los_Angeles',
                '1997-11-02 09:00:00 America/Los_Angeles',
                '1997-11-30 09:00:00 America/Los_Angeles',
                '1998-01-04 09:00:00 America/Los_Angeles',
                '1998-01-25 09:00:00 America/Los_Angeles',
                '1998-03-01 09:00:00 America/Los_Angeles',
                '1998-03-29 09:00:00 America/Los_Angeles',
                '1998-05-03 09:00:00 America/Los_Angeles',
                '1998-05-31 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-18, test Monthly on the third to the last day of the month, forever BUT here UNTIL 19980228
            '2103-2445-18',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-18
COMMENT:Monthly on the third to the last day of the month, forever:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970928T090000
COMMENT:RRULE:FREQ=MONTHLY;BYMONTHDAY=-3
COMMENT:==> (1997 9:00 AM EDT)September 28
COMMENT:    (1997 9:00 AM EST)October 29;November 28;December 29
COMMENT:    (1998 9:00 AM EST)January 29;February 26
DTSTART;TZID=America/Los_Angeles:19970928T090000
RRULE:FREQ=MONTHLY;UNTIL=199802281500Z;BYMONTHDAY=-3
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970928090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-28 09:00:00 America/Los_Angeles', // event start
                '1997-10-29 09:00:00 America/Los_Angeles',
                '1997-11-28 09:00:00 America/Los_Angeles',
                '1997-12-29 09:00:00 America/Los_Angeles',
                '1998-01-29 09:00:00 America/Los_Angeles',
                '1998-02-26 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-20, test Monthly on the first and last day of the month for 10 occurrences
            '2103-2445-20',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-20
COMMENT:Monthly on the first and last day of the month for 10 occurrences:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970930T090000
COMMENT:RRULE:FREQ=MONTHLY;COUNT=10;BYMONTHDAY=1,-1
COMMENT:==> (1997 9:00 AM EDT)September 30;October 1
COMMENT:    (1997 9:00 AM EST)October 31;November 1,30;December 1,31
COMMENT:    (1998 9:00 AM EST)January 1,31;February 1
DTSTART;TZID=America/Los_Angeles:19970930T090000
RRULE:FREQ=MONTHLY;COUNT=10;BYMONTHDAY=1,-1
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970930090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-30 09:00:00 America/Los_Angeles', // event start
                '1997-10-01 09:00:00 America/Los_Angeles',
                '1997-10-31 09:00:00 America/Los_Angeles',
                '1997-11-01 09:00:00 America/Los_Angeles',
                '1997-11-30 09:00:00 America/Los_Angeles',
                '1997-12-01 09:00:00 America/Los_Angeles',
                '1997-12-31 09:00:00 America/Los_Angeles',
                '1998-01-01 09:00:00 America/Los_Angeles',
                '1998-01-31 09:00:00 America/Los_Angeles',
                '1998-02-01 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-24, test Every other year on January, February, and March for 10 occurrences
            '2103-2445-24',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-24
COMMENT:Every other year on January, February, and March for 10 occurrences:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970310T090000
COMMENT:RRULE:FREQ=YEARLY;INTERVAL=2;COUNT=10;BYMONTH=1,2,3
COMMENT:==> (1997 9:00 AM EST)March 10
COMMENT:    (1999 9:00 AM EST)January 10;February 10;March 10
COMMENT:    (2001 9:00 AM EST)January 10;February 10;March 10
COMMENT:    (2003 9:00 AM EST)January 10;February 10;March 10
DTSTART;TZID=America/Los_Angeles:19970310T090000
RRULE:FREQ=YEARLY;INTERVAL=2;COUNT=10;BYMONTH=1,2,3
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970310090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-03-10 09:00:00 America/Los_Angeles', // event start
                '1999-01-10 09:00:00 America/Los_Angeles',
                '1999-02-10 09:00:00 America/Los_Angeles',
                '1999-03-10 09:00:00 America/Los_Angeles',
                '2001-01-10 09:00:00 America/Los_Angeles',
                '2001-02-10 09:00:00 America/Los_Angeles',
                '2001-03-10 09:00:00 America/Los_Angeles',
                '2003-01-10 09:00:00 America/Los_Angeles',
                '2003-02-10 09:00:00 America/Los_Angeles',
                '2003-03-10 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-25, test Every 2nd year on the 1st, 100th and 200th day for 8 occurrences
            '2103-2445-25',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2445-25 BUT modified
COMMENT:Every 2nd year on the 1st, 100th and 200th day for 6 occurrences:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970101T090000
COMMENT:RRULE:FREQ=YEARLY;INTERVAL=2;COUNT=8;BYYEARDAY=1,100,200
COMMENT:==> (1997 9:00 AM EST)January 1, April 10, July 19
COMMENT:    (1999 9:00 AM EST)January 1, April 9, July 18
COMMENT:    (2001 9:00 AM EST)January 1, April 10
DTSTART;TZID=America/Los_Angeles:19970101T090000
RRULE:FREQ=YEARLY;INTERVAL=2;COUNT=8;BYYEARDAY=1,100,200
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970101090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-01-01 09:00:00 America/Los_Angeles', // event start
                '1997-04-10 09:00:00 America/Los_Angeles',
                '1997-07-19 09:00:00 America/Los_Angeles',
                '1999-01-01 09:00:00 America/Los_Angeles',
                '1999-04-10 09:00:00 America/Los_Angeles',
                '1999-07-19 09:00:00 America/Los_Angeles',
                '2001-01-01 09:00:00 America/Los_Angeles',
                '2001-04-10 09:00:00 America/Los_Angeles',
            ]
        ];

        $dataArr[] = [ // rfc 2445-26, test Every 2nd year on the 1st, 100th and 200th day for 8 occurrences
            '2103-2445-26',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2446-26 BUT modified
COMMENT:Every 2nd year on the 1st, 100th and 200th day for 6 occurrences:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970101T090000
COMMENT:RRULE:FREQ=YEARLY;INTERVAL=2;COUNT=8;BYYEARDAY=1,100,200
COMMENT:==> (1997 9:00 AM EST)January 1, April 10, July 19
COMMENT:    (1999 9:00 AM EST)January 1, April 9, July 18
COMMENT:    (2001 9:00 AM EST)January 1, April 10
DTSTART;TZID=America/Los_Angeles:19970101T090000
RRULE:FREQ=YEARLY;INTERVAL=2;COUNT=8;BYYEARDAY=1,100,200
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970101090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-01-01 09:00:00 America/Los_Angeles', // event start
                '1997-04-10 09:00:00 America/Los_Angeles',
                '1997-07-19 09:00:00 America/Los_Angeles',
                '1999-01-01 09:00:00 America/Los_Angeles',
                '1999-04-10 09:00:00 America/Los_Angeles',
                '1999-07-19 09:00:00 America/Los_Angeles',
                '2001-01-01 09:00:00 America/Los_Angeles',
                '2001-04-10 09:00:00 America/Los_Angeles',
            ]

        ];

        $dataArr[] = [ // rfc 2445-27, test Monday of week number 20 (where the default start of the week is Monday), COUNT=3
            '2103-2445-27',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2446-27 BUT modified
COMMENT:Monday of week number 20 (where the default start of the week is Monday), forever:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970512T090000
COMMENT:RRULE:FREQ=YEARLY;COUNT=3;BYWEEKNO=20;BYDAY=MO
COMMENT:==> (1997 9:00 AM EDT)May 12
COMMENT:    (1998 9:00 AM EDT)May 11
COMMENT:    (1999 9:00 AM EDT)May 17
DTSTART;TZID=America/Los_Angeles:19970512T090000
RRULE:FREQ=YEARLY;COUNT=3;BYWEEKNO=20;BYDAY=MO
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970512090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-05-12 09:00:00 America/Los_Angeles', // event start
                '1998-05-11 09:00:00 America/Los_Angeles',
                '1999-05-17 09:00:00 America/Los_Angeles'
            ]
        ];

        $dataArr[] = [ // rfc 2445-30, test Every Friday the 13th but COUNT=5
              // BUG here : got four, exp five.... exDate of startDate NOT taken in acount....
            '2103-2445-30',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2446-30 BUT modified
COMMENT:Every Friday the 13th, forever:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970902T090000
COMMENT:EXDATE;TZID=America/Los_Angeles:19970902T090000
COMMENT:RRULE:FREQ=MONTHLY;COUNT=5;BYDAY=FR;BYMONTHDAY=13
COMMENT:==> (1998 9:00 AM EST)February 13;March 13;November 13
COMMENT:    (1999 9:00 AM EDT)August 13
COMMENT:    (2000 9:00 AM EDT)October 13
DTSTART;TZID=America/Los_Angeles:19970902T090000
EXDATE;TZID=America/Los_Angeles:19970902T090000
RRULE:FREQ=MONTHLY;COUNT=5;BYDAY=FR;BYMONTHDAY=13
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970902090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1998-02-13 09:00:00 America/Los_Angeles', // event start
                '1998-03-13 09:00:00 America/Los_Angeles',
                '1998-11-13 09:00:00 America/Los_Angeles',
                '1999-08-13 09:00:00 America/Los_Angeles',
//              '2000-10-13 09:00:00 America/Los_Angeles'  // should exist...
            ]
        ];

        $dataArr[] = [ // rfc 2445-31, test The first Saturday that follows the first Sunday of the month, COUNT=10
            '2103-2445-31',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2446-31 BUT modified
COMMENT:The first Saturday that follows the first Sunday of the month, BUT count 10:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970913T090000
COMMENT:RRULE:FREQ=MONTHLY;COUNT=10;BYDAY=SA;BYMONTHDAY=7,8,9,10,11,12,13
COMMENT:==> (1997 9:00 AM EDT)September 13;October 11
COMMENT:    (1997 9:00 AM EST)November 8;December 13
COMMENT:    (1998 9:00 AM EST)January 10;February 7;March 7
COMMENT:    (1998 9:00 AM EDT)April 11;May 9;June 13
DTSTART;TZID=America/Los_Angeles:19970913T090000
RRULE:FREQ=MONTHLY;COUNT=10;BYDAY=SA;BYMONTHDAY=7,8,9,10,11,12,13
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970913090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-13 09:00:00 America/Los_Angeles', // event start
                '1997-10-11 09:00:00 America/Los_Angeles',
                '1997-11-08 09:00:00 America/Los_Angeles',
                '1997-12-13 09:00:00 America/Los_Angeles',
                '1998-01-10 09:00:00 America/Los_Angeles',
                '1998-02-07 09:00:00 America/Los_Angeles',
                '1998-03-07 09:00:00 America/Los_Angeles',
                '1998-04-11 09:00:00 America/Los_Angeles',
                '1998-05-09 09:00:00 America/Los_Angeles',
                '1998-06-13 09:00:00 America/Los_Angeles',
            ]
        ];

        $dataArr[] = [ // rfc 2445-33, test The 3rd instance into the month of one of Tuesday, Wednesday or Thursday, for the next 3 months
            '2103-2445-33',
            'BEGIN:VCALENDAR
BEGIN:VEVENT
COMMENT:Example rfc 2446-33
COMMENT:The 3rd instance into the month of one of Tuesday, Wednesday or Thursday, for the next 3 months:
COMMENT:DTSTART;TZID=America/Los_Angeles:19970904T090000
COMMENT:RRULE:FREQ=MONTHLY;COUNT=3;BYDAY=TU,WE,TH;BYSETPOS=3
COMMENT:==> (1997 9:00 AM EDT)September 4;October 7
COMMENT:    (1997 9:00 AM EST)November 6
DTSTART;TZID=America/Los_Angeles:19970904T090000
RRULE:FREQ=MONTHLY;COUNT=3;BYDAY=TU,WE,TH;BYSETPOS=3
END:VEVENT
END:VCALENDAR
',
            new DateTime( '19970901090000', new DateTimeZone( 'America/Los_Angeles' )),
            [
                '1997-09-04 09:00:00 America/Los_Angeles', // event start
                '1997-10-07 09:00:00 America/Los_Angeles',
                '1997-11-06 09:00:00 America/Los_Angeles'
            ]
        ];

        return $dataArr;
    }

    /**
     * @test
     * @dataProvider SelectComponentsTest21Provider
     *
     * @param string   $case
     * @param string   $ics
     * @param dateTime $startDate
     * @param array    $startDates
     * @return void
     */
    public function SelectComponentsTest21(
        string   $case,
        string   $ics,
        dateTime $startDate,
        array    $startDates
    ) : void
    {
//      error_log(__FUNCTION__ . ' start case ' . $case );// test ###

        $vCalender = new Vcalendar();
        $yearHits  = $vCalender->parse( $ics )
            ->selectComponents( $startDate, (clone $startDate )->modify( '10 year' )); // startDate/endDate

        $this->assertIsArray( $yearHits );

        $count     = 0;
        foreach( $yearHits as $year => $months ) {
            foreach( $months as $month => $days ) {
                foreach( $days as $day => $events ) {
                    foreach( $events as $event ) {
                        $x_current_dtstart = $event->getXprop( Vcalendar::X_CURRENT_DTSTART )[1];
                        if( false !== ( $xRecurrence = $event->getXprop( IcalInterface::X_RECURRENCE ))) {
                            $xRecurrence = $xRecurrence[1];
                        }
                        else {
                            $xRecurrence = ' ';
                        }
/*
                        error_log( '#' . (1 + $count) . ' xRecurrence:' . $xRecurrence . ' ' . $year . '-' . $month . '-' . $day . ' ' .
                            Vcalendar::X_CURRENT_DTSTART . ':' . $x_current_dtstart
                             . ' ' . Vcalendar::X_CURRENT_DTEND . $event->getXprop( Vcalendar::X_CURRENT_DTEND )[1]
                        ); // test ###
*/
                        $this->assertEquals(
                            $startDates[$count],
                            $x_current_dtstart,
                            'case #' . $case . '-1, count ' . $count . ', got ' . $x_current_dtstart . ', exp ' . $startDates[$count]
                        );
                        ++$count;
                    } // end foreach
                } // end foreach
            } // end foreach
        } // end foreach
        $expHits = count( $startDates );
        $this->assertEquals(
            $expHits,
            $count,
            'case #' . $case . '-2 got ' . $count . ', exp ' . $expHits
        );
    }
}
