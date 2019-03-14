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

namespace Kigkonsult\Icalcreator\Util;

use Kigkonsult\Icalcreator\Vcalendar;

use function clearstatcache;
use function file_put_contents;
use function fclose;
use function filesize;
use function filemtime;
use function fopen;
use function fpassthru;
use function gzencode;
use function header;
use function is_file;
use function is_readable;
use function sprintf;
use function strlen;
use function sys_get_temp_dir;
use function tempnam;
use function time;
use function unlink;
use function utf8_encode;

/**
 * iCalcreator redirect support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class UtilRedirect
{
    /**
     * HTTP headers
     *
     * @var array $headers
     * @access private
     * @static
     */
    private static $headers = [
        'Content-Encoding: gzip',
        'Vary: *',
        'Content-Length: %s',
        'Content-Type: text/calendar; charset=utf-8',
        'Content-Disposition: attachment; filename="%s"',
        'Content-Disposition: inline; filename="%s"',
        'Cache-Control: max-age=10',
    ];

    /**
     * Return created, updated and/or parsed calendar, sending a HTTP redirect header.
     *
     * @param Vcalendar $calendar
     * @param bool      $utf8Encode
     * @param bool      $gzip
     * @param bool      $cdType true : Content-Disposition: attachment... (default), false : ...inline...
     * @return bool true on success, false on error
     * @static
     */
    public static function returnCalendar(
        Vcalendar $calendar,
        $utf8Encode = false,
        $gzip       = false,
        $cdType     = true
    ) {
        static $ICR = 'iCr';
        $filename = $calendar->getConfig( Util::$FILENAME );
        $output   = $calendar->createCalendar();
        if( $utf8Encode ) {
            $output = utf8_encode( $output );
        }
        $fsize = null;
        if( $gzip ) {
            $output = gzencode( $output, 9 );
            $fsize  = strlen( $output );
            header( self::$headers[0] );
            header( self::$headers[1] );
        }
        else {
            if( false !== ( $temp = tempnam( sys_get_temp_dir(), $ICR ))) {
                if( false !== file_put_contents( $temp, $output )) {
                    $fsize = @filesize( $temp );
                }
                unlink( $temp );
            }
        }
        if( ! empty( $fsize )) {
            header( sprintf( self::$headers[2], $fsize ));
        }
        header( self::$headers[3] );
        $cdType = ( $cdType ) ? 4 : 5;
        header( sprintf( self::$headers[$cdType], $filename ));
        header( self::$headers[6] );
        echo $output;
        return true;
    }

    /**
     * If recent version of calendar file exists (default one hour), an HTTP redirect header is sent
     *
     * @param Vcalendar $calendar
     * @param int       $timeout default 3600 sec
     * @param bool      $cdType  true : Content-Disposition: attachment... (default), false : ...inline...
     * @return bool true on success, false on error
     * @static
     */
    public static function useCachedCalendar(
        Vcalendar $calendar,
        $timeout = 3600,
        $cdType  = true
    ) {
        static $R = 'r';
        if( false === ( $dirfile = $calendar->getConfig( Util::$URL ))) {
            $dirfile = $calendar->getConfig( Util::$DIRFILE );
        }
        if( ! is_file( $dirfile ) || ! is_readable( $dirfile )) {
            return false;
        }
        if( time() - filemtime( $dirfile ) > $timeout ) {
            return false;
        }
        clearstatcache();
        $fsize    = @filesize( $dirfile );
        $filename = $calendar->getConfig( Util::$FILENAME );
        header( self::$headers[3] );
        if( ! empty( $fsize )) {
            header( sprintf( self::$headers[2], $fsize ));
        }
        $cdType = ( $cdType ) ? 4 : 5;
        header( sprintf( self::$headers[$cdType], $filename ));
        header( self::$headers[6] );
        if( false === ( $fp = @fopen( $dirfile, $R ))) {
            return false;
        }
        fpassthru( $fp );
        fclose( $fp );
        return true;
    }
}
