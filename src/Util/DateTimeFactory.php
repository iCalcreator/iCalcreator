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
namespace Kigkonsult\Icalcreator\Util;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Vcalendar;

use function ctype_digit;
use function date_default_timezone_get;
use function in_array;
use function is_string;
use function sprintf;
use function strcasecmp;
use function strlen;
use function strrpos;
use function strtotime;
use function substr;
use function trim;
use function var_export;

/**
 * iCalcreator DateTime support class
 *
 * @see https://en.wikipedia.org/wiki/Iso8601
 * @since  2.29.21 - 2020-01-31
 */
class DateTimeFactory
{

    /**
     * @var array
     */
    public static $DEFAULTVALUEDATETIME = [ Vcalendar::VALUE => Vcalendar::DATE_TIME ];

    /**
     * @var string
     */
    public static $Ymd          = 'Ymd';
    public static $YmdTHis      = 'Ymd\THis';
    public static $YmdHis       = 'YmdHis';
    public static $YMDHISe      = 'Y-m-d H:i:s e';
    public static $NOW          = 'now';

    /**
     * @var string
     */
    private static $ERR1        = 'Invalid date : %s';
    private static $ERR3        = 'Can\'t update date with timezone : %s';
    private static $ERR4        = 'Invalid date \'%s\' - \'%s\'';

    /**
     * Return new DateTime object instance
     *
     * @param null|string $dateTimeString  default 'now'
     * @param null|string $timeZoneString
     * @return DateTime
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.29.21 - 2020-01-31
     */
    public static function factory( $dateTimeString = null, $timeZoneString = null ) : DateTime
    {
        static $AT      = '@';
        $dateTimeString = ( null === $dateTimeString ) ? 'now' : (string) $dateTimeString;
        if(( $AT == substr( $dateTimeString, 0, 1 )) &&
            ctype_digit( substr( $dateTimeString, 1 ))) {
            try {
                $dateTime = new DateTime( $dateTimeString );
                $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
                if( ! empty( $timeZoneString ) &&
                    ! DateTimeZoneFactory::isUTCtimeZone( $timeZoneString ) &&
                    ( false === $dateTime->setTimezone(
                            DateTimeZoneFactory::factory( $timeZoneString )
                        )
                    )) {
                    throw new InvalidArgumentException(
                        sprintf( self::$ERR3, $timeZoneString )
                    );
                }
                return $dateTime;
            }
            catch( InvalidArgumentException $e ) {
                throw $e;
            }
            catch( Exception $e ) {
                throw $e;
            }
        } // end if
        return self::assertDateTimeString( $dateTimeString, $timeZoneString );
    }

    /**
     * Assert DateTime String
     *
     * @param string $dateTimeString
     * @param string $timeZoneString
     * @return DateTime
     * @throws InvalidArgumentException
     * @since  2.27.8 - 2019-01-12
     */
    public static function assertDateTimeString(
        string $dateTimeString,
        $timeZoneString = null
    ) : DateTime
    {
        try {
            $dateTime = empty( $timeZoneString )
                ? new DateTime( $dateTimeString )
                : new DateTime(
                    $dateTimeString,
                    DateTimeZoneFactory::factory( $timeZoneString )
                );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException(
                sprintf( self::$ERR1, $dateTimeString ),
                $e->getCode(),
                $e );
        }
        return $dateTime;
    }

    /**
     * Return DateTime if DateTimeInterface else string
     *
     * @param DateTimeInterface $dateTime
     * @return DateTime
     * @throws Exception
     * @since 2.39 2021-06-15
     */
    public static function toDateTime( DateTimeInterface $dateTime ) : DateTime
    {
        if( $dateTime instanceof DateTime ) {
            return $dateTime;
        }
        try {
            $dtTmp = new DateTime( self::$NOW, $dateTime->getTimezone());
            $dtTmp->setTimestamp( $dateTime->getTimestamp() );
        }
        catch( Exception $e ) {
            throw $e;
        }
        return $dtTmp;
    }

