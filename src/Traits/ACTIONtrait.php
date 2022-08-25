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

use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

use Kigkonsult\Icalcreator\Util\Util;
use function strtoupper;

/**
 * ACTION property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait ACTIONtrait
{
    /**
     * @var null|Pc component property ACTION value
     */
    protected ? Pc $action = null;

    /**
     * Return formatted output for calendar component property action
     *
     * @return string
     */
    public function createAction() : string
    {
        return Property::format(
            self::ACTION,
            $this->action,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property action
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteAction() : bool
    {
        $this->action = null;
        return true;
    }

    /**
     * Get calendar component property action
     *
     * @param null|bool  $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getAction( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->action )) {
            return false;
        }
        return $inclParam ? clone $this->action : $this->action->value;
    }

    /**
     * Return bool true if set (ignore 'empty' property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isActionSet() : bool
    {
        return ! empty( $this->action->value );
    }

    /**
     * Set calendar component property action
     *
     * @param null|string|Pc   $value "AUDIO" / "DISPLAY" / "EMAIL" / "PROCEDURE"  / iana-token / x-name ??
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setAction( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        static $STDVALUES = [
            self::AUDIO,
            self::DISPLAY,
            self::EMAIL,
            self::PROCEDURE  // deprecated in rfc5545
        ];
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::ACTION );
            $value->value  = self::$SP0;
            $value->removeParam();
        }
        elseif( ! in_array( $value->value, $STDVALUES, true )) {
            $value->value = Util::assertString( $value->value, self::ACTION );
            $value->value = strtoupper( StringFactory::trimTrailNL( $value->value ));
        }
        $this->action  = $value;
        return $this;
    }
}
