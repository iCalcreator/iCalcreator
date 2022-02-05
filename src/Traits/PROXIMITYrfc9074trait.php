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
 * PROXIMITY property functions
 *
 * @since 2.40.11 2022-01-15
 */
trait PROXIMITYrfc9074trait
{
    /**
     * @var null|mixed[]   Valarm component property PROXIMITY value
     */
    protected ? array $proximity = null;

    /**
     * Return formatted output for calendar Valarm component property proximity
     *
     * @return string
     */
    public function createProximity() : string
    {
        if( empty( $this->proximity )) {
            return Util::$SP0;
        }
        if( empty( $this->proximity[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::PROXIMITY )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::PROXIMITY,
            ParameterFactory::createParams( $this->proximity[Util::$LCparams] ),
            StringFactory::strrep( $this->proximity[Util::$LCvalue] )
        );
    }

    /**
     * Delete calendar Valarm component property proximity
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteProximity() : bool
    {
        $this->proximity = null;
        return true;
    }

    /**
     * Get calendar Valarm component property proximity
     *
     * @param null|bool   $inclParam
     * @return bool|string|mixed[]
     * @since  2.27.1 - 2018-12-13
     */
    public function getProximity( ? bool $inclParam = false ) : array | bool | string
    {
        if( empty( $this->proximity )) {
            return false;
        }
        return $inclParam ? $this->proximity : $this->proximity[Util::$LCvalue];
    }

    /**
     * Set calendar component property proximity
     *
     * @since 2.23.12 - 2017-04-22
     * @param null|string   $value
     * @param null|mixed[]  $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.27.3 2018-12-22
     */
    public function setProximity( ? string $value = null, ? array $params = [] ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::PROXIMITY );
            $value  = Util::$SP0;
            $params = [];
        }
        $this->proximity = [
            Util::$LCvalue  => StringFactory::trimTrailNL( $value ),
            Util::$LCparams => ParameterFactory::setParams( $params ),
        ];
        return $this;
    }
}
