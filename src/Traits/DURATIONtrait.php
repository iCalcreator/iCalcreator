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
use Kigkonsult\Icalcreator\Util\UtilDuration;
use DateInterval;
use Exception;

use function count;
use function in_array;
use function is_array;
use function is_string;
use function strlen;
use function substr;

/**
 * DURATION property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26.8 - 2018-12-12
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
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-28
     */
    public function createDuration() {
        if( ! isset( $this->duration[Util::$LCvalue] )) {
            return null;
        }
        if( isset( $this->duration[Util::$LCvalue]['invert'] )) { // fix pre 7.0.5 bug
            $dateInterval = UtilDuration::DateIntervalArr2DateInterval( $this->duration[Util::$LCvalue] );
            return Util::createElement(
                Util::$DURATION,
                Util::createParams( $this->duration[Util::$LCparams] ),
                UtilDuration::dateInterval2String( $dateInterval )
            );
        }
        else {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                return Util::createElement( Util::$DURATION );
            }
            else {
                return null;
            }
        }
    }

    /**
     * Set calendar component property duration
     *
     * @param mixed $week
     * @param mixed $day
     * @param int   $hour
     * @param int   $min
     * @param int   $sec
     * @param array $params
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-02
     */
    public function setDuration(
        $week,
        $day    = null,
        $hour   = null,
        $min    = null,
        $sec    = null,
        $params = null
    ) {
        if( empty( $week ) && empty( $day ) && empty( $hour ) && empty( $min ) && empty( $sec )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $week = $day = null;
            }
            else {
                return false;
            }
        }
        if( $week instanceof DateInterval ) {
            $this->duration = [
                Util::$LCvalue  => (array) $week,  // fix pre 7.0.5 bug
                Util::$LCparams => Util::setParams( $day ),
            ];
        }
        elseif( is_array( $week ) && ( 1 <= count( $week ))) {
            try {
                $dateInterval = new DateInterval(
                    UtilDuration::duration2str(
                        UtilDuration::duration2arr( $week )
                    )
                );
                $week = UtilDuration::conformDateInterval( $dateInterval );
            }
            catch( Exception $e ) {
                return false;
            }
            $this->duration = [
                Util::$LCvalue  => (array) $week,  // fix pre 7.0.5 bug
                Util::$LCparams => Util::setParams( $day ),
            ];
        }
        elseif( is_string( $week ) && ( 3 <= strlen( trim( $week )))) {
            $week = Util::trimTrailNL( trim( $week ));
            if( in_array( $week[0], Util::$PLUSMINUSARR )) { // can only be positive
                $week = substr( $week, 1 );
            }
            try {
                $dateInterval = new DateInterval( $week );
                $week = UtilDuration::conformDateInterval( $dateInterval );
            }
            catch( Exception $e ) {
                return false;
            }
            $this->duration = [
                Util::$LCvalue  => (array) $week,  // fix pre 7.0.5 bug
                Util::$LCparams => Util::setParams( $day ),
            ];
        }
        else {
            try {
                $dateInterval = new DateInterval(
                    UtilDuration::duration2str(
                        UtilDuration::duration2arr(
                            [
                                Util::$LCWEEK => (int) $week,
                                Util::$LCDAY  => (int) $day,
                                Util::$LCHOUR => (int) $hour,
                                Util::$LCMIN  => (int) $min,
                                Util::$LCSEC  => (int) $sec,
                            ]
                        )
                    )
                );
                $week = UtilDuration::conformDateInterval( $dateInterval );
            }
            catch( Exception $e ) {
                return false;
            }
            $this->duration = [
                Util::$LCvalue  => (array) $week,  // fix pre 7.0.5 bug
                Util::$LCparams => Util::setParams( $params ),
            ];
        }
        return true;
    }
}
