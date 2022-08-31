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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Requeststatus;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

use function number_format;
use function filter_var;
use function sprintf;
use function var_export;

/**
 * REQUEST-STATUS property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait REQUEST_STATUStrait
{
    /**
     * @var null|Pc[] component property REQUEST-STATUS value
     */
    protected ? array $requeststatus = null;

    /**
     * Return formatted output for calendar component property request-status
     *
     * @return string
     * @since 2.41.55 - 2022-08-13
     */
    public function createRequeststatus() : string
    {
        return Requeststatus::format(
            self::REQUEST_STATUS,
            $this->requeststatus ?? [],
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property request-status
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteRequeststatus( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->requeststatus )) {
            unset( $this->propDelIx[self::REQUEST_STATUS] );
            return false;
        }
        return self::deletePropertyM(
            $this->requeststatus,
            self::REQUEST_STATUS,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property request-status
     *
     * @param null|int    $propIx specific property in case of multiply occurrence
     * @param null|bool   $inclParam
     * @return bool|array|Pc
     * @since 2.41.40 2022-04-15
     */
    public function getRequeststatus( ? int $propIx = null, ? bool $inclParam = false ) : bool | array | Pc
    {
        if( empty( $this->requeststatus )) {
            unset( $this->propIx[self::REQUEST_STATUS] );
            return false;
        }
        return self::getMvalProperty(
            $this->requeststatus,
            self::REQUEST_STATUS,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return array, all calendar component property requeststatus
     *
     * @param null|bool   $inclParam
     * @return array|Pc[]
     * @since 2.41.58 2022-08-24
     */
    public function getAllRequeststatus( ? bool $inclParam = false ) : array
    {
        return self::getMvalProperties( $this->requeststatus, $inclParam );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isRequeststatusSet() : bool
    {
        return self::isMvalSet( $this->requeststatus );
    }

    /**
     * Set calendar component property request-status
     *
     * Empty statCode/test not allowed
     *
     * @param null|int|float|string|Pc $statCode 1*DIGIT 1*2("." 1*DIGIT)
     * @param null|int|string    $text
     * @param null|string    $extData
     * @param null|array $params
     * @param null|int       $index
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setRequeststatus(
        null|int|float|string|Pc $statCode = null,
        null|int|string $text = null,
        ? string $extData = null,
        ? array $params = [],
        ? int $index = null
    ) : static
    {
        static $ERR = 'Invalid %s status code value %s';
        if( $statCode instanceof Pc ) {
            $index    = ( null !== $text ) ? (int) $text : null;
            $params   = (array) $statCode->getParams();
            $extData  = $statCode->value[self::EXTDATA]  ?? self::$SP0;
            $text     = $statCode->value[self::STATDESC] ?? self::$SP0;
            $statCode = $statCode->value[self::STATCODE] ?? null;
        }
        if( empty( $statCode ) || empty( $text )) {
            $this->assertEmptyValue( self::$SP0, self::REQUEST_STATUS );
            $statCode = null;
            $text     = self::$SP0;
            $params   = [];
        }
        else {
            if( false === filter_var( $statCode, FILTER_VALIDATE_FLOAT )) {
                throw new InvalidArgumentException(
                    sprintf( $ERR, self::REQUEST_STATUS, var_export( $statCode, true ) )
                );
            }
            Util::assertString( $text, self::REQUEST_STATUS );
        }
        $input = [
            self::STATCODE => number_format((float) $statCode, 2, Util::$DOT, null ),
            self::STATDESC => StringFactory::trimTrailNL( $text ),
        ];
        if(( ! empty( $statCode ) || empty( $text )) && ! empty( $extData )) {
            Util::assertString( $extData, self::REQUEST_STATUS );
            $input[self::EXTDATA] = StringFactory::trimTrailNL( $extData );
        }
        self::setMval(
            $this->requeststatus,
            Pc::factory( $input, ParameterFactory::setParams( $params )),
            $index
        );
        return $this;
    }
}
