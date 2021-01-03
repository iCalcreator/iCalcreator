<?php
/**
  * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
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

use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Vcalendar;

use function clearstatcache;
use function file_put_contents;
use function filesize;
use function filter_var;
use function gzencode;
use function header;
use function sprintf;
use function strcasecmp;
use function strlen;
use function strpos;
use function substr;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function utf8_encode;

/**
 * iCalcreator http support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.29.15 - 2020-01-19
 */
class HttpFactory
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
     * @param string    $fileName
     * @return bool true on success, false on error
     * @throws Exception
     * @static
     * @since  2.29.15 - 2020-01-19
     */
    public static function returnCalendar(
        Vcalendar $calendar,
        $utf8Encode = false,
        $gzip       = false,
        $cdType     = true,
        $fileName   = null
    ) {
        static $ICR = 'iCr';
        $utf8Encode ?: false;
        $gzip ?: false;
        $cdType ?: false;
        if( empty( $fileName ) ) {
            $fileName = self::getFakedFilename();
        }
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
                clearstatcache();
            }
        } // end else
        if( ! empty( $fsize )) {
            header( sprintf( self::$headers[2], $fsize ));
        }
        header( self::$headers[3] );
        $cdType = ( $cdType ) ? 4 : 5;
        header( sprintf( self::$headers[$cdType], $fileName ));
        header( self::$headers[6] );
        echo $output;
        return true;
    }

    /**
     * Return faked filename
     *
     * @return string $propName
     * @access private
     * @static
     * @since  2.29.4 - 2019-07-02
     */
    private static function getFakedFilename()
    {
        static $DOTICS = '.ics';
        return date(
            DateTimeFactory::$YmdHis,
            intval( microtime( true ))
            ) . $DOTICS;
    }

    /**
     * Assert URL
     *
     * @param string $url
     * @throws InvalidArgumentException
     * @static
     * @since  2.27.3 - 2018-12-28
     */
    public static function assertUrl( $url )
    {
        static $UC   = '_';
        static $URN  = 'urn';
        static $HTTP = 'http://';
        static $MSG  = 'URL validity error #%d, \'%s\'';
        $url2 = ( false !== strpos( $url, $UC ))
            ? str_replace( $UC, Util::$MINUS, $url )
            : $url;
        $no   = 0;
        do {
            if( false !== filter_var( $url2, FILTER_VALIDATE_URL )) {
                break;
            }
            if( empty( parse_url( $url2, PHP_URL_SCHEME)) &&
                ( false !== filter_var( $HTTP . $url2, FILTER_VALIDATE_URL ))) {
                break;
            }
            $no = 1;
            if( 0 != strcasecmp( $URN, substr( $url, 0, 3 ))) {
                $no = 2;
            }
            break;
        } while( true );
        if( ! empty( $no )) {
            throw new InvalidArgumentException( sprintf( $MSG, $no, $url ));
        }
    }
}
