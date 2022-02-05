<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software iCalcreator.
 *            The above copyright, link, package and version notices,
 *            this licence notice and the invariant [rfc5545] PRODID result use
 *            as implemented and invoked in iCalcreator shall be included in
 *            all copies or substantial portions of the iCalcreator.
 *
 *            iCalcreator is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            iCalcreator is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator\Traits;

use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * RRULE property functions
 *
 * @since 2.40.11 2022-01-15
 */
trait RRULEtrait
{
    /**
     * @var null|mixed[] component property RRULE value
     */
    protected ? array $rrule = null;

    /**
     * Return formatted output for calendar component property rrule
     *
     * "Recur UNTIL, the value of the UNTIL rule part MUST have the same value type as the "DTSTART" property."
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.27.13 - 2019-01-09
     */
    public function createRrule() : string
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
    public function deleteRrule() : static
    {
        $this->rrule = null;
        return $this;
    }

    /**
     * Get calendar component property rrule
     *
     * @param null|bool   $inclParam
     * @return bool|string|mixed[]
     * @since 2.29.6 2019-06-23
     */
    public function getRrule( ? bool $inclParam = false ) : bool | array | string
    {
        if( empty( $this->rrule )) {
            return false;
        }
        return $inclParam ? $this->rrule : $this->rrule[Util::$LCvalue];
    }

    /**
     * Set calendar component property rrule
     *
     * @param null|mixed[]  $rruleset  string[]
     * @param null|mixed[]  $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.29.6 2019-06-23
     */
    public function setRrule( ? array $rruleset = null, ? array $params = [] ) : static
    {
        if( empty( $rruleset )) {
            $this->assertEmptyValue( $rruleset, self::RRULE );
            $rruleset = [];
            $params   = [];
        }
        $this->rrule = RecurFactory::setRexrule(
            $rruleset,
            array_merge( $params ?? [], $this->getDtstartParams())
        );
        return $this;
    }
}
