<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.16
 * license   By obtaining and/or copying the Software, iCalcreator,
 *           you (the licensee) agree that you have read, understood,
 *           and will comply with the following terms and conditions.
 *           a. The above copyright, link, package and version notices,
 *              this licence notice and
 *              the [rfc5545] PRODID as implemented and invoked in the software
 *              shall be included in all copies or substantial portions of the Software.
 *           b. The Software, iCalcreator, is for
 *              individual evaluation use and evaluation result use only;
 *              non assignable, non-transferable, non-distributable,
 *              non-commercial and non-public rights, use and result use.
 *           c. Creative Commons
 *              Attribution-NonCommercial-NoDerivatives 4.0 International License
 *              (http://creativecommons.org/licenses/by-nc-nd/4.0/)
 *           In case of conflict, a and b supercede c.
 *
 * This file is a part of iCalcreator.
 */
namespace kigkonsult\iCalcreator;
use kigkonsult\iCalcreator\util\util;
/**
 * iCalcreator VTODO component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-01
 */
class vtodo extends calendarComponent {
  use traits\ATTACHtrait,
      traits\ATTENDEEtrait,
      traits\CATEGORIEStrait,
      traits\CLASStrait,
      traits\COMMENTtrait,
      traits\COMPLETEDtrait,
      traits\CONTACTtrait,
      traits\CREATEDtrait,
      traits\DESCRIPTIONtrait,
      traits\DTSTAMPtrait,
      traits\DTSTARTtrait,
      traits\DUEtrait,
      traits\DURATIONtrait,
      traits\EXDATEtrait,
      traits\EXRULEtrait,
      traits\GEOtrait,
      traits\LAST_MODIFIEDtrait,
      traits\LOCATIONtrait,
      traits\ORGANIZERtrait,
      traits\PERCENT_COMPLETEtrait,
      traits\PRIORITYtrait,
      traits\RDATEtrait,
      traits\RECURRENCE_IDtrait,
      traits\RELATED_TOtrait,
      traits\REQUEST_STATUStrait,
      traits\RESOURCEStrait,
      traits\RRULEtrait,
      traits\SEQUENCEtrait,
      traits\STATUStrait,
      traits\SUMMARYtrait,
      traits\UIDtrait,
      traits\URLtrait;
/**
 * Constructor for calendar component VTODO object
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-01
 * @param array $config
 * @uses calendarComponent::__contruct()
 * @uses calendarComponent::setConfig()
 * @uses util::initConfig()
 */
  public function __construct( $config = array()) {
    static $T = 't';
    parent::__construct();
    $this->setConfig( util::initConfig( $config ));
    $this->cno = $T . parent::getObjectNo();
  }
/**
 * Destructor
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-03-17
 */
  public function __destruct() {
    if( ! empty( $this->components ))
      foreach( $this->components as $cix => $component )
        $this->components[$cix]->__destruct();
    unset( $this->xprop,
           $this->components,
           $this->unparsed,
           $this->config );
    unset( $this->objName,
           $this->cno,
           $this->propix,
           $this->compix,
           $this->propdelix );
    unset( $this->attach,
           $this->attendee,
           $this->categories,
           $this->class,
           $this->comment,
           $this->completed,
           $this->contact,
           $this->created,
           $this->description,
           $this->dtstamp,
           $this->dtstart,
           $this->due,
           $this->duration,
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
           $this->summary,
           $this->uid,
           $this->url );
  }
/**
 * Return formatted output for calendar component VTODO object instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-11-07
 * @return string
 * @uses calendarComponent::createUid()
 * @uses calendarComponent::createDtstamp()
 * @uses vtodo::createAttach()
 * @uses vtodo::createAttendee()
 * @uses vtodo::createCategories()
 * @uses vtodo::createClass()
 * @uses vtodo::createComment()
 * @uses vtodo::createCompleted()
 * @uses vtodo::createContact()
 * @uses vtodo::createDescription()
 * @uses vtodo::createDtstart()
 * @uses vtodo::createDtend()
 * @uses vtodo::createDuration()
 * @uses vtodo::createExdate()
 * @uses vtodo::createExrule()
 * @uses vtodo::createGeo()
 * @uses vtodo::createLastModified()
 * @uses vtodo::createLocation()
 * @uses vtodo::createOrganizer()
 * @uses vtodo::createPriority()
 * @uses vtodo::createRdate()
 * @uses vtodo::createRelatedTo()
 * @uses vtodo::createRequestStatus()
 * @uses vtodo::createRecurrenceid()
 * @uses vtodo::createResources()
 * @uses vtodo::createRrule()
 * @uses vtodo::createSequence()
 * @uses vtodo::createStatus()
 * @uses vtodo::createSummary()
 * @uses vtodo::createUrl()
 * @uses calendarComponent::createXprop()
 * @uses calendarComponent::createSubComponent()
 */
  public function createComponent() {
    $objectname =  strtoupper( $this->objName );
    $component  = sprintf( util::$FMTBEGIN, $objectname );
    $component .= $this->createUid();
    $component .= $this->createDtstamp();
    $component .= $this->createAttach();
    $component .= $this->createAttendee();
    $component .= $this->createCategories();
    $component .= $this->createClass();
    $component .= $this->createComment();
    $component .= $this->createCompleted();
    $component .= $this->createContact();
    $component .= $this->createCreated();
    $component .= $this->createDescription();
    $component .= $this->createDtstart();
    $component .= $this->createDue();
    $component .= $this->createDuration();
    $component .= $this->createExdate();
    $component .= $this->createExrule();
    $component .= $this->createGeo();
    $component .= $this->createLastModified();
    $component .= $this->createLocation();
    $component .= $this->createOrganizer();
    $component .= $this->createPercentComplete();
    $component .= $this->createPriority();
    $component .= $this->createRdate();
    $component .= $this->createRelatedTo();
    $component .= $this->createRequestStatus();
    $component .= $this->createRecurrenceid();
    $component .= $this->createResources();
    $component .= $this->createRrule();
    $component .= $this->createSequence();
    $component .= $this->createStatus();
    $component .= $this->createSummary();
    $component .= $this->createUrl();
    $component .= $this->createXprop();
    $component .= $this->createSubComponent();
    return $component . sprintf( util::$FMTEND, $objectname );
  }
}
