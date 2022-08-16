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
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator;

/**
 * interface IcalInterface
 *
 * @since  2.41.21 - 2022-02-19
 */
interface IcalInterface
{
    /**
     * Class constants, components
     */
    public const VTIMEZONE        = 'Vtimezone';
    public const STANDARD         = 'Standard';
    public const DAYLIGHT         = 'Daylight';
    public const VEVENT           = 'Vevent';
    public const VTODO            = 'Vtodo';
    public const VJOURNAL         = 'Vjournal';
    public const VFREEBUSY        = 'Vfreebusy';
    public const VALARM           = 'Valarm';
    /*  rfc9073 component names */
    public const PARTICIPANT      = 'Participant';
    public const VLOCATION        = 'Vlocation';
    public const VRESOURCE        = 'Vresource';
    /*  rfc7953 component names */
    public const VAVAILABILITY    = 'Vavailability';
    public const AVAILABLE        = 'Available';

    /**
     * Class constants, iCal component property names
     */
    public const ACTION             = 'ACTION';
    public const ATTACH             = 'ATTACH';
    public const ATTENDEE           = 'ATTENDEE';
    public const CALSCALE           = 'CALSCALE';
    public const CATEGORIES         = 'CATEGORIES';
    public const KLASS              = 'CLASS';        // note CLASS
    public const COMMENT            = 'COMMENT';
    public const COMPLETED          = 'COMPLETED';
    public const CONTACT            = 'CONTACT';
    public const CREATED            = 'CREATED';
    public const DESCRIPTION        = 'DESCRIPTION';
    public const DTEND              = 'DTEND';
    public const DTSTAMP            = 'DTSTAMP';
    public const DTSTART            = 'DTSTART';
    public const DUE                = 'DUE';
    public const DURATION           = 'DURATION';
    public const EXDATE             = 'EXDATE';
    public const EXRULE             = 'EXRULE';
    public const FREEBUSY           = 'FREEBUSY';
    public const GEO                = 'GEO';
    public const GEOLOCATION        = 'GEOLOCATION';
    public const LAST_MODIFIED      = 'LAST-MODIFIED';
    public const LOCATION           = 'LOCATION';
    public const METHOD             = 'METHOD';
    public const ORGANIZER          = 'ORGANIZER';
    public const PERCENT_COMPLETE   = 'PERCENT-COMPLETE';
    public const PRIORITY           = 'PRIORITY';
    public const PRODID             = 'PRODID';
    public const RECURRENCE_ID      = 'RECURRENCE-ID';
    public const RELATED_TO         = 'RELATED-TO';
    public const REPEAT             = 'REPEAT';
    public const REQUEST_STATUS     = 'REQUEST-STATUS';
    public const RESOURCES          = 'RESOURCES';
    public const RDATE              = 'RDATE';
    public const RRULE              = 'RRULE';
    public const SEQUENCE           = 'SEQUENCE';
    public const STATUS             = 'STATUS';
    public const SUMMARY            = 'SUMMARY';
    public const TRANSP             = 'TRANSP';
    public const TRIGGER            = 'TRIGGER';
    public const TZID               = 'TZID';
    public const TZID_ALIAS_OF      = 'TZID-ALIAS-OF';
    public const TZNAME             = 'TZNAME';
    public const TZOFFSETFROM       = 'TZOFFSETFROM';
    public const TZOFFSETTO         = 'TZOFFSETTO';
    public const TZUNTIL            = 'TZUNTIL';
    public const TZURL              = 'TZURL';
    public const UID                = 'UID';
    public const URL                = 'URL';
    public const VERSION            = 'VERSION';
    public const X_PROP             = 'X-PROP';

    /**
     * Class constants, iCal rfc7986 property names
     */
    public const COLOR              = 'COLOR';
    public const CONFERENCE         = 'CONFERENCE';
    public const IMAGE              = 'IMAGE';
    public const NAME               = 'NAME';
    public const REFRESH_INTERVAL   = 'REFRESH-INTERVAL';
    public const SOURCE             = 'SOURCE';

