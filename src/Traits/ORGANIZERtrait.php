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

use Kigkonsult\Icalcreator\Formatter\Property\SingleProps;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use InvalidArgumentException;

/**
 * ORGANIZER property functions
 *
 * @since 2.41.85 2024-01-18
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
     * @since 2.41.85 2024-01-18
     */
    public function getOrganizer( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->organizer )) {
            return false;
        }
        return $inclParam ? clone $this->organizer : $this->organizer->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isOrganizerSet() : bool
    {
        return self::isPropSet( $this->organizer );
    }

    /**
     * Set calendar component property organizer
     *
     * @param null|string|Pc   $value
     * @param null|mixed[] $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setOrganizer( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::ORGANIZER );
            $pc->setEmpty();
        }
        else {
            Util::assertString( $pcValue, self::ORGANIZER );
            if( ! CalAddressFactory::hasProtocolPrefix( $pcValue )) {
                $pc->setValue( CalAddressFactory::conformCalAddress( $pcValue, true ));
            }
            elseif( CalAddressFactory::hasMailtoPrefix( $pcValue )) {
                $pcValue = CalAddressFactory::conformCalAddress( $pcValue );
                CalAddressFactory::assertCalAddress( $pcValue );
                $pc->setValue( $pcValue );
                CalAddressFactory::sameValueAndEMAILparam( $pc );
            }
        }
        if( $pc->hasParamKey( self::EMAIL )) {
            $pc->addParam(
                self::EMAIL,
                CalAddressFactory::prepEmail( $pc->getParams( self::EMAIL ))
            );
        } // end if
        if( $pc->hasParamKey( self::SENT_BY )) {
            $pc->addParam(
                self::SENT_BY,
                CalAddressFactory::prepSentBy( $pc->getParams( self::SENT_BY ))
            );
        } // end if
        $this->organizer = $pc;
        return $this;
    }
}
