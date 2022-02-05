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

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

use function array_change_key_case;

/**
 * ACKNOWLEDGED property functions
 *
 * @since 2.41.2 2022-01-16
 */
trait ACKNOWLEDGEDrfc9074trait
{
    /**
     * @var null|mixed[] component property ACKNOWLEDGED value
     */
    protected ? array $acknowledged = null;

    /**
     * Return formatted output for calendar component property acknowledged
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function createAcknowledged() : string
    {
        if( empty( $this->acknowledged )) {
            return Util::$SP0;
        }
        return StringFactory::createElement(
            self::ACKNOWLEDGED,
            ParameterFactory::createParams( $this->acknowledged[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str( $this->acknowledged[Util::$LCvalue] )
        );
    }

    /**
     * Delete calendar component property acknowledged
     *
     * @return bool
     */
    public function deleteAcknowledged() : bool
    {
        $this->acknowledged = null;
        return true;
    }

    /**
     * Return calendar component property acknowledged
     *
     * @param null|bool   $inclParam
     * @return bool|string|DateTime|mixed[]
     */
    public function getAcknowledged( ? bool $inclParam = false ) : DateTime | bool | string | array
    {
        if( empty( $this->acknowledged )) {
            return false;
        }
        return $inclParam
            ? $this->acknowledged
            : $this->acknowledged[Util::$LCvalue];
    }

    /**
     * Set calendar component property acknowledged, if empty: 'now'
     *
     * @param null|string|DateTimeInterface  $value
     * @param null|mixed[]   $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setAcknowledged( DateTimeInterface | string | null $value = null, ? array $params = [] ) : static
    {
        if( empty( $value )) {
            $this->acknowledged = [
                Util::$LCvalue  => DateTimeFactory::factory( null, self::UTC ),
                Util::$LCparams => [],
            ];
            return $this;
        }
        $params = array_change_key_case( $params ?? [], CASE_UPPER );
        $params[IcalInterface::VALUE] = IcalInterface::DATE_TIME;
        $this->acknowledged = DateTimeFactory::setDate( $value, $params, true ); // $forceUTC
        return $this;
    }
}