    /**
     * Class constants, iCal rfc9073 property names
     */
    public const CALENDAR_ADDRESS   = 'CALENDAR-ADDRESS';
    public const LOCATION_TYPE      = 'LOCATION-TYPE';
    public const PARTICIPANT_TYPE   = 'PARTICIPANT-TYPE';
    public const RESOURCE_TYPE      = 'RESOURCE-TYPE';
    public const STYLED_DESCRIPTION = 'STYLED-DESCRIPTION';
    public const STRUCTURED_DATA    = 'STRUCTURED-DATA';

    /**
     * Class constants, iCal rfc7953 property names
     */
    public const BUSYTYPE           = 'BUSYTYPE';

    /**
     * Class constants, iCal rfc9074 property names
     */
    public const ACKNOWLEDGED       = 'ACKNOWLEDGED';
    public const PROXIMITY          = 'PROXIMITY';

    /**
     * iCal property METHOD types
     */
    public const PUBLISH            = 'PUBLISH';
    public const REQUEST            = 'REQUEST';
    public const REPLY              = 'REPLY';
    public const ADD                = 'ADD';
    public const CANCEL             = 'CANCEL';
    public const REFRESH            = 'REFRESH';
    public const COUNTER            = 'COUNTER';
    public const DECLINECOUNTER     = 'DECLINECOUNTER';

    /**
     * iCal property CALSCALE default value
     */
    public const GREGORIAN          = 'GREGORIAN';

    /**
     * iCal global component parameter keywords
     */
    public const ALTREP             = 'ALTREP';           // Alternate Text Representation
    public const DERIVED            = 'DERIVED';          // rfc9073 : DESCRIPTION/STYLED-DESCRIPTION
    public const LANGUAGE           = 'LANGUAGE';         // values defined in [RFC5646]
    public const VALUE              = 'VALUE';
    public const SCHEMA             = 'SCHEMA';           // rfc9073 : STRUCTURED-DATA

    /**
     * iCal component properties VALUE parameter key values
     *
     * DURATION, set above,                      // TRIGGERtrait
     */
    public const BINARY             = 'BINARY';
    public const BOOLEAN            = 'BOOLEAN';
    public const CAL_ADDRESS        = 'CAL_ADDRESS';
    public const DATE               = 'DATE';             // YYYYMMDD
    public const DATE_TIME          = 'DATE-TIME';        // YYYYMMDDTHHMMDD[Z/timezone]
    public const FLOAT              = 'FLOAT';
    public const INTEGER            = 'INTEGER';
    public const PERIOD             = 'PERIOD';           // date-time / date-time  or  date-time / dur-value
    public const RECUR              = 'RECUR';
    public const TEXT               = 'TEXT';
    public const TIME               = 'TIME';             // HHMMSS
    public const URI                = 'URI';              // Section 3 of [RFC3986]
    public const UTC_OFFSET         = 'UTC-OFFSET';       // ("+" / "-") time-hour time-minute [time-second

    /**
     * (rfc9073) opt x-params keys
     */
    public const X_PARTICIPANTID    = 'X-PARTICIPANTID';  // In Attendee with Participant UID value
    public const X_PARTICIPANT_TYPE = 'X-PARTICIPANT-TYPE';  // In Attendee with Participant PARTICIPANT_TYPE value
    public const X_VLOCATIONID      = 'X-VLOCATIONID';    // In Location with Vlocation UID value
    public const X_LOCATION_TYPE    = 'X-LOCATION-TYPE';  // In Location with Vlocation LOCATION_TYPE value
    public const X_VRESOURCEID      = 'X-VRESOURCEID';    // In Resource with Vresource UID value
    public const X_RESOURCE_TYPE    = 'X-RESOURCE-TYPE';  // In Resource with Vresource RESOURCE_TYPE value

