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

use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\HttpFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use InvalidArgumentException;

use function stripos;
use function strtolower;
use function substr;

/**
 * URL property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait URLtrait
{
    /**
     * @var null|Pc component property URL value
     */
    protected ? Pc $url = null;

    /**
     * Return formatted output for calendar component property url
     *
     * @return string
     */
    public function createUrl() : string
    {
        return Property::format(
            self::URL,
            $this->url,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property url
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteUrl() : bool
    {
        $this->url = null;
        return true;
    }

    /**
     * Get calendar component property url
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getUrl( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->url )) {
            return false;
        }
        return $inclParam ? clone $this->url : $this->url->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isUrlSet() : bool
    {
        return ! empty( $this->url->value );
    }

    /**
     * Set calendar component property url
     *
     * 2.41.12
     * rfc5870 Uniform Resource Identifier for Geographic Locations ('geo' URI)
     *   rfc9073 (7.2. Location) defines VLOCATION with a GEO property
     *   rfc9074 (8.  Alarm Proximity Trigger) add VLOCATION(s) to VALARM
     *   with an URL 'geo' URI [RFC5870] property
     *  As for now, accept 'global' URL with 'geo' URI "as is"
     *  Ex. 'URL:geo:40.443,-79.945;u=10'
     *
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setUrl( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::URL );
            $this->url = Pc::factory( self::$SP0 );
        }
        elseif( 0 === stripos( $value->value, self::GEO )) {
            $value->value = strtolower( self::GEO ) . substr( $value->value, 3 );
            $this->url = $value->setValue( StringFactory::trimTrailNL( $value->value ))
                ->removeParam(self::VALUE );
        }
        else {
            Util::assertString( $value->value, self::URL );
            HttpFactory::urlSet( $this->url, $value );
        }
        return $this;
    }
}
