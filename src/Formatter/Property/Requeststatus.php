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

use Kigkonsult\Icalcreator\Pc;

/**
 * Format REQUEST_STATUS
 *
 * 1
 * @since 2.41.59 - 2022-08-25
 */
final class Requeststatus extends PropertyBase
{
    /**
     * @param string $propName
     * @param Pc[] $values
     * @param null|bool $allowEmpty
     * @param null|bool|string $lang
     * @return string
     */
    public static function format(
        string $propName,
        array $values,
        ? bool $allowEmpty = true,
        null|bool|string $lang = false
    ) : string
    {
        if( empty( $values )) {
            return self::$SP0;
        }
        $output   = self::$SP0;
        foreach( $values as $pc ) {
            if( ! empty( $pc->value )) {
                $content =
                    $pc->value[self::STATCODE] .
                    self::$SEMIC .
                    self::strrep( $pc->value[self::STATDESC] );
                if( isset( $pc->value[self::EXTDATA] )) {
                    $content .= self::$SEMIC . self::strrep( $pc->value[self::EXTDATA] );
                }
                $output .= self::renderProperty(
                    $propName,
                    self::formatParams( $pc->params, [ self::LANGUAGE ], $lang ),
                    $content
                );
            }
            elseif( $allowEmpty ) {
                $output .= self::renderProperty( $propName );
            }
        } // end foreach
        return $output;
    }
}
