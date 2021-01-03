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
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

/**
 * RECURRENCE-ID property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.16 2020-01-24
 */
trait RECURRENCE_IDtrait
{
    /**
     * @var array component property RECURRENCE_ID value
     */
    protected $recurrenceid = null;

    /**
     * Return formatted output for calendar component property recurrence-id
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.1 2019-06-24
     */
    public function createRecurrenceid()
    {
        if( empty( $this->recurrenceid )) {
            return null;
        }
        if( empty( $this->recurrenceid[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::RECURRENCE_ID )
                : null;
        }
        $isDATE      = ParameterFactory::isParamsValueSet(
            $this->recurrenceid,
            self::DATE
        );
        $isLocalTime = isset( $this->recurrenceid[Util::$LCparams][Util::$ISLOCALTIME] );
        return StringFactory::createElement(
            self::RECURRENCE_ID,
            ParameterFactory::createParams( $this->recurrenceid[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str(
                $this->recurrenceid[Util::$LCvalue],
                $isDATE,
                $isLocalTime
            )
        );
    }

    /**
     * Delete calendar component property recurrence-id
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteRecurrenceid()
    {
        $this->recurrenceid = null;
        return true;
    }

    /**
     * Return calendar component property recurrence-id
     *
     * @param bool   $inclParam
     * @return bool|DateTime|array
     * @since 2.29.1 2019-06-22
     */
    public function getRecurrenceid( $inclParam = false )
    {
        if( empty( $this->recurrenceid )) {
            return false;
        }
        return ( $inclParam )
            ? $this->recurrenceid
            : $this->recurrenceid[Util::$LCvalue];
    }

    /**
     * Set calendar component property recurrence-id
     *
     * @param string|DateTimeInterface $value
     * @param array           $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setRecurrenceid( $value = null, $params = [] )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::RECURRENCE_ID );
            $this->recurrenceid = [
                Util::$LCvalue  => Util::$SP0,
                Util::$LCparams => [],
            ];
            return $this;
        }
        $this->recurrenceid = DateTimeFactory::setDate(
            $value,
            ParameterFactory::setParams(
                $params,
                DateTimeFactory::$DEFAULTVALUEDATETIME
            )
        );
        return $this;
    }
}
