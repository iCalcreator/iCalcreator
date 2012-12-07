<?php
/**
 * class for calendar component VTODO
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-12
 */
class vtodo extends calendarComponent {
  var $attach;
  var $attendee;
  var $categories;
  var $comment;
  var $completed;
  var $contact;
  var $class;
  var $created;
  var $description;
  var $dtstart;
  var $due;
  var $duration;
  var $exdate;
  var $exrule;
  var $geo;
  var $lastmodified;
  var $location;
  var $organizer;
  var $percentcomplete;
  var $priority;
  var $rdate;
  var $recurrenceid;
  var $relatedto;
  var $requeststatus;
  var $resources;
  var $rrule;
  var $sequence;
  var $status;
  var $summary;
  var $url;
  var $xprop;
            //  component subcomponents container
  var $components;
/**
 * constructor for calendar component VTODO object
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.8.2 - 2011-05-01
 * @param array $config
 * @return void
 */
  function vtodo( $config = array()) {
    $this->calendarComponent();

    $this->attach          = '';
    $this->attendee        = '';
    $this->categories      = '';
    $this->class           = '';
    $this->comment         = '';
    $this->completed       = '';
    $this->contact         = '';
    $this->created         = '';
    $this->description     = '';
    $this->dtstart         = '';
    $this->due             = '';
    $this->duration        = '';
    $this->exdate          = '';
    $this->exrule          = '';
    $this->geo             = '';
    $this->lastmodified    = '';
    $this->location        = '';
    $this->organizer       = '';
    $this->percentcomplete = '';
    $this->priority        = '';
    $this->rdate           = '';
    $this->recurrenceid    = '';
    $this->relatedto       = '';
    $this->requeststatus   = '';
    $this->resources       = '';
    $this->rrule           = '';
    $this->sequence        = '';
    $this->status          = '';
    $this->summary         = '';
    $this->url             = '';
    $this->xprop           = '';

    $this->components      = array();

    if( defined( 'ICAL_LANG' ) && !isset( $config['language'] ))
                                          $config['language']   = ICAL_LANG;
    if( !isset( $config['allowEmpty'] ))  $config['allowEmpty'] = TRUE;
    if( !isset( $config['nl'] ))          $config['nl']         = "\r\n";
    if( !isset( $config['format'] ))      $config['format']     = 'iCal';
    if( !isset( $config['delimiter'] ))   $config['delimiter']  = DIRECTORY_SEPARATOR;
    $this->setConfig( $config );

  }
/**
 * create formatted output for calendar component VTODO object instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-11-07
 * @param array $xcaldecl
 * @return string
 */
  function createComponent( &$xcaldecl ) {
    $objectname    = $this->_createFormat();
    $component     = $this->componentStart1.$objectname.$this->componentStart2.$this->nl;
    $component    .= $this->createUid();
    $component    .= $this->createDtstamp();
    $component    .= $this->createAttach();
    $component    .= $this->createAttendee();
    $component    .= $this->createCategories();
    $component    .= $this->createClass();
    $component    .= $this->createComment();
    $component    .= $this->createCompleted();
    $component    .= $this->createContact();
    $component    .= $this->createCreated();
    $component    .= $this->createDescription();
    $component    .= $this->createDtstart();
    $component    .= $this->createDue();
    $component    .= $this->createDuration();
    $component    .= $this->createExdate();
    $component    .= $this->createExrule();
    $component    .= $this->createGeo();
    $component    .= $this->createLastModified();
    $component    .= $this->createLocation();
    $component    .= $this->createOrganizer();
    $component    .= $this->createPercentComplete();
    $component    .= $this->createPriority();
    $component    .= $this->createRdate();
    $component    .= $this->createRelatedTo();
    $component    .= $this->createRequestStatus();
    $component    .= $this->createRecurrenceid();
    $component    .= $this->createResources();
    $component    .= $this->createRrule();
    $component    .= $this->createSequence();
    $component    .= $this->createStatus();
    $component    .= $this->createSummary();
    $component    .= $this->createUrl();
    $component    .= $this->createXprop();
    $component    .= $this->createSubComponent();
    $component    .= $this->componentEnd1.$objectname.$this->componentEnd2;
    if( is_array( $this->xcaldecl ) && ( 0 < count( $this->xcaldecl ))) {
      foreach( $this->xcaldecl as $localxcaldecl )
        $xcaldecl[] = $localxcaldecl;
    }
    return $component;
  }
}
