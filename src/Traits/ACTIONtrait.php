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

/**
 * ACTION property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26.2 - 2018-11-27
 */
trait ACTIONtrait
{
    /**
     * @var array component property ACTION value
     * @access protected
     */
    protected $action = null;

    /**
     * Return formatted output for calendar component property action
     *
     * @return string
     */
    public function createAction() {
        if( empty( $this->action )) {
            return null;
        }
        if( empty( $this->action[Util::$LCvalue] )) {
            return ( $this->getConfig( Util::$ALLOWEMPTY )) ? Util::createElement( Util::$ACTION ) : null;
        }
        return Util::createElement(
            Util::$ACTION,
            Util::createParams( $this->action[Util::$LCparams] ),
            $this->action[Util::$LCvalue]
        );
    }

    /**
     * Set calendar component property action
     *
     * @param string $value "AUDIO" / "DISPLAY" / "EMAIL" / "PROCEDURE"  / iana-token / x-name
     * @param mixed  $params
     * @return bool
     */
    public function setAction( $value, $params = null ) {
        if( empty( $value )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $value = Util::$SP0;
            }
            else {
                return false;
            }
        }
        $this->action = [
            Util::$LCvalue  => Util::trimTrailNL( $value ),
            Util::$LCparams => Util::setParams( $params ),
        ];
        return true;
    }
}
