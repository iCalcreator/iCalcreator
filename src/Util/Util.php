<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * This file is a part of iCalcreator.
 *
 * Copyright (c) 2007-2018 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      http://kigkonsult.se/iCalcreator/index.php
 * Package   iCalcreator
 * Version   2.26
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the [rfc5545] PRODID as implemented and
 *           invoked in iCalcreator shall be included in all copies or
 *           substantial portions of the iCalcreator.
 *           iCalcreator can be used either under the terms of
 *           a proprietary license, available from iCal_at_kigkonsult_dot_se
 *           or the GNU Affero General Public License, version 3:
 *           iCalcreator is free software: you can redistribute it and/or
 *           modify it under the terms of the GNU Affero General Public License
 *           as published by the Free Software Foundation, either version 3 of
 *           the License, or (at your option) any later version.
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *           GNU Affero General Public License for more details.
 *           You should have received a copy of the GNU Affero General Public
 *           License along with this program.
 *           If not, see <http://www.gnu.org/licenses/>.
 */

namespace Kigkonsult\Icalcreator\Util;

use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Vcalendar;
use DateTimeZone;
use DateTime;
use Exception;

/**
 * iCalcreator utility/support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class Util implements IcalInterface
{

    /**
     * @var string  some common X-properties
     * @see http://en.wikipedia.org/wiki/ICalendar#Calendar_extensions
     * @static
     */
    public static $X_WR_CALNAME    = 'X-WR-CALNAME';
    public static $X_WR_CALDESC    = 'X-WR-CALDESC';
    public static $X_WR_RELCALID   = 'X-WR-RELCALID';
    public static $X_WR_TIMEZONE   = 'X-WR-TIMEZONE';
    public static $X_LIC_LOCATION  = 'X-LIC-LOCATION';

    /**
     * @var string  iCal property names
     * @static
     */
    public static $ACTION           = 'ACTION';
    public static $ATTACH           = 'ATTACH';
    public static $ATTENDEE         = 'ATTENDEE';
    public static $CALSCALE         = 'CALSCALE';
    public static $CATEGORIES       = 'CATEGORIES';
    public static $CLASS            = 'CLASS';
    public static $COMMENT          = 'COMMENT';
    public static $COMPLETED        = 'COMPLETED';
    public static $CONTACT          = 'CONTACT';
    public static $CREATED          = 'CREATED';
    public static $DESCRIPTION      = 'DESCRIPTION';
    public static $DTEND            = 'DTEND';
    public static $DTSTAMP          = 'DTSTAMP';
    public static $DTSTART          = 'DTSTART';
    public static $DUE              = 'DUE';
    public static $DURATION         = 'DURATION';
    public static $EXDATE           = 'EXDATE';
    public static $EXRULE           = 'EXRULE';
    public static $FREEBUSY         = 'FREEBUSY';
    public static $GEO              = 'GEO';
    public static $GEOLOCATION      = 'GEOLOCATION';
    public static $LAST_MODIFIED    = 'LAST-MODIFIED';
    public static $LOCATION         = 'LOCATION';
    public static $METHOD           = 'METHOD';
    public static $ORGANIZER        = 'ORGANIZER';
    public static $PERCENT_COMPLETE = 'PERCENT-COMPLETE';
    public static $PRIORITY         = 'PRIORITY';
    public static $PRODID           = 'PRODID';
    public static $RECURRENCE_ID    = 'RECURRENCE-ID';
    public static $RELATED_TO       = 'RELATED-TO';
    public static $REPEAT           = 'REPEAT';
    public static $REQUEST_STATUS   = 'REQUEST-STATUS';
    public static $RESOURCES        = 'RESOURCES';
    public static $RDATE            = 'RDATE';
    public static $RRULE            = 'RRULE';
    public static $SEQUENCE         = 'SEQUENCE';
    public static $STATUS           = 'STATUS';
    public static $SUMMARY          = 'SUMMARY';
    public static $TRANSP           = 'TRANSP';
    public static $TRIGGER          = 'TRIGGER';
    public static $TZID             = 'TZID';
    public static $TZNAME           = 'TZNAME';
    public static $TZOFFSETFROM     = 'TZOFFSETFROM';
    public static $TZOFFSETTO       = 'TZOFFSETTO';
    public static $TZURL            = 'TZURL';
    public static $UID              = 'UID';
    public static $URL              = 'URL';
    public static $VERSION          = 'VERSION';
    public static $X_PROP           = 'X-PROP';

    /**
     * @var array  iCal component collections
     * @static
     */
    public static $VCOMPS   = [
        Vcalendar::VEVENT,
        Vcalendar::VTODO,
        Vcalendar::VJOURNAL,
        Vcalendar::VFREEBUSY
    ];
    public static $MCOMPS   = [
        Vcalendar::VEVENT,
        Vcalendar::VTODO,
        Vcalendar::VJOURNAL,
        Vcalendar::VFREEBUSY,
        Vcalendar::VALARM,
        Vcalendar::VTIMEZONE
    ];
    public static $SUBCOMPS = [
        Vcalendar::VALARM,
        Vcalendar::VTIMEZONE,
        Vcalendar::STANDARD,
        Vcalendar::DAYLIGHT
    ];
    public static $TZCOMPS  = [
        Vcalendar::VTIMEZONE,
        Vcalendar::STANDARD,
        Vcalendar::DAYLIGHT
    ];
    public static $ALLCOMPS = [
        Vcalendar::VTIMEZONE,
        Vcalendar::STANDARD,
        Vcalendar::DAYLIGHT,
        Vcalendar::VEVENT,
        Vcalendar::VTODO,
        Vcalendar::VJOURNAL,
        Vcalendar::VFREEBUSY,
        Vcalendar::VALARM
    ];

    /**
     * @var array  iCal component property collections
     * @static
     */
    public static $PROPNAMES  = [
        'ACTION', 'ATTACH', 'ATTENDEE', 'CATEGORIES',
        'CLASS', 'COMMENT', 'COMPLETED', 'CONTACT',
        'CREATED', 'DESCRIPTION', 'DTEND', 'DTSTAMP',
        'DTSTART', 'DUE', 'DURATION', 'EXDATE', 'EXRULE',
        'FREEBUSY', 'GEO', 'LAST-MODIFIED', 'LOCATION',
        'ORGANIZER', 'PERCENT-COMPLETE', 'PRIORITY',
        'RECURRENCE-ID', 'RELATED-TO', 'REPEAT',
        'REQUEST-STATUS', 'RESOURCES', 'RRULE', 'RDATE',
        'SEQUENCE', 'STATUS', 'SUMMARY', 'TRANSP',
        'TRIGGER', 'TZNAME', 'TZID', 'TZOFFSETFROM',
        'TZOFFSETTO', 'TZURL', 'UID', 'URL', 'X-',
    ];

    public static $DATEPROPS  = [
        'DTSTART', 'DTEND', 'DUE', 'CREATED', 'COMPLETED',
        'DTSTAMP', 'LAST-MODIFIED', 'RECURRENCE-ID',
    ];

    public static $OTHERPROPS = [
        'ATTENDEE', 'CATEGORIES', 'CONTACT', 'LOCATION',
        'ORGANIZER', 'PRIORITY', 'RELATED-TO', 'RESOURCES',
        'STATUS', 'SUMMARY', 'UID', 'URL',
    ];

    public static $MPROPS1    = [
        'ATTENDEE', 'CATEGORIES', 'CONTACT',
        'RELATED-TO', 'RESOURCES',
    ];

    public static $MPROPS2    = [
        'ATTACH', 'ATTENDEE', 'CATEGORIES',
        'COMMENT', 'CONTACT', 'DESCRIPTION',
        'EXDATE', 'EXRULE', 'FREEBUSY', 'RDATE',
        'RELATED-TO', 'RESOURCES', 'RRULE',
        'REQUEST-STATUS', 'TZNAME', 'X-PROP',
    ];

    /**
     * @var string  iCalcreator config keys
     * @static
     */
    public static $ALLOWEMPTY       = 'ALLOWEMPTY';
    public static $COMPSINFO        = 'COMPSINFO';
    public static $DELIMITER        = 'DELIMITER';
    public static $DIRECTORY        = 'DIRECTORY';
    public static $FILENAME         = 'FILENAME';
    public static $DIRFILE          = 'DIRFILE';
    public static $FILESIZE         = 'FILESIZE';
    public static $FILEINFO         = 'FILEINFO';
    public static $LANGUAGE         = 'LANGUAGE';
    public static $PROPINFO         = 'PROPINFO';
    public static $SETPROPERTYNAMES = 'SETPROPERTYNAMES';
    public static $UNIQUE_ID        = 'UNIQUE_ID';

    /**
     * @var string  iCal date/time parameter key values
     * @static
     */
    public static $DATE                 = 'DATE';
    public static $PERIOD               = 'PERIOD';
    public static $DATE_TIME            = 'DATE-TIME';
    public static $DEFAULTVALUEDATETIME = [ 'VALUE' => 'DATE-TIME' ];
    public static $T                    = 'T';
    public static $Z                    = 'Z';
    public static $UTC                  = 'UTC';
    public static $GMT                  = 'GMT';
    public static $LCYEAR               = 'year';
    public static $LCMONTH              = 'month';
    public static $LCDAY                = 'day';
    public static $LCHOUR               = 'hour';
    public static $LCMIN                = 'min';
    public static $LCSEC                = 'sec';
    public static $LCtz                 = 'tz';
    public static $LCWEEK               = 'week';
    public static $LCTIMESTAMP          = 'timestamp';

    /**
     * @var string  iCal ATTENDEE, ORGANIZER etc param keywords
     * @static
     */
    public static $CUTYPE          = 'CUTYPE';
    public static $MEMBER          = 'MEMBER';
    public static $ROLE            = 'ROLE';
    public static $PARTSTAT        = 'PARTSTAT';
    public static $RSVP            = 'RSVP';
    public static $DELEGATED_TO    = 'DELEGATED-TO';
    public static $DELEGATED_FROM  = 'DELEGATED-FROM';
    public static $SENT_BY         = 'SENT-BY';
    public static $CN              = 'CN';
    public static $DIR             = 'DIR';
    public static $INDIVIDUAL      = 'INDIVIDUAL';
    public static $NEEDS_ACTION    = 'NEEDS-ACTION';
    public static $REQ_PARTICIPANT = 'REQ-PARTICIPANT';
    public static $false           = 'false';

    /**
     * @var array  iCal ATTENDEE, ORGANIZER etc param collections
     * @static
     */
    public static $ATTENDEEPARKEYS    = [ 'DELEGATED-FROM', 'DELEGATED-TO', 'MEMBER' ];
    public static $ATTENDEEPARALLKEYS = [
        'CUTYPE', 'MEMBER', 'ROLE', 'PARTSTAT',
        'RSVP', 'DELEGATED-TO', 'DELEGATED-FROM',
        'SENT-BY', 'CN', 'DIR', 'LANGUAGE',
    ];

    /**
     * @var string  iCal RRULE, EXRULE etc param keywords
     * @static
     */
    public static $FREQ       = 'FREQ';
    public static $UNTIL      = 'UNTIL';
    public static $COUNT      = 'COUNT';
    public static $INTERVAL   = 'INTERVAL';
    public static $WKST       = 'WKST';
    public static $BYMONTHDAY = 'BYMONTHDAY';
    public static $BYYEARDAY  = 'BYYEARDAY';
    public static $BYWEEKNO   = 'BYWEEKNO';
    public static $BYMONTH    = 'BYMONTH';
    public static $BYSETPOS   = 'BYSETPOS';
    public static $BYDAY      = 'BYDAY';
    public static $DAY        = 'DAY';

    /**
     * @var string  misc. values
     * @static
     */
    public static $ALTREP        = 'ALTREP';
    public static $ALTRPLANGARR  = [ 'ALTREP', 'LANGUAGE' ];
    public static $VALUE         = 'VALUE';
    public static $BINARY        = 'BINARY';
    public static $LCvalue       = 'value';
    public static $LCparams      = 'params';
    public static $UNPARSEDTEXT  = 'unparsedtext';
    public static $SERVER_NAME   = 'SERVER_NAME';
    public static $LOCALHOST     = 'localhost';
    public static $EMPTYPROPERTY = '';
    public static $FMTBEGIN      = "BEGIN:%s\r\n";
    public static $FMTEND        = "END:%s\r\n";
    public static $CRLF          = "\r\n";
    public static $COMMA         = ',';
    public static $COLON         = ':';
    public static $QQ            = '"';
    public static $SEMIC         = ';';
    public static $MINUS         = '-';
    public static $PLUS          = '+';
    public static $SP1           = ' ';
    public static $ZERO          = '0';
    public static $DOT           = '.';
    public static $L             = '/';

    /**
     * @var string  Util date/datetime formats
     * @access private
     * @static
     */
    private static $YMDHIS3 = 'Y-m-d-H-i-s';

    /**
     * Initiates configuration, set defaults
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-03-11
     * @param array $config
     * @return array
     * @static
     */
    public static function initConfig( $config ) {
        $config = \array_change_key_case( $config, CASE_UPPER );
        if( ! isset( $config[Util::$ALLOWEMPTY] )) {
            $config[Util::$ALLOWEMPTY] = true;
        }
        if( ! isset( $config[Util::$DELIMITER] )) {
            $config[Util::$DELIMITER] = DIRECTORY_SEPARATOR;
        }
        if( ! isset( $config[Util::$DIRECTORY] )) {
            $config[Util::$DIRECTORY] = Util::$DOT;
        }
        return $config;
    }

    /**
     * Return bool true if comptype is in array
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-03
     * @param string $compType   component name
     * @param array  $compList   list of components
     * @return bool
     * @static
     */
    public static function isCompInList( $compType, array $compList ) {
        return \in_array( \ucfirst( \strtolower( $compType )), $compList);
    }

    /**
     * Return bool true if property is in array
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-04
     * @param string $propName   property name
     * @param array  $propList   list of properties
     * @return bool
     * @static
     */
    public static function isPropInList( $propName, array $propList ) {
        return \in_array( \strtoupper( $propName ), $propList);
    }

    /**
     * Return date YMD string
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-05
     * @param array  $date
     * @return string
     * @static
     */
    public static function getYMDString( array $date ) {
        static $YMD = '%04d%02d%02d';
        return \sprintf( $YMD, $date[Util::$LCYEAR], $date[Util::$LCMONTH], $date[Util::$LCDAY] );
    }

    /**
     * Return date His string
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-05
     * @param array  $date
     * @return string
     * @static
     */
    public static function getHisString( array $date ) {
        static $HIS = '%02d%02d%02d';
        return \sprintf( $HIS, (int) $date[Util::$LCHOUR], (int) $date[Util::$LCMIN], (int) $date[Util::$LCSEC] );
    }

    /**
     * Return date YMDHISE string
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-05
     * @param array  $date
     * @param string $tz
     * @return string
     * @static
     */
    public static function getYMDHISEString( array $date, $tz ) {
        static $YMDHISE = '%04d-%02d-%02d %02d:%02d:%02d %s';
        return \sprintf(
            $YMDHISE,
            (int) $date[Util::$LCvalue][Util::$LCYEAR],
            (int) $date[Util::$LCvalue][Util::$LCMONTH],
            (int) $date[Util::$LCvalue][Util::$LCDAY],
            (int) $date[Util::$LCvalue][Util::$LCHOUR],
            (int) $date[Util::$LCvalue][Util::$LCMIN],
            (int) $date[Util::$LCvalue][Util::$LCSEC],
            $tz
        );
    }

    /**
     * Ensure array datetime
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-05
     * @param array  $date
     * @param string $tz
     * @param int    $parno
     * @return array
     * @static
     */
    public static function ensureArrDatetime( array $date, $tz, $parno ) {
        return Util::strDate2ArrayDate( Util::getYMDHISEString( $date, $tz ), $parno );
    }

    /**
     * Return formatted output for calendar component property
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.20 - 2017-01-30
     * @param string $label      property name
     * @param string $attributes property attributes
     * @param string $content    property content
     * @return string
     * @static
     */
    public static function createElement( $label, $attributes = null, $content = null ) {
        $output = \strtoupper( $label );
        if( ! empty( $attributes )) {
            $output .= \trim( $attributes );
        }
        $output .= Util::$COLON . $content;
        return Util::size75( $output );
    }

    /**
     * Return formatted output for calendar component property parameters
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-01-29
     * @param array  $params
     * @param array  $ctrKeys
     * @param string $lang
     * @return string
     * @static
     */
    public static function createParams( $params = null, $ctrKeys = null, $lang = null ) {
        static $FMTFMTTYPE  = ';FMTTYPE=%s%s';
        static $FMTKEQV     = '%s=%s';
        static $ENCODING    = 'ENCODING';
        static $FMTTYPE     = 'FMTTYPE';
        static $RANGE       = 'RANGE';
        static $RELTYPE     = 'RELTYPE';
        static $PARAMSARRAY = null;
        if( is_null( $PARAMSARRAY )) {
            $PARAMSARRAY = [
                Util::$ALTREP,
                Util::$CN,
                Util::$DIR,
                $ENCODING,
                $FMTTYPE,
                Util::$LANGUAGE,
                $RANGE,
                $RELTYPE,
                Util::$SENT_BY,
                Util::$TZID,
                Util::$VALUE,
            ];
        }
        static $FMTQ   = '"%s"';
        static $FMTQTD = ';%s=%s%s%s';
        static $FMTCMN = ';%s=%s';
        if( ! \is_array( $params )) {
            $params = [];
        }
        if( ! \is_array( $ctrKeys ) || empty( $ctrKeys )) {
            $ctrKeys = [];
        }
        if( empty( $params ) && empty( $ctrKeys )) {
            return null;
        }
        $attrLANG       = $attr1 = $attr2 = null;
        $hasCNattrKey   = ( \in_array( Util::$CN, $ctrKeys ));
        $hasLANGattrKey = ( \in_array( Util::$LANGUAGE, $ctrKeys ));
        $CNattrExist    = false;
        $xparams        = [];
        $params         = \array_change_key_case( $params, CASE_UPPER );
        foreach( $params as $paramKey => $paramValue ) {
            if(( false !== \strpos( $paramValue, Util::$COLON )) ||
                ( false !== \strpos( $paramValue, Util::$SEMIC )) ||
                ( false !== \strpos( $paramValue, Util::$COMMA ))) {
                $paramValue = \sprintf( $FMTQ, $paramValue );
            }
            if( \ctype_digit((string) $paramKey )) {
                $xparams[] = $paramValue;
                continue;
            }
            if( ! \in_array( $paramKey, $PARAMSARRAY )) {
                $xparams[$paramKey] = $paramValue;
            }
            else {
                $params[$paramKey] = $paramValue;
            }
        }
        ksort( $xparams, SORT_STRING );
        foreach( $xparams as $paramKey => $paramValue ) {
            $attr2 .= Util::$SEMIC;
            $attr2 .= ( \ctype_digit((string) $paramKey ))
                ? $paramValue
                : sprintf( $FMTKEQV, $paramKey, $paramValue );
        }
        if( isset( $params[$FMTTYPE] ) && ! \in_array( $FMTTYPE, $ctrKeys )) {
            $attr1 .= \sprintf( $FMTFMTTYPE, $params[$FMTTYPE], $attr2 );
            $attr2 = null;
        }
        if( isset( $params[$ENCODING] ) &&
            ! \in_array( $ENCODING, $ctrKeys )) {
            if( ! empty( $attr2 )) {
                $attr1 .= $attr2;
                $attr2 = null;
            }
            $attr1 .= \sprintf( $FMTCMN, $ENCODING, $params[$ENCODING] );
        }
        if( isset( $params[Util::$VALUE] ) &&
            ! \in_array( Util::$VALUE, $ctrKeys )) {
            $attr1 .= \sprintf( $FMTCMN, Util::$VALUE, $params[Util::$VALUE] );
        }
        if( isset( $params[Util::$TZID] ) && ! \in_array( Util::$TZID, $ctrKeys )) {
            $attr1 .= \sprintf( $FMTCMN, Util::$TZID, $params[Util::$TZID] );
        }
        if( isset( $params[$RANGE] ) && ! \in_array( $RANGE, $ctrKeys )) {
            $attr1 .= \sprintf( $FMTCMN, $RANGE, $params[$RANGE] );
        }
        if( isset( $params[$RELTYPE] ) && ! \in_array( $RELTYPE, $ctrKeys )) {
            $attr1 .= \sprintf( $FMTCMN, $RELTYPE, $params[$RELTYPE] );
        }
        if( isset( $params[Util::$CN] ) && $hasCNattrKey ) {
            $attr1       = \sprintf( $FMTCMN, Util::$CN, $params[Util::$CN] );
            $CNattrExist = true;
        }
        if( isset( $params[Util::$DIR] ) && \in_array( Util::$DIR, $ctrKeys )) {
            $delim = ( false !== \strpos( $params[Util::$DIR], Util::$QQ )) ? null : Util::$QQ;
            $attr1 .= \sprintf( $FMTQTD, Util::$DIR, $delim, $params[Util::$DIR], $delim );
        }
        if( isset( $params[Util::$SENT_BY] ) && \in_array( Util::$SENT_BY, $ctrKeys )) {
            $attr1 .= \sprintf( $FMTCMN, Util::$SENT_BY, $params[Util::$SENT_BY] );
        }
        if( isset( $params[Util::$ALTREP] ) && \in_array( Util::$ALTREP, $ctrKeys )) {
            $delim = ( false !== \strpos( $params[Util::$ALTREP], Util::$QQ )) ? null : Util::$QQ;
            $attr1 .= \sprintf( $FMTQTD, Util::$ALTREP, $delim, $params[Util::$ALTREP], $delim );
        }
        if( isset( $params[Util::$LANGUAGE] ) && $hasLANGattrKey ) {
            $attrLANG .= \sprintf( $FMTCMN, Util::$LANGUAGE, $params[Util::$LANGUAGE] );
        }
        elseif(( $CNattrExist || $hasLANGattrKey ) && ! empty( $lang )) {
            $attrLANG .= \sprintf( $FMTCMN, Util::$LANGUAGE, $lang );
        }
        return $attr1 . $attrLANG . $attr2;
    }

    /**
     * Return (conformed) iCal component property parameters
     *
     * Trim quoted values, default parameters may be set, if missing
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-04-08
     * @param array $params
     * @param array $defaults
     * @return array
     * @static
     */
    public static function setParams( $params, $defaults = null ) {
        if( ! \is_array( $params )) {
            $params = [];
        }
        $output = [];
        $params = \array_change_key_case( $params, CASE_UPPER );
        foreach( $params as $paramKey => $paramValue ) {
            if( \is_array( $paramValue )) {
                foreach( $paramValue as $pkey => $pValue ) {
                    $paramValue[$pkey] = \trim( $pValue, Util::$QQ );
                }
            }
            else {
                $paramValue = \trim( $paramValue, Util::$QQ );
            }
            if( Util::$VALUE == $paramKey ) {
                $output[Util::$VALUE] = \strtoupper( $paramValue );
            }
            else {
                $output[$paramKey] = $paramValue;
            }
        } // end foreach
        if( \is_array( $defaults )) {
            $output = \array_merge( \array_change_key_case( $defaults, CASE_UPPER ), $output );
        }
        return ( 0 < \count( $output )) ? $output : null;
    }

    /**
     * Remove expected key/value from array and returns foundValue (if found) else returns elseValue
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.24.1 - 2018-10-22
     * @param array  $array          iCal property parameters
     * @param string $expectedKey    expected key
     * @param string $expectedValue  expected value
     * @param int    $foundValue     return value if found
     * @param int    $elseValue      return value if not found
     * @param int    $preSet         return value if already preset
     * @return int
     * @static
     */
    public static function existRem(
        array & $array,
                $expectedKey,
                $expectedValue  = null,
                $foundValue     = null,
                $elseValue      = null,
                $preSet         = null
    ) {
        if( $preSet ) {
            return $preSet;
        }
        if( empty( $array )) {
            return $elseValue;
        }
        foreach( $array as $key => $value ) {
            if( 0 == \strcasecmp( $expectedKey, $key )) {
                if( empty( $expectedValue ) || ( 0 == \strcasecmp( $expectedValue, $value ))) {
                    unset( $array[$key] );
                    return $foundValue;
                }
            }
        }
        return $elseValue;
    }

    /**
     * Delete component property value, managing components with multiple occurencies
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.8.8 - 2011-03-15
     * @param array $multiprop component (multi-)property
     * @param int   $propix    removal counter
     * @return bool true
     * @static
     */
    public static function deletePropertyM( & $multiprop, & $propix ) {
        if( isset( $multiprop[$propix] )) {
            unset( $multiprop[$propix] );
        }
        if( empty( $multiprop )) {
            $multiprop = null;
            unset( $propix );
            return false;
        }
        return true;
    }

    /**
     * Recount property propix, used at consecutive getProperty calls
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-18
     * @param array $prop   component (multi-)property
     * @param int   $propix getter counter
     * @return bool true
     * @static
     */
    public static function recountMvalPropix( & $prop, & $propix ) {
        if( ! \is_array( $prop ) || empty( $prop )) {
            return false;
        }
        $last = \key( \array_slice( $prop, -1, 1, true ));
        while( ! isset( $prop[$propix] ) && ( $last > $propix )) {
            $propix++;
        }
        return true;
    }

    /**
     * Check index and set (an indexed) content in a multiple value array
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-04-08
     * @param array $valArr
     * @param mixed $value
     * @param array $params
     * @param array $defaults
     * @param int   $index
     * @static
     */
    public static function setMval(
        & $valArr,
          $value,
          $params   = null,
          $defaults = null,
          $index    = null
    ) {
        if( ! \is_array( $valArr )) {
            $valArr = [];
        }
        if( ! \is_null( $params )) {
            $params = Util::setParams( $params, $defaults );
        }
        if( \is_null( $index )) { // i.e. next
            $valArr[] = [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params,
            ];
            return;
        }
        $index = $index - 1;
        if( isset( $valArr[$index] )) { // replace
            $valArr[$index] = [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params,
            ];
            return;
        }
        $valArr[$index] = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params,
        ];
        \ksort( $valArr ); // order
    }

    /**
     * Return datestamp for calendar component object instance dtstamp
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-17
     * @return array
     * @static
     */
    public static function makeDtstamp() {
        $date = \explode( Util::$MINUS, \gmdate( Util::$YMDHIS3, time()));
        return [
            Util::$LCvalue  => [
                Util::$LCYEAR  => $date[0],
                Util::$LCMONTH => $date[1],
                Util::$LCDAY   => $date[2],
                Util::$LCHOUR  => $date[3],
                Util::$LCMIN   => $date[4],
                Util::$LCSEC   => $date[5],
                Util::$LCtz    => Util::$Z,
            ],
            Util::$LCparams => null,
        ];
    }

    /**
     * Return an unique id for a calendar component object instance
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-17
     * @param string $unique_id
     * @return array
     * @static
     */
    public static function makeUid( $unique_id ) {
        static $FMT = '%s-%s@%s';
        static $TMDTHIS = 'Ymd\THisT';
        return [
            Util::$LCvalue  => \sprintf( $FMT,
                                         date( $TMDTHIS ),
                                         substr( microtime(), 2, 4 ) . Util::getRandChars( 6 ),
                                         $unique_id
            ),
            Util::$LCparams => null,
        ];
    }

    /**
     * Return a random (and unique) sequence of characters
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-18
     * @param int $cnt
     * @return string
     * @access private
     * @static
     */
    private static function getRandChars( $cnt ) {
        $cnt = (int) \floor( $cnt / 2 );
        $x   = 0;
        do {
            $randChars = \bin2hex( \openssl_random_pseudo_bytes( $cnt, $cStrong ));
            $x         += 1;
        } while(( 3 > $x ) && ( false == $cStrong ));
        return $randChars;
    }

    /**
     * Return true if a date property has NO date parts
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-17
     * @param array $content
     * @return bool
     * @static
     */
    public static function hasNodate( $content ) {
        return (
            ! isset( $content[Util::$LCvalue][Util::$LCYEAR] ) &&
            ! isset( $content[Util::$LCvalue][Util::$LCMONTH] ) &&
            ! isset( $content[Util::$LCvalue][Util::$LCDAY] ) &&
            ! isset( $content[Util::$LCvalue][Util::$LCHOUR] ) &&
            ! isset( $content[Util::$LCvalue][Util::$LCMIN] ) &&
            ! isset( $content[Util::$LCvalue][Util::$LCSEC] ));
    }

    /**
     * Return true if property parameter VALUE is set to argument, otherwise false
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-12
     * @param array  $content
     * @param string $arg
     * @return bool
     * @static
     */
    public static function isParamsValueSet( array $content, $arg ) {
        return ( isset( $content[Util::$LCparams][Util::$VALUE] ) &&
              ( $arg == $content[Util::$LCparams][Util::$VALUE] ));
    }

    /**
     * Return bool true if name is X-prefixed
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-17
     * @param string $name
     * @return bool
     * @static
     */
    public static function isXprefixed( $name ) {
        static $X_ = 'X-';
        return ( 0 == \strcasecmp( $X_, \substr( $name, 0, 2 )));
    }

    /**
     * Return property name  and  opt.params and property value
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-16
     * @param  string $row
     * @return array
     * @static
     */
    public static function getPropName( $row ) {
        static $COLONSEMICARR = [ ':', ';' ];
        $propName = null;
        $cix      = 0;
        $len      = \strlen( $row );
        while( $cix < $len ) {
            if( \in_array( $row[$cix], $COLONSEMICARR )) {
                break;
            }
            $propName .= $row[$cix];
            $cix++;
        } // end while...
        if( isset( $row[$cix] )) {
            $row = \substr( $row, $cix );
        }
        else {
            $propName = Util::trimTrailNL( $propName ); // property without colon and content
            $row      = null;
        }
        return [ $propName, $row ];
    }

    /**
     * Return array from content split by '\,'
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-16
     * @param string $content
     * @return array
     * @static
     */
    public static function commaSplit( $content ) {
        static $DBBS = "\\";
        $output = [ 0 => null ];
        $cix    = $lix = 0;
        $len    = \strlen( $content );
        while( $lix < $len ) {
            if(( Util::$COMMA == $content[$lix] ) && ( $DBBS != $content[( $lix - 1 )] )) {
                $output[++$cix] = null;
            }
            else {
                $output[$cix] .= $content[$lix];
            }
            $lix++;
        }
        return \array_filter( $output );
    }

    /**
     * Return concatenated calendar rows, one row for each property
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-17
     * @param array $rows
     * @return array
     * @static
     */
    public static function concatRows( $rows ) {
        $output = [];
        $cnt    = \count( $rows );
        for( $i = 0; $i < $cnt; $i++ ) {
            $line = \rtrim( $rows[$i], Util::$CRLF );
            while( isset( $rows[$i + 1] ) &&
                 ! empty( $rows[$i + 1] ) &&
                 ( Util::$SP1 == $rows[$i + 1]{0} )) {
                $line .= \rtrim( substr( $rows[++$i], 1 ), Util::$CRLF );
            }
            $output[] = $line;
        }
        return $output;
    }

    /**
     * Return string with removed ical line folding
     *
     * Remove any line-endings that may include spaces or tabs
     * and convert all line endings (iCal default '\r\n'),
     * takes care of '\r\n', '\r' and '\n' and mixed '\r\n'+'\r', '\r\n'+'\n'
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-03-01
     * @param string $text
     * @return array
     * @static
     */
    public static function convEolChar( & $text ) {
        static $BASEDELIM  = null;
        static $BASEDELIMs = null;
        static $EMPTYROW   = null;
        static $FMT        = '%1$s%2$75s%1$s';
        static $SP0        = '';
        static $CRLFs      = [ "\r\n", "\n\r", "\n", "\r" ];
        static $CRLFexts   = [ "\r\n ", "\n\r\t" ];
        /* fix dummy line separator etc */
        if( empty( $BASEDELIM )) {
            $BASEDELIM  = Util::getRandChars( 16 );
            $BASEDELIMs = $BASEDELIM . $BASEDELIM;
            $EMPTYROW   = sprintf( $FMT, $BASEDELIM, $SP0 );
        }
        /* fix eol chars */
        $text = \str_replace( $CRLFs, $BASEDELIM, $text );
        /* fix empty lines */
        $text = \str_replace( $BASEDELIMs, $EMPTYROW, $text );
        /* fix line folding */
        $text = \str_replace( $BASEDELIM, Util::$CRLF, $text );
        $text = \str_replace( $CRLFexts, null, $text );
        /* split in component/property lines */
        return \explode( Util::$CRLF, $text );
    }

    /**
     * Return wrapped string with (byte oriented) line breaks at pos 75
     *
     * Lines of text SHOULD NOT be longer than 75 octets, excluding the line
     * break. Long content lines SHOULD be split into a multiple line
     * representations using a line "folding" technique. That is, a long
     * line can be split between any two characters by inserting a CRLF
     * immediately followed by a single linear white space character (i.e.,
     * SPACE, US-ASCII decimal 32 or HTAB, US-ASCII decimal 9). Any sequence
     * of CRLF followed immediately by a single linear white space character
     * is ignored (i.e., removed) when processing the content type.
     *
     * Edited 2007-08-26 by Anders Litzell, anders@litzell.se to fix bug where
     * the reserved expression "\n" in the arg $string could be broken up by the
     * folding of lines, causing ambiguity in the return string.
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-03-01
     * @param string $string
     * @return string
     * @access private
     * @static
     * @link   http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
     */
    private static function size75( $string ) {
        static $DBS = '\\';
        static $LCN = 'n';
        static $UCN = 'N';
        static $SPBSLCN = ' \n';
        static $SP1 = ' ';
        $tmp    = $string;
        $string = null;
        $cCnt   = $x = 0;
        while( true ) {
            if( ! isset( $tmp[$x] )) {
                $string .= Util::$CRLF;        // loop breakes here
                break;
            }
            elseif(( 74 <= $cCnt ) &&
                ( $DBS == $tmp[$x] ) &&
                (( $LCN == $tmp[$x + 1] ) || ( $UCN == $tmp[$x + 1] ))) {
                $string .= Util::$CRLF . $SPBSLCN; // don't break lines inside '\n'
                $x      += 2;
                if( ! isset( $tmp[$x] )) {
                    $string .= Util::$CRLF;
                    break;
                }
                $cCnt = 3;
            }
            elseif( 75 <= $cCnt ) {
                $string .= Util::$CRLF . $SP1;
                $cCnt   = 1;
            }
            $byte   = \ord( $tmp[$x] );
            $string .= $tmp[$x];
            switch( true ) {
                case(( $byte >= 0x20 ) && ( $byte <= 0x7F )) :
                    $cCnt += 1;                    // characters U-00000000 - U-0000007F (same as ASCII)
                    break;                         // add a one byte character
                case(( $byte & 0xE0 ) == 0xC0 ) :  // characters U-00000080 - U-000007FF, mask 110XXXXX
                    if( isset( $tmp[$x + 1] )) {
                        $cCnt   += 1;
                        $string .= $tmp[$x + 1];
                        $x      += 1;              // add a two bytes character
                    }
                    break;
                case(( $byte & 0xF0 ) == 0xE0 ) :  // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    if( isset( $tmp[$x + 2] )) {
                        $cCnt   += 1;
                        $string .= $tmp[$x + 1] . $tmp[$x + 2];
                        $x      += 2;              // add a three bytes character
                    }
                    break;
                case(( $byte & 0xF8 ) == 0xF0 ) :  // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    if( isset( $tmp[$x + 3] )) {
                        $cCnt   += 1;
                        $string .= $tmp[$x + 1] . $tmp[$x + 2] . $tmp[$x + 3];
                        $x      += 3;              // add a four bytes character
                    }
                    break;
                case(( $byte & 0xFC ) == 0xF8 ) :  // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    if( isset( $tmp[$x + 4] )) {
                        $cCnt   += 1;
                        $string .= $tmp[$x + 1] . $tmp[$x + 2] . $tmp[$x + 3] . $tmp[$x + 4];
                        $x      += 4;              // add a five bytes character
                    }
                    break;
                case(( $byte & 0xFE ) == 0xFC ) :  // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    if( isset( $tmp[$x + 5] )) {
                        $cCnt   += 1;
                        $string .= $tmp[$x + 1] . $tmp[$x + 2] . $tmp[$x + 3] . $tmp[$x + 4] . $tmp[$x + 5];
                        $x      += 5;              // add a six bytes character
                    }
                    break;
                default:                           // add any other byte without counting up $cCnt
                    break;
            } // end switch( true )
            $x += 1;                 // next 'byte' to test
        } // end while( true )
        return $string;
    }

    /**
     * Separate (string) to iCal property value and attributes
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.13 - 2017-05-02
     * @param string $line     property content
     * @param array  $propAttr property parameters
     * @static
     * @TODO   same as in Util::calAddressCheck() ??
     */
    public static function splitContent( & $line, & $propAttr = null ) {
        static $CSS = '://';
        static $MSTZ = [ 'utc-', 'utc+', 'gmt-', 'gmt+' ];
        static $PROTO3 = [ 'fax:', 'cid:', 'sms:', 'tel:', 'urn:' ];
        static $PROTO4 = [ 'crid:', 'news:', 'pres:' ];
        static $PROTO6 = [ 'mailto:' ];
        static $EQ = '=';
        $attr         = [];
        $attrix       = -1;
        $clen         = \strlen( $line );
        $WithinQuotes = false;
        $len          = \strlen( $line );
        $cix          = 0;
        while( $cix < $len ) {
            if( ! $WithinQuotes && ( Util::$COLON == $line[$cix] ) &&
                ( \substr( $line, $cix, 3 ) != $CSS ) &&
                ( ! \in_array( strtolower( \substr( $line, $cix - 6, 4 )), $MSTZ )) &&
                ( ! \in_array( strtolower( \substr( $line, $cix - 3, 4 )), $PROTO3 )) &&
                ( ! \in_array( strtolower( \substr( $line, $cix - 4, 5 )), $PROTO4 )) &&
                ( ! \in_array( strtolower( \substr( $line, $cix - 6, 7 )), $PROTO6 ))) {
                $attrEnd = true;
                if(( $cix < ( $clen - 4 )) &&
                    \ctype_digit( \substr( $line, $cix + 1, 4 ))) { // an URI with a (4pos) portnr??
                    for( $c2ix = $cix; 3 < $c2ix; $c2ix-- ) {
                        if( $CSS == \substr( $line, $c2ix - 2, 3 )) {
                            $attrEnd = false;
                            break; // an URI with a portnr!!
                        }
                    }
                }
                if( $attrEnd ) {
                    $line = \substr( $line, ( $cix + 1 ));
                    break;
                }
                $cix++;
            } // end if(  ! $WithinQuotes...
            if( Util::$QQ == $line[$cix] ) { // '"'
                $WithinQuotes = ! $WithinQuotes;
            }
            if( Util::$SEMIC == $line[$cix] ) { // ';'
                $attr[++$attrix] = null;
            }
            else {
                if( 0 > $attrix ) {
                    $attrix = 0;
                }
                $attr[$attrix] .= $line[$cix];
            }
            $cix++;
        } // end while...
        /* make attributes in array format */
        $propAttr = [];
        foreach( $attr as $attribute ) {
            $attrsplit = explode( $EQ, $attribute, 2 );
            if( 1 < \count( $attrsplit )) {
                $propAttr[$attrsplit[0]] = $attrsplit[1];
            }
        }
    }

    /**
     * Special characters management output
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-17
     * @param string $string
     * @return string
     * @static
     */
    public static function strrep( $string ) {
        static $BSLCN    = '\n';
        static $SPECCHAR = [ 'n', 'N', 'r', ',', ';' ];
        static $DBS      = "\\";
        static $SQ       = "'";
        static $BSCOMMA  = '\,';
        static $BSSEMIC  = '\;';
        static $BSLCR    = "\r";
        static $QBSLCN   = "\n";
        static $BSUCN    = '\N';
        $string = (string) $string;
        $strLen = \strlen( $string );
        $pos    = 0;
        while( $pos < $strLen ) {
            if( false === ( $pos = \strpos( $string, $DBS, $pos ))) {
                break;
            }
            if( ! \in_array( \substr( $string, $pos, 1 ), $SPECCHAR )) {
                $string = \substr( $string, 0, $pos ) . $DBS . \substr( $string, ( $pos + 1 ));
                $pos    += 1;
            }
            $pos += 1;
        }
        if( false !== \strpos( $string, Util::$QQ )) {
            $string = \str_replace( Util::$QQ, $SQ, $string );
        }
        if( false !== \strpos( $string, Util::$COMMA )) {
            $string = \str_replace( Util::$COMMA, $BSCOMMA, $string );
        }
        if( false !== \strpos( $string, Util::$SEMIC )) {
            $string = \str_replace( Util::$SEMIC, $BSSEMIC, $string );
        }
        if( false !== \strpos( $string, Util::$CRLF )) {
            $string = \str_replace( Util::$CRLF, $BSLCN, $string );
        }
        elseif( false !== strpos( $string, $BSLCR )) {
            $string = \str_replace( $BSLCR, $BSLCN, $string );
        }
        elseif( false !== strpos( $string, $QBSLCN )) {
            $string = \str_replace( $QBSLCN, $BSLCN, $string );
        }
        if( false !== \strpos( $string, $BSUCN )) {
            $string = \str_replace( $BSUCN, $BSLCN, $string );
        }
        $string = \str_replace( Util::$CRLF, $BSLCN, $string );
        return $string;
    }

    /**
     * Special characters management input
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.2 - 2015-06-25
     * @param string $string
     * @return string
     * @static
     */
    public static function strunrep( $string ) {
        static $BS4 = '\\\\';
        static $BS2 = '\\';
        static $BSCOMMA = '\,';
        static $BSSEMIC = '\;';
        $string = \str_replace( $BS4, $BS2, $string );
        $string = \str_replace( $BSCOMMA, Util::$COMMA, $string );
        $string = \str_replace( $BSSEMIC, Util::$SEMIC, $string );
        return $string;
    }

    /**
     * Return string with trimmed trailing \n
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-17
     * @param string $value
     * @return string
     * @static
     */
    public static function trimTrailNL( $value ) {
        static $NL = '\n';
        if( $NL == \strtolower( \substr( $value, -2 ))) {
            $value = \substr( $value, 0, ( \strlen( $value ) - 2 ));
        }
        return $value;
    }

    /**
     * Return internal date (format) with parameters based on input date
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.21.11 - 2015-03-21
     * @param mixed  $year
     * @param mixed  $month
     * @param int    $day
     * @param int    $hour
     * @param int    $min
     * @param int    $sec
     * @param string $tz
     * @param array  $params
     * @param string $caller
     * @param string $compType
     * @param string $tzid
     * @return array
     * @static
     */
    public static function setDate(
        $year,
        $month    = null,
        $day      = null,
        $hour     = null,
        $min      = null,
        $sec      = null,
        $tz       = null,
        $params   = null,
        $caller   = null,
        $compType = null,
        $tzid     = null
    ) {
        $input     = [];
        $parno     = null;
        $localtime = (( Util::$DTSTART == $caller ) &&
            Util::isCompInList( $compType, Util::$TZCOMPS )) ? true : false;
        Util::strDate2arr( $year );
        if( Util::isArrayDate( $year )) {
            $input[Util::$LCvalue] = Util::chkDateArr( $year );
            if( 100 > $input[Util::$LCvalue][Util::$LCYEAR] ) {
                $input[Util::$LCvalue][Util::$LCYEAR] += 2000;
            }
            if( $localtime ) {
                unset( $month[Util::$VALUE], $month[Util::$TZID] );
            }
            elseif( ! isset( $month[Util::$TZID] ) && isset( $tzid )) {
                $month[Util::$TZID] = $tzid;
            }
            if( isset( $input[Util::$LCvalue][Util::$LCtz] ) &&
                Util::isOffset( $input[Util::$LCvalue][Util::$LCtz] )) {
                unset( $month[Util::$TZID] );
            }
            elseif( ! isset( $input[Util::$LCvalue][Util::$LCtz] ) &&
                isset( $month[Util::$TZID] ) &&
                Util::isOffset( $month[Util::$TZID] )) {
                $input[Util::$LCvalue][Util::$LCtz] = $month[Util::$TZID];
                unset( $month[Util::$TZID] );
            }
            $input[Util::$LCparams] = Util::setParams( $month, Util::$DEFAULTVALUEDATETIME );
            $foundValue = ( isset( $input[Util::$LCvalue][Util::$LCtz] )) ? 7 : 6;
            $parno  = Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE_TIME, $foundValue );
            $parno  = Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE,3, \count( $input[Util::$LCvalue] ), $parno );
            if( 6 > $parno ) {
                unset( $input[Util::$LCvalue][Util::$LCtz],
                    $input[Util::$LCparams][Util::$TZID],
                    $tzid
                );
            }
            if(( 6 <= $parno ) &&
                         isset( $input[Util::$LCvalue][Util::$LCtz] ) &&
                  ( Util::$Z != $input[Util::$LCvalue][Util::$LCtz] ) &&
                Util::isOffset( $input[Util::$LCvalue][Util::$LCtz] )) {
                $input[Util::$LCvalue] = Util::ensureArrDatetime( $input, $input[Util::$LCvalue][Util::$LCtz], $parno );
                unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT],
                       $input[Util::$LCparams][Util::$TZID]
                );
            }
            if(            isset( $input[Util::$LCvalue][Util::$LCtz] ) &&
                ! Util::isOffset( $input[Util::$LCvalue][Util::$LCtz] )) {
                $input[Util::$LCparams][Util::$TZID] = $input[Util::$LCvalue][Util::$LCtz];
                unset( $input[Util::$LCvalue][Util::$LCtz] );
            }
        } // end if( Util::isArrayDate( $year ))
        elseif( Util::isArrayTimestampDate( $year )) {
            if( $localtime ) {
                unset( $month[Util::$LCvalue], $month[Util::$TZID] );
            }
            $input[Util::$LCparams] = Util::setParams( $month, Util::$DEFAULTVALUEDATETIME );
            $parno  = Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE,3 );
            $foundValue = 7;
            $parno  = Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE_TIME, $foundValue, $parno );
            if( isset( $year[Util::$LCtz] ) && ! empty( $year[Util::$LCtz] )) {
                if( ! Util::isOffset( $year[Util::$LCtz] )) {
                    $input[Util::$LCparams][Util::$TZID] = $year[Util::$LCtz];
                    unset( $year[Util::$LCtz], $tzid );
                }
                else {
                    if( isset( $input[Util::$LCparams][Util::$TZID] ) &&
                      ! empty( $input[Util::$LCparams][Util::$TZID] )) {
                        if( ! Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                            unset( $tzid );
                        }
                        else {
                            unset( $input[Util::$LCparams][Util::$TZID] );
                        }
                    }
                    elseif( isset( $tzid ) && ! Util::isOffset( $tzid )) {
                        $input[Util::$LCparams][Util::$TZID] = $tzid;
                    }
                }
            }
            elseif( isset( $input[Util::$LCparams][Util::$TZID] ) &&
                  ! empty( $input[Util::$LCparams][Util::$TZID] )) {
                if( Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                    $year[Util::$LCtz] = $input[Util::$LCparams][Util::$TZID];
                    unset( $input[Util::$LCparams][Util::$TZID] );
                    if( isset( $tzid ) && ! empty( $tzid ) && ! Util::isOffset( $tzid )) {
                        $input[Util::$LCparams][Util::$TZID] = $tzid;
                    }
                }
            }
            elseif( isset( $tzid ) && ! empty( $tzid )) {
                if( Util::isOffset( $tzid )) {
                    $year[Util::$LCtz] = $tzid;
                    unset( $input[Util::$LCparams][Util::$TZID] );
                }
                else {
                    $input[Util::$LCparams][Util::$TZID] = $tzid;
                }
            }
            $input[Util::$LCvalue] = Util::timestamp2date( $year, $parno );
        } // end elseif( Util::isArrayTimestampDate( $year ))
        elseif( 8 <= strlen( \trim((string) $year )
            )) { // string ex. "2006-08-03 10:12:18 [[[+/-]1234[56]] / timezone]"
            if( $localtime ) {
                unset( $month[Util::$LCvalue], $month[Util::$TZID] );
            }
            elseif( ! isset( $month[Util::$TZID] ) && ! empty( $tzid )) {
                $month[Util::$TZID] = $tzid;
            }
            $input[Util::$LCparams] = Util::setParams( $month, Util::$DEFAULTVALUEDATETIME );
            $parno = Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE_TIME, 7, $parno );
            $parno = Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE, 3, $parno, $parno );
            $input[Util::$LCvalue]  = Util::strDate2ArrayDate( $year, $parno );
            if( 3 == $parno ) {
                unset( $input[Util::$LCvalue][Util::$LCtz],
                       $input[Util::$LCparams][Util::$TZID]
                );
            }
            unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT] );
            if( isset( $input[Util::$LCvalue][Util::$LCtz] )) {
                if( Util::isOffset( $input[Util::$LCvalue][Util::$LCtz] )) {
                    $input[Util::$LCvalue] = Util::ensureArrDatetime($input, $input[Util::$LCvalue][Util::$LCtz], 7 );
                    unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT],
                           $input[Util::$LCparams][Util::$TZID]
                    );
                }
                else {
                    $input[Util::$LCparams][Util::$TZID] = $input[Util::$LCvalue][Util::$LCtz];
                    unset( $input[Util::$LCvalue][Util::$LCtz] );
                }
            }
            elseif( isset( $input[Util::$LCparams][Util::$TZID] ) &&
                Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                $input[Util::$LCvalue] = Util::ensureArrDatetime($input, $input[Util::$LCparams][Util::$TZID], 7 );
                unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT],
                       $input[Util::$LCparams][Util::$TZID]
                );
            }
        } // end elseif( 8 <= strlen( trim((string) $year )))
        else { // using all (?) args
            if( 100 > $year ) {
                $year += 2000;
            }
            if( is_array( $params )) {
                $input[Util::$LCparams] = Util::setParams( $params, Util::$DEFAULTVALUEDATETIME );
            }
            elseif( is_array( $tz )) {
                $input[Util::$LCparams] = Util::setParams( $tz, Util::$DEFAULTVALUEDATETIME );
                $tz                     = false;
            }
            elseif( is_array( $hour )) {
                $input[Util::$LCparams] = Util::setParams( $hour, Util::$DEFAULTVALUEDATETIME );
                $hour                   = $min = $sec = $tz = false;
            }
            if( $localtime ) {
                unset ( $input[Util::$LCparams][Util::$LCvalue],
                        $input[Util::$LCparams][Util::$TZID]
                );
            }
            elseif( ! isset( $tz ) && ! isset( $input[Util::$LCparams][Util::$TZID] ) && ! empty( $tzid )) {
                $input[Util::$LCparams][Util::$TZID] = $tzid;
            }
            elseif( isset( $tz ) && Util::isOffset( $tz )) {
                unset( $input[Util::$LCparams][Util::$TZID] );
            }
            elseif( isset( $input[Util::$LCparams][Util::$TZID] ) &&
                Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                $tz = $input[Util::$LCparams][Util::$TZID];
                unset( $input[Util::$LCparams][Util::$TZID] );
            }
            if( ! isset( $input[Util::$LCparams] )) {
                $input[Util::$LCparams] = [];
            }
            $parno  = Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE, 3 );
            $foundValue = ( Util::isOffset( $tz )) ? 7 : 6;
            $parno  = Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE_TIME, $foundValue, $parno, $parno );
            $input[Util::$LCvalue] = [
                Util::$LCYEAR  => $year,
                Util::$LCMONTH => $month,
                Util::$LCDAY   => $day,
            ];
            if( 3 != $parno ) {
                $input[Util::$LCvalue][Util::$LCHOUR] = ( $hour ) ? $hour : '0';
                $input[Util::$LCvalue][Util::$LCMIN]  = ( $min )  ? $min : '0';
                $input[Util::$LCvalue][Util::$LCSEC]  = ( $sec )  ? $sec : '0';
                if( ! empty( $tz )) {
                    $input[Util::$LCvalue][Util::$LCtz] = $tz;
                }
                $strdate = Util::date2strdate( $input[Util::$LCvalue], $parno );
                if( ! empty( $tz ) && ! Util::isOffset( $tz )) {
                    $strdate .= ( Util::$Z == $tz ) ? $tz : ' ' . $tz;
                }
                $input[Util::$LCvalue] = Util::strDate2ArrayDate( $strdate, $parno );
                unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT] );
                if( isset( $input[Util::$LCvalue][Util::$LCtz] )) {
                    if( Util::isOffset( $input[Util::$LCvalue][Util::$LCtz] )) {
                        $input[Util::$LCvalue] = Util::ensureArrDatetime($input, $input[Util::$LCvalue][Util::$LCtz], 7 );
                        unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT],
                               $input[Util::$LCparams][Util::$TZID]
                        );
                    }
                    else {
                        $input[Util::$LCparams][Util::$TZID] = $input[Util::$LCvalue][Util::$LCtz];
                        unset( $input[Util::$LCvalue][Util::$LCtz] );
                    }
                }
                elseif(      isset( $input[Util::$LCparams][Util::$TZID] ) &&
                    Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                    $input[Util::$LCvalue] = Util::ensureArrDatetime($input, $input[Util::$LCparams][Util::$TZID], 7 );
                    unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT],
                           $input[Util::$LCparams][Util::$TZID]
                    );
                }
            }
        } // end else (i.e. using all arguments)
        if(( 3 == $parno ) || Util::isParamsValueSet( $input, Util::$DATE )) {
            $input[Util::$LCparams][Util::$VALUE] = Util::$DATE;
            unset(
                $input[Util::$LCvalue][Util::$LCHOUR],
                $input[Util::$LCvalue][Util::$LCMIN],
                $input[Util::$LCvalue][Util::$LCSEC],
                $input[Util::$LCvalue][Util::$LCtz],
                $input[Util::$LCparams][Util::$TZID]
            );
        }
        elseif( isset( $input[Util::$LCparams][Util::$TZID] )) {
            if(( 0 == \strcasecmp( Util::$UTC, $input[Util::$LCparams][Util::$TZID] )) ||
                ( 0 == \strcasecmp( Util::$GMT, $input[Util::$LCparams][Util::$TZID] ))) {
                $input[Util::$LCvalue][Util::$LCtz] = Util::$Z;
                unset( $input[Util::$LCparams][Util::$TZID] );
            }
            else {
                unset( $input[Util::$LCvalue][Util::$LCtz] );
            }
        }
        elseif( isset( $input[Util::$LCvalue][Util::$LCtz] )) {
            if(( 0 == \strcasecmp( Util::$UTC, $input[Util::$LCvalue][Util::$LCtz] )) ||
                ( 0 == \strcasecmp( Util::$GMT, $input[Util::$LCvalue][Util::$LCtz] ))) {
                $input[Util::$LCvalue][Util::$LCtz] = Util::$Z;
            }
            if( Util::$Z != $input[Util::$LCvalue][Util::$LCtz] ) {
                $input[Util::$LCparams][Util::$TZID] = $input[Util::$LCvalue][Util::$LCtz];
                unset( $input[Util::$LCvalue][Util::$LCtz] );
            }
            else {
                unset( $input[Util::$LCparams][Util::$TZID] );
            }
        }
        if( $localtime ) {
            unset( $input[Util::$LCvalue][Util::$LCtz], $input[Util::$LCparams][Util::$TZID] );
        }
        return $input;
    }

    /**
     * Return input (UTC) date to internal date with parameters
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-17
     * @param mixed $year
     * @param mixed $month
     * @param int   $day
     * @param int   $hour
     * @param int   $min
     * @param int   $sec
     * @param array $params
     * @return array
     * @static
     */
    public static function setDate2(
        $year,
        $month  = null,
        $day    = null,
        $hour   = null,
        $min    = null,
        $sec    = null,
        $params = null
    ) {
        $input = [];
        Util::strDate2arr( $year );
        if( Util::isArrayDate( $year )) {
            $input[Util::$LCvalue] = Util::chkDateArr( $year, 7 );
            if( isset( $input[Util::$LCvalue][Util::$LCYEAR] ) &&
                ( 100 > $input[Util::$LCvalue][Util::$LCYEAR] )) {
                $input[Util::$LCvalue][Util::$LCYEAR] += 2000;
            }
            $input[Util::$LCparams] = Util::setParams( $month, Util::$DEFAULTVALUEDATETIME );
            if(          isset( $input[Util::$LCvalue][Util::$LCtz] ) &&
                Util::isOffset( $input[Util::$LCvalue][Util::$LCtz] )) {
                $tzid = $input[Util::$LCvalue][Util::$LCtz];
            }
            elseif( isset( $input[Util::$LCparams][Util::$TZID] ) &&
                Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                $tzid = $input[Util::$LCparams][Util::$TZID];
            }
            else {
                $tzid = null;
            }
            if( ! empty( $tzid ) && ( Util::$Z != $tzid ) && Util::isOffset( $tzid )) {
                $input[Util::$LCvalue] = Util::ensureArrDatetime($input, $tzid, 7 );
                unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT] );
            }
        } // end if( Util::isArrayDate( $year ))
        elseif( Util::isArrayTimestampDate( $year )) {
            if(            isset( $year[Util::$LCtz] ) &&
                ! Util::isOffset( $year[Util::$LCtz] )) {
                $year[Util::$LCtz] = Util::$UTC;
            }
            elseif(      isset( $input[Util::$LCparams][Util::$TZID] ) &&
                Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                $year[Util::$LCtz] = $input[Util::$LCparams][Util::$TZID];
            }
            else {
                $year[Util::$LCtz] = Util::$UTC;
            }
            $input[Util::$LCvalue]  = Util::timestamp2date( $year, 7 );
            $input[Util::$LCparams] = Util::setParams( $month, Util::$DEFAULTVALUEDATETIME );
        } // end elseif( Util::isArrayTimestampDate( $year ))
        elseif( 8 <= strlen( trim((string) $year ))) { // ex. 2006-08-03 10:12:18
            $input[Util::$LCvalue] = Util::strDate2ArrayDate( $year, 7 );
            unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT] );
            $input[Util::$LCparams] = Util::setParams( $month, Util::$DEFAULTVALUEDATETIME );
            if((     ! isset( $input[Util::$LCvalue][Util::$LCtz] ) ||
                       empty( $input[Util::$LCvalue][Util::$LCtz] )) &&
                       isset( $input[Util::$LCparams][Util::$TZID] ) &&
              Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                $input[Util::$LCvalue] = Util::ensureArrDatetime($input, $input[Util::$LCparams][Util::$TZID], 7 );
                unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT] );
            }
        } // end elseif( 8 <= strlen( trim((string) $year )))
        else {
            if( 100 > $year ) {
                $year += 2000;
            }
            $input[Util::$LCvalue] = [
                Util::$LCYEAR  => $year,
                Util::$LCMONTH => $month,
                Util::$LCDAY   => $day,
                Util::$LCHOUR  => $hour,
                Util::$LCMIN   => $min,
                Util::$LCSEC   => $sec,
            ];
            if( isset( $tz )) {
                $input[Util::$LCvalue][Util::$LCtz] = $tz;
            }
            if(( isset( $tz ) && Util::isOffset( $tz )) ||
                        ( isset( $input[Util::$LCparams][Util::$TZID] ) &&
                 Util::isOffset( $input[Util::$LCparams][Util::$TZID] ))) {
                if( ! isset( $tz ) &&
                    isset( $input[Util::$LCparams][Util::$TZID] ) &&
                    Util::isOffset( $input[Util::$LCparams][Util::$TZID] )) {
                    $input[Util::$LCvalue][Util::$LCtz] = $input[Util::$LCparams][Util::$TZID];
                }
                unset( $input[Util::$LCparams][Util::$TZID] );
                $strdate               = Util::date2strdate( $input[Util::$LCvalue], 7 );
                $input[Util::$LCvalue] = Util::strDate2ArrayDate( $strdate, 7 );
                unset( $input[Util::$LCvalue][Util::$UNPARSEDTEXT] );
            }
            $input[Util::$LCparams] = Util::setParams( $params, Util::$DEFAULTVALUEDATETIME );
        } // end else
        unset( $input[Util::$LCparams][Util::$VALUE], $input[Util::$LCparams][Util::$TZID] );
        if( ! isset( $input[Util::$LCvalue][Util::$LCHOUR] )) {
            $input[Util::$LCvalue][Util::$LCHOUR] = 0;
        }
        if( ! isset( $input[Util::$LCvalue][Util::$LCMIN] )) {
            $input[Util::$LCvalue][Util::$LCMIN] = 0;
        }
        if( ! isset( $input[Util::$LCvalue][Util::$LCSEC] )) {
            $input[Util::$LCvalue][Util::$LCSEC] = 0;
        }
        $input[Util::$LCvalue][Util::$LCtz] = Util::$Z;
        return $input;
    }

    /**
     * Return array (in internal format) for an input date-time/date array (keyed or unkeyed)
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-19
     * @param array $datetime
     * @param int   $parno default null, 3: DATE(Ymd), 6: YmdHis, 7: YmdHis + offset/timezone
     * @return array
     * @static
     */
    public static function chkDateArr( $datetime, $parno = null ) {
        static $PLUS4ZERO = '+0000';
        static $MINUS4ZERO = '-0000';
        static $PLUS6ZERO = '+000000';
        static $MINUS6ZERO = '-000000';
        $output = [];
        if(( \is_null( $parno ) || ( 6 <= $parno )) &&
            isset( $datetime[3] ) &&
            ! isset( $datetime[4] )) { // Y-m-d with tz
            $temp        = $datetime[3];
            $datetime[3] = $datetime[4] = $datetime[5] = 0;
            $datetime[6] = $temp;
        }
        foreach( $datetime as $dateKey => $datePart ) {
            switch( $dateKey ) {
                case '0':
                case Util::$LCYEAR :
                    $output[Util::$LCYEAR] = $datePart;
                    break;
                case '1':
                case Util::$LCMONTH :
                    $output[Util::$LCMONTH] = $datePart;
                    break;
                case '2':
                case Util::$LCDAY :
                    $output[Util::$LCDAY] = $datePart;
                    break;
            }
            if( 3 != $parno ) {
                switch( $dateKey ) {
                    case '0':
                    case '1':
                    case '2':
                        break;
                    case '3':
                    case Util::$LCHOUR:
                        $output[Util::$LCHOUR] = $datePart;
                        break;
                    case '4':
                    case Util::$LCMIN :
                        $output[Util::$LCMIN] = $datePart;
                        break;
                    case '5':
                    case Util::$LCSEC :
                        $output[Util::$LCSEC] = $datePart;
                        break;
                    case '6':
                    case Util::$LCtz  :
                        $output[Util::$LCtz] = $datePart;
                        break;
                }
            }
        }
        if( 3 != $parno ) {
            if( ! isset( $output[Util::$LCHOUR] )) {
                $output[Util::$LCHOUR] = 0;
            }
            if( ! isset( $output[Util::$LCMIN] )) {
                $output[Util::$LCMIN] = 0;
            }
            if( ! isset( $output[Util::$LCSEC] )) {
                $output[Util::$LCSEC] = 0;
            }
            if( isset( $output[Util::$LCtz] ) &&
                (( $PLUS4ZERO  == $output[Util::$LCtz] ) ||
                 ( $MINUS4ZERO == $output[Util::$LCtz] ) ||
                 ( $PLUS6ZERO  == $output[Util::$LCtz] ) ||
                 ( $MINUS6ZERO == $output[Util::$LCtz] ))) {
                $output[Util::$LCtz] = Util::$Z;
            }
        }
        return $output;
    }

    /**
     * Return iCal formatted string for (internal array) date/date-time
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-24
     * @param array $datetime
     * @param int   $parno default 6
     * @return string
     * @static
     */
    public static function date2strdate( $datetime, $parno = null ) {
        static $SECONDS = ' seconds';
        static $YMDYHIS = 'Ymd\THis';
        if( ! isset( $datetime[Util::$LCYEAR] ) &&
            ! isset( $datetime[Util::$LCMONTH] ) &&
            ! isset( $datetime[Util::$LCDAY] ) &&
            ! isset( $datetime[Util::$LCHOUR] ) &&
            ! isset( $datetime[Util::$LCMIN] ) &&
            ! isset( $datetime[Util::$LCSEC] )) {
            return null;
        }
        if( \is_null( $parno )) {
            $parno = 6;
        }
        $output = null;
        foreach( $datetime as $dkey => & $dvalue ) {
            if( Util::$LCtz != $dkey ) {
                $dvalue = (int) $dvalue;
            }
        }
        $output = Util::getYMDString( $datetime );
        if( 3 == $parno ) {
            return $output;
        }
        if( ! isset( $datetime[Util::$LCHOUR] )) {
            $datetime[Util::$LCHOUR] = 0;
        }
        if( ! isset( $datetime[Util::$LCMIN] )) {
            $datetime[Util::$LCMIN] = 0;
        }
        if( ! isset( $datetime[Util::$LCSEC] )) {
            $datetime[Util::$LCSEC] = 0;
        }
        $output .= Util::$T . Util::getHisString( $datetime );
        if( isset( $datetime[Util::$LCtz] )) {
            $datetime[Util::$LCtz] = \trim( $datetime[Util::$LCtz] );
            if( ! empty( $datetime[Util::$LCtz] )) {
                if( Util::$Z == $datetime[Util::$LCtz] ) {
                    $parno = 7;
                }
                elseif( Util::isOffset( $datetime[Util::$LCtz] )) {
                    $parno  = 7;
                    $offset = Util::tz2offset( $datetime[Util::$LCtz] );
                    try {
                        $timezone = new DateTimeZone( Util::$UTC );
                        $d        = new DateTime( $output, $timezone );
                        if( 0 != $offset ) { // adjust för offset
                            $d->modify( $offset . $SECONDS );
                        }
                        $output = $d->format( $YMDYHIS );
                    }
                    catch( Exception $e ) {
                        $output = \date(
                            $YMDYHIS,
                            \mktime( $datetime[Util::$LCHOUR],
                                     $datetime[Util::$LCMIN],
                                   ( $datetime[Util::$LCSEC] - $offset ),
                                     $datetime[Util::$LCMONTH],
                                     $datetime[Util::$LCDAY],
                                     $datetime[Util::$LCYEAR]
                            )
                        );
                    }
                }
                if( 7 == $parno ) {
                    $output .= Util::$Z;
                }
            } // end if( ! empty( $datetime[Util::$LCtz] ))
        }
        return $output;
    }

    /**
     * Return array (in internal format) for a (array) duration
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.19.4 - 2014-03-14
     * @param array $duration
     * @return array
     * @static
     */
    public static function duration2arr( $duration ) {
        $seconds = 0;
        foreach( $duration as $durKey => $durValue ) {
            if( empty( $durValue )) {
                continue;
            }
            switch( $durKey ) {
                case '0':
                case Util::$LCWEEK:
                    $seconds += (((int) $durValue ) * 60 * 60 * 24 * 7 );
                    break;
                case '1':
                case Util::$LCDAY:
                    $seconds += (((int) $durValue ) * 60 * 60 * 24 );
                    break;
                case '2':
                case Util::$LCHOUR:
                    $seconds += (((int) $durValue ) * 60 * 60 );
                    break;
                case '3':
                case Util::$LCMIN:
                    $seconds += (((int) $durValue ) * 60 );
                    break;
                case '4':
                case Util::$LCSEC:
                    $seconds += (int) $durValue;
                    break;
            }
        }
        $output                = [];
        $output[Util::$LCWEEK] = (int) \floor( $seconds / ( 60 * 60 * 24 * 7 ));
        if(( 0 < $output[Util::$LCWEEK] ) &&
            ( 0 == ( $seconds % ( 60 * 60 * 24 * 7 )))) {
            return $output;
        }
        unset( $output[Util::$LCWEEK] );
        $output[Util::$LCDAY]  = (int) \floor( $seconds / ( 60 * 60 * 24 ));
        $seconds               = ( $seconds % ( 60 * 60 * 24 ));
        $output[Util::$LCHOUR] = (int) \floor( $seconds / ( 60 * 60 ));
        $seconds               = ( $seconds % ( 60 * 60 ));
        $output[Util::$LCMIN]  = (int) \floor( $seconds / 60 );
        $output[Util::$LCSEC]  = ( $seconds % 60 );
        if( empty( $output[Util::$LCDAY] )) {
            unset( $output[Util::$LCDAY] );
        }
        if(( 0 == $output[Util::$LCHOUR] ) &&
            ( 0 == $output[Util::$LCMIN] ) &&
            ( 0 == $output[Util::$LCSEC] )) {
            unset( $output[Util::$LCHOUR], $output[Util::$LCMIN], $output[Util::$LCSEC] );
        }
        return $output;
    }

    /**
     * Return datetime array (in internal format) for startdate + duration
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.21.11 - 2015-03-21
     * @param array $startdate
     * @param array $duration
     * @return array, date format
     * @static
     */
    public static function duration2date( $startdate, $duration ) {
        $dateOnly                 = ( isset( $startdate[Util::$LCHOUR] ) ||
            isset( $startdate[Util::$LCMIN] ) ||
            isset( $startdate[Util::$LCSEC] )) ? false : true;
        $startdate[Util::$LCHOUR] = ( isset( $startdate[Util::$LCHOUR] ))
            ? $startdate[Util::$LCHOUR] : 0;
        $startdate[Util::$LCMIN]  = ( isset( $startdate[Util::$LCMIN] ))
            ? $startdate[Util::$LCMIN] : 0;
        $startdate[Util::$LCSEC]  = ( isset( $startdate[Util::$LCSEC] ))
            ? $startdate[Util::$LCSEC] : 0;
        $dtend                    = 0;
        if( isset( $duration[Util::$LCWEEK] )) {
            $dtend += ( $duration[Util::$LCWEEK] * 7 * 24 * 60 * 60 );
        }
        if( isset( $duration[Util::$LCDAY] )) {
            $dtend += ( $duration[Util::$LCDAY] * 24 * 60 * 60 );
        }
        if( isset( $duration[Util::$LCHOUR] )) {
            $dtend += ( $duration[Util::$LCHOUR] * 60 * 60 );
        }
        if( isset( $duration[Util::$LCMIN] )) {
            $dtend += ( $duration[Util::$LCMIN] * 60 );
        }
        if( isset( $duration[Util::$LCSEC] )) {
            $dtend += $duration[Util::$LCSEC];
        }
        $date   = \date( Util::$YMDHIS3,
                         \mktime(
                             (int) $startdate[Util::$LCHOUR],
                             (int) $startdate[Util::$LCMIN],
                             (int) ( $startdate[Util::$LCSEC] + $dtend ),
                             (int) $startdate[Util::$LCMONTH],
                             (int) $startdate[Util::$LCDAY],
                             (int) $startdate[Util::$LCYEAR]
                         )
        );
        $d      = \explode( Util::$MINUS, $date );
        $dtend2 = [
            Util::$LCYEAR  => $d[0],
            Util::$LCMONTH => $d[1],
            Util::$LCDAY   => $d[2],
            Util::$LCHOUR  => $d[3],
            Util::$LCMIN   => $d[4],
            Util::$LCSEC   => $d[5],
        ];
        if( isset( $startdate[Util::$LCtz] )) {
            $dtend2[Util::$LCtz] = $startdate[Util::$LCtz];
        }
        if( $dateOnly &&
            (( 0 == $dtend2[Util::$LCHOUR] ) &&
             ( 0 == $dtend2[Util::$LCMIN] ) &&
             ( 0 == $dtend2[Util::$LCSEC] ))) {
            unset( $dtend2[Util::$LCHOUR], $dtend2[Util::$LCMIN], $dtend2[Util::$LCSEC] );
        }
        return $dtend2;
    }

    /**
     * Return an iCal formatted string from (internal array) duration
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.15.8 - 2012-10-30
     * @param array $duration , array( week, day, hour, min, sec )
     * @return string
     * @static
     */
    public static function duration2str( array $duration ) {
        static $P  = 'P';
        static $W  = 'W';
        static $D  = 'D';
        static $H  = 'H';
        static $OH = '0H';
        static $M  = 'M';
        static $OM = '0M';
        static $S  = 'S';
        static $OS = '0S';
        static $PT0H0M0S = 'PT0H0M0S';
        if( ! isset( $duration[Util::$LCWEEK] ) &&
            ! isset( $duration[Util::$LCDAY] )  &&
            ! isset( $duration[Util::$LCHOUR] ) &&
            ! isset( $duration[Util::$LCMIN] )  &&
            ! isset( $duration[Util::$LCSEC] )) {
            return null;
        }
        if( isset( $duration[Util::$LCWEEK] ) &&
             ( 0 < $duration[Util::$LCWEEK] )) {
            return $P . $duration[Util::$LCWEEK] . $W;
        }
        $output = $P;
        if( isset( $duration[Util::$LCDAY] ) &&
             ( 0 < $duration[Util::$LCDAY] )) {
            $output .= $duration[Util::$LCDAY] . $D;
        }
        if(( isset( $duration[Util::$LCHOUR] ) &&
               ( 0 < $duration[Util::$LCHOUR] )) ||
            ( isset( $duration[Util::$LCMIN] ) &&
               ( 0 < $duration[Util::$LCMIN] )) ||
            ( isset( $duration[Util::$LCSEC] ) &&
               ( 0 < $duration[Util::$LCSEC] ))) {
            $output .= Util::$T;
            $output .= ( isset( $duration[Util::$LCHOUR] ) && ( 0 < $duration[Util::$LCHOUR] ))
                ? $duration[Util::$LCHOUR] . $H : $OH;
            $output .= ( isset( $duration[Util::$LCMIN] ) && ( 0 < $duration[Util::$LCMIN] ))
                ? $duration[Util::$LCMIN] . $M : $OM;
            $output .= ( isset( $duration[Util::$LCSEC] ) && ( 0 < $duration[Util::$LCSEC] ))
                ? $duration[Util::$LCSEC] . $S : $OS;
        }
        if( $P == $output ) {
            $output = $PT0H0M0S;
        }
        return $output;
    }

    /**
     * Return array (in internal format) from string duration
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-17
     * @param string $duration
     * @return array|bool  false on error
     * @static
     */
    public static function durationStr2arr( $duration ) {
        static $P = 'P';
        static $Tt = [ 't', 'T' ];
        static $W = 'W';
        static $D = 'D';
        static $H = 'H';
        static $M = 'M';
        static $S = 'S';
        $duration = (string) \trim( $duration );
        while( 0 != \strcasecmp( $P, $duration[0] )) {
            if( 0 < \strlen( $duration )) {
                $duration = \substr( $duration, 1 );
            }
            else {
                return false;
            } // no leading P !?!?
        }
        $duration = \substr( $duration, 1 ); // skip P
        $duration = \str_replace( $Tt, null, $duration );
        $output   = [];
        $val      = null;
        $durLen   = \strlen( $duration );
        for( $ix = 0; $ix < $durLen; $ix++ ) {
            switch( \strtoupper( $duration[$ix] )) {
                case $W :
                    $output[Util::$LCWEEK] = $val;
                    $val                   = null;
                    break;
                case $D :
                    $output[Util::$LCDAY] = $val;
                    $val                  = null;
                    break;
                case $H :
                    $output[Util::$LCHOUR] = $val;
                    $val                   = null;
                    break;
                case $M :
                    $output[Util::$LCMIN] = $val;
                    $val                  = null;
                    break;
                case $S :
                    $output[Util::$LCSEC] = $val;
                    $val                  = null;
                    break;
                default:
                    if( ! \ctype_digit( $duration[$ix] )) {
                        return false;
                    } // unknown duration control character  !?!?
                    else {
                        $val .= $duration[$ix];
                    }
            }
        }
        return Util::duration2arr( $output );
    }

    /**
     * Return bool true if input contains a date/time (in array format)
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.16.24 - 2013-07-02
     * @param array $input
     * @return bool
     * @static
     */
    public static function isArrayDate( $input ) {
        if( ! \is_array( $input ) ||
            isset( $input[Util::$LCWEEK] ) ||
            isset( $input[Util::$LCTIMESTAMP] ) ||
            ( 3 > count( $input ))) {
            return false;
        }
        if( 7 == \count( $input )) {
            return true;
        }
        if( isset( $input[Util::$LCYEAR] ) &&
            isset( $input[Util::$LCMONTH] ) &&
            isset( $input[Util::$LCDAY] )) {
            return \checkdate((int) $input[Util::$LCMONTH], (int) $input[Util::$LCDAY], (int) $input[Util::$LCYEAR] );
        }
        if( isset( $input[Util::$LCDAY] ) ||
            isset( $input[Util::$LCHOUR] ) ||
            isset( $input[Util::$LCMIN] ) ||
            isset( $input[Util::$LCSEC] )) {
            return false;
        }
        if(( 0 == $input[0] ) || ( 0 == $input[1] ) || ( 0 == $input[2] )) {
            return false;
        }
        if(( 1970 > $input[0] ) || ( 12 < $input[1] ) || ( 31 < $input[2] )) {
            return false;
        }
        if(( isset( $input[0] ) && isset( $input[1] ) && isset( $input[2] )) &&
            checkdate((int) $input[1],
                       (int) $input[2],
                       (int) $input[0]
            )) {
            return true;
        }
        $input = Util::strDate2ArrayDate( $input[1] . Util::$L . $input[2] . Util::$L . $input[0], 3 ); //  m - d - Y
        if( isset( $input[Util::$LCYEAR] ) && isset( $input[Util::$LCMONTH] ) && isset( $input[Util::$LCDAY] )) {
            return checkdate((int) $input[Util::$LCMONTH], (int) $input[Util::$LCDAY], (int) $input[Util::$LCYEAR] );
        }
        return false;
    }

    /**
     * Return bool true if input array contains a (keyed) timestamp date
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.4.16 - 2008-10-18
     * @param array $input
     * @return bool
     * @static
     */
    public static function isArrayTimestampDate( $input ) {
        return ( \is_array( $input ) && isset( $input[Util::$LCTIMESTAMP] ));
    }

    /**
     * Return bool true if input string contains (trailing) UTC/iCal offset
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.14.1 - 2012-09-21
     * @param string $input
     * @return bool
     * @static
     */
    public static function isOffset( $input ) {
        static $PLUSMINUSARR = [ '+', '-' ];
        static $ZERO4 = '0000';
        static $NINE4 = '9999';
        static $ZERO6 = '000000';
        static $NINE6 = '999999';
        $input = \trim((string) $input );
        if( Util::$Z == \substr( $input, -1 )) {
            return true;
        }
        elseif(( 5 <= \strlen( $input )) &&
            ( \in_array( \substr( $input, -5, 1 ), $PLUSMINUSARR )) &&
            ( $ZERO4 <= \substr( $input, -4 )) && ( $NINE4 >= \substr( $input, -4 ))) {
            return true;
        }
        elseif(( 7 <= \strlen( $input )) &&
            ( \in_array( \substr( $input, -7, 1 ), $PLUSMINUSARR )) &&
            ( $ZERO6 <= \substr( $input, -6 )) && ( $NINE6 >= \substr( $input, -6 ))) {
            return true;
        }
        return false;
    }

    /**
     * Convert a date from string to (internal, keyed) array format, return true on success
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.11.8 - 2012-01-27
     * @param mixed $date
     * @return bool, true on success
     * @static
     */
    public static function strDate2arr( & $date ) {
        static $ET = [ ' ', 't', 'T' ];
        if( \is_array( $date )) {
            return false;
        }
        if( 5 > \strlen((string) $date )) {
            return false;
        }
        $work = $date;
        if( 2 == \substr_count( $work, Util::$MINUS )) {
            $work = \str_replace( Util::$MINUS, null, $work );
        }
        if( 2 == \substr_count( $work, Util::$L )) {
            $work = \str_replace( Util::$L, null, $work );
        }
        if( ! \ctype_digit( \substr( $work, 0, 8 ))) {
            return false;
        }
        $temp = [
            Util::$LCYEAR  => (int) \substr( $work, 0, 4 ),
            Util::$LCMONTH => (int) \substr( $work, 4, 2 ),
            Util::$LCDAY   => (int) \substr( $work, 6, 2 ),
        ];
        if( ! \checkdate( $temp[Util::$LCMONTH], $temp[Util::$LCDAY], $temp[Util::$LCYEAR] )) {
            return false;
        }
        if( 8 == \strlen( $work )) {
            $date = $temp;
            return true;
        }
        if( \in_array( $work[8], $ET )) {
            $work = \substr( $work, 9 );
        }
        elseif( \ctype_digit( $work[8] )) {
            $work = \substr( $work, 8 );
        }
        else {
            return false;
        }
        if( 2 == \substr_count( $work, Util::$COLON )) {
            $work = \str_replace( Util::$COLON, null, $work );
        }
        if( ! \ctype_digit( \substr( $work, 0, 4 ))) {
            return false;
        }
        $temp[Util::$LCHOUR] = \substr( $work, 0, 2 );
        $temp[Util::$LCMIN]  = \substr( $work, 2, 2 );
        if((( 0 > $temp[Util::$LCHOUR] ) || ( $temp[Util::$LCHOUR] > 23 )) ||
           (( 0 > $temp[Util::$LCMIN] )  || ( $temp[Util::$LCMIN] > 59 ))) {
            return false;
        }
        if( \ctype_digit( \substr( $work, 4, 2 ))) {
            $temp[Util::$LCSEC] = \substr( $work, 4, 2 );
            if(( 0 > $temp[Util::$LCSEC] ) || ( $temp[Util::$LCSEC] > 59 )) {
                return false;
            }
            $len = 6;
        }
        else {
            $temp[Util::$LCSEC] = 0;
            $len                = 4;
        }
        if( $len < \strlen( $work )) {
            $temp[Util::$LCtz] = \trim( \substr( $work, 6 ));
        }
        $date = $temp;
        return true;
    }

    /**
     * Return string date-time/date as array (in internal format, keyed)
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-19
     * Modified to also return original string value by Yitzchok Lavi <icalcreator@onebigsystem.com>
     * @param string $datetime
     * @param int    $parno default false
     * @param mixed  $wtz   default null
     * @return array
     * @static
     */
    public static function strDate2ArrayDate(
        $datetime,
        $parno = null,
        $wtz   = null
    ) {
        static $SECONDS = ' seconds';
        $unparseddatetime = $datetime;
        $datetime         = (string) \trim( $datetime );
        $tz               = null;
        $offset           = 0;
        $tzSts            = false;
        $len              = \strlen( $datetime );
        if( Util::$Z == \substr( $datetime, -1 )) {
            $tz       = Util::$Z;
            $datetime = \trim( \substr( $datetime, 0, ( $len - 1 )));
            $tzSts    = true;
        }
        if( Util::isOffset( \substr( $datetime, -5, 5 ))) { // [+/-]NNNN offset
            $tz       = \substr( $datetime, -5, 5 );
            $datetime = \trim( substr( $datetime, 0, ( $len - 5 )));
        }
        elseif( Util::isOffset( substr( $datetime, -7, 7 ))) { // [+/-]NNNNNN offset
            $tz       = \substr( $datetime, -7, 7 );
            $datetime = \trim( substr( $datetime, 0, ( $len - 7 )));
        }
        elseif( empty( $wtz ) &&
            \ctype_digit( substr( $datetime, 0, 4 )) &&
            \ctype_digit( substr( $datetime, -2, 2 )) &&
            Util::strDate2arr( $datetime )) { // array
            $output = (array) $datetime;
            if( ! empty( $tz )) {
                $output[Util::$LCtz] = Util::$Z;
            }
            $output[Util::$UNPARSEDTEXT] = $unparseddatetime;
            return $output;
        }
        else {
            $tx  = 0;  //  find any TRAILING timezone or offset
            $len = \strlen( $datetime );
            for( $cx = -1; $cx > ( 9 - $len ); $cx-- ) {
                $char = \substr( $datetime, $cx, 1 );
                if(( Util::$SP1 == $char ) || \ctype_digit( $char )) {
                    break;
                }       // if exists, tz ends here.. . ?
                else {
                    $tx--;
                }      // tz length counter
            }
            if( 0 > $tx ) {  // if any timezone or offset found
                $tz       = \substr( $datetime, $tx );
                $datetime = \trim( substr( $datetime, 0, $len + $tx ));
            }
            if(( \ctype_digit( \substr( $datetime, 0, 8 )) &&
                    ( Util::$T == $datetime[8] ) &&
                    ctype_digit( \substr( $datetime, -6, 6 ))) ||
                ( ctype_digit( \substr( $datetime, 0, 14 )))) {
                $tzSts = true;
            }
        }
        if( empty( $tz ) && ! empty( $wtz )) {
            $tz = $wtz;
        }
        if( 3 == $parno ) {
            $tz = null;
        }
        if( ! empty( $tz )) { // tz set
            if(( Util::$Z != $tz ) && ( Util::isOffset( $tz ))) {
                $offset = (string) Util::tz2offset( $tz ) * -1;
                $tz     = Util::$UTC;
                $tzSts  = true;
            }
            elseif( ! empty( $wtz )) {
                $tzSts = true;
            }
            $tz = \trim( $tz );
            if(( 0 == \strcasecmp( Util::$Z, $tz )) ||
                ( 0 == \strcasecmp( Util::$GMT, $tz ))) {
                $tz = Util::$UTC;
            }
            if( 0 < \substr_count( $datetime, Util::$MINUS )) {
                $datetime = \str_replace( Util::$MINUS, Util::$L, $datetime );
            }
            try {
                $timezone = new DateTimeZone( $tz );
                $d        = new DateTime( $datetime, $timezone );
                if( 0 != $offset )  // adjust for offset
                {
                    $d->modify( $offset . $SECONDS );
                }
                $datestring = $d->format( Util::$YMDHIS3 );
                unset( $d );
            }
            catch( Exception $e ) {
                $datestring = \date( Util::$YMDHIS3, \strtotime( $datetime ));
            }
        } // end if( ! empty( $tz ))
        else {
            $datestring = \date( Util::$YMDHIS3, \strtotime( $datetime ));
        }
        if( Util::$UTC == $tz ) {
            $tz = Util::$Z;
        }
        $d      = \explode( Util::$MINUS, $datestring );
        $output = [
            Util::$LCYEAR  => $d[0],
            Util::$LCMONTH => $d[1],
            Util::$LCDAY   => $d[2],
        ];
        if( ! empty( $parno ) || ( 3 != $parno )) { // parno is set to 6 or 7
            $output[Util::$LCHOUR] = $d[3];
            $output[Util::$LCMIN]  = $d[4];
            $output[Util::$LCSEC]  = $d[5];
            if(( $tzSts || ( 7 == $parno )) && ! empty( $tz )) {
                $output[Util::$LCtz] = $tz;
            }
        }
        // return original string in the array in case strtotime failed to make sense of it
        $output[Util::$UNPARSEDTEXT] = $unparseddatetime;
        return $output;
    }

    /**
     * Return string/array timestamp(+ offset/timezone (default UTC)) as array (in internal format, keyed).
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.21.11 - 2015-03-07
     * @param mixed  $timestamp
     * @param int    $parno
     * @param string $wtz
     * @return array
     * @static
     */
    public static function timestamp2date( $timestamp, $parno = 6, $wtz = null ) {
        static $FMTTIMESTAMP = '@%s';
        static $SPSEC        = ' seconds';
        if( \is_array( $timestamp )) {
            $tz        = ( isset( $timestamp[Util::$LCtz] )) ? $timestamp[Util::$LCtz] : $wtz;
            $timestamp = $timestamp[Util::$LCTIMESTAMP];
        }
        $tz     = ( isset( $tz )) ? $tz : $wtz;
        $offset = 0;
        if( empty( $tz ) ||
            ( Util::$Z == $tz ) ||
            ( 0 == \strcasecmp( Util::$GMT, $tz ))) {
            $tz = Util::$UTC;
        }
        elseif( Util::isOffset( $tz )) {
            $offset = Util::tz2offset( $tz );
        }
        try {
            $timestamp = \sprintf( $FMTTIMESTAMP, $timestamp );
            $d         = new DateTime( $timestamp );     // set UTC date
            if( 0 != $offset )                           // adjust for offset
            {
                $d->modify( $offset . $SPSEC );
            }
            elseif( Util::$UTC != $tz ) {
                $d->setTimezone( new DateTimeZone( $tz ));
            } // convert to local date
            $date = $d->format( Util::$YMDHIS3 );
        }
        catch( \Exception $e ) {
            $date = \date( Util::$YMDHIS3, $timestamp );
        }
        $date   = \explode( Util::$MINUS, $date );
        $output = [
            Util::$LCYEAR  => $date[0],
            Util::$LCMONTH => $date[1],
            Util::$LCDAY   => $date[2],
        ];
        if( 3 != $parno ) {
            $output[Util::$LCHOUR] = $date[3];
            $output[Util::$LCMIN]  = $date[4];
            $output[Util::$LCSEC]  = $date[5];
            if(( Util::$UTC == $tz ) || ( 0 == $offset )) {
                $output[Util::$LCtz] = Util::$Z;
            }
        }
        return $output;
    }

    /**
     * Return seconds based on an offset, [+/-]HHmm[ss], used when correcting UTC to localtime or v.v.
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-17
     * @param string $tz
     * @return integer
     * @static
     */
    public static function tz2offset( $tz ) {
        static $ZERO4 = '0000';
        static $NINE4 = '9999';
        static $ZERO2 = '00';
        static $NINE2 = '99';
        $tz     = \trim((string) $tz );
        $offset = 0;
        if((( 5 != \strlen( $tz )) &&
            ( 7 != \strlen( $tz ))) ||
           (( Util::$PLUS != $tz[0] &&
            ( Util::$MINUS != $tz[0] ))) ||
            (( $ZERO4 >= \substr( $tz, 1, 4 )) &&
             ( $NINE4 < \substr( $tz, 1, 4 ))) ||
            (( 7 == \strlen( $tz )) &&
                ( $ZERO2 > \substr( $tz, 5, 2 )) &&
                ( $NINE2 < \substr( $tz, 5, 2 )))) {
            return $offset;
        }
        $hours2sec = (int) \substr( $tz, 1, 2 ) * 3600;
        $min2sec   = (int) \substr( $tz, 3, 2 ) * 60;
        $sec       = ( 7 == \strlen( $tz ))
            ? (int) \substr( $tz, -2 ) : $ZERO2;
        $offset    = $hours2sec + $min2sec + $sec;
        $offset    = ( Util::$MINUS == $tz[0] )
            ? $offset * -1 : $offset;
        return $offset;
    }
}
