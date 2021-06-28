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

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * TZID property functions
 *
 * @since 2.27.3 2018-12-22
 */
trait TZIDtrait
{
    /**
     * @var array component property TZID value
     */
    protected $tzid = null;

    /**
     * Return formatted output for calendar component property tzid
     *
     * @return string
     */
    public function createTzid() : string
    {
        if( empty( $this->tzid )) {
            return Util::$SP0;
        }
        if( empty( $this->tzid[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::TZID )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::TZID,
            ParameterFactory::createParams( $this->tzid[Util::$LCparams] ),
            StringFactory::strrep( $this->tzid[Util::$LCvalue] )
        );
    }

    /**
     * Delete calendar component property tzid
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteTzid() : bool
    {
        $this->tzid = null;
        return true;
    }

    /**
     * Get calendar component property tzid
     *
     * @param null|bool   $inclParam
     * @return bool|array
     * @since  2.27.1 - 2018-12-13
     */
    public function getTzid( $inclParam = false )
    {
        if( empty( $this->tzid )) {
            return false;
        }
        return ( $inclParam ) ? $this->tzid : $this->tzid[Util::$LCvalue];
    }

    /**
     * Set calendar component property tzid
     *
     * @since 2.23.12 - 2017-04-22
     * @param null|string $value
     * @param null|array  $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.27.3 2018-12-22
     * @todo assert PHP timezone ?
     */
    public function setTzid( $value = null, $params = [] ) : self
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::TZID );
            $value  = Util::$SP0;
            $params = [];
        }
        $this->tzid = [
            Util::$LCvalue  => StringFactory::trimTrailNL( $value ),
            Util::$LCparams => ParameterFactory::setParams( $params ?? [] ),
        ];
        return $this;
    }
}
