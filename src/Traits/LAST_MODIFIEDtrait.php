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
use Kigkonsult\Icalcreator\Formatter\Property\DtxProperty;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;

/**
 * LAST-MODIFIED property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait LAST_MODIFIEDtrait
{
    /**
     * @var null|Pc component property LAST-MODIFIED value
     */
    protected ? Pc $lastmodified = null;

    /**
     * Return formatted output for calendar component property last-modified
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.55 - 2022-08-13
     */
    public function createLastmodified() : string
    {
        return  DtxProperty::format(
            self::LAST_MODIFIED,
            $this->lastmodified,
            $this->getConfig( self::ALLOWEMPTY )
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
     * @return bool|string|DateTime|Pc
     * @since 2.29.9 2019-08-05
     */
    public function getLastmodified( ? bool $inclParam = false ) : DateTime | bool | string | Pc
    {
        if( empty( $this->lastmodified )) {
            return false;
        }
        return $inclParam ? clone $this->lastmodified : $this->lastmodified->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isLastmodifiedSet() : bool
    {
        return ! empty( $this->lastmodified->value );
    }

    /**
     * Set calendar component property last-modified
     *
     * @param null|string|Pc|DateTimeInterface  $value
     * @param null|array $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setLastmodified(
        null | string | Pc | DateTimeInterface $value = null,
        ? array $params = []
    ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, $params );
        $value->addParamValue(self::DATE_TIME ); // req
        $this->lastmodified = empty( $value->value )
            ? $value->setValue( DateTimeFactory::factory( null, self::UTC ))
                ->removeParam( self::VALUE )
            : DateTimeFactory::setDate( $value, true );
        return $this;
    }
}
