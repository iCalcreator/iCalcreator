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
 * LAST-MODIFIED property functions
 *
 * @since 2.40.11 2022-01-15
 */
trait LAST_MODIFIEDtrait
{
    /**
     * @var null|mixed[] component property LAST-MODIFIED value
     */
    protected ? array $lastmodified = null;

    /**
     * Return formatted output for calendar component property last-modified
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.9 2019-08-05
     */
    public function createLastmodified() : string
    {
        if( empty( $this->lastmodified )) {
            return Util::$SP0;
        }
        return StringFactory::createElement(
            self::LAST_MODIFIED,
            ParameterFactory::createParams( $this->lastmodified[Util::$LCparams] ),
            DateTimeFactory::dateTime2Str( $this->lastmodified[Util::$LCvalue] )
        );
    }

    /**
     * Delete calendar component property lastmodified
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteLastmodified() : bool
    {
        $this->lastmodified = null;
        return true;
    }

    /**
     * Return calendar component property last-modified
     *
     * @param null|bool   $inclParam
     * @return bool|string|DateTime|mixed[]
     * @since 2.29.9 2019-08-05
     */
    public function getLastmodified( ? bool $inclParam = false ) : DateTime | bool | string | array
    {
        if( empty( $this->lastmodified )) {
            return false;
        }
        return $inclParam
            ? $this->lastmodified
            : $this->lastmodified[Util::$LCvalue];
    }

    /**
     * Set calendar component property last-modified
     *
     * @param null|string|DateTimeInterface  $value
     * @param null|mixed[]   $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setLastmodified( DateTimeInterface | string | null $value = null, ? array $params = [] ) : static
    {
        if( empty( $value )) {
            $this->lastmodified = [
                Util::$LCvalue  => DateTimeFactory::factory( null, self::UTC ),
                Util::$LCparams => [],
            ];
            return $this;
        }
        $params = array_change_key_case( $params ?? [], CASE_UPPER );
        $params[IcalInterface::VALUE] = IcalInterface::DATE_TIME;
        $this->lastmodified = DateTimeFactory::setDate( $value, $params, true ); // $forceUTC
        return $this;
    }
}
