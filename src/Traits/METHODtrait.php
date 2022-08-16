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

use Kigkonsult\Icalcreator\Formatter\Property\CalMetProVer;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * METHOD property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait METHODtrait
{
    /**
     * @var null|string calendar property METHOD
     */
    protected ? string $method = null;

    /**
     * Return formatted output for calendar property method
     *
     * @return string
     */
    public function createMethod() : string
    {
        return CalMetProVer::format( self::METHOD, $this->method );
    }

    /**
     * Delete calendar component property method
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteMethod() : bool
    {
        $this->method = null;
        return true;
    }

    /**
     * Return method
     *
     * @return bool|string
     * @since  3.4 - 2021-06-11
     */
    public function getMethod() : bool | string
    {
        if( empty( $this->method )) {
            return false;
        }
        return $this->method;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isMethodSet() : bool
    {
        return ! empty( $this->method );
    }

    /**
     * Set calendar property method
     *
     * @param null|string $value
     * @return static
     * @since  2.29.14 - 2019-09-03
     */
    public function setMethod( null|string $value = null ) : static
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::METHOD );
            $value = self::$SP0;
        }
        Util::assertString( $value, self::METHOD );
        $this->method = (string) $value;
        return $this;
    }
}
