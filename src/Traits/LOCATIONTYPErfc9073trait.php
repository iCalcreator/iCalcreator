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
use InvalidArgumentException;

/**
 * LOCATION-TYPE property functions
 *
 * @since 2.41.5 2022-01-19
 */
trait LOCATIONTYPErfc9073trait
{
    /**
     * @var null|mixed[] component property LOCATION-TYPE value
     */
    protected ? array $locationtype = null;

    /**
     * Return formatted output for calendar component property LOCATION-TYPE
     *
     * @return string
     */
    public function createLocationtype() : string
    {
        if( empty( $this->locationtype )) {
            return Util::$SP0;
        }
        if( empty( $this->locationtype[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::LOCATION_TYPE )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::LOCATION_TYPE,
            ParameterFactory::createParams( $this->locationtype[Util::$LCparams] ),
            $this->locationtype[Util::$LCvalue]
        );
    }

    /**
     * Delete calendar component property transp
     *
     * @return bool
     */
    public function deleteLocationtype() : bool
    {
        $this->locationtype = null;
        return true;
    }

    /**
     * Get calendar component property LOCATION-TYPE
     *
     * @param null|bool   $inclParam
     * @return bool|string|mixed[]
     */
    public function getLocationtype( ? bool $inclParam = false ) : array | bool | string
    {
        if( empty( $this->locationtype )) {
            return false;
        }
        return $inclParam ? $this->locationtype : $this->locationtype[Util::$LCvalue];
    }

    /**
     * Set calendar component property LOCATION-TYPE
     *
     * Values for this parameter are taken from the values defined in Section 3 of [RFC4589].
     * New location types SHOULD be registered in the manner laid down in Section 5 of [RFC4589].
     *
     * @param null|string   $value
     * @param null|mixed[]  $params
     * @return static
     * @throws InvalidArgumentException
     */
    public function setLocationtype( ? string $value = null, ? array $params = [] ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::LOCATION_TYPE );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            $value  = StringFactory::trimTrailNL( $value );
        }
        $this->locationtype = [
            Util::$LCvalue  => $value,
            Util::$LCparams => ParameterFactory::setParams( $params ),
        ];
        return $this;
    }
}
