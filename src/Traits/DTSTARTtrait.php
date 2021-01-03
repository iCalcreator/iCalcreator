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
use Kigkonsult\Icalcreator\Vcalendar;

/**
 * DTSTART property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.25 2020-08-26
 */
trait DTSTARTtrait
{
    /**
     * @var array component property DTSTART value
     */
    protected $dtstart = null;

    /**
     * Return formatted output for calendar component property dtstart
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.1 2019-06-22
     */
    public function createDtstart()
    {
        if( empty( $this->dtstart )) {
            return null;
        }
        if( empty( $this->dtstart[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::DTSTART )
                : null;
        }
        $isLocalTime = isset( $this->dtstart[Util::$LCparams][Util::$ISLOCALTIME] );
        return StringFactory::createElement(
            self::DTSTART,
            ParameterFactory::createParams( $this->dtstart[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str(
                $this->dtstart[Util::$LCvalue],
                ParameterFactory::isParamsValueSet( $this->dtstart, self::DATE ),
                $isLocalTime
            )
        );
    }

    /**
     * Delete calendar component property dtstart
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteDtstart()
    {
        $this->dtstart = null;
        return true;
    }

    /**
     * Return calendar component property dtstart
     *
     * @param bool   $inclParam
     * @return bool|DateTime|array
     * @since 2.29.1 2019-06-22
     */
    public function getDtstart( $inclParam = false )
    {
        if( empty( $this->dtstart )) {
            return false;
        }
        return ( $inclParam ) ? $this->dtstart : $this->dtstart[Util::$LCvalue];
    }

    /**
     * Get calendar component property dtstart params, opt TZID only
     *
     * @param bool $tzid   if true, only params TZID, if exists
     * @return array
     * @since 2.29.25 2020-08-26
     */
    private function getDtstartParams( $tzid = true )
    {
        if( ! $tzid ) {
            return ( empty( $this->dtstart ) || empty( $this->dtstart[Util::$LCparams] ))
                ? []
                : $this->dtstart[Util::$LCparams];
        }
        if( empty( $this->dtstart ) ||
            empty( $this->dtstart[Util::$LCparams] ) ||
            ! isset( $this->dtstart[Util::$LCparams][Vcalendar::TZID] )) {
            return [];
        }
        return isset( $this->dtstart[Util::$LCparams][Vcalendar::TZID] )
            ? [ Vcalendar::TZID => $this->dtstart[Util::$LCparams][Vcalendar::TZID] ]
            : [];
    }

    /**
     * Set calendar component property dtstart
     *
     * @param string|DateTimeInterface  $value
     * @param array  $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setDtstart( $value = null, $params = [] )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::DTSTART );
            $this->dtstart = [
                Util::$LCvalue  => Util::$SP0,
                Util::$LCparams => [],
            ];
            return $this;
        }
        $compType = $this->getCompType();
        $params   = ParameterFactory::setParams(
            $params,
            DateTimeFactory::$DEFAULTVALUEDATETIME
        );
        if( Util::isCompInList( $compType, self::$TZCOMPS )) {
            $params[Util::$ISLOCALTIME] = true;
            $params[Vcalendar::VALUE]   = Vcalendar::DATE_TIME;
        }
        $this->dtstart = DateTimeFactory::setDate(
            $value,
            $params,
            ( Vcalendar::VFREEBUSY == $compType ) // $forceUTC
        );
        return $this;
    }
}