    /**
     * Return internal date (format) with parameters based on input date
     *
     * @param string|DateTimeInterface  $value
     * @param array  $params
     * @param bool   $forceUTC
     * @return array
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public static function setDate( $value, $params = [], $forceUTC = false ) : array
    {
        $output      = [ Util::$LCparams => $params ];
        $isValueDate = ParameterFactory::isParamsValueSet( $output, Vcalendar::DATE );
        $paramTZid   = ParameterFactory::getParamTzid( $output );
        $isLocalTime = isset( $params[Util::$ISLOCALTIME] );
        if( ! empty( $paramTZid )) {
            if( DateTimeZoneFactory::hasOffset( $paramTZid )) {
                $paramTZid =
                    DateTimeZoneFactory::getTimeZoneNameFromOffset( $paramTZid );
            }
            else {
                DateTimeZoneFactory::assertDateTimeZone( $paramTZid );
            }
        } // end if
        switch( true ) {
            case ( $value instanceof DateTimeInterface ) :
                $dateTime = self::conformDateTime(
                    self::toDateTime( $value ),
                    $isValueDate,
                    $forceUTC,
                    $paramTZid
                );
                break;
            case ( self::isStringAndDate( $value )) :
                // string ex. "2006-08-03 10:12:18 [[[+/-]1234[56]] / timezone]"
                $dateTime = self::conformStringDate(
                    $value,
                    $isValueDate,
                    $forceUTC,
                    $isLocalTime,
                    $paramTZid
                );
                if( $isLocalTime && $forceUTC ) {
                    $isLocalTime = false;
                }
                break;
            default :
                throw new InvalidArgumentException(
                    sprintf(
                        self::$ERR4,
                        var_export( $value, true ),
                        var_export( $params, true )
                    )
                );
        } // end switch
        $output[Util::$LCvalue] = $dateTime;
        self::conformDateTimeParams(
            $output[Util::$LCparams],
            $isValueDate,
            $isLocalTime,
            ( $forceUTC ? Vcalendar::UTC : $paramTZid )
        );
        return $output;
    }

    /**
     * Return conformed DateTime
     *
     * @param DateTime $input
     * @param bool     $isValueDate
     * @param bool     $forceUTC
     * @param string   $paramTZid
     * @return DateTime
     * @since  2.29.1 - 2019-06-26
     */
    public static function conformDateTime(
        DateTime $input,
        bool $isValueDate,
        bool $forceUTC,
        & $paramTZid
    ) : DateTime
    {
        switch( true ) {
            case ( ! $isValueDate && $forceUTC ) :
                $dateTime = self::setDateTimeTimeZone( $input, Vcalendar::UTC );
                break;
            case ( ! $forceUTC && ! empty( $paramTZid )) :
                $dateTime = self::setDateTimeTimeZone( $input, $paramTZid );
                break;
            case ( self::dateTimeHasOffset( $input )) :
                $dateTime = self::setDateTimeTimeZone(
                    $input,
                    $input->getTimezone()->getName()
                );
                break;
            default :
                $dateTime = $input;
                break;
        } // end switch
        if( empty( $paramTZid )) {
            $paramTZid = $dateTime->getTimezone()->getName();
        }
        return $dateTime;
    }

    /**
     * Return conformed DateTime from string date
     *
     * @param string $input
     * @param bool   $isValueDate
     * @param bool   $forceUTC
     * @param bool   $isLocalTime
     * @param string $paramTZid
     * @return DateTime
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.29.1 - 2019-06-26
     */
    public static function conformStringDate(
        string $input,
        bool $isValueDate,
        bool $forceUTC,
        & $isLocalTime,
        & $paramTZid
    ) : DateTime
    {
        list( $dateStr, $timezonePart ) = self::splitIntoDateStrAndTimezone( $input );
        $isLocalTime = ( empty( $timezonePart ) && empty( $paramTZid ));
        $dateTime    = self::getDateTimeWithTimezoneFromString(
            $dateStr,
            $isLocalTime ? null : $timezonePart,
            $isLocalTime ? Vcalendar::UTC : $paramTZid,
            $forceUTC
        );
        if( ! $isValueDate && $forceUTC ) {
            $dateTime = self::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        }
        if( empty( $paramTZid ) && ! $isLocalTime ) {
            $paramTZid = $dateTime->getTimezone()->getName();
        }
        return $dateTime;
    }

    /**
     * Conform date parameters
     *
     * @param array  $params
     * @param bool   $isValueDate
     * @param bool   $isLocalTime
     * @param null|string $paramTZid
     * @since  2.29.1 - 2019-06-27
     */
    public static function conformDateTimeParams(
        array & $params,
        bool $isValueDate,
        bool $isLocalTime,
        $paramTZid
    )
    {
        ParameterFactory::ifExistRemove( // remove default
            $params,
            Vcalendar::VALUE,
            Vcalendar::DATE_TIME
        );
        switch( true ) {
            case ( $isValueDate ) :
                ParameterFactory::ifExistRemove( $params, Vcalendar::TZID );
                ParameterFactory::ifExistRemove( $params, Util::$ISLOCALTIME );
                break;
            case ( $isLocalTime ) :
                ParameterFactory::ifExistRemove( $params, Vcalendar::TZID );
                $params[Util::$ISLOCALTIME] = true;
                break;
            case ( ! empty( $paramTZid ) &&
                ! DateTimeZoneFactory::isUTCtimeZone( $paramTZid )) :
                $params[Vcalendar::TZID] = $paramTZid;
                break;
            default :
                ParameterFactory::ifExistRemove( $params, Vcalendar::TZID );
                break;
        } // end switch
    }

