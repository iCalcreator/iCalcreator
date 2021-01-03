<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
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

use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * RRULE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.6 2019-06-23
 */
trait RRULEtrait
{
    /**
     * @var array component property RRULE value
     */
    protected $rrule = null;

    /**
     * Return formatted output for calendar component property rrule
     *
     * "Recur UNTIL, the value of the UNTIL rule part MUST have the same value type as the "DTSTART" property."
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.27.13 - 2019-01-09
     */
    public function createRrule()
    {
        return RecurFactory::formatRecur(
            self::RRULE,
            $this->rrule,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property rrule
     *
     * @return static
     * @since 2.29.6 2019-06-23
     */
    public function deleteRrule()
    {
        $this->rrule = null;
        return $this;
    }

    /**
     * Get calendar component property rrule
     *
     * @param bool   $inclParam
     * @return bool|array
     * @since 2.29.6 2019-06-23
     */
    public function getRrule( $inclParam = false )
    {
        if( empty( $this->rrule )) {
            return false;
        }
        return ( $inclParam ) ? $this->rrule : $this->rrule[Util::$LCvalue];
    }

    /**
     * Set calendar component property rrule
     *
     * @param array   $rruleset
     * @param array   $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.29.6 2019-06-23
     */
    public function setRrule( $rruleset = null, $params = [] )
    {
        if( empty( $rruleset )) {
            $this->assertEmptyValue( $rruleset, self::RRULE );
            $rruleset = Util::$SP0;
            $params   = [];
        }
        $this->rrule = RecurFactory::setRexrule(
            $rruleset,
            array_merge( (array) $params, $this->getDtstartParams())
        );
        return $this;
    }
}
