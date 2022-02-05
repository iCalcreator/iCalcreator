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

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

use function strtoupper;

/**
 * rfc7953 BUSYTYPE property functions
 *
 * @since 2.41.9 2022-01-22
 */
trait BUSYTYPErfc7953trait
{
    /**
     * @var null|mixed[] component property busytype value
     */
    protected ? array $busytype = null;

    /**
     * Return formatted output for calendar component property busytype
     *
     * @return string
     */
    public function createBusytype() : string
    {
        if( empty( $this->busytype )) {
            return Util::$SP0;
        }
        if( empty( $this->busytype[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::BUSYTYPE )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::BUSYTYPE,
            ParameterFactory::createParams( $this->busytype[Util::$LCparams] ),
            $this->busytype[Util::$LCvalue]
        );
    }

    /**
     * Delete calendar component property busytype
     *
     * @return bool
     */
    public function deleteBusytype() : bool
    {
        $this->busytype = null;
        return true;
    }

    /**
     * Get calendar component property busytype
     *
     * @param null|bool   $inclParam
     * @return bool|string|mixed[]
     */
    public function getBusytype( ? bool $inclParam = false ) : array | bool | string
    {
        if( empty( $this->busytype )) {
            return false;
        }
        return $inclParam ? $this->busytype : $this->busytype[Util::$LCvalue];
    }

    /**
     * Set calendar component property busytype
     *
     * @param null|string   $value
     * @param null|mixed[]  $params
     * @return static
     * @throws InvalidArgumentException
     */
    public function setBusytype( ? string $value = null, ? array $params = [] ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::BUSYTYPE );
            $value  = Util::$SP0;
            $params = [];
        }
        else {
            $value = strtoupper( StringFactory::trimTrailNL( $value ));
        }
        $this->busytype = [
            Util::$LCvalue  => $value,
            Util::$LCparams => ParameterFactory::setParams( $params ),
        ];
        return $this;
    }
}
