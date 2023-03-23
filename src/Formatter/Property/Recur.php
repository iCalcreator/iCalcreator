<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2023 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
use Kigkonsult\Icalcreator\Util\Util;

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
 * @since 2.41.71 2022-11-28
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
        string $propName,
        null|bool|Pc $pc,
        ? bool $allowEmpty = true
    ) : string
    {
        if( empty( $pc )) {
            return self::$SP0;
        }
        if( empty( $pc->value )) {
            return $allowEmpty ? self::renderProperty( $propName ) : self::$SP0;
        }
        $content1 = $content2 = self::$SP0;
        $first    = true;
        foreach( $pc->value as $ruleLabel => $ruleValue ) {
            $ruleLabel = strtoupper( $ruleLabel );
            switch( $ruleLabel ) {
                case self::RSCALE : // fall through
                case self::FREQ :
                    $content1 .= self::renderFirst( $ruleLabel, $ruleValue, $first );
                    break;
                case self::UNTIL :
                    $ruleValue = DateTimeFactory::dateTime2Str( $ruleValue, self::getIsValueDate( $pc )); // fall through
                case self::COUNT :    // fall through
                case self::INTERVAL : // fall through
                case self::WKST :     // fall through
                    $content2 .= self::renderString( $ruleLabel, (string) $ruleValue );
                    break;
                case self::BYDAY :
                    $content2 .= self::renderByday( $ruleValue );
                    break;
                default : // BYSECOND/BYMINUTE/BYHOUR/BYMONTHDAY/BYYEARDAY/BYWEEKNO/BYMONTH/BYSETPOS...
                    $content2 .= self::renderDefault( $ruleLabel, $ruleValue );
                    break;
            } // end switch( $ruleLabel )
        } // end foreach( $pc->value as $ruleLabel => $ruleValue )
        return self::renderProperty( $propName, $pc->params,$content1 . $content2 );
    }

    /**
     * @param Pc $pc
     * @return bool
     */
    private static function getIsValueDate( Pc $pc ) : bool
    {
        $isValueDate = $pc->hasParamValue( self::DATE );
        if( ! empty( $pc->params )) {
            $pc = clone $pc;
            $pc->removeParam( self::VALUE );
        }
        return $isValueDate;
    }

    /**
     * @param string $ruleLabel
     * @param string $ruleValue
     * @param bool $first
     * @return string
     */
    private static function renderFirst( string $ruleLabel, string $ruleValue, bool & $first ) : string
    {
        static $FMT = '%s%s=%s';
        $output = sprintf( $FMT, ( $first ? Util::$SP0 : Util::$SEMIC ), $ruleLabel, $ruleValue );
        $first  = false;
        return $output;
    }

    /**
     * @param string $ruleLabel
     * @param string $ruleValue
     * @return string
     */
    private static function renderString( string $ruleLabel, string $ruleValue ) : string
    {
        static $FMT = ';%s=%s';
        return sprintf( $FMT, $ruleLabel, $ruleValue );
    }

    /**
     * @param string[]|string[][] $ruleValue
     * @return string
     */
    private static function renderByday( array $ruleValue ) : string
    {
        static $RECURBYDAYSORTER = [ __CLASS__, 'recurBydaySort' ];
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
        return self::renderString( self::BYDAY, implode( self::$COMMA, $byday ));
    }

    /**
     * Sort recur BYDAYs
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

    /**
     * @param string $ruleLabel
     * @param int|string|string[] $ruleValue
     * @return string
     */
    private static function renderDefault( string $ruleLabel, int|string|array $ruleValue ) : string
    {
        return self::renderString(
            $ruleLabel,
            ( is_array( $ruleValue ) ? implode( self::$COMMA, $ruleValue ) : ( string) $ruleValue )
        );
    }
}
