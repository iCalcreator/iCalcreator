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
use Kigkonsult\Icalcreator\Util\UtilGeo;

use function floatval;
use function is_array;

/**
 * GEO property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-02
 */
trait GEOtrait
{
    /**
     * @var array component property GEO value
     * @access protected
     */
    protected $geo = null;

    /**
     * Return formatted output for calendar component property geo
     *
     * @return string
     */
    public function createGeo() {
        if( empty( $this->geo )) {
            return null;
        }
        if( empty( $this->geo[Util::$LCvalue] )) {
            return ( $this->getConfig( Util::$ALLOWEMPTY )) ? Util::createElement( Util::$GEO ) : null;
        }
        return Util::createElement(
            Util::$GEO,
            Util::createParams(
                $this->geo[Util::$LCparams] ),
            UtilGeo::geo2str2( $this->geo[Util::$LCvalue][UtilGeo::$LATITUDE], UtilGeo::$geoLatFmt ) .
                Util::$SEMIC .
                UtilGeo::geo2str2( $this->geo[Util::$LCvalue][UtilGeo::$LONGITUDE], UtilGeo::$geoLongFmt ));
    }

    /**
     * Set calendar component property geo
     *
     * @param mixed $latitude
     * @param mixed $longitude
     * @param array $params
     * @return bool
     */
    public function setGeo( $latitude, $longitude, $params = null ) {
        if( isset( $latitude ) && isset( $longitude )) {
            if( ! is_array( $this->geo )) {
                $this->geo = [];
            }
            $this->geo[Util::$LCvalue][UtilGeo::$LATITUDE]  = floatval( $latitude );
            $this->geo[Util::$LCvalue][UtilGeo::$LONGITUDE] = floatval( $longitude );
            $this->geo[Util::$LCparams]                     = Util::setParams( $params );
        }
        elseif( $this->getConfig( Util::$ALLOWEMPTY )) {
            $this->geo = [
                Util::$LCvalue  => Util::$SP0,
                Util::$LCparams => Util::setParams( $params ),
            ];
        }
        else {
            return false;
        }
        return true;
    }
}
