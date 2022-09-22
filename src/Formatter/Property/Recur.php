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

use function count;
use function ctype_digit;
use function implode;
use function is_array;
use function sprintf;
use function strtoupper;
use function substr;
use function usort;

/**
 * Format EXRULE, RRULE
 *
 * 2
 * @since 2.41.66 2022-09-07
 */
final class Recur extends PropertyBase
{
    /**
     * @param string $propName
     * @param null|bool|Pc $pc
     * @param bool|null $allowEmpty
     * @return string
     * @throws Exception
     */
    public static function format(
        string  $propName,
        null|bool|Pc $pc,
        ? bool  $allowEmpty = true
    ) : string
    {
        static $FMTRSCALEEQ      = 'RSCALE=%s';
        static $FMTFREQEQ        = 'FREQ=%s';
        static $FMTDEFAULTEQ     = ';%s=%s';
        static $FMTOTHEREQ       = ';%s=';
        static $RECURBYDAYSORTER = [ __CLASS__, 'recurBydaySort' ];
        if( empty( $pc )) {
            return self::$SP0;
        }
        if( empty( $pc->value )) {
            return ( $allowEmpty ) ? self::renderProperty( $propName ) : self::$SP0;
        }
        $output      = self::$SP0;
        $isValueDate = $pc->hasParamValue( self::DATE );
        if( ! empty( $pc->params )) {
            $pc = clone $pc;
            $pc->removeParam( self::VALUE );
        }
        $content1 = $content2 = null;
        foreach( $pc->value as $ruleLabel => $ruleValue ) {
            $ruleLabel = strtoupper( $ruleLabel );
            switch( $ruleLabel ) {
                case self::RSCALE :
                    $content1 .= sprintf( $FMTRSCALEEQ, $ruleValue );
                    break;
                case self::FREQ :
                    if( ! empty( $content1 )) {
                        $content1 .= self::$SEMIC;
                    }
                    $content1 .= sprintf( $FMTFREQEQ, $ruleValue );
                    break;
                case self::UNTIL :
                    $content2 .= sprintf(
                        $FMTDEFAULTEQ,
                        self::UNTIL,
                        DateTimeFactory::dateTime2Str( $ruleValue, $isValueDate )
                    );
                    break;
                case self::COUNT :
                case self::INTERVAL :
                case self::WKST :
                    $content2 .= sprintf( $FMTDEFAULTEQ, $ruleLabel, $ruleValue );
                    break;
                case self::BYDAY :
                    $byday = [ self::$SP0 ];
                    $bx    = 0;
                    foreach( $ruleValue as $bydayPart ) {
                        if( ! empty( $byday[$bx] ) &&   // new day
                            ! ctype_digit( substr( $byday[$bx], -1 ))) {
                            $byday[++$bx] = self::$SP0;
                        }
                        if( ! is_array( $bydayPart )) {  // day without rel pos number
                            $byday[$bx] .= $bydayPart;
                        }
                        else {                          // day with rel pos number
                            foreach( $bydayPart as $bydayPart2 ) {
                                $byday[$bx] .= $bydayPart2;
                            }
                        }
                    } // end foreach( $ruleValue as $bydayPart )
                    if( 1 < count( $byday )) {
                        usort( $byday, $RECURBYDAYSORTER );
                    }
                    $content2 .= sprintf(
                        $FMTDEFAULTEQ,
                        self::BYDAY,
                        implode( self::$COMMA, $byday )
                    );
                    break;
                default : // BYSECOND/BYMINUTE/BYHOUR/BYMONTHDAY/BYYEARDAY/BYWEEKNO/BYMONTH/BYSETPOS...
                    if( is_array( $ruleValue )) {
                        $content2 .= sprintf( $FMTOTHEREQ, $ruleLabel );
                        $content2 .= implode( self::$COMMA, $ruleValue );
                    }
                    else {
                        $content2 .= sprintf( $FMTDEFAULTEQ, $ruleLabel, $ruleValue );
                    }
                    break;
            } // end switch( $ruleLabel )
        } // end foreach( $pc->value as $ruleLabel => $ruleValue )
        $output .= self::renderProperty( $propName, $pc->params,$content1 . $content2 );
        return $output;
    }

    /**
     * Sort recur dates
     *
     * @param string $byDayA
     * @param string $byDayB
     * @return int
     */
    private static function recurBydaySort( string $byDayA, string $byDayB ) : int
    {
        static $days = [
            self::SU => 0,
            self::MO => 1,
            self::TU => 2,
            self::WE => 3,
            self::TH => 4,
            self::FR => 5,
            self::SA => 6,
        ];
        return ( $days[substr( $byDayA, -2 )] < $days[substr( $byDayB, -2 )] )
            ? -1
            : 1;
    }
}
