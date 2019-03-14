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

use function filter_var;
use function substr;

/**
 * URL property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.22.23 - 2017-02-05
 */
trait URLtrait
{
    /**
     * @var array component property URL value
     * @access protected
     */
    protected $url = null;

    /**
     * Return formatted output for calendar component property url
     *
     * @return string
     */
    public function createUrl() {
        if( empty( $this->url )) {
            return null;
        }
        if( empty( $this->url[Util::$LCvalue] )) {
            return ( $this->getConfig( Util::$ALLOWEMPTY )) ? Util::createElement( Util::$URL ) : null;
        }
        return Util::createElement(
            Util::$URL,
            Util::createParams( $this->url[Util::$LCparams] ),
            $this->url[Util::$LCvalue]
        );
    }

    /**
     * Set calendar component property url
     *
     * @param string $value
     * @param array  $params
     * @return bool
     */
    public function setUrl( $value, $params = null ) {
        static $URN = 'urn';
        if( ! empty( $value )) {
            if( ! filter_var( $value, FILTER_VALIDATE_URL ) &&
                ( 0 != strcasecmp( $URN, substr( $value, 0, 3 )))) {
                return false;
            }
        }
        elseif( $this->getConfig( Util::$ALLOWEMPTY )) {
            $value = Util::$SP0;
        }
        else {
            return false;
        }
        $this->url = [
            Util::$LCvalue  => $value,
            Util::$LCparams => Util::setParams( $params ),
        ];
        return true;
    }
}
