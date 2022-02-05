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
namespace Kigkonsult\Icalcreator;

use Exception;

use function array_keys;
use function sprintf;
use function strtoupper;

/**
 * iCalcreator VTODO component class
 *
 * @since 2.41.3 2022-01-17
 */
final class Vtodo extends V3component
{
    use Traits\ATTACHtrait;
    use Traits\ATTENDEEtrait;
    use Traits\Participants2AttendeesTrait;
    use Traits\CATEGORIEStrait;
    use Traits\CLASStrait;
    use Traits\COLORrfc7986trait;
    use Traits\COMMENTtrait;
    use Traits\COMPLETEDtrait;
    use Traits\CONFERENCErfc7986trait;
    use Traits\CONTACTtrait;
    use Traits\CREATEDtrait;
    use Traits\DESCRIPTIONtrait;
    use Traits\DTSTARTtrait;
    use Traits\DUEtrait;
    use Traits\DURATIONtrait;
    use Traits\EXDATEtrait;
    use Traits\EXRULEtrait;
    use Traits\GEOtrait;
    use Traits\IMAGErfc7986trait;
    use Traits\LAST_MODIFIEDtrait;
    use Traits\LOCATIONtrait;
    use Traits\ORGANIZERtrait;
    use Traits\PERCENT_COMPLETEtrait;
    use Traits\PRIORITYtrait;
    use Traits\RDATEtrait;
    use Traits\RECURRENCE_IDtrait;
    use Traits\RELATED_TOtrait;
    use Traits\REQUEST_STATUStrait;
    use Traits\RESOURCEStrait;
    use Traits\RRULEtrait;
    use Traits\SEQUENCEtrait;
    use Traits\STATUStrait;
    use Traits\STRUCTURED_DATArfc9073trait;
    use Traits\STYLED_DESCRIPTIONrfc9073trait;
    use Traits\SUMMARYtrait;
    use Traits\UIDrfc7986trait;
    use Traits\URLtrait;

    /**
     * @var string
     */
    protected static string $compSgn = 't';

    /**
     * Destructor
     *
     * @since 2.41.3 2022-01-17
     */
    public function __destruct()
    {
        if( ! empty( $this->components )) {
            foreach( array_keys( $this->components ) as $cix ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->propIx,
            $this->compix,
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
            $this->color,
            $this->conference,
            $this->comment,
            $this->completed,
            $this->contact,
            $this->created,
            $this->description,
            $this->dtstamp,
            $this->dtstart,
            $this->due,
            $this->duration,
            $this->image,
            $this->exdate,
            $this->exrule,
            $this->geo,
            $this->lastmodified,
            $this->location,
            $this->organizer,
            $this->percentcomplete,
            $this->priority,
            $this->rdate,
            $this->recurrenceid,
            $this->relatedto,
            $this->requeststatus,
            $this->resources,
            $this->rrule,
            $this->sequence,
            $this->status,
            $this->structureddata,
            $this->styleddescription,
            $this->summary,
            $this->uid,
            $this->url
        );
    }

    /**
     * Return formatted output for calendar component VTODO object instance
     *
     * @return string
     * @throws Exception  (on Duration/Rdate err)
     * @since 2.41.3 2022-01-17
     */
    public function createComponent() : string
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
        $component  .= $this->createCompleted();
        $component  .= $this->createContact();
        $component  .= $this->createCreated();
        $component  .= $this->createDescription();
        $component  .= $this->createStyleddescription();
        $component  .= $this->createStructureddata();
        $component  .= $this->createDtstart();
        $component  .= $this->createDue();
        $component  .= $this->createDuration();
        $component  .= $this->createExdate();
        $component  .= $this->createExrule();
        $component  .= $this->createImage();
        $component  .= $this->createGeo();
        $component  .= $this->createLastmodified();
        $component  .= $this->createLocation();
        $component  .= $this->createOrganizer();
        $component  .= $this->createPercentcomplete();
        $component  .= $this->createPriority();
        $component  .= $this->createRdate();
        $component  .= $this->createRelatedto();
        $component  .= $this->createRequeststatus();
        $component  .= $this->createRecurrenceid();
        $component  .= $this->createResources();
        $component  .= $this->createRrule();
        $component  .= $this->createSequence();
        $component  .= $this->createStatus();
        $component  .= $this->createSummary();
        $component  .= $this->createUrl();
        $component  .= $this->createXprop();
        $component  .= $this->createSubComponent();
        return $component . sprintf( self::$FMTEND, $compType );
    }
}
