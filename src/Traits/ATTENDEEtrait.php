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
 * ATTENDEE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-17
 */
trait ATTENDEEtrait
{
    /**
     * @var array component property ATTENDEE value
     * @access protected
     */
    protected $attendee = null;

    /**
     * Return formatted output for calendar component property attendee
     *
     * @return string
     */
    public function createAttendee() {
        if( empty( $this->attendee )) {
            return null;
        }
        return UtilAttendee::formatAttendee( $this->attendee, $this->getConfig( Util::$ALLOWEMPTY ));
    }

    /**
     * Set calendar component property attach
     *
     * @param string  $value
     * @param array   $params
     * @param integer $index
     * @return bool
     */
    public function setAttendee( $value, $params = null, $index = null ) {
        if( empty( $value )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $value = Util::$SP0;
            }
            else {
                return false;
            }
        }
        Util::setMval(
            $this->attendee,
            UtilAttendee::calAddressCheck( $value, false ),
            UtilAttendee::prepAttendeeParams( $params, $this->compType, $this->getConfig( Util::$LANGUAGE )),
            false,
            $index
        );
        return true;
    }
}
