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

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

/**
 * LOCATION property functions
 *
 * @since 2.41.4 2022-01-20
 */
trait LOCATIONtrait
{
    /**
     * @var null|mixed[] component property LOCATION value
     */
    protected ? array $location = null;

    /**
     * Return formatted output for calendar component property location
     *
     * @return string
     */
    public function createLocation() : string
    {
        if( empty( $this->location )) {
            return Util::$SP0;
        }
        $output = Util::$SP0;
        $lang   = $this->getConfig( self::LANGUAGE );
        foreach( $this->location as $locationPart ) {
            if( empty( $locationPart[Util::$LCvalue] ) ) {
                $output .= $this->getConfig( self::ALLOWEMPTY )
                    ? StringFactory::createElement( self::LOCATION )
                    : Util::$SP0;
                continue;
            }
            $output .= StringFactory::createElement(
                self::LOCATION,
                ParameterFactory::createParams( $locationPart[Util::$LCparams], self::$ALTRPLANGARR, $lang ),
                StringFactory::strrep( $locationPart[Util::$LCvalue] )
            ); // end foreach
        }
        return $output;
    }

    /**
     * Delete calendar component property location
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since 2.41.4 2022-01-20
     */
    public function deleteLocation( ? int $propDelIx = null ) : bool
    {
        $this->location = null;
        if( empty( $this->location )) {
            unset( $this->propDelIx[self::LOCATION] );
            return false;
        }
        if( self::PARTICIPANT !== $this->getCompType()) {
            $propDelIx = null;
        }
        return  self::deletePropertyM(
            $this->location,
            self::LOCATION,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property location
     *
     * @param null|bool|int   $propIx specific property in case of multiply occurrence
     * @param null|bool  $inclParam
     * @return bool|string|mixed[]
     * @since 2.41.4 2022-01-20
     */
    public function getLocation( null|bool|int $propIx = null, ? bool $inclParam = false ) : array | bool | string
    {
        if( empty( $this->location )) {
            unset( $this->propIx[self::LOCATION] );
            return false;
        }
        if( self::PARTICIPANT !== $this->getCompType()) {
            if( is_bool( $propIx )) {
                $inclParam = $propIx;
            }
//          $propIx = null;
            $propIx = 1;
        }
        return self::getPropertyM(
            $this->location,
            self::LOCATION,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Set calendar component property location
     *
     * @param null|string   $value
     * @param null|mixed[]  $params
     * @param null|int      $index  if NOT comp PARTICIPANT : 1
     * @return static
     * @since 2.41.4 2022-01-18
     */
    public function setLocation( ? string $value = null, ? array $params = [], ? int $index = null ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::LOCATION );
            $value  = Util::$SP0;
            $params = [];
        }
        if( self::PARTICIPANT !== $this->getCompType()) {
            $index = 1;
        }
        self::setMval( $this->location, $value, $params, null, $index );
        return $this;
    }
}
