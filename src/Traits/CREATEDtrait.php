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
use Kigkonsult\Icalcreator\Util\ParameterFactory;

/**
 * CREATED property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait CREATEDtrait
{
    /**
     * @var null|Pc component property CREATED value
     */
    protected ? Pc $created = null;

    /**
     * Return formatted output for calendar component property created
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.55 - 2022-08-13
     */
    public function createCreated() : string
    {
        return  DtxProperty::format(
            self::CREATED,
            $this->created,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property created
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteCreated() : bool
    {
        $this->created = null;
        return true;
    }

    /**
     * Return calendar component property created
     *
     * @param null|bool   $inclParam
     * @return bool|string|DateTime|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getCreated( ? bool $inclParam = false ) : DateTime | bool | string | Pc
    {
        if( empty( $this->created )) {
            return false;
        }
        return $inclParam ? clone $this->created : $this->created->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isCreatedSet() : bool
    {
        return ! empty( $this->created->value );
    }

    /**
     * Set calendar component property created, 'now' in UTC if empty
     *
     * @param null|string|Pc|DateTimeInterface $value
     * @param null|array $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setCreated( null|string|DateTimeInterface|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        $value->addParamValue( self::DATE_TIME ); // req
        $this->created = empty( $value->value )
            ? $value->setValue( self::getUtcDateTimePc()->value )
                ->removeParam( self::VALUE )
            : DateTimeFactory::setDate( $value, true );
        return $this;
    }
}
