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

use function count;
use function implode;
use function is_array;
use function is_numeric;
use function strtoupper;

/**
 * X-property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-02
 */
trait X_PROPtrait
{
    /**
     * @var array component property X-property value
     * @access protected
     */
    protected $xprop = null;

    /**
     * Return formatted output for calendar/component property x-prop
     *
     * @return string
     */
    public function createXprop() {
        if( empty( $this->xprop ) || ! is_array( $this->xprop )) {
            return null;
        }
        $output = null;
        $lang   = $this->getConfig( Util::$LANGUAGE );
        foreach( $this->xprop as $label => $xpropPart ) {
            if( ! isset( $xpropPart[Util::$LCvalue] ) ||
                ( empty( $xpropPart[Util::$LCvalue] ) && ! is_numeric( $xpropPart[Util::$LCvalue] ))) {
                if( $this->getConfig( Util::$ALLOWEMPTY )) {
                    $output .= Util::createElement( $label );
                }
                continue;
            }
            if( is_array( $xpropPart[Util::$LCvalue] )) {
                foreach( $xpropPart[Util::$LCvalue] as $pix => $theXpart ) {
                    $xpropPart[Util::$LCvalue][$pix] = Util::strrep( $theXpart );
                }
                $xpropPart[Util::$LCvalue] = implode( Util::$COMMA, $xpropPart[Util::$LCvalue] );
            }
            else {
                $xpropPart[Util::$LCvalue] = Util::strrep( $xpropPart[Util::$LCvalue] );
            }
            $output .= Util::createElement(
                $label,
                Util::createParams( $xpropPart[Util::$LCparams], [ Util::$LANGUAGE ], $lang ),
                Util::trimTrailNL( $xpropPart[Util::$LCvalue] )
            );
        }
        return $output;
    }

    /**
     * Set calendar property x-prop
     *
     * @param string $label
     * @param string $value
     * @param array  $params   optional
     * @return bool
     */
    public function setXprop( $label, $value, $params = null ) {
        if( empty( $label ) || ! Util::isXprefixed( $label )) {
            return false;
        }
        if( empty( $value ) && ! is_numeric( $value )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                $value = Util::$SP0;
            }
            else {
                return false;
            }
        }
        $xprop = [ Util::$LCvalue => $value ];
        $xprop[Util::$LCparams] = Util::setParams( $params );
        if( ! is_array( $this->xprop )) {
            $this->xprop = [];
        }
        $this->xprop[strtoupper( $label )] = $xprop;
        return true;
    }

    /**
     * Delete component property X-prop value
     *
     * @param string $propName
     * @param array  $xProp  component X-property
     * @param int    $propix removal counter
     * @param array  $propdelix
     * @access protected
     * @return bool
     * @static
     */
    protected static function deleteXproperty( $propName, & $xProp, & $propix, & $propdelix ) {
        $reduced = [];
        if( $propName != Util::$X_PROP ) {
            if( ! isset( $xProp[$propName] )) {
                unset( $propdelix[$propName] );
                return false;
            }
            foreach( $xProp as $k => $xValue ) {
                if(( $k != $propName ) && ! empty( $xValue )) {
                    $reduced[$k] = $xValue;
                }
            }
        }
        else {
            if( count( $xProp ) <= $propix ) {
                unset( $propdelix[$propName] );
                return false;
            }
            $xpropno = 0;
            foreach( $xProp as $xPropKey => $xPropValue ) {
                if( $propix != $xpropno ) {
                    $reduced[$xPropKey] = $xPropValue;
                }
                $xpropno++;
            }
        }
        $xProp = $reduced;
        if( empty( $xProp )) {
            $xProp = null;
            unset( $propdelix[$propName] );
            return false;
        }
        return true;
    }
}
