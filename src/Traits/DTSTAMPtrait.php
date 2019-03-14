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
 * DTSTAMP property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-02
 */
trait DTSTAMPtrait
{
    /**
     * @var array component property DTSTAMP value
     * @access protected
     */
    protected $dtstamp = null;

    /**
     * Return formatted output for calendar component property dtstamp
     *
     * @return string
     */
    public function createDtstamp() {
        if( Util::hasNodate( $this->dtstamp )) {
            $this->dtstamp = Util::makeDtstamp();
        }
        return Util::createElement(
            Util::$DTSTAMP,
            Util::createParams( $this->dtstamp[Util::$LCparams] ),
            Util::date2strdate( $this->dtstamp[Util::$LCvalue], 7 )
        );
    }

    /**
     * Set calendar component property dtstamp
     *
     * @param mixed $year
     * @param mixed $month
     * @param int   $day
     * @param int   $hour
     * @param int   $min
     * @param int   $sec
     * @param array $params
     * @return bool
     */
    public function setDtstamp(
        $year,
        $month  = null,
        $day    = null,
        $hour   = null,
        $min    = null,
        $sec    = null,
        $params = null
    ) {
        $this->dtstamp = ( empty( $year ))
            ? Util::makeDtstamp()
            : Util::setDate2( $year, $month, $day, $hour, $min, $sec, $params );
        return true;
    }
}
