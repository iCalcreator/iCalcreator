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
use Kigkonsult\Icalcreator\Util\UtilRecur;

/**
 * RRULE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-04-03
 */
trait RRULEtrait
{
    /**
     * @var array component property RRULE value
     * @access protected
     */
    protected $rrule = null;

    /**
     * Return formatted output for calendar component property rrule
     *
     * @return string
     */
    public function createRrule() {
        return UtilRecur::formatRecur(  Util::$RRULE, $this->rrule, $this->getConfig( Util::$ALLOWEMPTY ));
    }

    /**
     * Set calendar component property rrule
     *
     * @param array   $rruleset
     * @param array   $params
     * @param integer $index
     * @return bool
     */
    public function setRrule( $rruleset, $params = null, $index = null ) {
        if( empty( $rruleset )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $rruleset = Util::$SP0;
            }
            else {
                return false;
            }
        }
        Util::setMval( $this->rrule, UtilRecur::setRexrule( $rruleset ), $params,false, $index );
        return true;
    }
}
