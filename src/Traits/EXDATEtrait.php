<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

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
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
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
    public function createExdate()
    {
        if( empty( $this->exdate )) {
            return null;
        }
        return RexdateFactory::formatExdate(
            $this->exdate,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property exdate
     *
     * @param int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteExdate( $propDelIx = null )
    {
        if( empty( $this->exdate )) {
            unset( $this->propDelIx[self::EXDATE] );
            return false;
        }
        return $this->deletePropertyM( $this->exdate, self::EXDATE, $propDelIx );
    }

    /**
     * Get calendar component property exdate
     *
     * @param int    $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getExdate( $propIx = null, $inclParam = false )
    {
        if( empty( $this->exdate )) {
            unset( $this->propIx[self::EXDATE] );
            return false;
        }
        return $this->getPropertyM( $this->exdate, self::EXDATE, $propIx, $inclParam );
    }

    /**
     * Set calendar component property exdate
     *
     * @param string|string[]|DateTimeInterface|DateTimeInterface[] $value
     * @param array   $params
     * @param integer $index
     * @return static
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.29.16 2020-01-24
     */
    public function setExdate( $value = null, $params = [], $index = null )
    {
        if( empty( $value ) ||
            ( is_array( $value) && ( 1 == count( $value )) && empty( reset( $value )))
        ) {
            $this->assertEmptyValue( $value, self::EXDATE );
            $this->setMval( $this->exdate, Util::$SP0, [], null, $index );
            return $this;
        }
        $value = self::checkSingleExdates( $value );
        $input = RexdateFactory::prepInputExdate( $value, $params );
        $this->setMval(
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
     * @return array
     * @since 2.29.16 2020-01-24
     */
    private static function checkSingleExdates( $value )
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