    /**
     * Return array [<datePart>, <timezonePart>] from (split) string
     *
     * @param string $string
     * @return array  [<datePart>, <timezonePart>]
     * @since  2.27.14 - 2019-03-08
     */
    public static function splitIntoDateStrAndTimezone( string $string ) : array
    {
        $string = trim( $string );
        if(( DateTimeZoneFactory::$UTCARR[0] == substr( $string, -1 )) &&
            ( ctype_digit( substr( $string, -3, 2 )))) { // nnZ
            return [ substr( $string, 0, -1 ), DateTimeZoneFactory::$UTCARR[1] ]; // UTC
        }
        $strLen = strlen( $string );
        if( self::isDateTimeStrInIcal( $string )) {
            $icalDateTimeString = substr( $string, 0, 15 );
            if(( DateTimeZoneFactory::$UTCARR[0] ==
                    substr( $string, 15, 1 )) && ( 16 == $strLen )) {
                return [ $icalDateTimeString, Vcalendar::UTC ]; // 'Z'
            }
            if( 15 == $strLen ) {
                return [ $string, null ];
            }
        }
        elseif( ctype_digit( $string ) && ( 9 > $strLen )) { // ex. YYYYmmdd
            return [ $string, null ];
        }
        if( DateTimeZoneFactory::hasOffset( $string )) {
            $tz      = DateTimeZoneFactory::getOffset( $string );
            $string2 = trim( substr( $string, 0, 0 - strlen( $tz )));
            if( Vcalendar::GMT == substr( $string2, -3 )) {
                $string2 = trim( substr( $string2, 0, -3 ));
            }
            $tz      = DateTimeZoneFactory::getTimeZoneNameFromOffset( $tz );
            return [ $string2, $tz ];
        } // end if
        if( false !== strrpos( $string, Util::$SP1 )) {
            $tz      = StringFactory::afterLast( Util::$SP1, $string );
            $string2 = StringFactory::beforeLast( Util::$SP1, $string );
            if( DateTimeZoneFactory::isUTCtimeZone( $tz )) {
                $tz = Vcalendar::UTC;
            }
            $found = true;
            try {
                DateTimeZoneFactory::assertDateTimeZone( $tz );
            }
            catch( InvalidArgumentException $e ) {
                $found = false;
            }
            if( $found ) {
                return [ $string2, $tz ];
            }
        } // end if
        return [ $string, null ];
    }

    /**
     * Return DateTime with the right timezone set
     *
     * @param string $dateStr
     * @param string $timezonePart
     * @param string $paramTZid
     * @param bool   $forceUTC
     * @return DateTime
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.27.8 - 2019-01-14
     */
    public static function getDateTimeWithTimezoneFromString(
        string $dateStr,
        $timezonePart = null,
        $paramTZid    = null,
        $forceUTC     = false
    ) : DateTime
    {
        $tz2 = null;
        switch( true ) {
            case ( empty( $timezonePart ) && ! empty( $paramTZid )) :
                $tz  = $paramTZid;
                break;
            case ( empty( $timezonePart )) :
                $tz  = date_default_timezone_get(); // local time
                break;
            case ( ! empty( $paramTZid )) :
                $tz  = $timezonePart;
                if( ! $forceUTC ) {
                    $tz2 = $paramTZid;
                }
                break;
            default :
                $tz  = $timezonePart;
                break;
        } // end switch
        $dateTime = self::getDateTimeFromDateString( $dateStr, $tz );
        if( ! empty( $tz2 )) {
            $dateTime = self::setDateTimeTimeZone( $dateTime, $tz2 );
        }
        return $dateTime;
    }

    /**
     * Return string formatted DateTime, if offset then set timezone UTC
     *
     * @param DateTimeInterface $dateTime
     * @param bool     $isDATE
     * @param bool     $isLocalTime
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.29.21 - 2020-01-31
     * @usedby RexdateFactory::getPeriod()/prepInputRdate() + <dateProp>::get<dateProp>()
     */
    public static function dateTime2Str(
        DateTimeInterface $dateTime,
        $isDATE = false,
        $isLocalTime = false
    ) : string
    {
        $dateTime = self::toDateTime( $dateTime );
        if( self::dateTimeHasOffset( $dateTime )) {
            $dateTime = self::setDateTimeTimeZone(
                $dateTime,
                $dateTime->getTimezone()->getName()
            );
        }
        $fmt    = ( $isDATE ?? false ) ? self::$Ymd : self::$YmdTHis;
        $output = $dateTime->format( $fmt );
        if( ! ( $isDATE ?? false ) && ! ( $isLocalTime ?? false ) &&
            DateTimeZoneFactory::isUTCtimeZone( $dateTime->getTimezone()->getName())) {
            $output .= DateTimeZoneFactory::$UTCARR[0];
        }
        return $output;
    }

