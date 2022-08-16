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
namespace Kigkonsult\Icalcreator;

use DateInterval;
use DateTimeInterface;
use Exception;

/**
 * iCalcreator VEVENT/VTODO component base class
 *
 * @since  2.41.53 - 2022-08-08
 */
abstract class V3component extends V2component
{
    /**
     * Return Valarm object instance
     *
     * @param null|string $action property ACTION value
     * @param null|string|DateInterval|DateTimeInterface $trigger  property TRIGGER value
     * @return Valarm
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public function newValarm(
        ? string $action = null,
        null|string|DateInterval|DateTimeInterface $trigger = null
    ) : Valarm
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = Valarm::factory( $this->getConfig(), $action, $trigger );
        return $this->components[$ix];
    }

    use Traits\Participants2AttendeesTrait;

    /**
     * Set Vevent/Vtodo subComponent Vlocation names as Locations, skip if set
     *
     * Vlocation UID set as Location X-param x-vlocationid
     * All Vlocation name parameters are set if not exist.
     * Vlocation LOCATION_TYPE set as Location X-param x-location-type
     *
     * @return static
     * @since 2.41.19 - 2022-02-19
     */
    public function vlocationNames2Location() : static
    {
        // get all (sub) Vlocation names etc (incl params)
        [ $vLocations, $lcArr ] = $this->getSubCompsDetailType(
            self::VLOCATION,
            self::NAME,
            self::X_VLOCATIONID,
            self::LOCATION_TYPE,
            self::X_LOCATION_TYPE
        );
        if( ! empty( $vLocations )) {
            $this->comPropUpdFromSub( self::LOCATION, false, $vLocations, $lcArr );
        }
        return $this;
    }

    /**
     * Set Vevent/Vtodo subComponent Vresource names as Resource, skip if set
     *
     * Vresource UID set as Resurce X-param x-participantid
     * Other Vresource name parameters are set if ot exist.
     * Vresource RESOURCE_TYPE set as Location X-param x-resource-type
     *
     * @return static
     * @since 2.41.21 - 2022-02-18
     */
    public function vresourceNames2Resources() : static
    {
        // get all (sub) Vresource names etc (incl params)
        [ $vResources, $lcArr ] = $this->getSubCompsDetailType(
            self::VRESOURCE,
            self::NAME,
            self::X_VRESOURCEID,
            self::RESOURCE_TYPE,
            self::X_RESOURCE_TYPE
        );
        if( ! empty( $vResources )) {
            $this->comPropUpdFromSub( self::RESOURCES, true, $vResources, $lcArr );
        }
        return $this;
    }

    use Traits\SubCompsGetTrait;
}
