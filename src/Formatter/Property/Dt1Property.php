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
namespace Kigkonsult\Icalcreator\Formatter\Property;

use Exception;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;

/**
 * Format DTSTART, RECURRENCE_ID and DUE, DTEND (has spec cond on DATE-format)
 *
 * 4
 * @since 2.41.66 2022-09-07
 */
final class Dt1Property extends PropertyBase
{
    /**
     * @param string $propName
     * @param null|bool|Pc $pc
     * @param null|bool $allowEmpty
     * @param null|bool $isDate
     * @param null|bool $isLocalTime
     * @return string
     * @throws Exception
     */
    public static function format(
        string $propName,
        null|bool|Pc $pc,
        ? bool $allowEmpty = true,
        ? bool $isDate = false,
        ? bool $isLocalTime = false
    ) : string
    {
        return match( true ) {
            empty( $pc )        => self::$SP0,
            empty( $pc->value ) => self::renderSinglePropEmpty( $propName, $allowEmpty ),
            default             => self::renderProperty(
                $propName,
                $pc->params,
                DateTimeFactory::dateTime2Str( $pc->value, $isDate, $isLocalTime )
            )
        };
    }

    /**
     * @param bool|Pc $dtStart
     * @param null|bool|Pc $endDue
     * @return bool
     */
    public static function getIsDate( bool | Pc $dtStart, null | bool | Pc $endDue = null ) : bool
    {
        return match ( true ) {
            ! empty( $dtStart ) => $dtStart->hasParamValue( self::DATE ),
            ! empty( $endDue )  => $endDue->hasParamValue( self::DATE ),
            default             => false
        };
    }

    public static function getIsLocalTime( null | bool | Pc $dtDate ) : bool
    {
        return ( ! empty( $dtDate ) && $dtDate->hasParamKey( self::ISLOCALTIME ) );
    }
}
