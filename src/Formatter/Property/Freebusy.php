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

use DateInterval;
use Exception;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\SortFactory;

use function sprintf;
use function usort;

/**
 * Format FREEBUSY
 *
 * 1
 * @since 2.41.59 - 2022-08-25
 */
final class Freebusy extends PropertyBase
{
    /**
     * @param string $propName
     * @param Pc[] $values
     * @param bool|null $allowEmpty
     * @return string
     * @throws Exception
     */
    public static function format( string $propName, array $values , ? bool $allowEmpty = true ) : string
    {
        static $FMT    = ';FBTYPE=%s';
        static $SORTER = [ SortFactory::class, 'sortRdate1' ];
        if( empty( $values )) {
            return self::$SP0;
        }
        $output = self::$SP0;
        foreach( $values as $freebusyPart ) { // Pc
            if( empty( $freebusyPart->value )) {
                if( $allowEmpty ) {
                    $output .= self::renderProperty( $propName );
                }
                continue;
            }
            $params      = $freebusyPart->getParams();
            $attributes  = sprintf( $FMT, $params[self::FBTYPE] ); // always set
            unset( $params[self::FBTYPE] );
            $attributes .= self::formatParams( $params );
            $cnt         = count( $freebusyPart->value );
            if( 1 < $cnt ) {
                usort( $freebusyPart->value, $SORTER );
            }
            $content      = self::$SP0;
            foreach( $freebusyPart->value as $freebusyPeriod ) {
                if( ! empty( $content )) {
                    $content .= self::$COMMA;
                }
                $content .= DateTimeFactory::dateTime2Str( $freebusyPeriod[0] );
                $content .= self::$SLASH;
                if( $freebusyPeriod[1] instanceof DateInterval ) {  // period with duration
                    $content .= DateIntervalFactory::dateInterval2String( $freebusyPeriod[1] );
                }
                else {  // period ends with date-time
                    $content .= DateTimeFactory::dateTime2Str( $freebusyPeriod[1] );
                }
            } // end foreach
            $output .= self::renderProperty( $propName, $attributes, $content );
        } // end foreach( $values as $freebusyPart )
        return $output;
    }
}
