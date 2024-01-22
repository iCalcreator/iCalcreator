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

use Kigkonsult\Icalcreator\Util\StringFactory;
use function is_array;
use function is_numeric;

/**
 * Format X-properties
 *
 * @since 2.41.88 2024-01-18
 */
final class Xproperty extends PropertyBase
{
    /**
     * @param array            $values  [ *( propname, Pc ) ]
     * @param null|bool        $allowEmpty
     * @param null|bool|string $lang
     * @return string
     * @since 2.41.88 2024-01-18
     */
    public static function format(
        array $values,
        ? bool $allowEmpty = true,
        null|bool|string $lang = false
    ) : string
    {
        $output = StringFactory::$SP0;
        if( empty( $values )) {
            return $output;
        }
        foreach( $values as $xpropBase ) {
            [ $xpropName, $xpropPc ] = $xpropBase;
            $xpropPcValue = $xpropPc->getValue();
            if( ! $xpropPc->isset() ||
                ( empty( $xpropPcValue ) && ! is_numeric( $xpropPcValue ))) {
                if( $allowEmpty ) {
                    $output .= self::renderProperty( $xpropName );
                }
                continue;
            }
            if( is_array( $xpropPcValue )) {
                foreach( $xpropPcValue as $pix => $theXpart ) {
                    $xpropPcValue[$pix] = self::strrep( $theXpart );
                }
                $xpropPcValue = implode( StringFactory::$COMMA, $xpropPcValue );
            }
            else {
                $xpropPcValue = self::strrep( $xpropPcValue );
            }
            $output .= self::renderProperty(
                $xpropName,
                self::formatParams( $xpropPc->params, [ self::LANGUAGE ], $lang ),
                $xpropPcValue
            );
        } // end foreach
        return $output;
    }
}
