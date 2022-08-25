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
 * EXRULE property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait EXRULEtrait
{
    /**
     * @var null|Pc component property EXRULE value
     */
    protected ? Pc $exrule = null;

    /**
     * Return formatted output for calendar component property exrule
     *
     * "Recur UNTIL, the value of the UNTIL rule part MUST have the same value type as the "DTSTART" property."
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.55 - 2022-08-13
     */
    public function createExrule() : string
    {
        return Recur::format(
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
    public function deleteExrule() : static
    {
        $this->exrule = null;
        return $this;
    }

    /**
     * Get calendar component property exrule
     *
     * @param null|bool $inclParam
     * @return bool|array|Pc
     * @since 2.41.41 2022-04-15
     */
    public function getExrule( ? bool $inclParam = false ) : bool | array | Pc
    {
        if( empty( $this->exrule )) {
            return false;
        }
        return $inclParam ? clone $this->exrule : $this->exrule->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isExruleSet() : bool
    {
        return ! empty( $this->exrule->value );
    }

    /**
     * Set calendar component property exrule
     *
     * @param null|array $exruleset
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public function setExrule( ? array $exruleset = null, ? array $params = [] ) : static
    {
        $value = ( $exruleset instanceof Pc )
            ? clone $exruleset
            : Pc::factory( $exruleset, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::EXRULE );
            $value->setEmpty();
        }
        else {
            foreach( $this->getDtstartParams() as $k => $v ) {
                $value->addParam( $k, $v );
            }
        }
        $this->exrule = RecurFactory::setRexrule( $value );
        return $this;
    }
}
