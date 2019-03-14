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
use Kigkonsult\Icalcreator\Util\UtilDuration;

use function sprintf;
use function strtoupper;

/**
 * iCalcreator VALARM component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class Valarm extends CalendarComponent
{
    use Traits\ACTIONtrait,
        Traits\ATTACHtrait,
        Traits\ATTENDEEtrait,
        Traits\DESCRIPTIONtrait,
        Traits\DURATIONtrait,
        Traits\REPEATtrait,
        Traits\SUMMARYtrait,
        Traits\TRIGGERtrait;

    /**
     * Constructor for calendar component VALARM object
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.20 - 2017-02-01
     * @param array $config
     */
    public function __construct( $config = [] ) {
        static $A = 'a';
        parent::__construct();
        $this->setConfig( Util::initConfig( $config ));
        $this->cno = $A . parent::getObjectNo();
    }

    /**
     * Destructor
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     */
    public function __destruct() {
        unset( $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->propix,
            $this->propdelix
        );
        unset( $this->compType,
            $this->cno,
            $this->srtk
        );
        unset( $this->action,
            $this->attach,
            $this->attendee,
            $this->description,
            $this->duration,
            $this->repeat,
            $this->summary,
            $this->trigger
        );
    }

    /**
     * Return formatted output for calendar component VALARM object instance
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return string
     */
    public function createComponent() {
        $compType    = strtoupper( $this->compType );
        $component   = sprintf( Util::$FMTBEGIN, $compType );
        $component  .= $this->createAction();
        $component  .= $this->createAttach();
        $component  .= $this->createAttendee();
        $component  .= $this->createDescription();
        $component  .= $this->createDuration();
        $component  .= $this->createRepeat();
        $component  .= $this->createSummary();
        $component  .= $this->createTrigger();
        $component  .= $this->createXprop();
        return $component . sprintf( Util::$FMTEND, $compType );
    }

    /**
     * Return Valarm component property value/params,
     *
     * If arg $inclParam, return array with keys VALUE/PARAMS.
     *
     * @param string $propName
     * @param int    $propix specific property in case of multiply occurences
     * @param bool   $inclParam
     * @param bool   $specform
     * @return mixed
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-02
     */
    public function getProperty(
        $propName  = null,
        $propix    = null,
        $inclParam = null,
        $specform  = null
    ) {
        switch( strtoupper( $propName )) {
            case Util::$ACTION:
                if( isset( $this->action[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->action : $this->action[Util::$LCvalue];
                }
                break;
            case Util::$REPEAT:
                if( isset( $this->repeat[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->repeat : $this->repeat[Util::$LCvalue];
                }
                break;
            case Util::$TRIGGER:
                if( ! isset( $this->trigger[Util::$LCvalue] )) {
                    break;
                }
                if( isset( $this->trigger[Util::$LCvalue]['invert'] )) { // fix pre 7.0.5 bug
                    $dateInterval = UtilDuration::DateIntervalArr2DateInterval( $this->trigger[Util::$LCvalue] );
                    $value = UtilDuration::dateInterval2arr( $dateInterval );
                    $value[UtilDuration::$BEFORE]       =
                        ( 0 < $this->trigger[Util::$LCvalue]['invert'] ) ? true : false;
                    $value[UtilDuration::$RELATEDSTART] =
                        ( isset( $this->trigger[Util::$LCparams ][UtilDuration::$RELATED] ) &&
                        ( UtilDuration::$END == $this->trigger[Util::$LCparams ][UtilDuration::$RELATED] ))
                            ? false
                            : true;
                }
                else {
                    $value = $this->trigger[Util::$LCvalue];
                }
                return ( $inclParam )
                    ? [ Util::$LCvalue => $value, Util::$LCparams => $this->trigger[Util::$LCparams ] ]
                    : $value;
                break;
            default:
                return parent::getProperty( $propName, $propix, $inclParam, $specform );
                break;
        }
        return false;
    }
}
