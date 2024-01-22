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
namespace Kigkonsult\Icalcreator\Formatter\Property;

use Exception;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;

/**
 * Format ACKNOWLEDGED, COMPLETED, CREATED, DTSTAMP, LAST_MODIFIED, TZUNTIL
 *
 * 6
 * @since 2.41.88 2024-01-18
 */
final class DtxProperty extends PropertyBase
{
    /**
     * @param string $propName
     * @param null|bool|Pc $pc
     * @param bool|null $allowEmpty
     * @return string
     * @throws Exception
     * @since 2.41.88 2024-01-18
     */
    public static function format( string $propName, null|bool|Pc $pc, ? bool $allowEmpty = true ) : string
    {
        if( empty( $pc )) {
            return StringFactory::$SP0;
        }
        $pcValue = $pc->getValue();
        return empty( $pcValue )
            ? self::renderSinglePropEmpty( $propName, $allowEmpty )
            : self::renderProperty(
                $propName,
                $pc->getParams(),
                DateTimeFactory::dateTime2Str( $pcValue )
            );
    }
}
