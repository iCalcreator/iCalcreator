<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2024 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

use function in_array;
use function strtoupper;

/**
 * CLASS property functions
 *
 * @since 2.41.85 2024-01-18
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
     * @since 2.41.85 2024-01-18
     */
    public function getClass( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->{self::$KLASS} )) {
            return false;
        }
        return $inclParam ? clone $this->{self::$KLASS} : $this->{self::$KLASS}->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isClassSet() : bool
    {
        return self::isPropSet( $this->{self::$KLASS} );
    }

    /**
     * Set calendar component property class
     *
     * @param null|string|Pc   $value  "PUBLIC" / "PRIVATE" / "CONFIDENTIAL" / iana-token / x-name
     * @param null|mixed[] $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setClass( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        static $STDVALUES = [
            self::P_BLIC,
            self::P_IVATE,
            self::CONFIDENTIAL
        ];
        $pc      = Pc::factory( $value, $params );
        $pcValue = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::KLASS );
            $pc->setEmpty();
        }
        elseif( ! in_array( $pcValue, $STDVALUES, true )) {
            $pcValue = Util::assertString( $pcValue, self::KLASS );
            $pcValue = StringFactory::trimTrailNL( $pcValue );
            $pc->setValue( strtoupper( $pcValue ));
        }
        $this->{self::$KLASS} = $pc;
        return $this;
    }
}
