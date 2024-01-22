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

use Kigkonsult\Icalcreator\Formatter\Property\Geo;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\GeoFactory;

/**
 * GEO property functions
 *
 * @since 2.41.85 2024-01-18
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
     * @since 2.41.85 2024-01-18
     */
    public function getGeo( ? bool $inclParam = false ) : bool | array | Pc
    {
        if( empty( $this->geo )) {
            return false;
        }
        return $inclParam ? clone $this->geo : $this->geo->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isGeoSet() : bool
    {
        return self::isPropSet( $this->geo );
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
        $content = ( empty( $loc )) ? self::$SP0 : $loc . StringFactory::$SLASH;
        return $content .
            GeoFactory::geo2str2( $geo[self::LATITUDE], GeoFactory::$geoLatFmt ) .
            GeoFactory::geo2str2( $geo[self::LONGITUDE], GeoFactory::$geoLongFmt);
    }

    /**
     * Return array ( <lat>, <long> ) on valid input OR null-array
     *
     * @param string|array $input
     * @return array|null[]
     * @since 2.41.88 2024-01-21
     */
    public static function extractGeoLatLong( string|array $input ) : array
    {
        static $nullArr = [ null, null ];
        if( is_string( $input )) {
            return match ( true ) {
                empty( $input ), ! str_contains( $input, StringFactory::$SEMIC )
                        => $nullArr,
                default => explode( StringFactory::$SEMIC, $input, 2 ),
            };
        }
        return match( true ) {
            ( 2 !== count( $input )) => $nullArr,
            ( ! empty( $input[0] ) && ! empty( $input[1] )) => $input,
            ( ! empty( $input[self::LATITUDE] ) && ! empty( $input[self::LONGITUDE] )) =>
            [ $input[self::LATITUDE], $input[self::LONGITUDE] ],
            default => $nullArr
        };
    }

    /**
     * Set calendar component property geo
     *
     * @param null|int|float|string|Pc $latitude
     * @param null|int|float|string $longitude
     * @param null|mixed[] $params
     * @return static
     * @since 2.41.85 2024-01-18
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
                $pc = clone $latitude;
                break;
            default :
                $pc = Pc::factory(
                    [ self::LATITUDE  => (float) $latitude, self::LONGITUDE => (float) $longitude ],
                    $params
                );
        } // end switch
        $this->geo = $pc;
        return $this;
    }
}
