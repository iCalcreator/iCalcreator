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

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Property\CalMetProVer;

/**
 * VERSION property functions
 *
 * @since 2.41.62 2022-08-29
 */
trait VERSIONtrait
{
    /**
     * Property Name: VERSION
     *
     * Description: A value of "2.0" corresponds to this memo.
     *
     * @var string calendar property VERSION
     */
    protected string $version = '2.0';

    /**
     * Return formatted output for calendar property version
     *
     * @return string
     */
    public function createVersion() : string
    {
        return CalMetProVer::format( self::VERSION, $this->version );
    }

    /**
     * Return version
     *
     * @return string
     * @since  2.27.1 - 2018-12-16
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * Set (another?) calendar version
     *
     * @param string $value
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.62 2022-08-29
     */
    public function setVersion( string $value ) : static
    {
        static $ERRMSG = 'Empty Version value not allowed';
        if( empty( $value )) {
            throw new InvalidArgumentException( $ERRMSG );
        }
        $this->version = $value;
        return $this;
    }
}
