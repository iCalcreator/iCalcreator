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

use function gethostbyname;
use function sprintf;
use function strtoupper;

/**
 * PRODID property functions
 *
 * @since 2.41.55 - 2022-08-13
 */
trait PRODIDtrait
{
    /**
     * @var string calendar property PRODID
     */
    protected string $prodid;

    /**
     * Return formatted output for calendar property prodid
     *
     * @return string
     * @since 2.41.55 - 2022-08-13
     */
    public function createProdid() : string
    {
        return CalMetProVer::format( self::PRODID, $this->prodid );
    }

    /**
     * Return prodid
     *
     * @return string
     * @since  2.41.55 - 2022-08-11
     */
    public function getProdid() : string
    {
        return $this->prodid;
    }

    /**
     * Create default value for calendar prodid,
     * Do NOT alter or remove this method or the invoke of this method,
     * a licence violation.
     *
     * [rfc5545]
     * "Conformance: The property MUST be specified once in an iCalendar object.
     *  Description: The vendor of the implementation SHOULD assure that this
     *  is a globally unique identifier; using some technique such as an FPI
     *  value, as defined in [ISO 9070]."
     *
     * @return string
     * @since  2.41.55 - 2022-08-11
     */
    public function makeProdid() : string
    {
        static $SERVER_NAME = 'SERVER_NAME';
        static $LOCALHOST   = 'localhost';
        static $FMT         = '-//%s//NONSGML kigkonsult.se %s//%s';
        $unique_id = $this->getConfig( self::UNIQUE_ID );
        if( empty( $unique_id )) {
            $unique_id = ( isset( $_SERVER[$SERVER_NAME] ))
                ? gethostbyname( $_SERVER[$SERVER_NAME] )
                : $LOCALHOST;
        }
        if( false !== ( $lang = $this->getConfig( self::LANGUAGE ))) {
            $lang = strtoupper( $lang );
        }
        else {
            $lang = self::$SP0;
        }
        return sprintf( $FMT, $unique_id, ICALCREATOR_VERSION, $lang );
    }
}
