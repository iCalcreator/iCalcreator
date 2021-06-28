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
use Kigkonsult\Icalcreator\Util\RexdateFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * EXDATE property functions
 *
 * @since 2.29.2 2019-06-23
 */
trait EXDATEtrait
{
    /**
     * @var array component property EXDATE value
     */
    protected $exdate = null;

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
            return Util::$SP0;
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
    public function deleteExdate( $propDelIx = null ) : bool
    {
        if( empty( $this->exdate )) {
            unset( $this->propDelIx[self::EXDATE] );
            return false;
        }
        return  self::deletePropertyM(
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
     * @return bool|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getExdate( $propIx = null, $inclParam = false )
    {
        if( empty( $this->exdate )) {
            unset( $this->propIx[self::EXDATE] );
            return false;
        }
        return self::getPropertyM(
            $this->exdate,
            self::EXDATE,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property exdate
     *
     * @param null|string|string[]|DateTimeInterface|DateTimeInterface[] $value
     * @param null|array   $params
     * @param null|integer $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setExdate( $value = null, $params = [], $index = null ) : self
    {
        if( empty( $value ) ||
            ( is_array( $value) && ( 1 == count( $value )) && empty( reset( $value )))
        ) {
            $this->assertEmptyValue( $value, self::EXDATE );
             self::setMval( $this->exdate, Util::$SP0, [], null, $index );
            return $this;
        }
        $value = self::checkSingleExdates( $value );
        $input = RexdateFactory::prepInputExdate( $value, $params );
         self::setMval(
            $this->exdate,
            $input[Util::$LCvalue],
            $input[Util::$LCparams],
            null,
            $index
        );
        return $this;
    }

    /**
     * Return $value is single input
     *
     * @param string|string[]|DateTimeInterface|DateTimeInterface[] $value
     * @return mixed
     * @since 2.29.16 2020-01-24
     */
    private static function checkSingleExdates( $value ) : array
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
