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

use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\HttpFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * SOURCE property functions
 *
 * @since  2.30.2 - 2021-02-04
 */
trait SOURCErfc7986trait
{
    /**
     * @var array component property SOURCE value
     */
    protected $source = null;

    /**
     * Return formatted output for calendar component property source
     *
     * @return string
     */
    public function createSource() : string
    {
        if( empty( $this->source )) {
            return Util::$SP0;
        }
        if( empty( $this->source[Util::$LCvalue] )) {
            return $this->getConfig( self::ALLOWEMPTY )
                ? StringFactory::createElement( self::SOURCE )
                : Util::$SP0;
        }
        return StringFactory::createElement(
            self::SOURCE,
            ParameterFactory::createParams( $this->source[Util::$LCparams] ),
            $this->source[Util::$LCvalue]
        );
    }

    /**
     * Delete calendar component property source
     *
     * @return bool
     */
    public function deleteSource() : bool
    {
        $this->source = null;
        return true;
    }

    /**
     * Get calendar component property source
     *
     * @param null|bool   $inclParam
     * @return bool|array
     */
    public function getSource( $inclParam = false )
    {
        if( empty( $this->source )) {
            return false;
        }
        return ( $inclParam ) ? $this->source : $this->source[Util::$LCvalue];
    }

    /**
     * Set calendar component property source
     *
     * @param null|string $value
     * @param null|array  $params
     * @return static
     * @throws InvalidArgumentException
     * @since  2.30.2 - 2021-02-04
     */
    public function setSource( $value = null, $params = [] ) : self
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::SOURCE );
            $this->source = [
                Util::$LCvalue  => Util::$SP0,
                Util::$LCparams => [],
            ];
            return $this;
        }
        HttpFactory::urlSet( $this->source, $value, $params );
        return $this;
    }
}
