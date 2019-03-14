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

use function gmdate;

/**
 * LAST-MODIFIED property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-02
 */
trait LAST_MODIFIEDtrait
{
    /**
     * @var array component property LAST-MODIFIED value
     * @access protected
     */
    protected $lastmodified = null;

    /**
     * Return formatted output for calendar component property last-modified
     *
     * @return string
     */
    public function createLastModified() {
        if( empty( $this->lastmodified )) {
            return null;
        }
        return Util::createElement(
            Util::$LAST_MODIFIED,
            Util::createParams( $this->lastmodified[Util::$LCparams] ),
            Util::date2strdate( $this->lastmodified[Util::$LCvalue], 7 )
        );
    }

    /**
     * Set calendar component property completed
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
    public function setLastModified(
        $year   = null,
        $month  = null,
        $day    = null,
        $hour   = null,
        $min    = null,
        $sec    = null,
        $params = null
    ) {
        static $TMDTHIS = 'Ymd\THis';
        if( empty( $year )) {
            $year = gmdate( $TMDTHIS );
        }
        $this->lastmodified = Util::setDate2( $year, $month, $day, $hour, $min, $sec, $params );
        return true;
    }
}
