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
 * CREATED property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.16 2020-01-24
 */
trait CREATEDtrait
{
    /**
     * @var array component property CREATED value
     */
    protected $created = null;

    /**
     * Return formatted output for calendar component property created
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.1 2019-06-22
     */
    public function createCreated()
    {
        if( empty( $this->created )) {
            return null;
        }
        return StringFactory::createElement(
            self::CREATED,
            ParameterFactory::createParams( $this->created[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str( $this->created[Util::$LCvalue] )
        );
    }

    /**
     * Delete calendar component property created
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteCreated()
    {
        $this->created = null;
        return true;
    }

    /**
     * Return calendar component property created
     *
     * @param bool   $inclParam
     * @return bool|DateTime|array
     * @since  2.27.14 - 2019-01-27
     */
    public function getCreated( $inclParam = false )
    {
        if( empty( $this->created )) {
            return false;
        }
        return ( $inclParam ) ? $this->created : $this->created[Util::$LCvalue];
    }

    /**
     * Set calendar component property created
     *
     * @param string|DateTimeInterface $value
     * @param mixed  $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setCreated(  $value  = null, $params = [] )
    {
        if( empty( $value )) {
            $this->created = [
                Util::$LCvalue  => DateTimeFactory::factory( null, self::UTC ),
                Util::$LCparams => [],
            ];
            return $this;
        }
        $params = array_change_key_case( $params, CASE_UPPER );
        $params[Vcalendar::VALUE] = Vcalendar::DATE_TIME;
        $this->created = DateTimeFactory::setDate( $value, $params, true ); // $forceUTC
        return $this;
    }
}
