<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2024 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
use Kigkonsult\Icalcreator\Util\StringFactory;
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
 * @since 2.41.85 2024-01-18
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
     * @since 2.41.85 2024-01-18
     */
    public function getTrigger( ? bool $inclParam = false ) :bool|string|DateInterval|DateTimeInterface|Pc
    {
        if( empty( $this->trigger )) {
            return false;
        }
        return $inclParam ? clone $this->trigger : $this->trigger->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isTriggerSet() : bool
    {
        return self::isPropSet( $this->trigger );
    }

    /**
     * Set calendar component property trigger
     *
     * @param null|string|Pc|DateTimeInterface|DateInterval $value
     * @param null|mixed[] $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
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
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( self::$SP0, self::TRIGGER );
            $this->trigger = $pc->setEmpty();
            return $this;
        }
        $isParamsDateTimeSet = $pc->hasParamValue( self::DATE_TIME );
        $pc->addParamValue( self::DURATION, false ); // default
        if( $pc->hasParamKey( self::RELATED )) {
            $pc->addParam( self::RELATED, strtoupper( $pc->getParams( self::RELATED )));
        } // end if
        switch( true ) {
            // duration DateInterval
            case ( ! $isParamsDateTimeSet && ( $pcValue instanceof DateInterval )) :
                return $this->setTriggerDateIntervalValue( $pc );
            // datetime DateTimeInterface
            case ( $pcValue instanceof DateTimeInterface ) :
                $pc->addParamValue( self::DATE_TIME ); // force date-time...
                return $this->setTriggerDateTimeValue(
                    $pc->setValue( DateTimeFactory::toDateTime( $pcValue ))
                );
            // duration in a string
            case ( ! $isParamsDateTimeSet && DateIntervalFactory::isStringAndDuration( $pcValue )) :
                return $this->setTriggerStringDurationValue( $pc );
            // date in a string
            case( $isParamsDateTimeSet && DateTimeFactory::isStringAndDate( $pcValue )) :
                return $this->setTriggerStringDateValue( $pc );
        } // end switch
        throw new InvalidArgumentException(
            sprintf( $FMTERRPROPFMT, self::TRIGGER, var_export( $pcValue, true ))
        );
    }

    /**
     * Set trigger DateInterval value
     *
     * @param Pc $value
     * @return static
     * @throws Exception
     * @since 2.41.85 2024-01-18
     */
    private function setTriggerDateIntervalValue( Pc $value ) : static
    {
        $dateInterval = DateIntervalFactory::conformDateInterval( $value->getValue());
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
     * @since 2.41.85 2024-01-18
     */
    private function setTriggerDateTimeValue( Pc $value ) : static
    {
        $value->removeParam( self::RELATED ); // n.a. for date-time
        $this->trigger = $value->setValue(
            DateTimeFactory::setDateTimeTimeZone( $value->getValue(), self::UTC )
        );
        return $this;
    }

    /**
     * Set trigger string duration value
     *
     * @param Pc  $value
     * @return static
     * @throws Exception
     * @since 2.41.85 2024-01-18
     */
    private function setTriggerStringDurationValue( Pc $value ) : static
    {
        $pcValue = $value->getValue();
        $before  = ( StringFactory::$MINUS === $pcValue[0] );
        if( DateIntervalFactory::$P !== $pcValue[0] ) {
            $pcValue = substr( $pcValue, 1 );
        }
        $dateInterval1 = new DateInterval( $pcValue );
        $dateInterval1->invert = ( $before ) ? 1 : 0;
        $dateInterval  = DateIntervalFactory::conformDateInterval( $dateInterval1 );
        if( true !== self::isDurationRelatedEnd( $value->getParams())) {
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
     * @since 2.41.85 2024-01-18
     */
    private function setTriggerStringDateValue( Pc $value ) : static
    {
        [ $dateStr, $timezonePart ] =
            DateTimeFactory::splitIntoDateStrAndTimezone( $value->getValue());
        $tmpDate = DateTimeFactory::getDateTimeWithTimezoneFromString(
            $dateStr,
            $timezonePart,
            self::UTC,
            true
        );
        if( ! DateTimeZoneFactory::isUTCtimeZone(
            $tmpDate->getTimezone()->getName(),
            $tmpDate->format( DateTimeFactory::$YmdTHis ))
        ) {
            $tmpDate = DateTimeFactory::setDateTimeTimeZone( $tmpDate, self::UTC );
        }
        $value->removeParam(self::RELATED ); // n.a. for date-time
        $this->trigger = $value->setValue( $tmpDate );
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
