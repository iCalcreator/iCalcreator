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
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * DUE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.16 2020-01-24
 */
trait DUEtrait
{
    /**
     * @var array component property DUE value
     */
    protected $due = null;

    /**
     * Return formatted output for calendar component property due
     *
     * "The value type of the "DTEND" or "DUE" properties MUST match the value type of "DTSTART" property."
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.1 2019-06-24
     */
    public function createDue()
    {
        if( empty( $this->due )) {
            return null;
        }
        if( empty( $this->due[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::DUE )
                : null;
        }
        $isDATE = ( ! empty( $this->dtstart ))
            ? ParameterFactory::isParamsValueSet( $this->dtstart, self::DATE )
            : ParameterFactory::isParamsValueSet( $this->due, self::DATE );
        $isLocalTime = isset( $this->due[Util::$LCparams][Util::$ISLOCALTIME] );
        return StringFactory::createElement(
            self::DUE,
            ParameterFactory::createParams( $this->due[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str(
                $this->due[Util::$LCvalue],
                $isDATE, $isLocalTime
            )
        );
    }

    /**
     * Delete calendar component property due
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteDue()
    {
        $this->due = null;
        return true;
    }

    /**
     * Return calendar component property due
     *
     * @param bool   $inclParam
     * @return bool|DateTime|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getDue( $inclParam = false )
    {
        if( empty( $this->due )) {
            return false;
        }
        return ( $inclParam ) ? $this->due : $this->due[Util::$LCvalue];
    }

    /**
     * Set calendar component property due
     *
     * @param string|DateTimeInterface $value
     * @param array           $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setDue( $value = null, $params = [] )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::DUE );
            $this->due = [
                Util::$LCvalue  => Util::$SP0,
                Util::$LCparams => [],
            ];
            return $this;
        }
        $dtstart = $this->getDtstart( true );
        if( isset( $dtstart[Util::$LCparams][self::VALUE] )) {
            $params[self::VALUE] = $dtstart[Util::$LCparams][self::VALUE];
        }
        if( isset( $dtstart[Util::$LCparams][Util::$ISLOCALTIME] )) {
            $params[Util::$ISLOCALTIME] = true;
        }
        $this->due = DateTimeFactory::setDate(
            $value,
            ParameterFactory::setParams(
                $params,
                DateTimeFactory::$DEFAULTVALUEDATETIME
            )
        );
        if( ! empty( $dtstart ) && Util::issetAndNotEmpty( $dtstart, Util::$LCvalue )) {
            DateTimeFactory::assertDatesAreInSequence(
                $dtstart[Util::$LCvalue], $this->due[Util::$LCvalue], self::DUE
            );
        }
        return $this;
    }
}
