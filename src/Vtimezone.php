<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.8
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

namespace Kigkonsult\Icalcreator;

use Kigkonsult\Icalcreator\Util\Util;

use function is_array;
use function sprintf;
use function strtolower;
use function strtoupper;
use function ucfirst;

/**
 * iCalcreator VTIMEZONE component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class Vtimezone extends CalendarComponent
{
    use Traits\COMMENTtrait,
        Traits\DTSTARTtrait,
        Traits\LAST_MODIFIEDtrait,
        Traits\RDATEtrait,
        Traits\RRULEtrait,
        Traits\TZIDtrait,
        Traits\TZNAMEtrait,
        Traits\TZOFFSETFROMtrait,
        Traits\TZOFFSETTOtrait,
        Traits\TZURLtrait;
    /**
     * @var string $timezonetype Vtimezone type value
     * @access protected
     */
    protected $timezonetype;

    /**
     * Constructor for calendar component VTIMEZONE object
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param mixed $timezonetype default false ( STANDARD / DAYLIGHT )
     * @param array $config
     */
    public function __construct( $timezonetype = null, $config = [] ) {
        static $TZ = 'tz';
        if( is_array( $timezonetype )) {
            $config       = $timezonetype;
            $timezonetype = null;
        }
        $this->timezonetype = ( empty( $timezonetype )) ? self::VTIMEZONE : ucfirst( strtolower( $timezonetype ));
        parent::__construct();
        $this->setConfig( Util::initConfig( $config ));
        $prf       = ( empty( $timezonetype )) ? $TZ : \substr( $timezonetype, 0, 1 );
        $this->cno = $prf . parent::getObjectNo();
    }

    /**
     * Destructor
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     */
    public function __destruct() {
        if( ! empty( $this->components )) {
            foreach( $this->components as $cix => $component ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset( $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->propix,
            $this->compix,
            $this->propdelix
        );
        unset( $this->compType,
            $this->cno,
            $this->srtk
        );
        unset( $this->comment,
            $this->dtstart,
            $this->lastmodified,
            $this->rdate,
            $this->rrule,
            $this->tzid,
            $this->tzname,
            $this->tzoffsetfrom,
            $this->tzoffsetto,
            $this->tzurl,
            $this->timezonetype
        );
    }

    /**
     * Return formatted output for calendar component VTIMEZONE object instance
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return string
     */
    public function createComponent() {
        $compType    = strtoupper(( isset( $this->timezonetype )) ? $this->timezonetype : $this->compType );
        $component   = sprintf( Util::$FMTBEGIN, $compType );
        $component  .= $this->createTzid();
        $component  .= $this->createLastModified();
        $component  .= $this->createTzurl();
        $component  .= $this->createDtstart();
        $component  .= $this->createTzoffsetfrom();
        $component  .= $this->createTzoffsetto();
        $component  .= $this->createComment();
        $component  .= $this->createRdate();
        $component  .= $this->createRrule();
        $component  .= $this->createTzname();
        $component  .= $this->createXprop();
        $component  .= $this->createSubComponent();
        return $component . sprintf( Util::$FMTEND, $compType );
    }

    /**
     * Return Vtimezone component property value/params
     *
     * If arg $inclParam, return array with keys VALUE/PARAMS
     *
     * @param string $propName
     * @param int    $propix specific property in case of multiply occurences
     * @param bool   $inclParam
     * @param bool   $specform
     * @return mixed
     */
    public function getProperty(
        $propName  = null,
        $propix    = null,
        $inclParam = false,
        $specform  = false
    ) {
        switch( strtoupper( $propName )) {
            case Util::$TZID:
                if( isset( $this->tzid[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->tzid : $this->tzid[Util::$LCvalue];
                }
                break;
            case Util::$TZOFFSETFROM:
                if( isset( $this->tzoffsetfrom[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->tzoffsetfrom : $this->tzoffsetfrom[Util::$LCvalue];
                }
                break;
            case Util::$TZOFFSETTO:
                if( isset( $this->tzoffsetto[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->tzoffsetto : $this->tzoffsetto[Util::$LCvalue];
                }
                break;
            case Util::$TZURL:
                if( isset( $this->tzurl[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->tzurl : $this->tzurl[Util::$LCvalue];
                }
                break;
            default:
                return parent::getProperty( $propName, $propix, $inclParam, $specform );
                break;
        }
        return false;
    }

    /**
     * Return timezone standard object instance, Vtimezone::newComponent() wrapper
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return object
     */
    public function newStandard() {
        return $this->newComponent( self::STANDARD );
    }

    /**
     * Return timezone daylight object instance, Vtimezone::newComponent() wrapper
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return object
     */
    public function newDaylight() {
        return $this->newComponent( self::DAYLIGHT );
    }
}
