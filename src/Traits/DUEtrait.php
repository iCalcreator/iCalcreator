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

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Dt1Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;

/**
 * DUE property functions
 *
 * @since 2.41.85 2024-01-18
 */
trait DUEtrait
{
    /**
     * @var null|Pc component property DUE value
     */
    protected ? Pc $due = null;

    /**
     * Return formatted output for calendar component property due
     *
     * "The value type of the "DTEND" or "DUE" properties MUST match the value type of "DTSTART" property."
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function createDue() : string
    {
        return  Dt1Property::format(
            self::DUE,
            $this->due,
            $this->getConfig( self::ALLOWEMPTY ),
            Dt1Property::getIsDate( $this->dtstart, $this->due ),
            Dt1Property::getIsLocalTime( $this->due )
        );
    }

    /**
     * Delete calendar component property due
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteDue() : bool
    {
        $this->due = null;
        return true;
    }

    /**
     * Return calendar component property due
     *
     * @param null|bool   $inclParam
     * @return bool|string|DateTime|Pc
     * @since 2.41.85 2024-01-18
     */
    public function getDue( ? bool $inclParam = false ) : DateTime | bool | string | Pc
    {
        if( empty( $this->due )) {
            return false;
        }
        return $inclParam ? clone $this->due : $this->due->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isDueSet() : bool
    {
        return self::isPropSet( $this->due );
    }

    /**
     * Set calendar component property due
     *
     * @param null|string|Pc|DateTimeInterface $value
     * @param null|mixed[] $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setDue( null|string|DateTimeInterface|Pc $value = null, ? array $params = [] ) : static
    {
        $pc = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::DUE );
            $this->due = $pc->setEmpty();
            return $this;
        }
        $dtstart      = $this->getDtstart( true );
        $isDtstartSet = $this->isDtstartSet();
        if( $isDtstartSet ) {
            if( $dtstart->hasParamValue()) {
                $pc->addParamValue( $dtstart->getValueParam());
            }
            if( $dtstart->hasParamIsLocalTime()) {
                $pc->addParam( self::ISLOCALTIME, true );
            }
        }
        $pc->addParamValue( self::DATE_TIME, false );
        $this->due = DateTimeFactory::setDate( $pc );
        if( $isDtstartSet ) {
            DateTimeFactory::assertDatesAreInSequence(
                $dtstart->getValue(),
                $this->due->getValue(),
                self::DUE
            );
        }
        return $this;
    }
}