    /**
     * iCal component properties ATTENDEE/ORGANIZER parameter keywords
     */
    public const CUTYPE             = 'CUTYPE';           // Calendar User Type
    public const MEMBER             = 'MEMBER';           // Group or List Membership
    public const ROLE               = 'ROLE';             // Participation Role
    public const PARTSTAT           = 'PARTSTAT';         // Participation Status
    public const RSVP               = 'RSVP';             // 'reply expected'
    public const DELEGATED_TO       = 'DELEGATED-TO';     // Delegatees
    public const DELEGATED_FROM     = 'DELEGATED-FROM';   // Delegators
    public const SENT_BY            = 'SENT-BY';
    public const CN                 = 'CN';               // Common name
    public const DIR                = 'DIR';              // Directory Entry Reference

    /**
     * iCal component properties ATTENDEE/ORGANIZER CUTYPE parameter key values
     */
    public const GROUP              = 'GROUP';
    public const INDIVIDUAL         = 'INDIVIDUAL';       // (default)
    public const RESOURCE           = 'RESOURCE';
    public const ROOM               = 'ROOM';
    public const UNKNOWN            = 'UNKNOWN';

    /**
     * iCal component properties ATTENDEE PARTSTAT parameter key values
     *
     * COMPLETED, Vtodo, set above
     */
    public const NEEDS_ACTION       = 'NEEDS-ACTION';     // Vevent, Vtodo, Vjournal (default)
    public const ACCEPTED           = 'ACCEPTED';         // Vevent, Vtodo, Vjournal
    public const DECLINED           = 'DECLINED';         // Vevent, Vtodo, Vjournal
    public const TENTATIVE          = 'TENTATIVE';        // Vevent, Vtodo
    public const DELEGATED          = 'DELEGATED';        // Vevent, Vtodo
    public const IN_PROCESS         = 'IN-PROCESS';       // Vtodo

    /**
     * iCal component properties ATTENDEE ROLE parameter keywords
     */
    public const CHAIR              = 'CHAIR';
    public const REQ_PARTICIPANT    = 'REQ-PARTICIPANT';   // (default)
    public const OPT_PARTICIPANT    = 'OPT-PARTICIPANT';
    public const NON_PARTICIPANT    = 'NON-PARTICIPANT';

    /**
     * iCal component property param value, VALUE=BOOLEAN, ex ATTENDEE  param key RSVP
     */
    public const FALSE              = 'FALSE';
    public const TRUE               = 'TRUE';

    /**
     * iCal component properties RRULE, EXRULE 'RECUR' keywords
     */
    public const FREQ               = 'FREQ';
    public const UNTIL              = 'UNTIL';
    public const COUNT              = 'COUNT';
    public const INTERVAL           = 'INTERVAL';
    public const BYSECOND           = 'BYSECOND';
    public const BYMINUTE           = 'BYMINUTE';
    public const BYHOUR             = 'BYHOUR';
    public const BYDAY              = 'BYDAY';
    public const BYMONTHDAY         = 'BYMONTHDAY';
    public const BYYEARDAY          = 'BYYEARDAY';
    public const BYWEEKNO           = 'BYWEEKNO';
    public const BYMONTH            = 'BYMONTH';
    public const BYSETPOS           = 'BYSETPOS';
    public const WKST               = 'WKST';
    public const SECONDLY           = 'SECONDLY';         // FREQ value
    public const MINUTELY           = 'MINUTELY';         // FREQ value
    public const HOURLY             = 'HOURLY';           // FREQ value
    public const DAILY              = 'DAILY';            // FREQ value
    public const WEEKLY             = 'WEEKLY';           // FREQ value
    public const MONTHLY            = 'MONTHLY';          // FREQ value
    public const YEARLY             = 'YEARLY';           // FREQ value
    public const DAY                = 'DAY';
    public const SU                 = 'SU';               // SUNDAY
    public const MO                 = 'MO';               // MONDAY
    public const TU                 = 'TU';               // TUESDAY
    public const WE                 = 'WE';               // WEDNESDAY
    public const TH                 = 'TH';               // THURSDAY
    public const FR                 = 'FR';               // FRIDAY
    public const SA                 = 'SA';               // SATURDAY
    /*  rfc7529 component names RRULE, EXRULE 'RECUR' keywords and values */
    public const RSCALE             = 'RSCALE';
    public const SKIP               = 'SKIP';
    public const OMIT               = 'OMIT';
    public const BACKWARD           = 'BACKWARD';
    public const FORWARD            = 'FORWARD';

