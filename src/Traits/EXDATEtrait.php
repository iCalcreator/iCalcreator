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

use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\RexdateFactory;

/**
 * EXDATE property functions
 *
 * @since 2.41.36 2022-04-09
 */
trait EXDATEtrait
{
    /**
     * @var null|Pc[] component property EXDATE value
     */
    protected ? array $exdate = null;

    /**
     * Return formatted output for calendar component property exdate
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function createExdate() : string
    {
        if( empty( $this->exdate )) {
            return self::$SP0;
        }
        return RexdateFactory::formatExdate(
            $this->exdate,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property exdate
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteExdate( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->exdate )) {
            unset( $this->propDelIx[self::EXDATE] );
            return false;
        }
        return self::deletePropertyM(
            $this->exdate,
            self::EXDATE,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property exdate
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|string|array|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getExdate( ? int $propIx = null, ? bool $inclParam = false ) : bool | string | array | Pc
    {
        if( empty( $this->exdate )) {
            unset( $this->propIx[self::EXDATE] );
            return false;
        }
        return self::getMvalProperty(
            $this->exdate,
            self::EXDATE,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isExdateSet() : bool
    {
        return self::isMvalSet( $this->exdate );
    }

    /**
     * Set calendar component property exdate
     *
     * @param null|string|Pc|mixed[]|DateTimeInterface|DateTimeInterface[] $value
     * @param null|int|mixed[]  $params
     * @param null|int          $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-09
     */
    public function setExdate(
        null|string|array|DateTimeInterface|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value ) ||
            ( is_array( $value->value ) && ( 1 === count( $value->value )) && empty( reset( $value->value )))) {
            $this->assertEmptyValue( $value->value, self::EXDATE );
            $value->setEmpty();
        }
        else {
            $value->value = self::checkSingleExdates( $value->value );
            $value = RexdateFactory::prepInputExdate( $value );
        }
        self::setMval( $this->exdate, $value, $index );
        return $this;
    }

    /**
     * Return $value is single input
     *
     * @param DateTimeInterface|string|DateTimeInterface[]|string[] $value
     * @return string|mixed[]
     * @since 2.29.16 2020-01-24
     */
    private static function checkSingleExdates( array | DateTimeInterface | string $value ) : string|array
    {
        if( $value instanceof DateTimeInterface ) {
            return [ $value ];
        }
        if( DateTimeFactory::isStringAndDate( $value )) {
            return [ $value ];
        }
        return $value;
    }
}
