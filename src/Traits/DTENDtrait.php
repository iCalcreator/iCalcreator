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
 * DTEND property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.16 2020-01-24
 */
trait DTENDtrait
{
    /**
     * @var array component property DTEND value
     */
    protected $dtend = null;

    /**
     * Return formatted output for calendar component property dtend
     *
     * "The value type of the "DTEND" or "DUE" properties MUST match the value type of "DTSTART" property."
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.1 2019-06-24
     */
    public function createDtend()
    {
        if( empty( $this->dtend )) {
            return null;
        }
        if( empty( $this->dtend[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::DTEND )
                : null;
        }
        $isDATE = ( ! empty( $this->dtstart ))
            ? ParameterFactory::isParamsValueSet( $this->dtstart, self::DATE )
            : ParameterFactory::isParamsValueSet( $this->dtend, self::DATE );
        $isLocalTime = isset( $this->dtend[Util::$LCparams][Util::$ISLOCALTIME] );
        return StringFactory::createElement(
            self::DTEND,
            ParameterFactory::createParams( $this->dtend[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str(
                $this->dtend[Util::$LCvalue],
                $isDATE,
                $isLocalTime
            )
        );
    }

    /**
     * Delete calendar component property dtend
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteDtend()
    {
        $this->dtend = null;
        return true;
    }

    /**
     * Return calendar component property dtend
     *
     * @param bool   $inclParam
     * @return bool|DateTime|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getDtend( $inclParam = false )
    {
        if( empty( $this->dtend )) {
            return false;
        }
        return ( $inclParam ) ? $this->dtend : $this->dtend[Util::$LCvalue];
    }

    /**
     * Set calendar component property dtend
     *
     * @param string|DateTimeInterface $value
     * @param array           $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setDtend( $value = null, $params = [] )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::DTEND );
            $this->dtend = [
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
        $this->dtend = DateTimeFactory::setDate(
            $value,
            ParameterFactory::setParams(
                $params,
                DateTimeFactory::$DEFAULTVALUEDATETIME
            )
        );
        if( ! empty( $dtstart ) && ( Util::issetAndNotEmpty( $dtstart, Util::$LCvalue ))) {
            DateTimeFactory::assertDatesAreInSequence(
                $dtstart[Util::$LCvalue],
                $this->dtend[Util::$LCvalue],
                self::DTEND
            );
        }
        return $this;
    }
}
