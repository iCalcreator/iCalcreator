<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator\Traits;

use DateTime;
use DateTimeInterface;
use DateInterval;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;

use function is_array;
use function strtoupper;
use function substr;

/**
 *    rfc5545 : DURATION
 *    Format Definition:  This value type is defined by the following notation:
 *
 *    dur-value  = (["+"] / "-") "P" (dur-date / dur-time / dur-week)
 *
 *    dur-date   = dur-day [dur-time]
 *    dur-time   = "T" (dur-hour / dur-minute / dur-second)
 *    dur-week   = 1*DIGIT "W"
 *    dur-hour   = 1*DIGIT "H" [dur-minute]
 *    dur-minute = 1*DIGIT "M" [dur-second]
 *    dur-second = 1*DIGIT "S"
 *    dur-day    = 1*DIGIT "D"
 */
/**
 * TRIGGER property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.16 2020-01-24
 */
trait TRIGGERtrait
{
    /**
     * @var array component property TRIGGER value
     */
    protected $trigger = null;

    /**
     * @var string  iCal TRIGGER param keywords
     * @since  2.26.8 - 2019-03-08
     */
    public static $RELATEDSTART = 'relatedStart';
    public static $BEFORE       = 'before';

    /**
     * Return formatted output for calendar component property trigger
     *
     * @return string
     * @throws Exception
     * @since  2.29.2 - 2019-06-27
     */
    public function createTrigger()
    {
        if( empty( $this->trigger )) {
            return null;
        }
        if( empty( $this->trigger[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::TRIGGER )
                : null;
        }
        if( DateIntervalFactory::isDateIntervalArrayInvertSet(
            $this->trigger[Util::$LCvalue]
        )) { // fix pre 7.0.5 bug
            try {
                $dateInterval =
                    DateIntervalFactory::DateIntervalArr2DateInterval(
                        $this->trigger[Util::$LCvalue]
                    );
            }
            catch( Exception $e ) {
                throw $e;
            }
            return StringFactory::createElement(
                self::TRIGGER,
                ParameterFactory::createParams( $this->trigger[Util::$LCparams] ),
                DateIntervalFactory::dateInterval2String( $dateInterval, true )
            );
        }
        return StringFactory::createElement(
            self::TRIGGER,
            ParameterFactory::createParams( $this->trigger[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str( $this->trigger[Util::$LCvalue] )
        );
    }

    /**
     * Delete calendar component property trigger
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteTrigger()
    {
        $this->trigger = null;
        return true;
    }

    /**
     * Get calendar component property trigger
     *
     * @param bool   $inclParam
     * @return bool|array
     * @throws Exception
     * @since 2.29.2 2019-06-27
     */
    public function getTrigger( $inclParam = false )
    {
        if( empty( $this->trigger )) {
            return false;
        }
        if( DateIntervalFactory::isDateIntervalArrayInvertSet(
            $this->trigger[Util::$LCvalue]
        )) { // fix pre 7.0.5 bug
            try {
                $value =
                    DateIntervalFactory::DateIntervalArr2DateInterval(
                        $this->trigger[Util::$LCvalue]
                    );
            }
            catch( Exception $e ) {
                throw $e;
            }
        }
        else {
            $value = $this->trigger[Util::$LCvalue]; // DateTime
        }
        return ( $inclParam )
            ? [
                Util::$LCvalue => $value,
                Util::$LCparams => (array) $this->trigger[Util::$LCparams]
            ]
            : $value;
    }

    /**
     * Set calendar component property trigger
     *
     * @param DateTimeInterface|DateInterval|string $value
     * @param array $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     * @todo "If the trigger is set relative to START, then the "DTSTART"
     *        property MUST be present in the associated "VEVENT" or "VTODO"
     *        calendar component.  If an alarm is specified for an event with
     *        the trigger set relative to the END, then the "DTEND" property or
     *        the "DTSTART" and "DURATION " properties MUST be present in the
     *        associated "VEVENT" calendar component.  If the alarm is specified
     *        for a to-do with a trigger set relative to the END, then either
     *        the "DUE" property or the "DTSTART" and "DURATION " properties
     *        MUST be present in the associated "VTODO" calendar component."
     */
    public function setTrigger( $value = null, $params = [] )
    {
        if( empty( $value ) && self::isArrayOrEmpty( $params )) {
            $this->assertEmptyValue( Util::$SP0, self::TRIGGER );
            $this->trigger = [
                Util::$LCvalue  => Util::$SP0,
                Util::$LCparams => [],
            ];
            return $this;
        }
        $isParamsDateTimeSet = self::isDurationParamValueDateTime( $params );
        $params2 = [];
        if( is_array( $params )) {
            $params2 = ParameterFactory::setParams(
                $params,
                [ Vcalendar::VALUE => Vcalendar::DURATION ]
            );
            if( isset( $params2[Vcalendar::RELATED] )) {
                $params2[Vcalendar::RELATED] =
                    strtoupper( $params2[Vcalendar::RELATED] );
            }
        }
        switch( true ) {
            // duration DateInterval
            case ( ! $isParamsDateTimeSet && ( $value instanceof DateInterval )) :
                return $this->setTriggerDateIntervalValue( $value, $params2 );
                break;
            // datetime DateTimeInterface
            case ( $value instanceof DateTimeInterface ) :
                $arg2[Vcalendar::VALUE] = Vcalendar::DATE_TIME; // force date-time...
                return $this->setTriggerDateTimeValue(
                    DateTimeFactory::cnvrtDateTimeInterface( $value ),
                    $params2
                );
                break;
            // duration in a string
            case ( ! $isParamsDateTimeSet &&
                DateIntervalFactory::isStringAndDuration( $value )) :
                return $this->setTriggerStringDurationValue( $value, $params2 );
                break;
            // date in a string
            case( $isParamsDateTimeSet && DateTimeFactory::isStringAndDate( $value )) :
                return $this->setTriggerStringDateValue( $value, $params2 );
                break;
        } // end switch
        throw new InvalidArgumentException(
            sprintf( self::$FMTERRPROPFMT, self::TRIGGER, var_export( $value, true ))
        );
    }

    /**
     * Set trigger DateInterval value
     *
     * @param DateInterval $value
     * @param null|array   $params
     * @return static
     * @throws Exception
     * @since  2.27.2 - 2019-01-04
     */
    private function setTriggerDateIntervalValue( DateInterval $value, $params = [] )
    {
        try {
            $dateInterval = DateIntervalFactory::conformDateInterval( $value );
        }
        catch( Exception $e ) {
            throw $e;
        }
        if( true != self::isDurationRelatedEnd( $params )) {
            ParameterFactory::ifExistRemove( $params, self::RELATED ); // remove default
        }
        ParameterFactory::ifExistRemove( $params, self::VALUE ); // remove default
        $this->trigger[Util::$LCvalue]  = (array) $dateInterval;  // fix pre 7.0.5 bug
        $this->trigger[Util::$LCparams] = $params;
        return $this;
    }

    /**
     * Set trigger DateTime value
     *
     * @param DateTime $value
     * @param null|array   $params
     * @return static
     * @throws Exception
     * @since  2.29.2 - 2019-06-28
     */
    private function setTriggerDateTimeValue( DateTime $value, $params = [] )
    {
        ParameterFactory::ifExistRemove( $params, self::RELATED ); // n.a. for date-time
        $this->trigger = [
            Util::$LCvalue  =>
                DateTimeFactory::setDateTimeTimeZone( $value, Vcalendar::UTC ),
            Util::$LCparams => $params
        ];
        return $this;
    }

    /**
     * Set trigger string duration value
     *
     * @param string     $value
     * @param null|array $params
     * @return static
     * @throws Exception
     * @since  2.27.22 - 2020-08-22
     */
    private function setTriggerStringDurationValue( $value, $params = [] )
    {
        $before = ( Util::$MINUS == substr( $value, 0, 1 ));
        if( DateIntervalFactory::$P != substr( $value, 0, 1 )) {
            $value = substr( $value, 1 );
        }
        try {
            $dateInterval1 = new DateInterval( $value );
            $dateInterval1->invert = ( $before ) ? 1 : 0;
            $dateInterval = DateIntervalFactory::conformDateInterval( $dateInterval1 );
        }
        catch( Exception $e ) {
            throw $e;
        }
        if( true != self::isDurationRelatedEnd( $params )) {
            ParameterFactory::ifExistRemove( $params, self::RELATED ); // remove default
        }
        ParameterFactory::ifExistRemove( $params, self::VALUE ); // remove default
        $this->trigger = [
            Util::$LCvalue  => (array) $dateInterval, // fix pre 7.0.5 bug
            Util::$LCparams => $params
        ];
        return $this;
    }

    /**
     * Set trigger string date value
     *
     * @param string     $value
     * @param null|array $params
     * @return static
     * @throws Exception
     * @since  2.29.2 - 2019-06-28
     */
    private function setTriggerStringDateValue( $value, $params = [] )
    {
        list( $dateStr, $timezonePart ) =
            DateTimeFactory::splitIntoDateStrAndTimezone( $value );
        $dateTime = DateTimeFactory::getDateTimeWithTimezoneFromString(
            $dateStr,
            $timezonePart,
            Vcalendar::UTC,
            true
        );
        if( ! DateTimeZoneFactory::isUTCtimeZone( $dateTime->getTimezone()->getName())) {
            $dateTime = DateTimeFactory::setDateTimeTimeZone(
                $dateTime,
                Vcalendar::UTC
            );
        }
        ParameterFactory::ifExistRemove( $params, self::RELATED ); // n.a. for date-time
        $this->trigger = [
            Util::$LCvalue  => $dateTime,
            Util::$LCparams => $params
        ];
        return $this;
    }

    /**
     * Return bool true if value is array is empty
     *
     * @param array $value
     * @return bool
     * @since  2.27.2 - 2019-01-04
     */
    private static function isArrayOrEmpty( $value )
    {
        return ( is_array( $value ) || empty( $value ));
    }

    /**
     * Return bool true if duration is related END
     *
     * @param null|array $params
     * @return bool
     * @static
     * @since  2.26.7 - 2018-12-01
     */
    private static function isDurationRelatedEnd( $params )
    {
        return Util::issetKeyAndEquals( $params, self::RELATED, self::END );
    }

    /**
     * Return bool true if arg is param and TRIGGER value is a DATE-TIME
     *
     * @param null|array $params
     * @return bool
     * @static
     * @since  2.26.14 - 2019-02-14
     */
    private static function isDurationParamValueDateTime( $params )
    {
        if( ! is_array( $params )) {
            return false;
        }
        $param = ParameterFactory::setParams( $params );
        return ParameterFactory::isParamsValueSet(
            [ Util::$LCparams => $param ],
            self::DATE_TIME
        );
    }
}
