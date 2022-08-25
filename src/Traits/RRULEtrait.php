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
use Kigkonsult\Icalcreator\Formatter\Property\Recur;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\RecurFactory;

/**
 * RRULE property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait RRULEtrait
{
    /**
     * @var null|Pc component property RRULE value
     */
    protected ? Pc $rrule = null;

    /**
     * Return formatted output for calendar component property rrule
     *
     * "Recur UNTIL, the value of the UNTIL rule part MUST have the same value type as the "DTSTART" property."
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.55 - 2022-08-13
     */
    public function createRrule() : string
    {
        return Recur::format(
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
     * @return bool|array|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getRrule( ? bool $inclParam = false ) : bool | array | Pc
    {
        if( empty( $this->rrule )) {
            return false;
        }
        return $inclParam ? clone $this->rrule : $this->rrule->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isRruleSet() : bool
    {
        return ! empty( $this->rrule->value );
    }

    /**
     * Set calendar component property rrule
     *
     * @param null|array|Pc  $rruleset  string[]
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public function setRrule( null|array|Pc $rruleset = null, ? array $params = [] ) : static
    {
        $value = ( $rruleset instanceof Pc )
            ? clone $rruleset
            : Pc::factory( $rruleset, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::RRULE );
            $value->setEmpty();
        }
        else {
            foreach( $this->getDtstartParams() as $k => $v ) {
                $value->addParam( $k, $v );
            }
        }
        $this->rrule = RecurFactory::setRexrule( $value );
        return $this;
    }
}
