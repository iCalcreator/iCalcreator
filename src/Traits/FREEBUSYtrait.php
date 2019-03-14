<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.8
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\UtilDuration;
use DateTime;
use DateTimeZone;
use DateInterval;
use Exception;

use function count;
use function in_array;
use function is_array;
use function is_string;
use function sprintf;
use function strlen;
use function usort;

/**
 * FREEBUSY property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26.8 - 2018-12-12
 */
trait FREEBUSYtrait
{
    /**
     * @var array component property FREEBUSY value
     * @access protected
     */
    protected $freebusy = null;
    /**
     * @var string FREEBUSY param keywords
     * @access protected
     * @static
     */
    protected static $LCFBTYPE     = 'fbtype';
    protected static $UCFBTYPE     = 'FBTYPE';
    protected static $FREEBUSYKEYS = [ 'FREE', 'BUSY', 'BUSY-UNAVAILABLE', 'BUSY-TENTATIVE' ];
    protected static $FREE         = 'FREE';
    protected static $BUSY         = 'BUSY';
    /*
       protected static $BUSY_UNAVAILABLE = 'BUSY-UNAVAILABLE';
       protected static $BUSY_TENTATIVE   = 'BUSY-TENTATIVE';
    */
    /**
     * Return formatted output for calendar component property freebusy
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-02
     * @return string
     */
    public function createFreebusy() {
        static $FMT = ';FBTYPE=%s';
        static $SORTER = [ 'Kigkonsult\Icalcreator\Util\VcalendarSortHandler', 'sortRdate1' ];
        if( empty( $this->freebusy )) {
            return null;
        }
        $output = null;
        foreach( $this->freebusy as $fx => $freebusyPart ) {
            if( empty( $freebusyPart[Util::$LCvalue] ) ||
                (( 1 == count( $freebusyPart[Util::$LCvalue] )) &&
                    isset( $freebusyPart[Util::$LCvalue][self::$LCFBTYPE] ))) {
                if( $this->getConfig( Util::$ALLOWEMPTY )) {
                    $output .= Util::createElement( Util::$FREEBUSY );
                }
                continue;
            }
            $attributes = $content = null;
            if( isset( $freebusyPart[Util::$LCvalue][self::$LCFBTYPE] )) {
                $attributes .= sprintf( $FMT, $freebusyPart[Util::$LCvalue][self::$LCFBTYPE] );
                unset( $freebusyPart[Util::$LCvalue][self::$LCFBTYPE] );
                $freebusyPart[Util::$LCvalue] = array_values( $freebusyPart[Util::$LCvalue] );
            }
            else {
                $attributes .= sprintf( $FMT, self::$BUSY );
            }
            $attributes .= Util::createParams( $freebusyPart[Util::$LCparams] );
            $fno        = 1;
            $cnt        = count( $freebusyPart[Util::$LCvalue] );
            if( 1 < $cnt ) {
                usort( $freebusyPart[Util::$LCvalue], $SORTER );
            }
            foreach( $freebusyPart[Util::$LCvalue] as $periodix => $freebusyPeriod ) {
                $content   .= Util::date2strdate( $freebusyPeriod[0] );
                $content   .= Util::$L;
                if( isset( $freebusyPeriod[1]['invert'] )) { // fix pre 7.0.5 bug
                    $dateInterval = UtilDuration::DateIntervalArr2DateInterval( $freebusyPeriod[1] );
                        // period=  -> duration
                    $content .= UtilDuration::dateInterval2String( $dateInterval );
                }
                else {  // period=  -> date-time
                    $content .= Util::date2strdate( $freebusyPeriod[1] );
                }
                if( $fno < $cnt ) {
                    $content .= Util::$COMMA;
                }
                $fno++;
            } // end foreach
            $output .= Util::createElement( Util::$FREEBUSY, $attributes, $content );
        } // end foreach( $this->freebusy as $fx => $freebusyPart )
        return $output;
    }

