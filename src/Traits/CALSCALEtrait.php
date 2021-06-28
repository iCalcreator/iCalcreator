<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;

use function sprintf;

/**
 * CALSCALE property functions
 *
 * @since 2.29.14 2019-09-03
 */
trait CALSCALEtrait
{
    /**
     * @var string calendar property CALSCALE
     */
    protected $calscale = null;

    /**
     * Return formatted output for calendar property calscale
     *
     * @return string
     */
    public function createCalscale() : string
    {
        if( empty( $this->calscale )) {
            $this->calscale = Vcalendar::GREGORIAN;
        }
        return sprintf( self::$FMTICAL, self::CALSCALE, $this->calscale );
    }

    /**
     * Delete calendar component property calscale
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteCalscale() : bool
    {
        $this->calscale = null;
        return true;
    }

    /**
     * Return calscale
     *
     * @return string
     * @since  2.27.1 - 2018-12-15
     */
    public function getCalscale() : string
    {
        if( empty( $this->calscale )) {
            $this->calscale = Vcalendar::GREGORIAN;
        }
        return $this->calscale;
    }

    /**
     * Set calendar property calscale
     *
     * @param string $value
     * @return static
     * @throws InvalidArgumentException;
     * @since  2.29.14 - 2019-09-03
     */
    public function setCalscale( $value ) : self
    {
        if( empty( $value )) {
            $value = Vcalendar::GREGORIAN;
        }
        Util::assertString( $value, self::CALSCALE );
        $this->calscale = (string) $value;
        return $this;
    }
}
