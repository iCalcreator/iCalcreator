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
 * COMPLETED property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait COMPLETEDtrait
{
    /**
     * @var null|Pc component property COMPLETED value
     */
    protected ? Pc $completed = null;

    /**
     * Return formatted output for calendar component property completed
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.55 - 2022-08-13
     */
    public function createCompleted() : string
    {
        return  DtxProperty::format(
            self::COMPLETED,
            $this->completed,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property completed
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteCompleted() : bool
    {
        $this->completed = null;
        return true;
    }

    /**
     * Return calendar component property completed
     *
     * @param null|bool  $inclParam
     * @return bool|string|DateTime|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getCompleted( ? bool $inclParam = false ) : DateTime | bool | string | Pc
    {
        if( empty( $this->completed )) {
            return false;
        }
        return $inclParam ? clone $this->completed : $this->completed->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isCompletedSet() : bool
    {
        return ! empty( $this->completed->value );
    }

    /**
     * Set calendar component property completed
     *
     * @param null|string|Pc|DateTimeInterface $value
     * @param null|array $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setCompleted( null|string|DateTimeInterface|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::COMPLETED );
            $value->setEmpty();
        }
        else {
            $value->addParamValue( self::DATE_TIME ); // req.
            $value = DateTimeFactory::setDate( $value, true ); // $forceUTC

        }
        $this->completed = $value;
        return $this;
    }
}
