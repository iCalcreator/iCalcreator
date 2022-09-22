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

use DateTime;
use Exception;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;

use function count;
use function reset;
use function strcmp;
use function usort;

/**
 * Format EXDATE
 *
 * 1
 * @since 2.41.66 2022-09-07
 */
final class Exdate extends PropertyBase
{
    /**
     * @param string $propName
     * @param Pc[] $values
     * @param bool|null $allowEmpty
     * @return string
     * @throws Exception
     */
    public static function format( string $propName, array $values, ? bool $allowEmpty = true ) : string
    {
        static $SORTER1 = [ __CLASS__, 'sortExdate1', ];
        static $SORTER2 = [ __CLASS__, 'sortExdate2', ];
        if( empty( $values )) {
            return self::$SP0;
        }
        $output  = self::$SP0;
        $exdates = [];
        foreach( $values as $theExdate ) { // Pc
            if( empty( $theExdate->value )) {
                if( $allowEmpty ) {
                    $output .= self::renderProperty( $propName );
                }
                continue;
            }
            if( 1 < count( $theExdate->value )) {
                usort( $theExdate->value, $SORTER1 );
            }
            $exdates[] = $theExdate;
        } // end foreach
        if( 1 < count( $exdates )) {
            usort( $exdates, $SORTER2 );
        }
        $eix = 0;
        foreach( $exdates as $theExdate ) { // Pc
            $content = self::$SP0;
            foreach( $theExdate->value as $exDatePart ) {
                $formatted = DateTimeFactory::dateTime2Str(
                    $exDatePart,
                    $theExdate->hasParamValue(self::DATE ),
                    $theExdate->hasParamKey( self::ISLOCALTIME )
                );
                $content .= ( 0 < $eix++ ) ? self::$COMMA . $formatted : $formatted;
            } // end foreach
            $output .= self::renderProperty( $propName, $theExdate->params, $content );
        } // end foreach(( array_keys( $exdates...
        return $output;
    }

    /**
     * Sort callback function for exdate
     *
     * @param DateTime $a
     * @param DateTime $b
     * @return int
     * @since 2.29.2 2019-06-23
     */
    public static function sortExdate1( DateTime $a, DateTime $b ) : int
    {
        return strcmp(
            $a->format( DateTimeFactory::$YmdTHis ),
            $b->format( DateTimeFactory::$YmdTHis )
        );
    }

    /**
     * Sort callback function for exdate
     *
     * @param Pc $a
     * @param Pc $b
     * @return int
     * @since 2.29.2 2019-06-23
     */
    public static function sortExdate2( Pc $a, Pc $b ) : int
    {
        $a1 = reset( $a->value );
        $b1 = reset( $b->value );
        return strcmp(
            $a1->format( DateTimeFactory::$YmdTHis ),
            $b1->format( DateTimeFactory::$YmdTHis )
        );
    }
}
