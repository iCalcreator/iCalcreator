<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;

use function sprintf;

/**
 * TZOFFSETFROM property functions
 *
 * @since 2.27.3 2018-12-22
 */
trait TZOFFSETFROMtrait
{
    /**
     * @var array component property TZOFFSETFROM value
     */
    protected $tzoffsetfrom = null;

    /**
     * Return formatted output for calendar component property tzoffsetfrom
     *
     * @return string
     */
    public function createTzoffsetfrom() : string
    {
        if( empty( $this->tzoffsetfrom )) {
            return Util::$SP0;
        }
        if( empty( $this->tzoffsetfrom[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::TZOFFSETFROM )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::TZOFFSETFROM,
            ParameterFactory::createParams( $this->tzoffsetfrom[Util::$LCparams] ),
            $this->tzoffsetfrom[Util::$LCvalue]
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
     * @return bool|array
     * @since  2.27.1 - 2018-12-13
     */
    public function getTzoffsetfrom( $inclParam = false )
    {
        if( empty( $this->tzoffsetfrom )) {
            return false;
        }
        return ( $inclParam )
            ? $this->tzoffsetfrom
            : $this->tzoffsetfrom[Util::$LCvalue];
    }

    /**
     * Set calendar component property tzoffsetfrom
     *
     * @param null|string $value
     * @param null|array  $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.27.3 2019-03-14
     */
    public function setTzoffsetfrom( $value = null, $params = [] ) : self
    {
        static $ERR = 'Invalid %s offset value %s';
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::TZOFFSETFROM );
            $value  = Util::$SP0;
            $params = [];
        }
        elseif( ! DateTimeZoneFactory::hasOffset( $value )) {
            throw new InvalidArgumentException( sprintf( $ERR, Vcalendar::TZOFFSETFROM, $value ));
        }
        $this->tzoffsetfrom = [
            Util::$LCvalue  => $value,
            Util::$LCparams => ParameterFactory::setParams( $params ?? [] ),
        ];
        return $this;
    }
}
