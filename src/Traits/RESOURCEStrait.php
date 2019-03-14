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

use function implode;
use function is_array;

/**
 * RESOURCES property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-17
 */
trait RESOURCEStrait
{
    /**
     * @var array component property RESOURCES value
     * @access protected
     */
    protected $resources = null;

    /**
     * Return formatted output for calendar component property resources
     *
     * @return string
     */
    public function createResources() {
        if( empty( $this->resources )) {
            return null;
        }
        $output = null;
        $lang   = $this->getConfig( Util::$LANGUAGE );
        foreach( $this->resources as $rx => $resource ) {
            if( empty( $resource[Util::$LCvalue] )) {
                if( $this->getConfig( Util::$ALLOWEMPTY )) {
                    $output .= Util::createElement( Util::$RESOURCES );
                }
                continue;
            }
            if( is_array( $resource[Util::$LCvalue] )) {
                foreach( $resource[Util::$LCvalue] as $rix => $rValue ) {
                    $resource[Util::$LCvalue][$rix] = Util::strrep( $rValue );
                }
                $content = implode( Util::$COMMA, $resource[Util::$LCvalue] );
            }
            else {
                $content = Util::strrep( $resource[Util::$LCvalue] );
            }
            $output .= Util::createElement(
                Util::$RESOURCES,
                Util::createParams( $resource[Util::$LCparams], Util::$ALTRPLANGARR, $lang ),
                $content
            );
        }
        return $output;
    }

    /**
     * Set calendar component property recources
     *
     * @param mixed   $value
     * @param array   $params
     * @param integer $index
     * @return bool
     */
    public function setResources( $value, $params = null, $index = null ) {
        if( empty( $value )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $value = Util::$SP0;
            }
            else {
                return false;
            }
        }
        if( is_array( $value )) {
            foreach( $value as & $valuePart ) {
                $valuePart = Util::trimTrailNL( $valuePart );
            }
        }
        else {
            $value = Util::trimTrailNL( $value );
        }
        Util::setMval( $this->resources, $value, $params, false, $index );
        return true;
    }
}
