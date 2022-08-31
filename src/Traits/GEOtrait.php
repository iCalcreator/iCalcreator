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

use Kigkonsult\Icalcreator\Formatter\Property\Geo;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\GeoFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;

/**
 * GEO property functions
 *
 * @since 2.41.62 2022-08-28
 */
trait GEOtrait
{
    /**
     * @var null|Pc component property GEO value
     */
    protected ? Pc $geo = null;

    /**
     * Return formatted output for calendar component property geo
     *
     * @return string
     */
    public function createGeo() : string
    {
        return Geo::format(
            self::GEO,
            $this->geo,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property geo
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteGeo() : bool
    {
        $this->geo = null;
        return true;
    }

    /**
     * Get calendar component property geo
     *
     * @param null|bool   $inclParam
     * @return bool|array|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getGeo( ? bool $inclParam = false ) : bool | array | Pc
    {
        if( empty( $this->geo )) {
            return false;
        }
        return $inclParam ? clone $this->geo : $this->geo->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isGeoSet() : bool
    {
        return ! empty( $this->geo->value );
    }

    /**
     * Get ISO6709 "Standard representation of geographic point location by coordinates"
     *
     * Combining the (first) LOCATION and GEO property values (only if GEO is set)
     * @return bool|string
     * @since 2.27.14 2019-02-27
     */
    public function getGeoLocation() : bool | string
    {
        if( false === ( $geo = $this->getGeo())) {
            return false;
        }
        if( ! method_exists( $this, StringFactory::getGetMethodName( self::LOCATION ))) {
            return false;
        }
        $loc     = $this->getLocation();
        $content = ( empty( $loc )) ? self::$SP0 : $loc . Util::$SLASH;
        return $content .
            GeoFactory::geo2str2( $geo[self::LATITUDE], GeoFactory::$geoLatFmt ) .
            GeoFactory::geo2str2( $geo[self::LONGITUDE], GeoFactory::$geoLongFmt);
    }

    /**
     * Set calendar component property geo
     *
     * @param null|int|float|string|Pc $latitude
     * @param null|int|float|string $longitude
     * @param null|array $params
     * @return static
     * @since 2.41.62 2022-08-28
     */
    public function setGeo(
        null|int|float|string|Pc $latitude = null,
        null|int|float|string $longitude = null,
        ? array $params = []
    ) : static
    {
        switch( true ) {
            case ( null === $latitude ) :
                $this->assertEmptyValue( $latitude, self::GEO );
                $this->geo = Pc::factory();
                return $this;
            case ( $latitude instanceof Pc ) :
                $value = clone $latitude;
                break;
            default :
                $value = Pc::factory(
                    [ self::LATITUDE  => (float) $latitude, self::LONGITUDE => (float) $longitude ],
                    ParameterFactory::setParams( $params )
                );
        } // end switch
        $this->geo = $value;
        return $this;
    }
}
