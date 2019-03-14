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
 * RELATED-TO property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-17
 */
trait RELATED_TOtrait
{
    /**
     * @var array component property RELATED_TO value
     * @access protected
     */
    protected $relatedto = null;

    /**
     * Return formatted output for calendar component property related-to
     *
     * @return string
     */
    public function createRelatedTo() {
        if( empty( $this->relatedto )) {
            return null;
        }
        $output = null;
        foreach( $this->relatedto as $rx => $relation ) {
            if( ! empty( $relation[Util::$LCvalue] )) {
                $output .= Util::createElement(
                    Util::$RELATED_TO,
                    Util::createParams( $relation[Util::$LCparams] ),
                    Util::strrep( $relation[Util::$LCvalue] )
                );
            }
            elseif( $this->getConfig( Util::$ALLOWEMPTY )) {
                $output .= Util::createElement( Util::$RELATED_TO );
            }
        }
        return $output;
    }

    /**
     * Set calendar component property related-to
     *
     * @param string $value
     * @param array  $params
     * @param int    $index
     * @return bool
     */
    public function setRelatedTo( $value, $params = [], $index = null ) {
        static $RELTYPE = 'RELTYPE';
        static $PARENT  = 'PARENT';
        if( empty( $value )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $value = Util::$SP0;
            }
            else {
                return false;
            }
        }
        if( ! empty( $params )) {
            Util::existRem( $params, $RELTYPE, $PARENT, true ); // remove default
        }
        Util::setMval( $this->relatedto, Util::trimTrailNL( $value ), $params, false, $index );
        return true;
    }
}
