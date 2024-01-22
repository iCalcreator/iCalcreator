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

use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use InvalidArgumentException;

/**
 * PROXIMITY property functions
 *
 * @since 2.41.85 2024-01-18
 */
trait PROXIMITYrfc9074trait
{
    /**
     * @var null|Pc   Valarm component property PROXIMITY value
     */
    protected ? Pc $proximity = null;

    /**
     * Return formatted output for calendar Valarm component property proximity
     *
     * @return string
     */
    public function createProximity() : string
    {
        return Property::format(
            self::PROXIMITY,
            $this->proximity,
            $this->getConfig( self::ALLOWEMPTY )
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
     * @return bool|string|Pc
     * @since 2.41.85 2024-01-18
     */
    public function getProximity( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->proximity )) {
            return false;
        }
        return $inclParam ? clone $this->proximity : $this->proximity->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isProximitySet() : bool
    {
        return self::isPropSet( $this->proximity );
    }

    /**
     * Set calendar component property proximity
     *
     * @since 2.23.12 - 2017-04-22
     * @param null|string|Pc   $value
     * @param null|mixed[] $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setProximity( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::PROXIMITY );
            $pc->setEmpty();
        }
        else {
            $pc->setValue( StringFactory::trimTrailNL( $pcValue ));
        }
        $this->proximity = $pc;
        return $this;
    }
}
