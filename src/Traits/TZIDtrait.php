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
 * TZID property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-05
 */
trait TZIDtrait
{
    /**
     * @var array component property TZID value
     * @access protected
     */
    protected $tzid = null;

    /**
     * Return formatted output for calendar component property tzid
     *
     * @return string
     */
    public function createTzid() {
        if( empty( $this->tzid )) {
            return null;
        }
        if( empty( $this->tzid[Util::$LCvalue] )) {
            return ( $this->getConfig( Util::$ALLOWEMPTY )) ? Util::createElement( Util::$TZID ) : null;
        }
        return Util::createElement(
            Util::$TZID,
            Util::createParams( $this->tzid[Util::$LCparams] ),
            Util::strrep( $this->tzid[Util::$LCvalue] )
        );
    }

    /**
     * Set calendar component property tzid
     *
     * @since 2.23.12 - 2017-04-22
     * @param string $value
     * @param array  $params
     * @return bool
     */
    public function setTzid( $value, $params = null ) {
        if( empty( $value )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $value = Util::$SP0;
            }
            else {
                return false;
            }
        }
        $this->tzid = [
            Util::$LCvalue  => trim( Util::trimTrailNL( $value )),
            Util::$LCparams => Util::setParams( $params ),
        ];
        return true;
    }
}
