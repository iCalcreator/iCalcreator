<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
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

use Exception;

use function sprintf;
use function strtoupper;

/**
 * iCalcreator VEVENT component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.29.9 - 2019-08-05
 */
final class Vevent extends VetComponent
{
    use Traits\ATTACHtrait,
        Traits\ATTENDEEtrait,
        Traits\CATEGORIEStrait,
        Traits\CLASStrait,
        Traits\COLORrfc7986trait,
        Traits\COMMENTtrait,
        Traits\CONFERENCErfc7986trait,
        Traits\CONTACTtrait,
        Traits\CREATEDtrait,
        Traits\DESCRIPTIONtrait,
        Traits\DTENDtrait,
        Traits\DTSTARTtrait,
        Traits\DURATIONtrait,
        Traits\EXDATEtrait,
        Traits\EXRULEtrait,
        Traits\GEOtrait,
        Traits\IMAGErfc7986trait,
        Traits\LAST_MODIFIEDtrait,
        Traits\LOCATIONtrait,
        Traits\ORGANIZERtrait,
        Traits\PRIORITYtrait,
        Traits\RDATEtrait,
        Traits\RECURRENCE_IDtrait,
        Traits\RELATED_TOtrait,
        Traits\REQUEST_STATUStrait,
        Traits\RESOURCEStrait,
        Traits\RRULEtrait,
        Traits\SEQUENCEtrait,
        Traits\STATUStrait,
        Traits\SUMMARYtrait,
        Traits\TRANSPtrait,
        Traits\UIDrfc7986trait,
        Traits\URLtrait;

    /**
     * @var string
     */
    protected static $compSgn = 'e';

    /**
     * Destructor
     *
     * @since  2.29.5 - 2019-06-20
     */
    public function __destruct()
    {
        if( ! empty( $this->components )) {
            foreach( $this->components as $cix => $component ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->compix,
            $this->propIx,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->attach,
            $this->attendee,
            $this->categories,
            $this->class,
            $this->comment,
            $this->color,
            $this->conference,
            $this->contact,
            $this->created,
            $this->description,
            $this->dtend,
            $this->dtstamp,
            $this->dtstart,
            $this->duration,
            $this->image,
            $this->exdate,
            $this->exrule,
            $this->geo,
            $this->lastmodified,
            $this->location,
            $this->organizer,
            $this->priority,
            $this->rdate,
            $this->recurrenceid,
            $this->relatedto,
            $this->requeststatus,
            $this->resources,
            $this->rrule,
            $this->sequence,
            $this->status,
            $this->summary,
            $this->transp,
            $this->uid,
            $this->url
        );
    }

    /**
     * Return formatted output for calendar component VEVENT object instance
     *
     * @return string
     * @throws Exception  (on Duration/Rdate err)
     * @since  2.29.9 - 2019-08-05
     */
    public function createComponent()
    {
        $compType    = strtoupper( $this->getCompType());
        $component   = sprintf( self::$FMTBEGIN, $compType );
        $component  .= $this->createUid();
        $component  .= $this->createDtstamp();
        $component  .= $this->createAttach();
        $component  .= $this->createAttendee();
        $component  .= $this->createCategories();
        $component  .= $this->createClass();
        $component  .= $this->createColor();
        $component  .= $this->createComment();
        $component  .= $this->createConference();
        $component  .= $this->createContact();
        $component  .= $this->createCreated();
        $component  .= $this->createDescription();
        $component  .= $this->createDtstart();
        $component  .= $this->createDtend();
        $component  .= $this->createDuration();
        $component  .= $this->createExdate();
        $component  .= $this->createExrule();
        $component  .= $this->createImage();
        $component  .= $this->createGeo();
        $component  .= $this->createLastmodified();
        $component  .= $this->createLocation();
        $component  .= $this->createOrganizer();
        $component  .= $this->createPriority();
        $component  .= $this->createRdate();
        $component  .= $this->createRrule();
        $component  .= $this->createRelatedto();
        $component  .= $this->createRequeststatus();
        $component  .= $this->createRecurrenceid();
        $component  .= $this->createResources();
        $component  .= $this->createSequence();
        $component  .= $this->createStatus();
        $component  .= $this->createSummary();
        $component  .= $this->createTransp();
        $component  .= $this->createUrl();
        $component  .= $this->createXprop();
        $component  .= $this->createSubComponent();
        return $component . sprintf( self::$FMTEND, $compType );
    }
}
