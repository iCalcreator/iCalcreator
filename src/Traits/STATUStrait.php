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
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

use function strtoupper;

/**
 * STATUS property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait STATUStrait
{
    /**
     * @var null|Pc component property STATUS value
     */
    protected ? Pc $status = null;

    /**
     * Return formatted output for calendar component property status
     *
     * @return string
     */
    public function createStatus() : string
    {
        return Property::format(
            self::STATUS,
            $this->status,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property status
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteStatus() : bool
    {
        $this->status = null;
        return true;
    }

    /**
     * Get calendar component property status
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getStatus( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->status )) {
            return false;
        }
        return $inclParam ? clone $this->status : $this->status->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isStatusSet() : bool
    {
        return ! empty( $this->status->value );
    }

    /**
     * Set calendar component property status
     *
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setStatus( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        static $ALLOWED_VEVENT = [
            self::CONFIRMED,
            self::CANCELLED,
            self::TENTATIVE
        ];
        static $ALLOWED_VTODO = [
            self::COMPLETED,
            self::CANCELLED,
            self::IN_PROCESS,
            self::NEEDS_ACTION,
        ];
        static $ALLOWED_VJOURNAL = [
            self::CANCELLED,
            self::DRAFT,
            self::F_NAL,
        ];
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( ! empty( $value->value )) {
            Util::assertString( $value->value, self::SOURCE );
            $value->value = strtoupper( StringFactory::trimTrailNL( $value->value ));
        }
        switch( true ) {
            case ( empty( $value->value )) :
                $this->assertEmptyValue( $value->value, self::STATUS );
                $value->setEmpty();
                break;
            case (self::VEVENT === $this->getCompType()) :
                Util::assertInEnumeration( $value->value, $ALLOWED_VEVENT, self::STATUS );
                break;
            case (self::VTODO === $this->getCompType()) :
                Util::assertInEnumeration( $value->value, $ALLOWED_VTODO, self::STATUS );
                break;
            case (self::VJOURNAL === $this->getCompType()) :
                Util::assertInEnumeration( $value->value, $ALLOWED_VJOURNAL, self::STATUS );
                break;
        } // end switch
        $this->status = $value;
        return $this;
    }
}
