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
 * DTSTART property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-02
 */
trait DTSTARTtrait
{
    /**
     * @var array component property DTSTART value
     * @access protected
     */
    protected $dtstart = null;

    /**
     * Return formatted output for calendar component property dtstart
     *
     * @return string
     */
    public function createDtstart() {
        if( empty( $this->dtstart )) {
            return null;
        }
        if( Util::hasNodate( $this->dtstart )) {
            return ( $this->getConfig( Util::$ALLOWEMPTY )) ? Util::createElement( Util::$DTSTART ) : null;
        }
        if( Util::isCompInList( $this->compType, Util::$TZCOMPS )) {
            unset( $this->dtstart[Util::$LCvalue][Util::$LCtz], $this->dtstart[Util::$LCparams][Util::$TZID] );
        }
        $parno = Util::isParamsValueSet( $this->dtstart, Util::$DATE ) ? 3 : null;
        return Util::createElement(
            Util::$DTSTART,
            Util::createParams( $this->dtstart[Util::$LCparams] ),
            Util::date2strdate( $this->dtstart[Util::$LCvalue], $parno  )
        );
    }

    /**
     * Set calendar component property dtstart
     *
     * @param mixed  $year
     * @param mixed  $month
     * @param int    $day
     * @param int    $hour
     * @param int    $min
     * @param int    $sec
     * @param string $tz
     * @param array  $params
     * @return bool
     */
    public function setDtstart(
        $year,
        $month  = null,
        $day    = null,
        $hour   = null,
        $min    = null,
        $sec    = null,
        $tz     = null,
        $params = null
    ) {
        if( empty( $year )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $this->dtstart = [
                    Util::$LCvalue  => Util::$SP0,
                    Util::$LCparams => Util::setParams( $params ),
                ];
                return true;
            }
            else {
                return false;
            }
        }
        if( false === ( $tzid = $this->getConfig( Util::$TZID ))) {
            $tzid = null;
        }
        $this->dtstart = Util::setDate(
            $year, $month, $day, $hour, $min, $sec, $tz,
            $params, Util::$DTSTART, $this->compType, $tzid
        );
        return true;
    }
}