    /**
     * Set calendar component property freebusy
     *
     * @param string  $fbType
     * @param array   $fbValues
     * @param array   $params
     * @param integer $index
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.27 - 2018-12-02
     */
    public function setFreebusy( $fbType, $fbValues, $params = null, $index = null ) {
        if( empty( $fbValues )) {
            if( $this->getConfig( Util::$ALLOWEMPTY )) {
                Util::setMval(
                    $this->freebusy,
                    Util::$SP0,
                    $params,
                    false,
                    $index
                );
                return true;
            }
            else {
                return false;
            }
        }
        $fbType = strtoupper( $fbType );
        if( ! in_array( $fbType, self::$FREEBUSYKEYS ) && ! Util::isXprefixed( $fbType )) {
            $fbType = self::$BUSY;
        }
        $input = [ self::$LCFBTYPE => $fbType ];
        foreach( $fbValues as $fbPeriod ) {                   // periods => period
            if( empty( $fbPeriod )) {
                continue;
            }
            $freebusyPeriod = [];
            foreach( $fbPeriod as $fbMember ) { // pairs => singlepart
                $freebusyPairMember = [];
                switch( true ) {
                    case ( $fbMember instanceof DateTime ) :     // datetime
                        $fbMember->setTimezone((new DateTimeZone( Util::$UTC )));
                        $date = Util::dateTime2Str( $fbMember );
                        Util::strDate2arr( $date );
                        $freebusyPairMember = $date;
                        $freebusyPairMember[Util::$LCtz] = Util::$Z;
                        break;
                    case ( $fbMember instanceof DateInterval ) : // interval
                        $freebusyPairMember = (array) $fbMember; // fix pre 7.0.5 bug
                        break;
                    case ( is_array( $fbMember )) :
                        if( Util::isArrayDate( $fbMember )) {    // date-time value
                            $freebusyPairMember              = Util::chkDateArr( $fbMember, 7 );
                            $freebusyPairMember[Util::$LCtz] = Util::$Z;
                        }
                        elseif( Util::isArrayTimestampDate( $fbMember )) { // timestamp value
                            $freebusyPairMember = Util::timestamp2date( $fbMember[Util::$LCTIMESTAMP], 7 );
                            $freebusyPairMember[Util::$LCtz] = Util::$Z;
                        }
                        else {                                    // array format duration
                            try {  // fix pre 7.0.5 bug
                                $freebusyPairMember = (array) UtilDuration::conformDateInterval(
                                    new DateInterval(
                                        UtilDuration::duration2str(
                                            UtilDuration::duration2arr( $fbMember )
                                        )
                                    )
                                );
                            }
                            catch( Exception $e ) {
                                return false;
                            }
                        }
                        break;
                    case ( ! is_string( $fbMember )) :
                        continue;
                        break;
                    case (( 3 <= strlen( trim( $fbMember ))) && ( in_array( $fbMember{0}, UtilDuration::$PREFIXARR ))) :
                        // string format duration
                        if( in_array( $fbMember{0}, Util::$PLUSMINUSARR )) { // can only be positive
                            $fbMember = substr( $fbMember, 1 );
                        }
                        try {  // fix pre 7.0.5 bug
                            $freebusyPairMember = (array) UtilDuration::conformDateInterval( new DateInterval( $fbMember ));
                        }
                        catch( Exception $e ) {
                            return false;
                        }
                        break;
                    case ( 8 <= strlen( trim( $fbMember ))) :   // text date ex. 2006-08-03 10:12:18
                        $freebusyPairMember = Util::strDate2ArrayDate( $fbMember, 7 );
                        unset( $freebusyPairMember[Util::$UNPARSEDTEXT] );
                        $freebusyPairMember[Util::$LCtz] = Util::$Z;
                } // end switch
                $freebusyPeriod[] = $freebusyPairMember;
            }
            $input[] = $freebusyPeriod;
        }
        Util::setMval( $this->freebusy, $input, $params, false, $index );
        return true;
    }
}
