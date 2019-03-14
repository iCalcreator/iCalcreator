<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.8
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\UtilAttendee;

/**
 * ORGANIZER property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-04-03
 */
trait ORGANIZERtrait
{
    /**
     * @var array component property ORGANIZER value
     * @access protected
     */
    protected $organizer = null;

    /**
     * Return formatted output for calendar component property organizer
     *
     * @return string
     */
    public function createOrganizer() {
        if( empty( $this->organizer )) {
            return null;
        }
        if( empty( $this->organizer[Util::$LCvalue] )) {
            return ( $this->getConfig( Util::$ALLOWEMPTY )) ? Util::createElement( Util::$ORGANIZER ) : null;
        }
        return Util::createElement(
            Util::$ORGANIZER,
            Util::createParams(
                $this->organizer[Util::$LCparams],
                [
                    Util::$CN,
                    Util::$DIR,
                    Util::$SENT_BY,
                    Util::$LANGUAGE,
                ],
                $this->getConfig( Util::$LANGUAGE )
            ),
            $this->organizer[Util::$LCvalue]
        );
    }

    /**
     * Set calendar component property organizer
     *
     * @param string $value
     * @param array  $params
     * @return bool
     */
    public function setOrganizer( $value, $params = null ) {
        if( empty( $value )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $value = Util::$SP0;
            }
            else {
                return false;
            }
        }
        $value           = UtilAttendee::calAddressCheck( $value, false );
        $this->organizer = [
            Util::$LCvalue  => $value,
            Util::$LCparams => Util::setParams( $params ),
        ];
        if( isset( $this->organizer[Util::$LCparams][Util::$SENT_BY] )) {
            $this->organizer[Util::$LCparams][Util::$SENT_BY] =
                UtilAttendee::calAddressCheck(
                    $this->organizer[Util::$LCparams][Util::$SENT_BY],
                    false
                );
        }
        return true;
    }
}
