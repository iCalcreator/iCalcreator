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
 * EXRULE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.6 2019-06-23
 */
trait EXRULEtrait
{
    /**
     * @var array component property EXRULE value
     */
    protected $exrule = null;

    /**
     * Return formatted output for calendar component property exrule
     *
     * "Recur UNTIL, the value of the UNTIL rule part MUST have the same value type as the "DTSTART" property."
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.27.13 - 2019-01-09
     */
    public function createExrule()
    {
        return RecurFactory::formatRecur(
            self::EXRULE,
            $this->exrule,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property exrule
     *
     * @return static
     * @since 2.29.6 2019-06-23
     */
    public function deleteExrule()
    {
        $this->exrule = null;
        return $this;
    }

    /**
     * Get calendar component property exrule
     *
     * @param bool $inclParam
     * @return bool|array
     * @since 2.29.6 2019-06-27
     */
    public function getExrule( $inclParam = false )
    {
        if( empty( $this->exrule )) {
            return false;
        }
        return ( $inclParam ) ? $this->exrule : $this->exrule[Util::$LCvalue];
    }

    /**
     * Set calendar component property exrule
     *
     * @param array   $exruleset
     * @param array   $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.29.6 2019-06-23
     */
    public function setExrule( $exruleset = null, $params = [] )
    {
        if( empty( $exruleset )) {
            $this->assertEmptyValue( $exruleset, self::EXRULE );
            $exruleset = Util::$SP0;
            $params    = [];
        }
        $this->exrule = RecurFactory::setRexrule(
            $exruleset,
            array_merge( (array) $params, $this->getDtstartParams())
        );
        return $this;
    }
}
