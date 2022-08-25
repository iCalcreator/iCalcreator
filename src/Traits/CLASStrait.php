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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

use function in_array;
use function strtoupper;

/**
 * CLASS property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait CLASStrait
{
    /**
     * @var null|Pc component property CLASS value
     */
    protected ? Pc $class = null;

    /**
     * @var string
     */
    protected static string $KLASS = 'class';

    /**
     * Return formatted output for calendar component property class
     *
     * @return string
     */
    public function createClass() : string
    {
        return Property::format(
            self::KLASS,
            $this->{self::$KLASS},
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property class
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteClass() : bool
    {
        $this->{self::$KLASS} = null;
        return true;
    }

    /**
     * Get calendar component property class
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getClass( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->{self::$KLASS} )) {
            return false;
        }
        return $inclParam ? clone $this->{self::$KLASS} : $this->{self::$KLASS}->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isClassSet() : bool
    {
        return ! empty( $this->{self::$KLASS}->value );
    }

    /**
     * Set calendar component property class
     *
     * @param null|string|Pc   $value  "PUBLIC" / "PRIVATE" / "CONFIDENTIAL" / iana-token / x-name
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setClass( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        static $STDVALUES = [
            self::P_BLIC,
            self::P_IVATE,
            self::CONFIDENTIAL
        ];
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::KLASS );
            $value->setEmpty();
        }
        elseif( ! in_array( $value->value, $STDVALUES, true )) {
            $value->value = Util::assertString( $value->value, self::KLASS );
            $value->value = StringFactory::trimTrailNL( $value->value );
            $value->value = strtoupper( $value->value );
        }
        $this->{self::$KLASS} = $value;
        return $this;
    }
}
