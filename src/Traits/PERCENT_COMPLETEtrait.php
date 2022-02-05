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

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

use function is_numeric;

/**
 * PERCENT-COMPLETE property functions
 *
 * @since 2.27.3 2018-12-22
 */
trait PERCENT_COMPLETEtrait
{
    /**
     * @var null|mixed[] component property PERCENT_COMPLETE value
     */
    protected ? array $percentcomplete = null;

    /**
     * Return formatted output for calendar component property percent-complete
     *
     * @return string
     */
    public function createPercentcomplete() : string
    {
        if( empty( $this->percentcomplete )) {
            return Util::$SP0;
        }
        if( ! isset( $this->percentcomplete[Util::$LCvalue] ) ||
            ( empty( $this->percentcomplete[Util::$LCvalue] ) &&
                ! is_numeric( $this->percentcomplete[Util::$LCvalue] ))) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::PERCENT_COMPLETE )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::PERCENT_COMPLETE,
            ParameterFactory::createParams( $this->percentcomplete[Util::$LCparams] ),
            (string) $this->percentcomplete[Util::$LCvalue]
        );
    }

    /**
     * Delete calendar component property percentcomplete
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deletePercentcomplete() : bool
    {
        $this->percentcomplete = null;
        return true;
    }

    /**
     * Get calendar component property percent-complete
     *
     * @param null|bool   $inclParam
     * @return bool|int|string|mixed[]
     * @since  2.27.1 - 2018-12-12
     */
    public function getPercentcomplete( ? bool $inclParam = false ) : array | bool | string | int
    {
        if( empty( $this->percentcomplete )) {
            return false;
        }
        if( null === $this->percentcomplete[Util::$LCvalue] ) {
            $this->percentcomplete[Util::$LCvalue] = Util::$SP0;
        }
        return $inclParam
            ? $this->percentcomplete
            : $this->percentcomplete[Util::$LCvalue];
    }

    /**
     * Set calendar component property percent-complete
     *
     * @param null|int|string  $value  0 accepted
     * @param null|mixed[]     $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.27.3 2018-12-22
     */
    public function setPercentcomplete( null|int|string $value = null, ? array $params = [] ) : static
    {
        if(( $value === null ) || ( $value === Util::$SP0 )) {
            $this->assertEmptyValue( $value, self::PERCENT_COMPLETE );
            $value  = null;
            $params = [];
        }
        else {
            Util::assertInteger( $value, self::PERCENT_COMPLETE, 0, 100 );
            $value = (int) $value;
        }
        $this->percentcomplete = [
            Util::$LCvalue  => $value,
            Util::$LCparams => ParameterFactory::setParams( $params ),
        ];
        return $this;
    }
}
