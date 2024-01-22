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

use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\StringFactory;

/**
 * Format REQUEST_STATUS
 *
 * 1
 * @since 2.41.88 - 2024-01-18
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
            return StringFactory::$SP0;
        }
        $output   = StringFactory::$SP0;
        foreach( $values as $pc ) {
            $pcValue = $pc->getValue();
            if( ! empty( $pcValue )) {
                $content =
                    $pcValue[self::STATCODE] .
                    StringFactory::$SEMIC .
                    self::strrep( $pcValue[self::STATDESC] );
                if( isset( $pcValue[self::EXTDATA] )) {
                    $content .= StringFactory::$SEMIC . self::strrep( $pcValue[self::EXTDATA] );
                }
                $output .= self::renderProperty(
                    $propName,
                    self::formatParams((array) $pc->getParams(), [ self::LANGUAGE ], $lang ),
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
