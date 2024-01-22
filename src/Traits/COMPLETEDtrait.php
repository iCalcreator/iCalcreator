<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2024 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
 * COMPLETED property functions
 *
 * @since 2.41.85 2024-01-18
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
     * @since 2.41.85 2024-01-18
     */
    public function getCompleted( ? bool $inclParam = false ) : DateTime | bool | string | Pc
    {
        if( empty( $this->completed )) {
            return false;
        }
        return $inclParam ? clone $this->completed : $this->completed->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isCompletedSet() : bool
    {
        return self::isPropSet( $this->completed );
    }

    /**
     * Set calendar component property completed
     *
     * @param null|string|Pc|DateTimeInterface $value
     * @param null|mixed[] $params
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setCompleted( null|string|DateTimeInterface|Pc $value = null, ? array $params = [] ) : static
    {
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::COMPLETED );
            $pc->setEmpty();
        }
        else {
            $pc->addParamValue( self::DATE_TIME ); // req.
            $pc = DateTimeFactory::setDate( $pc, true ); // $forceUTC

        }
        $this->completed = $pc;
        return $this;
    }
}