    /**
     * Return bool true if datetime har offset timezone
     *
     * @param DateTime $datetime
     * @return bool
     * @since  2.27.19 - 2019-04-09
     */
    public static function dateTimeHasOffset( DateTime $datetime ) : bool
    {
        return DateTimeZoneFactory::hasOffset( $datetime->getTimezone()->getName());
    }

    /*
     * Return bool true if date(times) are in sequence
     *
     * @param DateTime $first
     * @param DateTime $second
     * @param string $propName
     * @throws InvalidArgumentException
     * @since  2.27.14 - 2019-02-03
     */
    public static function assertDatesAreInSequence(
        DateTime $first,
        DateTime $second,
        string $propName
    )
    {
        static $ERR  = '%s, dates are not in (asc) order (%s < _%s_)';
        if( $first->getTimestamp() > $second->getTimestamp()) {
            throw new InvalidArgumentException(
                sprintf(
                    $ERR,
                    $propName,
                    $first->format( self::$YmdTHis ),
                    $second->format( self::$YmdTHis )
                )
            );
        }
    }

    /*
     * Return DateTime from string date, opt. with other timezone
     *
     * @param string $dateString
     * @param string $tz
     * @return DateTime
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.27.8 - 2019-01-12
     */
    private static function getDateTimeFromDateString(
        string $dateString,
        $tz = null
    ) : DateTime
    {
        $tz      = trim( $tz );
        switch( true ) {
            case ( empty( $tz )) :
                break;
            case ( DateTimeZoneFactory::isUTCtimeZone( $tz )) :
                $tz = Vcalendar::UTC;
                break;
            case ( DateTimeZoneFactory::hasOffset( $tz )) :
                $tz  = DateTimeZoneFactory::getTimeZoneNameFromOffset( $tz );
                break;
        } // end switch
        try {
            $dateTime = self::factory( $dateString, $tz );
        }
        catch( InvalidArgumentException $ie ) {
            throw $ie;
        }
        catch( Exception $e ) {
            throw $e;
        }
        return $dateTime;
    }

    /*
     * Return DateTime modified from (ext) timezone
     *
     * @param DateTimeInterface $dateTime
     * @param string   $tz
     * @return DateTime
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.27.14 - 2019-02-04
     */
    public static function setDateTimeTimeZone(
        DateTimeInterface $dateTime,
        string $tz
    ) : DateTime
    {
        $dateTime = self::toDateTime( $dateTime );
        if( empty( $tz )) {
            return $dateTime;
        }
        if( DateTimeZoneFactory::hasOffset( $tz )) {
            $tz = DateTimeZoneFactory::getTimeZoneNameFromOffset( $tz );
        }
        $currTz = $dateTime->getTimezone()->getName();
        if( DateTimeZoneFactory::isUTCtimeZone( $currTz ) &&
            DateTimeZoneFactory::isUTCtimeZone( $tz )) {
            return $dateTime;
        }
        if( 0 == strcasecmp( $currTz, $tz )) { // same
            return $dateTime;
        }
        try {
            $tzt = DateTimeZoneFactory::factory( $tz );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException(
                sprintf( self::$ERR4, $dateTime->format( self::$YMDHISe ), $tz ),
                $e->getCode(),
                $e
            );
        }
        if( false === $dateTime->setTimezone( $tzt )) {
            throw new InvalidArgumentException(
                sprintf( self::$ERR4, $dateTime->format( self::$YMDHISe ), $tz )
            );
        }
        return $dateTime;
    }

    /*
     *  Return bool true if string contains a valid date
     *
     * @param mixed $str
     * @return bool
     * @since  2.27.14 - 2019-02-17
     */
    public static function isStringAndDate( $string ) : bool
    {
        if( ! is_string( $string )) {
            return false;
        }
        $string = trim( $string );
        return (( 8 <= strlen( $string )) &&
            ( false !== strtotime ( $string )));
    }

    /*
     *  Return bool true if dateStr starts with format YYYYmmdd[T/t]HHmmss
     *
     * @param string $dateStr
     * @return bool
     * @since  2.27.8 - 2019-01-12
     */
    private static function isDateTimeStrInIcal( string $dateStr ) : bool
    {
        static $Tarr = ['T','t'];
        return (      is_string( $dateStr) &&
            ctype_digit( substr( $dateStr, 0, 8 )) &&
               in_array( substr( $dateStr, 8, 1 ), $Tarr ) &&
            ctype_digit( substr( $dateStr, 9, 6 )));
    }
}

