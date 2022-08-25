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
use Kigkonsult\Icalcreator\Formatter\Property\Exdate;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\RexdateFactory;

/**
 * EXDATE property functions
 *
 * @since 2.41.55 - 2022-08-13
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
        return Exdate::format(
            self::EXDATE,
            $this->exdate ?? [],
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
     * @since 2.41.46 2022-04-27
     */
    public function getExdate( ? int $propIx = null, ? bool $inclParam = false ) : bool | string | array | Pc
    {
        if( empty( $this->exdate )) {
            unset( $this->propIx[self::EXDATE] );
            return false;
        }
        $output = self::getMvalProperty(
            $this->exdate,
            self::EXDATE,
            $this,
            $propIx,
            $inclParam
        );
        return empty( $output ) ? false : $output;
    }

    /**
     * Return array, all calendar component property exdate
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllExdate( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->exdate, $inclParam );
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
     * @param null|string|Pc|array|DateTimeInterface|DateTimeInterface[] $value
     * @param null|int|array $params
     * @param null|int          $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.46 2022-04-27
     */
    public function setExdate(
        null|string|array|DateTimeInterface|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value        = self::marshallInputMval( $value, $params, $index );
        $value->value = self::checkSingleExdates( $value->value );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::EXDATE );
            $value->setEmpty();
        }
        else {
            $value = RexdateFactory::prepInputExdate( $value );
        }
        self::setMval( $this->exdate, $value, $index );
        return $this;
    }

    /**
     * Return $value as array of single (date) inputs
     *
     * Accepts only (array) DateTimeInterface/string-date OR empty
     *
     * @param null|string|DateTimeInterface|DateTimeInterface[]|string[] $value
     * @return array
     * @throws InvalidArgumentException
     * @since 2.41.57 2022-08-18
     */
    private static function checkSingleExdates( null|string|array|DateTimeInterface $value ) : array
    {
        if( empty( $value )) {
            return [];
        }
        if(( $value instanceof DateTimeInterface ) || DateTimeFactory::isStringAndDate( $value )) {
            return [ $value ];
        }
        if( is_array( $value )) {
            $output = [];
            foreach( $value as $x => $value2 ) {
                if( empty( $value2 )) {
                    continue;
                }
                if(( $value2 instanceof DateTimeInterface ) ||
                    DateTimeFactory::isStringAndDate( $value2 )) {
                    $output[] = $value2;
                    continue;
                }
                throw new InvalidArgumentException(
                    sprintf( RexdateFactory::$REXDATEERR, self::EXDATE, $x, var_export( $value2, true )
                    )
                );
            }
            return $output;
        }
        throw new InvalidArgumentException(
            sprintf( RexdateFactory::$REXDATEERR,self::EXDATE, 10, var_export( $value, true ))
        );
    }
}
