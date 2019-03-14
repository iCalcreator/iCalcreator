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

use function sprintf;
use function strtoupper;

/**
 * iCalcreator VFREEBUSY component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class Vfreebusy extends CalendarComponent
{
    use Traits\ATTENDEEtrait,
        Traits\COMMENTtrait,
        Traits\CONTACTtrait,
        Traits\DTENDtrait,
        Traits\DTSTAMPtrait,
        Traits\DTSTARTtrait,
        Traits\DURATIONtrait,
        Traits\FREEBUSYtrait,
        Traits\ORGANIZERtrait,
        Traits\REQUEST_STATUStrait,
        Traits\UIDtrait,
        Traits\URLtrait;

    /**
     * Constructor for calendar component VFREEBUSY object
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.20 - 2017-02-01
     * @param array $config
     */
    public function __construct( $config = [] ) {
        static $F = 'f';
        parent::__construct();
        $this->setConfig( Util::initConfig( $config ));
        $this->cno = $F . parent::getObjectNo();
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
            $this->compix,
            $this->propdelix
        );
        unset( $this->compType,
            $this->cno,
            $this->srtk
        );
        unset( $this->attendee,
            $this->comment,
            $this->contact,
            $this->dtend,
            $this->dtstamp,
            $this->dtstart,
            $this->duration,
            $this->freebusy,
            $this->organizer,
            $this->requeststatus,
            $this->uid,
            $this->url
        );
    }

    /**
     * Return formatted output for calendar component VFREEBUSY object instance
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return string
     */
    public function createComponent() {
        $compType    = strtoupper( $this->compType );
        $component   = sprintf( Util::$FMTBEGIN, $compType );
        $component  .= $this->createUid();
        $component  .= $this->createDtstamp();
        $component  .= $this->createAttendee();
        $component  .= $this->createComment();
        $component  .= $this->createContact();
        $component  .= $this->createDtstart();
        $component  .= $this->createDtend();
        $component  .= $this->createDuration();
        $component  .= $this->createFreebusy();
        $component  .= $this->createOrganizer();
        $component  .= $this->createRequestStatus();
        $component  .= $this->createUrl();
        $component  .= $this->createXprop();
        return $component . sprintf( Util::$FMTEND, $compType );
    }
}
