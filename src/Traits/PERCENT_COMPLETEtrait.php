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

use Kigkonsult\Icalcreator\Formatter\Property\IntProperty;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * PERCENT-COMPLETE property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait PERCENT_COMPLETEtrait
{
    /**
     * @var null|Pc component property PERCENT_COMPLETE value
     */
    protected ? Pc $percentcomplete = null;

    /**
     * Return formatted output for calendar component property percent-complete
     *
     * @return string
     */
    public function createPercentcomplete() : string
    {
        return IntProperty::format(
            self::PERCENT_COMPLETE,
            $this->percentcomplete,
            $this->getConfig( self::ALLOWEMPTY )
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
     * @return bool|int|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getPercentcomplete( ? bool $inclParam = false ) : bool | int | string | Pc
    {
        if( empty( $this->percentcomplete )) {
            return false;
        }
        return $inclParam ? clone $this->percentcomplete : $this->percentcomplete->value;
    }

    /**
     * Return bool true if set
     *
     * @return bool
     * @since 2.41.43 2022-04-15
     */
    public function isPercentcompleteSet() : bool
    {
        return ( ! empty( $this->percentcomplete->value ) ||
            (( null !== $this->percentcomplete ) && ( 0 === $this->percentcomplete->value )));
    }

    /**
     * Set calendar component property percent-complete
     *
     * .. a positive integer between 0 and
     * 100.  A value of "0" indicates the to-do has not yet been started.
     * A value of "100" indicates that the to-do has been completed.
     *
     * @param null|int|string|Pc  $value  0 accepted
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setPercentcomplete( null|int|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if(( $value->value === null ) || ( Util::$SP0 === $value->value )) {
            $this->assertEmptyValue( $value->value, self::PERCENT_COMPLETE );
            $value->setEmpty();
        }
        else {
            Util::assertInteger( $value->value, self::PERCENT_COMPLETE, 0, 100 );
            $value->value = (int) $value->value;
        }
        $this->percentcomplete = $value;
        return $this;
    }
}
