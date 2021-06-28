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

use function gethostbyname;
use function sprintf;
use function strtoupper;

/**
 * PRODID property functions
 *
 * @since  2.39.1 - 2021-06-26
 */
trait PRODIDtrait
{
    /**
     * @var string calendar property PRODID
     */
    protected $prodid = null;

    /**
     * Return formatted output for calendar property prodid
     *
     * @return string
     */
    public function createProdid() : string
    {
        if( empty( $this->prodid )) {
            $this->makeProdid();
        }
        return StringFactory::createElement( self::PRODID, null, $this->prodid );
    }

    /**
     * Return prodid
     *
     * @return string
     * @since  2.27.1 - 2018-12-16
     */
    public function getProdid() : string
    {
        if( empty( $this->prodid )) {
            $this->makeProdid();
        }
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
     * @since  2.39.1 - 2021-06-26
     */
    public function makeProdid()
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
            $lang = Util::$SP0;
        }
        $this->prodid = sprintf( $FMT, $unique_id, ICALCREATOR_VERSION, $lang );
    }
}
