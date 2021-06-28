<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use InvalidArgumentException;

/**
 * ORGANIZER property functions
 *
 * @since  2.27.8 - 2019-03-17
 */
trait ORGANIZERtrait
{
    /**
     * @var array component property ORGANIZER value
     */
    protected $organizer = null;

    /**
     * Return formatted output for calendar component property organizer
     *
     * @return string
     */
    public function createOrganizer() : string
    {
        if( empty( $this->organizer )) {
            return Util::$SP0;
        }
        if( empty( $this->organizer[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::ORGANIZER )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::ORGANIZER,
            ParameterFactory::createParams(
                $this->organizer[Util::$LCparams],
                [
                    self::CN,
                    self::DIR,
                    self::SENT_BY,
                    self::LANGUAGE,
                ],
                $this->getConfig( self::LANGUAGE )
            ),
            $this->organizer[Util::$LCvalue]
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
     * @return bool|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getOrganizer( $inclParam = false )
    {
        if( empty( $this->organizer )) {
            return false;
        }
        return ( $inclParam ) ? $this->organizer : $this->organizer[Util::$LCvalue];
    }

    /**
     * Set calendar component property organizer
     *
     * @param null|string $value
     * @param null|array  $params
     * @return static
     * @throws InvalidArgumentException
     * @since  2.39 - 2021-06-17
     * @todo ensure value is prefixed by protocol, mailto: if missing
      */
    public function setOrganizer( $value = null, $params = [] ) : self
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::ORGANIZER );
            $value  = Util::$SP0;
            $params = [];
        }
        $value = CalAddressFactory::conformCalAddress( $value, true );
        if( ! empty( $value )) {
            CalAddressFactory::assertCalAddress( $value );
        }
        $params = array_change_key_case( (array) $params, CASE_UPPER );
        CalAddressFactory::sameValueAndEMAILparam( $value, $params );
        $this->organizer = [
            Util::$LCvalue  => $value,
            Util::$LCparams => ParameterFactory::setParams( $params ?? [] ),
        ];
        if( isset( $this->organizer[Util::$LCparams][self::EMAIL] )) {
            $this->organizer[Util::$LCparams][self::EMAIL] =
                CalAddressFactory::prepEmail(
                    $this->organizer[Util::$LCparams][self::EMAIL]
                );
        } // end if
        if( isset( $this->organizer[Util::$LCparams][self::SENT_BY] )) {
            $this->organizer[Util::$LCparams][self::SENT_BY] =
                CalAddressFactory::prepSentBy(
                    $this->organizer[Util::$LCparams][self::SENT_BY]
                );
        } // end if
        return $this;
    }
}
