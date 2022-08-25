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
namespace Kigkonsult\Icalcreator\Traits;

use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

use function bin2hex;
use function chr;
use function ord;
use function str_split;
use function vsprintf;

/**
 * UID property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait UIDrfc7986trait
{
    /**
     * @var Pc component property UID value
     */
    protected Pc $uid;

    /**
     * Return formatted output for calendar component property uid
     *
     * If uid is missing, uid is created
     * @return string
     * @throws Exception
     * @since 2.41.55 2022-08-13
s     */
    public function createUid() : string
    {
        return Property::format(
            self::UID,
            $this->uid,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Get calendar component property uid
     *
     * @param null|bool $inclParam
     * @return string|Pc
     * @throws Exception
     * @since 2.41.53 2022-08-11
     */
    public function getUid( ? bool $inclParam = false ) : string | Pc
    {
        return $inclParam ? clone $this->uid : $this->uid->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isUidSet() : bool
    {
        return true;
    }

    /**
     * Return an unique id for a calendar/component object instance
     *
     * @return Pc
     * @throws Exception
     * @since 2.41.36 2022-04-03
     * @see https://www.php.net/manual/en/function.com-create-guid.php#117893
     */
    private static function makeUid() : Pc
    {
        static $FMT = '%s%s-%s-%s-%s-%s%s%s';
        $bytes    = StringFactory::getRandChars( 32 ); // i.e. 16
        $bytes[6] = chr(ord( $bytes[6] ) & 0x0f | 0x40 ); // set version to 0100
        $bytes[8] = chr(ord( $bytes[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10
        $uid      = vsprintf( $FMT, str_split( bin2hex( $bytes ), 4 ));
        return Pc::factory( $uid );
    }

    /**
     * Set calendar component property uid
     *
     * If empty input, male one
     * @param null|int|string|Pc $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.36 2022-04-03
     */
    public function setUid( null|int|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value ) && ( Util::$ZERO !== (string) $value->value )) {
            $this->uid = self::makeUid();
            return $this;
        } // no allowEmpty check here !!!!
        $value->value = Util::assertString( $value->value, self::UID );
        $value->value = StringFactory::trimTrailNL( $value->value );
        $this->uid = $value;
        return $this;
    }
}
