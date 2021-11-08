<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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

use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\RexdateFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;

use function count;
use function is_array;
use function reset;

/**
 * RDATE property functions
 *
 * @since 2.29.2 2019-06-23
 */
trait RDATEtrait
{
    /**
     * @var null|array component property RDATE value
     */
    protected ?array $rdate = null;

    /**
     * Return formatted output for calendar component property rdate
     *
     * @return string
     * @throws Exception
     */
    public function createRdate() : string
    {
        if( empty( $this->rdate )) {
            return Util::$SP0;
        }
        return RexdateFactory::formatRdate(
            $this->rdate,
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getCompType()
        );
    }

    /**
     * Delete calendar component property rdate
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteRdate( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->rdate )) {
            unset( $this->propDelIx[self::RDATE] );
            return false;
        }
        return  self::deletePropertyM(
            $this->rdate,
            self::RDATE,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property rdate
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return string|array|bool
     * @throws Exception
     * @since 2.40 2021-10-04
     */
    public function getRdate( ?int $propIx = null, ?bool $inclParam = false ) : array | string | bool
    {
        if( empty( $this->rdate )) {
            unset( $this->propIx[self::RDATE] );
            return false;
        }
        $output =  self::getPropertyM(
            $this->rdate,
            self::RDATE,
            $this,
            $propIx,
            $inclParam
        );
        if( empty( $output )) {
            return false;
        }
        return $output;
    }

    /**
     * Set calendar component property rdate
     *
     * @param null|string|array|DateTimeInterface $value
     * @param null|string[]   $params
     * @param null|integer $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.2 2019-06-23
     */
    public function setRdate(
        null|string|array|DateTimeInterface $value = null,
        ? array $params = [],
        ? int $index = null
    ) : static
    {
        if( empty( $value ) ||
            ( is_array( $value) && ( 1 === count( $value )) && empty( reset( $value )))
        ) {
            $this->assertEmptyValue( $value, self::RDATE );
            self::setMval( $this->rdate, Util::$SP0, [], null, $index );
            return $this;
        }
        $params = $params ?? [];
        $value  = self::checkSingleRdates(
            $value,
            ParameterFactory::isParamsValueSet(
                [ Util::$LCparams => $params ],
                self::PERIOD
            )
        );
        if( Util::isCompInList( $this->getCompType(), Vcalendar::$TZCOMPS )) {
            $params[Util::$ISLOCALTIME] = true;
        }
        $input = RexdateFactory::prepInputRdate( $value, $params );
        self::setMval(
            $this->rdate,
            $input[Util::$LCvalue],
            $input[Util::$LCparams],
            null,
            $index
        );
        return $this;
    }

    /**
     * Return Rdates is single input
     *
     * @param string|array|DateTimeInterface $rDates
     * @param bool $isPeriod
     * @return array
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    private static function checkSingleRdates( string|array|DateTimeInterface $rDates, bool $isPeriod ) : array
    {
        if( $rDates instanceof DateTimeInterface ) {
            return [ DateTimeFactory::toDateTime( $rDates ) ];
        }
        if( DateTimeFactory::isStringAndDate( $rDates )) {
            return [ $rDates ];
        }
        if( $isPeriod && is_array( $rDates ) && ( 2 === count( $rDates ))) {
            $first = reset( $rDates );
            if( $first instanceof DateTimeInterface ) {
                return [ $rDates ];
            }
            if( DateTimeFactory::isStringAndDate( $first )) {
                return [ $rDates ];
            }
        }
        return $rDates;
    }
}
