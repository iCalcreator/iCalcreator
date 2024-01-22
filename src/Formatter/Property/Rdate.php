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

use DateInterval;
use Exception;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\SortFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Vcalendar;

use function count;
use function is_array;
use function usort;

/**
 * Format RDATE
 *
 * 1
 * @since 2.41.88 2024-01-17
 */
final class Rdate extends PropertyBase
{
    /**
     * @param string $propName
     * @param Pc[] $values
     * @param bool|null $allowEmpty
     * @param null|string $compType
     * @return string
     * @throws Exception
     * @since 2.41.88 2024-01-17
     */
    public static function format(
        string $propName,
        array $values,
        ? bool $allowEmpty = true,
        ? string $compType = null
    ) : string
    {
        static $SORTER1 = [ SortFactory::class, 'sortRdate1' ];
        static $SORTER2 = [ SortFactory::class, 'sortRdate2' ];
        if( empty( $values )) {
            return StringFactory::$SP0;
        }
        $utcTime      = Vcalendar::isTzComp( $compType );
        $output       = StringFactory::$SP0;
        $rDates       = [];
        foreach( $values as $theRdate ) { // Pc
            $theRdateValue = $theRdate->getValue();
            if( empty( $theRdateValue )) {
                if( $allowEmpty ) {
                    $output .= self::renderProperty( $propName );
                }
                continue;
            }
            if( $utcTime ) {
                $theRdate->removeParam( self::TZID );
            }
            if( 1 < count( $theRdateValue )) {
                usort( $theRdateValue, $SORTER1 );
                $theRdate->setValue( $theRdateValue );
            }
            $rDates[] = $theRdate;
        } // end foreach
        if( 1 < count( $rDates )) {
            usort( $rDates, $SORTER2 );
        }
        foreach( $rDates as $theRdate ) { // Pc
            $output .= self::formatPart( $theRdate, $propName );
        }
        return $output;
    }

    /**
     * Format a Rdate row
     *
     * @param Pc     $theRdate
     * @param string $propName
     * @return string
     * @throws Exception
     */
    private static function formatPart( Pc $theRdate, string $propName ) : string
    {
        $isPeriod    = $theRdate->hasParamValue( self::PERIOD );
        $isValueDate = $theRdate->hasParamValue( self::DATE ); // i.e. NOT datetime
        $isLocalTime = $theRdate->hasParamIsLocalTime();
        $rdateValue  = $theRdate->getValue();
        $cnt         = count( $rdateValue );
        $content     = StringFactory::$SP0;
        $rno         = 1;
        foreach( $rdateValue as $rdatePart ) {
            $content .= ( is_array( $rdatePart ) && $isPeriod )
                ? self::getPeriod( $rdatePart, $isValueDate, $isLocalTime ) // is period
                : DateTimeFactory::dateTime2Str( $rdatePart, $isValueDate, $isLocalTime ); // is date[time]
            if( $rno < $cnt ) {
                $content .= StringFactory::$COMMA;
            }
            $rno++;
        } // end foreach( $rDates as $theRdate )
        return self::renderProperty( $propName, (array) $theRdate->getParams(), $content );
    }

    /**
     * @param array $rdatePart
     * @param bool  $isValueDate
     * @param bool  $isLocalTime
     * @return string
     * @throws Exception
     */
    private static function getPeriod( array $rdatePart, bool $isValueDate, bool $isLocalTime ) : string
    {
        static $S = '/';
        // PERIOD, part 1
        $period  = DateTimeFactory::dateTime2Str( $rdatePart[0], $isValueDate, $isLocalTime );
        $period .= $S;
        // PERIOD, part 2
        $period .= ( $rdatePart[1] instanceof DateInterval )
            ? DateIntervalFactory::dateInterval2String( $rdatePart[1] )
            : DateTimeFactory::dateTime2Str( $rdatePart[1], $isValueDate, $isLocalTime ); // date-time
        return $period;
    }
}