    /**
     * iCal component property ACTION, IMAGE values
     */
    public const AUDIO              = 'AUDIO';
    public const DISPLAY            = 'DISPLAY';
    public const EMAIL              = 'EMAIL';
    public const PROCEDURE          = 'PROCEDURE';        // Deprecated in rfc5545

    /**
     * iCal component property IMAGE parameter key DISPLAY values
     */
    public const BADGE              = 'BADGE';          // default
    public const GRAPHIC            = 'GRAPHIC';
    public const FULLSIZE           = 'FULLSIZE';
    public const THUMBNAIL          = 'THUMBNAIL';

    /**
     * iCal component property ATTACH, IMAGE parameter keywords
     *
     * VALUE, defined above
     */
    public const ENCODING           = 'ENCODING';         // Inline Encoding
    public const FMTTYPE            = 'FMTTYPE';          // (Inline ) Format Type (media type [RFC4288])

    /**
     * iCal component property CONFERENCE parameter keywords
     *
     * VALUE, defined above
     */
    public const FEATURE            = 'FEATURE';
    public const LABEL              = 'LABEL';

    /**
     * iCal component property CONFERENCE parameter key FEATURE values
     *
     * AUDIO, defined above
     */
    public const CHAT               = 'CHAT';
    public const FEED               = 'FEED';
    public const MODERATOR          = 'MODERATOR';
    public const PHONE              = 'PHONE';
    public const SCREEN             = 'SCREEN';
    public const VIDEO              = 'VIDEO';

    /**
     * iCal component property ATTACH, IMAGE parameter key ENCODING values
     */
    public const EIGHTBIT           = '8BIT';             // e.i 8BIT...
    public const BASE64             = 'BASE64';

    /**
     * iCal component property CLASS values
     */
    public const P_BLIC             = 'PUBLIC';           // note PUBLIC
    public const P_IVATE            = 'PRIVATE';          // note PRIVATE
    public const CONFIDENTIAL       = 'CONFIDENTIAL';

    /**
     * iCal component property FREEBUZY parameter keyword
     */
    public const FBTYPE             = 'FBTYPE';           // Free/Busy Time Type

    /**
     * iCal component property FREEBUZY parameter key FBTYPE values
     *
     * iCal rfc7953 VAVAILABILITY component property BUSYTYPE values, all but 'FREE'
     */
    public const FREE               = 'FREE';
    public const BUSY               = 'BUSY';
    public const BUSY_UNAVAILABLE   = 'BUSY-UNAVAILABLE';
    public const BUSY_TENTATIVE     = 'BUSY-TENTATIVE';

    /**
     * iCal component property RECURRENCE-ID parameter keyword
     */
    public const RANGE              = 'RANGE';            // Recurrence Identifier Range

    /**
     * iCal component property RECURRENCE-ID parameter key value
     */
    public const THISANDFUTURE      = 'THISANDFUTURE';    // RANGE value


    /**
     * iCal component property RELATED-TO parameter keyword
     */
    public const RELTYPE            = 'RELTYPE';          //  Relationship Type

    /**
     * iCal component property RELATED-TO parameter key RELTYPE value
     */
    public const PARENT             = 'PARENT';           // (default)
    public const CHILD              = 'CHILD';
    public const SIBLING            = 'SIBLING';
    public const SNOOZE             = 'SNOOZE';           // rfc9074 Valarm

    /**
     * iCal component property TRIGGER parameter keyword
     */
    public const RELATED            = 'RELATED';

    /**
     * iCal component property TRIGGER parameter key TRIGGER values
     */
    public const START              = 'START';
    public const END                = 'END';

    /**
     * iCal component property GEO parts
     */
    public const LATITUDE           = 'latitude';
    public const LONGITUDE          = 'longitude';

