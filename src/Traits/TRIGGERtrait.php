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
namespace Kigkonsult\Icalcreator\Traits;

use DateTimeInterface;
use DateInterval;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\DurDates;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\Util;

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
 * @since 2.41.56 2022-08-15
 */
trait TRIGGERtrait
{
    /**
     * @var null|Pc component property TRIGGER value
     */
    protected ? Pc $trigger = null;

    /**
     * @var string  iCal TRIGGER param keywords
     * @since  2.26.8 - 2019-03-08
     * @deprecated
     */
    public static string $RELATEDSTART = 'relatedStart';
    public static string $BEFORE       = 'before';

    /**
     * Return formatted output for calendar component property trigger
     *
     * @return string
     * @throws Exception
     * @since 2.41.55 2022-08-13
     */
    public function createTrigger() : string
    {
        return DurDates::format(
            self::TRIGGER,
            $this->trigger,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property trigger
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteTrigger() : bool
    {
        $this->trigger = null;
        return true;
    }

    /**
     * Get calendar component property trigger
     *
     * @param null|bool   $inclParam
     * @return bool|string|DateTimeInterface|DateInterval|Pc
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public function getTrigger( ? bool $inclParam = false ) :bool|string|DateInterval|DateTimeInterface|Pc
    {
        if( empty( $this->trigger )) {
            return false;
        }
        return $inclParam ? clone $this->trigger : $this->trigger->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isTriggerSet() : bool
    {
        return ! empty( $this->trigger->value );
    }

    /**
     * Set calendar component property trigger
     *
     * @param null|string|Pc|DateTimeInterface|DateInterval $value
     * @param null|array $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.56 2022-08-15
     * @todo ?? "If the trigger is set relative to START, then the "DTSTART"
     *        property MUST be present in the associated "VEVENT" or "VTODO"
     *        calendar component.  If an alarm is specified for an event with
     *        the trigger set relative to the END, then the "DTEND" property or
     *        the "DTSTART" and "DURATION " properties MUST be present in the
     *        associated "VEVENT" calendar component.  If the alarm is specified
     *        for a to-do with a trigger set relative to the END, then either
     *        the "DUE" property or the "DTSTART" and "DURATION " properties
     *        MUST be present in the associated "VTODO" calendar component."
     */
    public function setTrigger(
        null|string|Pc|DateTimeInterface|DateInterval $value = null,
        ? array $params = []
    ) : static
    {
        static $FMTERRPROPFMT = 'Invalid %s input format (%s)';
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( self::$SP0, self::TRIGGER );
            $this->trigger = $value->setEmpty();
            return $this;
        }
        $isParamsDateTimeSet = $value->hasParamValue( self::DATE_TIME );
        $value->addParamValue( self::DURATION, false ); // default
        if( $value->hasParamKey( self::RELATED )) {
            $value->addParam( self::RELATED, strtoupper( $value->getParams( self::RELATED )));
        } // end if
        switch( true ) {
            // duration DateInterval
            case ( ! $isParamsDateTimeSet && ( $value->value instanceof DateInterval )) :
                return $this->setTriggerDateIntervalValue( $value );
            // datetime DateTimeInterface
            case ( $value->value instanceof DateTimeInterface ) :
                $value->addParamValue( self::DATE_TIME ); // force date-time...
                return $this->setTriggerDateTimeValue(
                    $value->setValue( DateTimeFactory::toDateTime( $value->value ))
                );
            // duration in a string
            case ( ! $isParamsDateTimeSet && DateIntervalFactory::isStringAndDuration( $value->value )) :
                return $this->setTriggerStringDurationValue( $value );
            // date in a string
            case( $isParamsDateTimeSet &&
                DateTimeFactory::isStringAndDate( $value->value )) :
                return $this->setTriggerStringDateValue( $value );
        } // end switch
        throw new InvalidArgumentException(
            sprintf( $FMTERRPROPFMT, self::TRIGGER, var_export( $value->value, true ))
        );
    }

    /**
     * Set trigger DateInterval value
     *
     * @param Pc $value
     * @return static
     * @throws Exception
     * @since  2.40 - 2021-10-04
     */
    private function setTriggerDateIntervalValue( Pc $value ) : static
    {
        $dateInterval = DateIntervalFactory::conformDateInterval( $value->value );
        if( true !== self::isDurationRelatedEnd( $value->params )) {
            $value->removeParam(self::RELATED ); // remove default
        }
        $value->removeParam(self::VALUE ); // remove default
        $this->trigger = $value->setValue( $dateInterval );
        return $this;
    }

    /**
     * Set trigger DateTime value
     *
     * @param Pc $value
     * @return static
     * @throws Exception
     * @since  2.29.2 - 2019-06-28
     */
    private function setTriggerDateTimeValue( Pc $value ) : static
    {
        $value->removeParam( self::RELATED ); // n.a. for date-time
        $this->trigger = $value->setValue(
            DateTimeFactory::setDateTimeTimeZone( $value->value, self::UTC )
        );
        return $this;
    }

    /**
     * Set trigger string duration value
     *
     * @param Pc  $value
     * @return static
     * @throws Exception
     * @since  2.40 - 2021-10-04
     */
    private function setTriggerStringDurationValue( Pc $value ) : static
    {
        $before = ( Util::$MINUS === $value->value[0] );
        if( DateIntervalFactory::$P !== $value->value[0] ) {
            $value->value = substr( $value->value, 1 );
        }
        $dateInterval1 = new DateInterval( $value->value );
        $dateInterval1->invert = ( $before ) ? 1 : 0;
        $dateInterval  = DateIntervalFactory::conformDateInterval( $dateInterval1 );
        if( true !== self::isDurationRelatedEnd( $value->params )) {
            $value->removeParam( self::RELATED ); // remove default
        }
        $value->removeParam( self::VALUE ); // remove default
        $this->trigger = $value->setValue( $dateInterval );
        return $this;
    }

    /**
     * Set trigger string date value
     *
     * @param Pc   $value
     * @return static
     * @throws Exception
     * @since  2.29.2 - 2019-06-28
     */
    private function setTriggerStringDateValue( Pc $value ) : static
    {
        [ $dateStr, $timezonePart ] =
            DateTimeFactory::splitIntoDateStrAndTimezone( $value->value );
        $value->value = DateTimeFactory::getDateTimeWithTimezoneFromString(
            $dateStr,
            $timezonePart,
            self::UTC,
            true
        );
        if( ! DateTimeZoneFactory::isUTCtimeZone( $value->value->getTimezone()->getName())) {
            $value->value = DateTimeFactory::setDateTimeTimeZone(
                $value->value,
                self::UTC
            );
        }
        $value->removeParam(self::RELATED ); // n.a. for date-time
        $this->trigger = $value;
        return $this;
    }

    /**
     * Return bool true if duration is related END
     *
     * @param string[] $params
     * @return bool
     * @since  2.26.7 - 2018-12-01
     */
    private static function isDurationRelatedEnd( array $params ) : bool
    {
        return Util::issetKeyAndEquals( $params, self::RELATED, self::END );
    }
}
