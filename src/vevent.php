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
 * iCalcreator VEVENT component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
class vevent extends calendarComponent {
  use traits\ATTACHtrait,
      traits\ATTENDEEtrait,
      traits\CATEGORIEStrait,
      traits\CLASStrait,
      traits\COMMENTtrait,
      traits\CONTACTtrait,
      traits\CREATEDtrait,
      traits\DESCRIPTIONtrait,
      traits\DTENDtrait,
      traits\DTSTAMPtrait,
      traits\DTSTARTtrait,
      traits\DURATIONtrait,
      traits\EXDATEtrait,
      traits\EXRULEtrait,
      traits\GEOtrait,
      traits\LAST_MODIFIEDtrait,
      traits\LOCATIONtrait,
      traits\ORGANIZERtrait,
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
      traits\TRANSPtrait,
      traits\UIDtrait,
      traits\URLtrait;
/**
 * Constructor for calendar component VEVENT object
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.20 - 2017-02-01
 * @param  array $config
 * @uses calendarComponent::__contruct()
 * @uses calendarComponent::setConfig()
 * @uses util::initConfig()
 */
  public function __construct( $config = []) {
    static $E = 'e';
    parent::__construct();
    $this->setConfig( util::initConfig( $config ));
    $this->cno = $E . parent::getObjectNo();
  }
/**
 * Destructor
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-03-18
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
           $this->contact,
           $this->created,
           $this->description,
           $this->dtend,
           $this->dtstamp,
           $this->dtstart,
           $this->duration,
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
           $this->url );
  }
/**
 * Return formatted output for calendar component VEVENT object instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.10.16 - 2011-10-28
 * @return string
 * @uses calendarComponent::createUid()
 * @uses calendarComponent::createDtstamp()
 * @uses vevent::createAttach()
 * @uses vevent::createAttendee()
 * @uses vevent::createCategories()
 * @uses vevent::createComment()
 * @uses vevent::createContact()
 * @uses vevent::createClass()
 * @uses vevent::createCreated()
 * @uses vevent::createDescription()
 * @uses vevent::createDtstart()
 * @uses vevent::createDtend()
 * @uses vevent::createDuration()
 * @uses vevent::createExdate()
 * @uses vevent::createExrule()
 * @uses vevent::createGeo()
 * @uses vevent::createLastModified()
 * @uses vevent::createLocation()
 * @uses vevent::createOrganizer()
 * @uses vevent::createPriority()
 * @uses vevent::createRdate()
 * @uses vevent::createRrule()
 * @uses vevent::createRelatedTo()
 * @uses vevent::createRequestStatus()
 * @uses vevent::createRecurrenceid()
 * @uses vevent::createResources()
 * @uses vevent::createSequence()
 * @uses vevent::createStatus()
 * @uses vevent::createSummary()
 * @uses vevent::createTransp()
 * @uses vevent::createUrl()
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
    $component .= $this->createComment();
    $component .= $this->createContact();
    $component .= $this->createClass();
    $component .= $this->createCreated();
    $component .= $this->createDescription();
    $component .= $this->createDtstart();
    $component .= $this->createDtend();
    $component .= $this->createDuration();
    $component .= $this->createExdate();
    $component .= $this->createExrule();
    $component .= $this->createGeo();
    $component .= $this->createLastModified();
    $component .= $this->createLocation();
    $component .= $this->createOrganizer();
    $component .= $this->createPriority();
    $component .= $this->createRdate();
    $component .= $this->createRrule();
    $component .= $this->createRelatedTo();
    $component .= $this->createRequestStatus();
    $component .= $this->createRecurrenceid();
    $component .= $this->createResources();
    $component .= $this->createSequence();
    $component .= $this->createStatus();
    $component .= $this->createSummary();
    $component .= $this->createTransp();
    $component .= $this->createUrl();
    $component .= $this->createXprop();
    $component .= $this->createSubComponent();
    return $component . sprintf( util::$FMTEND, $objectname );
  }
}
