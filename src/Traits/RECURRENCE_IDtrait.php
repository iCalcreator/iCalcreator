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
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

/**
 * RECURRENCE-ID property functions
 *
 * @since 2.41.36 2022-04-03
 */
trait RECURRENCE_IDtrait
{
    /**
     * @var null|Pc component property RECURRENCE_ID value
     */
    protected ? Pc $recurrenceid = null;

    /**
     * Return formatted output for calendar component property recurrence-id
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function createRecurrenceid() : string
    {
        if( empty( $this->recurrenceid )) {
            return self::$SP0;
        }
        if( empty( $this->recurrenceid->value )) {
            return $this->createSinglePropEmpty( self::RECURRENCE_ID );
        }
        return StringFactory::createElement(
            self::RECURRENCE_ID,
            ParameterFactory::createParams( $this->recurrenceid->params ),
            DateTimeFactory::dateTime2Str(
                $this->recurrenceid->value,
                $this->recurrenceid->hasParamValue( self::DATE ),
                $this->recurrenceid->hasParamKey( Util::$ISLOCALTIME )
            )
        );
    }

    /**
     * Delete calendar component property recurrence-id
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteRecurrenceid() : bool
    {
        $this->recurrenceid = null;
        return true;
    }

    /**
     * Return calendar component property recurrence-id
     *
     * @param null|bool   $inclParam
     * @return bool|string|DateTime|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getRecurrenceid( ? bool $inclParam = false ) : bool | string | DateTime | Pc
    {
        if( empty( $this->recurrenceid )) {
            return false;
        }
        return $inclParam ? clone $this->recurrenceid : $this->recurrenceid->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isRecurrenceidSet() : bool
    {
        return ! empty( $this->recurrenceid->value );
    }

    /**
     * Set calendar component property recurrence-id
     *
     * @param null|string|Pc|DateTimeInterface $value
     * @param null|mixed[]  $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setRecurrenceid(
        null|string|DateTimeInterface|Pc $value = null,
        ? array $params = []
    ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::RECURRENCE_ID );
            $this->recurrenceid = $value->setEmpty();
        }
        else {
            $value->addParamValue( self::DATE_TIME, false ); // default
            $this->recurrenceid = DateTimeFactory::setDate( $value );
        }
        return $this;
    }
}
