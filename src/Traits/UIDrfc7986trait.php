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

namespace Kigkonsult\Icalcreator\Traits;

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

use function bin2hex;
use function chr;
use function openssl_random_pseudo_bytes;
use function ord;
use function str_split;
use function vsprintf;

/**
 * UID property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.29.14 2019-09-03
 */
trait UIDrfc7986trait
{
    /**
     * @var array component property UID value
     */
    protected $uid = null;

    /**
     * Return formatted output for calendar component property uid
     *
     * If uid is missing, uid is created
     * @return string
     * @since 2.29.5 2019-06-17
     */
    public function createUid()
    {
        if( self::isUidEmpty( $this->uid ))
        {
            $this->uid = self::makeUid();
        }
        return StringFactory::createElement(
            self::UID,
            ParameterFactory::createParams( $this->uid[Util::$LCparams] ),
            $this->uid[Util::$LCvalue]
        );
    }

    /**
     * Delete calendar component property uid
     *
     * @return bool
     * @since 2.29.5 2019-06-17
     */
    public function deleteUid()
    {
        $this->uid = null;
        return true;
    }

    /**
     * Get calendar component property uid
     *
     * @param bool   $inclParam
     * @return bool|array
     * @since 2.29.5 2019-06-17
     */
    public function getUid( $inclParam = false )
    {
        if( self::isUidEmpty( $this->uid )) {
            $this->uid = self::makeUid();
        }
        return ( $inclParam ) ? $this->uid : $this->uid[Util::$LCvalue];
    }

    /**
     * Return bool true if uid is empty
     *
     * @param array  $array
     * @return bool
     * @static
     * @since 2.29.5 2019-06-17
     */
    private static function isUidEmpty( array $array = null )
    {
        if( empty( $array )) {
            return true;
        }
        if( empty( $array[Util::$LCvalue] ) &&
            ( Util::$ZERO != $array[Util::$LCvalue] )) {
            return true;
        }
        return false;
    }

    /**
     * Return an unique id for a calendar component object instance
     *
     * @return array
     * @static
     * @see https://www.php.net/manual/en/function.com-create-guid.php#117893
     * @since 2.29.5 2019-06-17
     */
    private static function makeUid()
    {
        static $FMT = '%s%s-%s-%s-%s-%s%s%s';
        static $MAX = 10;
        $cnt = 0;
        do {
            do {
                $bytes = openssl_random_pseudo_bytes( 16, $cStrong );
            } while ( false === $bytes );
            $cnt += 1;
        } while(( $MAX > $cnt ) && ( false === $cStrong ));
        $bytes[6] = chr(ord( $bytes[6] ) & 0x0f | 0x40 ); // set version to 0100
        $bytes[8] = chr(ord( $bytes[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10
        $uid      = vsprintf( $FMT, str_split( bin2hex( $bytes ), 4 ));
        return [
            Util::$LCvalue  => $uid,
            Util::$LCparams => null,
        ];
    }

    /**
     * Set calendar component property uid
     *
     * If empty input, male one
     * @param string $value
     * @param array  $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.29.14 2019-09-03
     */
    public function setUid( $value = null, $params = [] )
    {
        if( empty( $value ) && ( Util::$ZERO != $value )) {
            $this->uid = self::makeUid();
            return $this;
        } // no allowEmpty check here !!!!
        Util::assertString( $value, self::UID );
        $this->uid = [
            Util::$LCvalue  => StringFactory::trimTrailNL( $value ),
            Util::$LCparams => ParameterFactory::setParams( $params ),
        ];
        return $this;
    }
}