    /**
     * iCal component rfc9073 property PARTICIPANT-TYPE parts
     *
     * CONTACT, defined above
     */
    public const ORDER              = 'ORDER';
    public const ACTIVE             = 'ACTIVE';
    public const INACTIVE           = 'INACTIVE';
    public const SPONSOR            = 'SPONSOR';
    public const BOOKING_CONTACT    = 'BOOKING-CONTACT';
    public const EMERGENCY_CONTACT  = 'EMERGENCY-CONTACT';
    public const PUBLICITY_CONTACT  = 'PUBLICITY-CONTACT';
    public const PLANNER_CONTACT    = 'PLANNER-CONTACT';
    public const PERFORMER          = 'PERFORMER';
    public const SPEAKER            = 'SPEAKER';

    /**
     * iCal component property Request-status  parts
     */
    public const STATCODE           = 'statcode';
    public const STATDESC           = 'statdesc';
    public const EXTDATA            = 'extdata';

    /**
     * iCal component property STATUS values
     *
     * NEEDS_ACTION, defined above               // Vtodo
     * TENTATIVE, defined above                  // Vevent
     * COMPLETED, defined above                  // Vtodo
     * IN_PROCESS                                // Vtodo
     */
    public const CONFIRMED          = 'CONFIRMED';        // Vevent
    public const CANCELLED          = 'CANCELLED';        // Vevent, Vtodo, Vjournal
    public const DRAFT              = 'DRAFT';            // Vjournal
    public const F_NAL              = 'FINAL';            // Vjournal

    /**
     * iCal component property TRANSP values
     */
    public const OPAQUE             = 'OPAQUE';           // default
    public const TRANSPARENT        = 'TRANSPARENT';

    /**
     * iCal Valarm component property PROXIMITY values
     */
    public const ARRIVE             = 'ARRIVE';
    public const DEPART             = 'DEPART';
    public const CONNECT            = 'CONNECT';
    public const DISCONNECT         = 'DISCONNECT';

    /**
     * iCal Vresource component property RESOURCE-TYPE values
     *
     * ROOM, defined above               // ATTENDEE/ORGANIZER CUTYPE parameter
     */
    public const PROJECTOR          = 'PROJECTOR';
    public const REMOTE_CONFERENCE_AUDIO = 'REMOTE-CONFERENCE-AUDIO';
    public const REMOTE_CONFERENCE_VIDEO = 'REMOTE-CONFERENCE-VIDEO';

    /**
     * UTC DateTimezones
     */
    public const Z                  = 'Z';
    public const UTC                = 'UTC';
    public const GMT                = 'GMT';

    /**
     * Calendar extension x-properties, some...
     * @link http://en.wikipedia.org/wiki/ICalendar#Calendar_extensions
     */
    public const X_WR_CALNAME       = 'X-WR-CALNAME';
    public const X_WR_CALDESC       = 'X-WR-CALDESC';
    public const X_WR_RELCALID      = 'X-WR-RELCALID';
    public const X_WR_TIMEZONE      = 'X-WR-TIMEZONE';
    public const X_LIC_LOCATION     = 'X-LIC-LOCATION';

    /**
     * Vcalendar::selectComponents() added component x-property names
     */
    public const X_CURRENT_DTSTART  = 'X-CURRENT-DTSTART';
    public const X_CURRENT_DTEND    = 'X-CURRENT-DTEND';
    public const X_CURRENT_DUE      = 'X-CURRENT-DUE';
    public const X_RECURRENCE       = 'X-RECURRENCE';
    public const X_OCCURENCE        = 'X-OCCURENCE';

    /**
     * Class constants, config keys
     *
     * LANGUAGE set above
     */
    public const ALLOWEMPTY         = 'ALLOWEMPTY';
    public const COMPSINFO          = 'COMPSINFO';
    public const ISLOCALTIME        = 'ISLOCALTIME';
    public const PROPINFO           = 'PROPINFO';
    public const SETPROPERTYNAMES   = 'SETPROPERTYNAMES';
    public const UNIQUE_ID          = 'UNIQUE_ID';
}
