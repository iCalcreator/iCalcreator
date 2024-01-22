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

use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Rdate;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\RexdateFactory;
use Kigkonsult\Icalcreator\Vcalendar;

use function count;
use function is_array;
use function reset;

/**
 * RDATE property functions
 *
 * @since 2.41.85 2024-01-18
 */
trait RDATEtrait
{
    /**
     * @var null|Pc[] component property RDATE value
     */
    protected ? array $rdate = null;

    /**
     * Return formatted output for calendar component property rdate
     *
     * @return string
     * @throws Exception
     * @since 2.41.68 2022-10-03
     */
    public function createRdate() : string
    {
        return Rdate::format(
            self::RDATE,
            $this->rdate ?? [],
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
        return self::deletePropertyM(
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
     * @return bool|string|array|Pc
     * @throws Exception
     * @since 2.41.44 2022-04-27
     */
    public function getRdate( ? int $propIx = null, ? bool $inclParam = false ) : bool | string | array | Pc
    {
        if( empty( $this->rdate )) {
            unset( $this->propIx[self::RDATE] );
            return false;
        }
        $output = self::getMvalProperty(
            $this->rdate,
            self::RDATE,
            $this,
            $propIx,
            $inclParam
        );
        return empty( $output ) ? false : $output;
    }

    /**
     * Return array, all calendar component property rdate
     *
     * @param null|bool   $inclParam
     * @return Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllRdate( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->rdate, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isRdateSet() : bool
    {
        return self::isMvalSet( $this->rdate );
    }

    /**
     * Set calendar component property rdate
     *
     * @param null|string|Pc|array|DateTimeInterface $value
     * @param null|int|array $params
     * @param null|int         $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setRdate(
        null|string|array|DateTimeInterface|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $pc      = self::marshallInputMval( $value, $params, $index );
        $pcValue = $pc->getValue();
        if( empty( $pcValue ) ||
            ( is_array( $pcValue ) && ( 1 === count( $pcValue )) && empty( reset( $pcValue )))) {
            $this->assertEmptyValue( $pcValue, self::RDATE );
            self::setMval( $this->rdate, $pc->setEmpty(), $index );
            return $this;
        }
        $pc->setValue( self::checkSingleRdates( $pcValue, $pc->hasParamValue( self::PERIOD )));
        if( Vcalendar::isTzComp( $this->getCompType() )) {
            $pc->addParam( self::ISLOCALTIME, true );
        }
        self::setMval( $this->rdate, RexdateFactory::prepInputRdate( $pc ), $index );
        return $this;
    }

    /**
     * Return Rdates is single input
     *
     * @param string|array|DateTimeInterface $rDates
     * @param bool $isPeriod
     * @return string|array
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.57 2022-08-57
     */
    private static function checkSingleRdates( string|array|DateTimeInterface $rDates, bool $isPeriod ) : string|array
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
            if( DateTimeFactory::isStringAndDate( $first )){
                return [ $rDates ];
            }
        }
        return $rDates;
    }
}
