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

use function array_change_key_case;

/**
 * DTSTAMP property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.16 2020-01-24
 */
trait DTSTAMPtrait
{
    /**
     * @var array component property DTSTAMP value
     */
    protected $dtstamp = null;

    /**
     * Return formatted output for calendar component property dtstamp
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.29.1 2019-06-22
     */
    public function createDtstamp()
    {
        if( empty( $this->dtstamp[Util::$LCvalue] ))
        {
            $this->dtstamp = [
                Util::$LCvalue  => DateTimeFactory::factory( null, self::UTC ),
                Util::$LCparams => [],
            ];
        }
        return StringFactory::createElement(
            self::DTSTAMP,
            ParameterFactory::createParams( $this->dtstamp[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str( $this->dtstamp[Util::$LCvalue] )
        );
    }

    /**
     * Delete calendar component property dtstamp
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteDtstamp()
    {
        $this->dtstamp = null;
        return true;
    }

    /**
     * Return calendar component property dtstamp
     *
     * @param bool   $inclParam
     * @return bool|DateTime|array
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.29.1 2019-06-22
     */
    public function getDtstamp( $inclParam = false )
    {
        if( Util::isCompInList( $this->getCompType(), self::$SUBCOMPS )) {
            return false;
        }
        if( empty( $this->dtstamp )) {
            $this->dtstamp = [
                Util::$LCvalue  => DateTimeFactory::factory( null, self::UTC ),
                Util::$LCparams => [],
            ];
        }
        return ( $inclParam ) ? $this->dtstamp : $this->dtstamp[Util::$LCvalue];
    }

    /**
     * Set calendar component property dtstamp
     *
     * @param string|DateTimeInterface  $value
     * @param array  $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.29.16 2020-01-24
     */
    public function setDtstamp( $value  = null, $params = [] )
    {
        if( empty( $value )) {
            $this->dtstamp = [
                Util::$LCvalue  => DateTimeFactory::factory( null, self::UTC ),
                Util::$LCparams => [],
            ];
            return $this;
        }
        $params = array_change_key_case( $params, CASE_UPPER );
        $params[Vcalendar::VALUE] = Vcalendar::DATE_TIME;
        $this->dtstamp = DateTimeFactory::setDate( $value, $params, true ); // $forceUTC
        return $this;
    }
}
