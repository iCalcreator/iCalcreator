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
 * DTSTAMP property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait DTSTAMPtrait
{
    /**
     * @var null|Pc component property DTSTAMP value
     */
    protected ? Pc $dtstamp = null;

    /**
     * Return formatted output for calendar component property dtstamp
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.55 - 2022-08-13
     */
    public function createDtstamp() : string
    {
        return  DtxProperty::format(
            self::DTSTAMP,
            $this->dtstamp,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Return calendar component property dtstamp
     *
     * @param bool   $inclParam
     * @return DateTime|Pc
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.53 2022-08-11
     */
    public function getDtstamp( ? bool $inclParam = false ) : DateTime | Pc
    {
        return $inclParam ? clone $this->dtstamp : $this->dtstamp->value;
    }

    /**
     * Return bool true
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isDtstampSet() : bool
    {
        return true;
    }

    /**
     * Set calendar component property dtstamp
     *
     * @param null|string|Pc|DateTimeInterface $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public function setDtstamp( null|string|DateTimeInterface|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        $value->addParamValue( self::DATE_TIME ); // req
        $this->dtstamp = empty( $value->value )
            ? $value->setValue( self::getUtcDateTimePc()->value )
                ->removeParam( self::VALUE )
            : DateTimeFactory::setDate( $value, true );
        return $this;
    }

    /**
     * @return Pc
     * @throws Exception
     */
    protected static function getUtcDateTimePc() : Pc
    {
        return Pc::factory( DateTimeFactory::factory( null, self::UTC ));
    }
}
