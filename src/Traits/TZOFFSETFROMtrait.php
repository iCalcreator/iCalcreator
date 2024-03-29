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
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
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
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;

use function sprintf;

/**
 * TZOFFSETFROM property functions
 *
 * @since 2.41.85 2024-01-18
 */
trait TZOFFSETFROMtrait
{
    /**
     * @var null|Pc component property TZOFFSETFROM value
     */
    protected ? Pc $tzoffsetfrom = null;

    /**
     * Return formatted output for calendar component property tzoffsetfrom
     *
     * @return string
     */
    public function createTzoffsetfrom() : string
    {
        return Property::format(
            self::TZOFFSETFROM,
            $this->tzoffsetfrom,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property tzoffsetfrom
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteTzoffsetfrom() : bool
    {
        $this->tzoffsetfrom = null;
        return true;
    }

    /**
     * Get calendar component property tzoffsetfrom
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.85 2024-01-18
     */
    public function getTzoffsetfrom( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->tzoffsetfrom )) {
            return false;
        }
        return $inclParam ? clone $this->tzoffsetfrom : $this->tzoffsetfrom->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isTzoffsetfromSet() : bool
    {
        return self::isPropSet( $this->tzoffsetfrom );
    }

    /**
     * Set calendar component property tzoffsetfrom
     *
     * @param null|string|Pc   $value
     * @param null|mixed[] $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.60 2022-08-24
     */
    public function setTzoffsetfrom( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $this->tzoffsetfrom = $this->conformTzOffset( self::TZOFFSETFROM, $value, $params );
        return $this;
    }

    /**
     * @param string $propName
     * @param string|Pc|null $value
     * @param array|null $params
     * @return Pc
     * @since 2.41.85 2024-01-18
     */
    private function conformTzOffset(
        string $propName,
        null|string|Pc $value = null,
        ? array $params = []
    ) : Pc
    {
        static $ERR = 'Invalid %s offset value %s';
        $pc         = Pc::factory( $value, $params );
        $pcValue    = $pc->getValue();
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, $propName );
            $pc->setEmpty();
        }
        elseif( ! DateTimeZoneFactory::hasOffset( $pcValue )) {
            throw new InvalidArgumentException( sprintf( $ERR,$propName, $pcValue ));
        }
        return $pc;
    }
}
