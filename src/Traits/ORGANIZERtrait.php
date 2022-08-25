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

use Kigkonsult\Icalcreator\Formatter\Property\SingleProps;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use InvalidArgumentException;

/**
 * ORGANIZER property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait ORGANIZERtrait
{
    /**
     * @var null|Pc component property ORGANIZER value
     */
    protected ? Pc $organizer = null;

    /**
     * Return formatted output for calendar component property organizer
     *
     * @return string
     */
    public function createOrganizer() : string
    {
        return SingleProps::format(
            self::ORGANIZER,
            $this->organizer,
            $this->getConfig( self::ALLOWEMPTY ),
            $this->getConfig( self::LANGUAGE )
        );
    }

    /**
     * Delete calendar component property organizer
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteOrganizer() : bool
    {
        $this->organizer = null;
        return true;
    }

    /**
     * Get calendar component property organizer
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getOrganizer( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->organizer )) {
            return false;
        }
        return $inclParam ? clone $this->organizer : $this->organizer->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isOrganizerSet() : bool
    {
        return ! empty( $this->organizer->value );
    }

    /**
     * Set calendar component property organizer
     *
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
      */
    public function setOrganizer( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::ORGANIZER );
            $value->setEmpty();
        }
        else {
            Util::assertString( $value->value, self::ORGANIZER );
            $value->value = CalAddressFactory::conformCalAddress( $value->value, true );
            CalAddressFactory::assertCalAddress( $value->value );
            CalAddressFactory::sameValueAndEMAILparam( $value );
        }
        $this->organizer = $value;
        if( $this->organizer->hasParamKey( self::EMAIL )) {
            $this->organizer->addParam(
                self::EMAIL,
                CalAddressFactory::prepEmail( $this->organizer->getParams( self::EMAIL ))
            );
        } // end if
        if( $this->organizer->hasParamKey( self::SENT_BY )) {
            $this->organizer->addParam(
                self::SENT_BY,
                CalAddressFactory::prepSentBy( $this->organizer->getParams( self::SENT_BY ))
            );
        } // end if
        return $this;
    }
}
