<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * This file is a part of iCalcreator.
 *
 * Copyright (c) 2007-2018 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      http://kigkonsult.se/iCalcreator/index.php
 * Package   iCalcreator
 * Version   2.26
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the [rfc5545] PRODID as implemented and
 *           invoked in iCalcreator shall be included in all copies or
 *           substantial portions of the iCalcreator.
 *           iCalcreator can be used either under the terms of
 *           a proprietary license, available from iCal_at_kigkonsult_dot_se
 *           or the GNU Affero General Public License, version 3:
 *           iCalcreator is free software: you can redistribute it and/or
 *           modify it under the terms of the GNU Affero General Public License
 *           as published by the Free Software Foundation, either version 3 of
 *           the License, or (at your option) any later version.
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *           GNU Affero General Public License for more details.
 *           You should have received a copy of the GNU Affero General Public
 *           License along with this program.
 *           If not, see <http://www.gnu.org/licenses/>.
 */

namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Util\Util;

/**
 * DURATION property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-05
 */
trait DURATIONtrait
{
    /**
     * @var array component property DURATION value
     * @access protected
     */
    protected $duration = null;

    /**
     * Return formatted output for calendar component property duration
     *
     * @return string
     */
    public function createDuration() {
        if( empty( $this->duration )) {
            return null;
        }
        if( ! isset( $this->duration[Util::$LCvalue][Util::$LCWEEK] ) &&
            ! isset( $this->duration[Util::$LCvalue][Util::$LCDAY] )  &&
            ! isset( $this->duration[Util::$LCvalue][Util::$LCHOUR] ) &&
            ! isset( $this->duration[Util::$LCvalue][Util::$LCMIN] )  &&
            ! isset( $this->duration[Util::$LCvalue][Util::$LCSEC] )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                return Util::createElement( Util::$DURATION );
            }
            else {
                return null;
            }
        }
        return Util::createElement(
            Util::$DURATION,
            Util::createParams( $this->duration[Util::$LCparams] ),
            Util::duration2str( $this->duration[Util::$LCvalue] )
        );
    }

    /**
     * Set calendar component property duration
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-17
     * @param mixed $week
     * @param mixed $day
     * @param int   $hour
     * @param int   $min
     * @param int   $sec
     * @param array $params
     * @return bool
     */
    public function setDuration(
        $week,
        $day    = null,
        $hour   = null,
        $min    = null,
        $sec    = null,
        $params = null
    ) {
        static $PLUSMINUSARR = [ '+', '-' ];
        if( empty( $week ) && empty( $day ) && empty( $hour ) && empty( $min ) && empty( $sec )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $week = $day = null;
            }
            else {
                return false;
            }
        }
        if( \is_array( $week ) && ( 1 <= \count( $week ))) {
            $this->duration = [
                Util::$LCvalue  => Util::duration2arr( $week ),
                Util::$LCparams => Util::setParams( $day ),
            ];
        }
        elseif( \is_string( $week ) && ( 3 <= \strlen( trim( $week )))) {
            $week = Util::trimTrailNL( trim( $week ));
            if( \in_array( $week[0], $PLUSMINUSARR )) {
                $week = \substr( $week, 1 );
            }
            $this->duration = [
                Util::$LCvalue  => Util::durationStr2arr( $week ), // array
                Util::$LCparams => Util::setParams( $day ),
            ];
        }
        else {
            $this->duration = [
                Util::$LCvalue  => Util::duration2arr(
                    [
                        Util::$LCWEEK => $week,
                        Util::$LCDAY  => $day,
                        Util::$LCHOUR => $hour,
                        Util::$LCMIN  => $min,
                        Util::$LCSEC  => $sec,
                    ]
                ),
                Util::$LCparams => Util::setParams( $params ),
            ];
        }
        return true;
    }
}
