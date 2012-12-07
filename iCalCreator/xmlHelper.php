<?php
/*********************************************************************************/
/*          iCalcreator XML (rfc6321) helper functions                           */
/*********************************************************************************/
/**
 * format iCal XML output, rfc6321, using PHP SimpleXMLElement
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.15.6 - 2012-10-19
 * @param object $calendar, iCalcreator vcalendar instance reference
 * @return string
 */
function iCal2XML( & $calendar ) {
            /** fix an SimpleXMLElement instance and create root element */
  $xmlstr     = '<?xml version="1.0" encoding="utf-8"?><icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">';
  $xmlstr    .= '<!-- created utilizing kigkonsult.se '.ICALCREATOR_VERSION.' iCal2XMl (rfc6321) -->';
  $xmlstr    .= '</icalendar>';
  $xml        = new SimpleXMLElement( $xmlstr );
  $vcalendar  = $xml->addChild( 'vcalendar' );
            /** fix calendar properties */
  $properties = $vcalendar->addChild( 'properties' );
  $calProps = array( 'prodid', 'version', 'calscale', 'method' );
  foreach( $calProps as $calProp ) {
    if( FALSE !== ( $content = $calendar->getProperty( $calProp )))
      _addXMLchild( $properties, $calProp, 'text', $content );
  }
  while( FALSE !== ( $content = $calendar->getProperty( FALSE, FALSE, TRUE )))
    _addXMLchild( $properties, $content[0], 'unknown', $content[1]['value'], $content[1]['params'] );
  $langCal = $calendar->getConfig( 'language' );
            /** prepare to fix components with properties */
  $components    = $vcalendar->addChild( 'components' );
  $comps         = array( 'vtimezone', 'vevent', 'vtodo', 'vjournal', 'vfreebusy' );
  foreach( $comps as $compName ) {
    switch( $compName ) {
      case 'vevent':
      case 'vtodo':
        $subComps     = array( 'valarm' );
        break;
      case 'vjournal':
      case 'vfreebusy':
        $subComps     = array();
        break;
      case 'vtimezone':
        $subComps     = array( 'standard', 'daylight' );
        break;
    } // end switch( $compName )
            /** fix component properties */
    while( FALSE !== ( $component = $calendar->getComponent( $compName ))) {
      $child      = $components->addChild( $compName );
      $properties = $child->addChild( 'properties' );
      $langComp   = $component->getConfig( 'language' );
      $props      = $component->getConfig( 'setPropertyNames' );
      foreach( $props as $prop ) {
        switch( strtolower( $prop )) {
          case 'attach':          // may occur multiple times, below
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              $type = ( isset( $content['params']['VALUE'] ) && ( 'BINARY' == $content['params']['VALUE'] )) ? 'binary' : 'uri';
              unset( $content['params']['VALUE'] );
              _addXMLchild( $properties, $prop, $type, $content['value'], $content['params'] );
            }
            break;
          case 'attendee':
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              if( isset( $content['params']['CN'] ) && !isset( $content['params']['LANGUAGE'] )) {
                if( $langComp )
                  $content['params']['LANGUAGE'] = $langComp;
                elseif( $langCal )
                  $content['params']['LANGUAGE'] = $langCal;
              }
              _addXMLchild( $properties, $prop, 'cal-address', $content['value'], $content['params'] );
            }
            break;
          case 'exdate':
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              $type = ( isset( $content['params']['VALUE'] ) && ( 'DATE' == $content['params']['VALUE'] )) ? 'date' : 'date-time';
              unset( $content['params']['VALUE'] );
              _addXMLchild( $properties, $prop, $type, $content['value'], $content['params'] );
            }
            break;
          case 'freebusy':
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              if( is_array( $content ) && isset( $content['value']['fbtype'] )) {
                $content['params']['FBTYPE'] = $content['value']['fbtype'];
                unset( $content['value']['fbtype'] );
              }
              _addXMLchild( $properties, $prop, 'period', $content['value'], $content['params'] );
            }
            break;
          case 'request-status':
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              if( !isset( $content['params']['LANGUAGE'] )) {
                if( $langComp )
                  $content['params']['LANGUAGE'] = $langComp;
                elseif( $langCal )
                  $content['params']['LANGUAGE'] = $langCal;
              }
              _addXMLchild( $properties, $prop, 'rstatus', $content['value'], $content['params'] );
            }
            break;
          case 'rdate':
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              $type = 'date-time';
              if( isset( $content['params']['VALUE'] )) {
                if( 'DATE' == $content['params']['VALUE'] )
                  $type = 'date';
                elseif( 'PERIOD' == $content['params']['VALUE'] )
                  $type = 'period';
              }
              unset( $content['params']['VALUE'] );
              _addXMLchild( $properties, $prop, $type, $content['value'], $content['params'] );
            }
            break;
          case 'categories':
          case 'comment':
          case 'contact':
          case 'description':
          case 'related-to':
          case 'resources':
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              if(( 'related-to' != $prop ) && !isset( $content['params']['LANGUAGE'] )) {
                if( $langComp )
                  $content['params']['LANGUAGE'] = $langComp;
                elseif( $langCal )
                  $content['params']['LANGUAGE'] = $langCal;
              }
              _addXMLchild( $properties, $prop, 'text', $content['value'], $content['params'] );
            }
            break;
          case 'x-prop':
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE )))
              _addXMLchild( $properties, $content[0], 'unknown', $content[1]['value'], $content[1]['params'] );
            break;
          case 'created':         // single occurence below, if set
          case 'completed':
          case 'dtstamp':
          case 'last-modified':
            $utcDate = TRUE;
          case 'dtstart':
          case 'dtend':
          case 'due':
          case 'recurrence-id':
            if( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              $type = ( isset( $content['params']['VALUE'] ) && ( 'DATE' == $content['params']['VALUE'] )) ? 'date' : 'date-time';
              unset( $content['params']['VALUE'] );
              if(( isset( $content['params']['TZID'] ) && empty( $content['params']['TZID'] )) || @is_null( $content['params']['TZID'] ))
                unset( $content['params']['TZID'] );
              _addXMLchild( $properties, $prop, $type, $content['value'], $content['params'] );
            }
            unset( $utcDate );
            break;
          case 'duration':
            if( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              if( !isset( $content['value']['relatedStart'] ) || ( TRUE !== $content['value']['relatedStart'] ))
                $content['params']['RELATED'] = 'END';
              _addXMLchild( $properties, $prop, 'duration', $content['value'], $content['params'] );
            }
            break;
          case 'rrule':
            while( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE )))
              _addXMLchild( $properties, $prop, 'recur', $content['value'], $content['params'] );
            break;
          case 'class':
          case 'location':
          case 'status':
          case 'summary':
          case 'transp':
          case 'tzid':
          case 'uid':
            if( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              if((( 'location' == $prop ) || ( 'summary' == $prop )) && !isset( $content['params']['LANGUAGE'] )) {
                if( $langComp )
                  $content['params']['LANGUAGE'] = $langComp;
                elseif( $langCal )
                  $content['params']['LANGUAGE'] = $langCal;
              }
              _addXMLchild( $properties, $prop, 'text', $content['value'], $content['params'] );
            }
            break;
          case 'geo':
            if( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE )))
              _addXMLchild( $properties, $prop, 'geo', $content['value'], $content['params'] );
            break;
          case 'organizer':
            if( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE ))) {
              if( isset( $content['params']['CN'] ) && !isset( $content['params']['LANGUAGE'] )) {
                if( $langComp )
                  $content['params']['LANGUAGE'] = $langComp;
                elseif( $langCal )
                  $content['params']['LANGUAGE'] = $langCal;
              }
              _addXMLchild( $properties, $prop, 'cal-address', $content['value'], $content['params'] );
            }
            break;
          case 'percent-complete':
          case 'priority':
          case 'sequence':
            if( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE )))
              _addXMLchild( $properties, $prop, 'integer', $content['value'], $content['params'] );
            break;
          case 'tzurl':
          case 'url':
            if( FALSE !== ( $content = $component->getProperty( $prop, FALSE, TRUE )))
              _addXMLchild( $properties, $prop, 'uri', $content['value'], $content['params'] );
            break;
        } // end switch( $prop )
      } // end foreach( $props as $prop )
            /** fix subComponent properties, if any */
      foreach( $subComps as $subCompName ) {
        while( FALSE !== ( $subcomp = $component->getComponent( $subCompName ))) {
          $child2       = $child->addChild( $subCompName );
          $properties   = $child2->addChild( 'properties' );
          $langComp     = $subcomp->getConfig( 'language' );
          $subCompProps = $subcomp->getConfig( 'setPropertyNames' );
          foreach( $subCompProps as $prop ) {
            switch( strtolower( $prop )) {
              case 'attach':          // may occur multiple times, below
                while( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE ))) {
                  $type = ( isset( $content['params']['VALUE'] ) && ( 'BINARY' == $content['params']['VALUE'] )) ? 'binary' : 'uri';
                  unset( $content['params']['VALUE'] );
                  _addXMLchild( $properties, $prop, $type, $content['value'], $content['params'] );
                }
                break;
              case 'attendee':
                while( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE ))) {
                  if( isset( $content['params']['CN'] ) && !isset( $content['params']['LANGUAGE'] )) {
                    if( $langComp )
                      $content['params']['LANGUAGE'] = $langComp;
                    elseif( $langCal )
                      $content['params']['LANGUAGE'] = $langCal;
                  }
                  _addXMLchild( $properties, $prop, 'cal-address', $content['value'], $content['params'] );
                }
                break;
              case 'comment':
              case 'tzname':
                while( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE ))) {
                  if( !isset( $content['params']['LANGUAGE'] )) {
                    if( $langComp )
                      $content['params']['LANGUAGE'] = $langComp;
                    elseif( $langCal )
                      $content['params']['LANGUAGE'] = $langCal;
                  }
                  _addXMLchild( $properties, $prop, 'text', $content['value'], $content['params'] );
                }
                break;
              case 'rdate':
                while( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE ))) {
                  $type = 'date-time';
                  if( isset( $content['params']['VALUE'] )) {
                    if( 'DATE' == $content['params']['VALUE'] )
                      $type = 'date';
                    elseif( 'PERIOD' == $content['params']['VALUE'] )
                      $type = 'period';
                  }
                  unset( $content['params']['VALUE'] );
                  _addXMLchild( $properties, $prop, $type, $content['value'], $content['params'] );
                }
                break;
              case 'x-prop':
                while( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE )))
                  _addXMLchild( $properties, $content[0], 'unknown', $content[1]['value'], $content[1]['params'] );
                break;
              case 'action':      // single occurence below, if set
              case 'description':
              case 'summary':
                if( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE ))) {
                  if(( 'action' != $prop ) && !isset( $content['params']['LANGUAGE'] )) {
                    if( $langComp )
                      $content['params']['LANGUAGE'] = $langComp;
                    elseif( $langCal )
                      $content['params']['LANGUAGE'] = $langCal;
                  }
                  _addXMLchild( $properties, $prop, 'text', $content['value'], $content['params'] );
                }
                break;
              case 'dtstart':
                if( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE ))) {
                  unset( $content['value']['tz'], $content['params']['VALUE'] ); // always local time
                  _addXMLchild( $properties, $prop, 'date-time', $content['value'], $content['params'] );
                }
                break;
              case 'duration':
                if( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE )))
                  _addXMLchild( $properties, $prop, 'duration', $content['value'], $content['params'] );
                break;
              case 'repeat':
                if( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE )))
                  _addXMLchild( $properties, $prop, 'integer', $content['value'], $content['params'] );
                break;
              case 'trigger':
                if( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE ))) {
                  if( isset( $content['value']['year'] )   &&
                      isset( $content['value']['month'] )  &&
                      isset( $content['value']['day'] ))
                    $type = 'date-time';
                  else {
                    $type = 'duration';
                    if( !isset( $content['value']['relatedStart'] ) || ( TRUE !== $content['value']['relatedStart'] ))
                      $content['params']['RELATED'] = 'END';
                  }
                  _addXMLchild( $properties, $prop, $type, $content['value'], $content['params'] );
                }
                break;
              case 'tzoffsetto':
              case 'tzoffsetfrom':
                if( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE )))
                  _addXMLchild( $properties, $prop, 'utc-offset', $content['value'], $content['params'] );
                break;
              case 'rrule':
                while( FALSE !== ( $content = $subcomp->getProperty( $prop, FALSE, TRUE )))
                  _addXMLchild( $properties, $prop, 'recur', $content['value'], $content['params'] );
                break;
            } // switch( $prop )
          } // end foreach( $subCompProps as $prop )
        } // end while( FALSE !== ( $subcomp = $component->getComponent( subCompName )))
      } // end foreach( $subCombs as $subCompName )
    } // end while( FALSE !== ( $component = $calendar->getComponent( $compName )))
  } // end foreach( $comps as $compName)
  return $xml->asXML();
}
/**
 * Add children to a SimpleXMLelement
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.15.5 - 2012-10-19
 * @param object $parent,  reference to a SimpleXMLelement node
 * @param string $name,    new element node name
 * @param string $type,    content type, subelement(-s) name
 * @param string $content, new subelement content
 * @param array  $params,  new element 'attributes'
 * @return void
 */
function _addXMLchild( & $parent, $name, $type, $content, $params=array()) {
            /** create new child node */
  $name  = strtolower( $name );
  $child = $parent->addChild( $name );
  if( isset( $params['VALUE'] ))
    unset( $params['VALUE'] );
  if( !empty( $params )) {
    $parameters = $child->addChild( 'parameters' );
    foreach( $params as $param => $parVal ) {
      $param = strtolower( $param );
      if( 'x-' == substr( $param, 0, 2  )) {
        $p1 = $parameters->addChild( $param );
        $p2 = $p1->addChild( 'unknown', htmlspecialchars( $parVal ));
      }
      else {
        $p1 = $parameters->addChild( $param );
        switch( $param ) {
          case 'altrep':
          case 'dir':            $ptype = 'uri';            break;
          case 'delegated-from':
          case 'delegated-to':
          case 'member':
          case 'sent-by':        $ptype = 'cal-address';    break;
          case 'rsvp':           $ptype = 'boolean';        break ;
          default:               $ptype = 'text';           break;
        }
        if( is_array( $parVal )) {
          foreach( $parVal as $pV )
            $p2 = $p1->addChild( $ptype, htmlspecialchars( $pV ));
        }
        else
          $p2 = $p1->addChild( $ptype, htmlspecialchars( $parVal ));
      }
    }
  }
  if( empty( $content ) && ( '0' != $content ))
    return;
            /** store content */
  switch( $type ) {
    case 'binary':
      $v = $child->addChild( $type, $content );
      break;
    case 'boolean':
      break;
    case 'cal-address':
      $v = $child->addChild( $type, $content );
      break;
    case 'date':
      if( array_key_exists( 'year', $content ))
        $content = array( $content );
      foreach( $content as $date ) {
        $str = sprintf( '%04d-%02d-%02d', $date['year'], $date['month'], $date['day'] );
        $v = $child->addChild( $type, $str );
      }
      break;
    case 'date-time':
      if( array_key_exists( 'year', $content ))
        $content = array( $content );
      foreach( $content as $dt ) {
        if( !isset( $dt['hour'] )) $dt['hour'] = 0;
        if( !isset( $dt['min'] ))  $dt['min']  = 0;
        if( !isset( $dt['sec'] ))  $dt['sec']  = 0;
        $str = sprintf( '%04d-%02d-%02dT%02d:%02d:%02d', $dt['year'], $dt['month'], $dt['day'], $dt['hour'], $dt['min'], $dt['sec'] );
        if( isset( $dt['tz'] ) && ( 'Z' == $dt['tz'] ))
          $str .= 'Z';
        $v = $child->addChild( $type, $str );
      }
      break;
    case 'duration':
      $output = (( 'trigger' == $name ) && ( FALSE !== $content['before'] )) ? '-' : '';
      $v = $child->addChild( $type, $output.iCalUtilityFunctions::_duration2str( $content ) );
      break;
    case 'geo':
      $v1 = $child->addChild( 'latitude',  number_format( (float) $content['latitude'],  6, '.', '' ));
      $v1 = $child->addChild( 'longitude', number_format( (float) $content['longitude'], 6, '.', '' ));
      break;
    case 'integer':
      $v = $child->addChild( $type, $content );
      break;
    case 'period':
      if( !is_array( $content ))
        break;
      foreach( $content as $period ) {
        $v1 = $child->addChild( $type );
        $str = sprintf( '%04d-%02d-%02dT%02d:%02d:%02d', $period[0]['year'], $period[0]['month'], $period[0]['day'], $period[0]['hour'], $period[0]['min'], $period[0]['sec'] );
        if( isset( $period[0]['tz'] ) && ( 'Z' == $period[0]['tz'] ))
          $str .= 'Z';
        $v2 = $v1->addChild( 'start', $str );
        if( array_key_exists( 'year', $period[1] )) {
          $str = sprintf( '%04d-%02d-%02dT%02d:%02d:%02d', $period[1]['year'], $period[1]['month'], $period[1]['day'], $period[1]['hour'], $period[1]['min'], $period[1]['sec'] );
          if( isset($period[1]['tz'] ) && ( 'Z' == $period[1]['tz'] ))
            $str .= 'Z';
          $v2 = $v1->addChild( 'end', $str );
        }
        else
          $v2 = $v1->addChild( 'duration', iCalUtilityFunctions::_duration2str( $period[1] ));
      }
      break;
    case 'recur':
      foreach( $content as $rulelabel => $rulevalue ) {
        $rulelabel = strtolower( $rulelabel );
        switch( $rulelabel ) {
          case 'until':
            if( isset( $rulevalue['hour'] ))
              $str = sprintf( '%04d-%02d-%02dT%02d:%02d:%02dZ', $rulevalue['year'], $rulevalue['month'], $rulevalue['day'], $rulevalue['hour'], $rulevalue['min'], $rulevalue['sec'] );
            else
              $str = sprintf( '%04d-%02d-%02d', $rulevalue['year'], $rulevalue['month'], $rulevalue['day'] );
            $v = $child->addChild( $rulelabel, $str );
            break;
          case 'bysecond':
          case 'byminute':
          case 'byhour':
          case 'bymonthday':
          case 'byyearday':
          case 'byweekno':
          case 'bymonth':
          case 'bysetpos': {
            if( is_array( $rulevalue )) {
              foreach( $rulevalue as $vix => $valuePart )
                $v = $child->addChild( $rulelabel, $valuePart );
            }
            else
              $v = $child->addChild( $rulelabel, $rulevalue );
            break;
          }
          case 'byday': {
            if( isset( $rulevalue['DAY'] )) {
              $str  = ( isset( $rulevalue[0] )) ? $rulevalue[0] : '';
              $str .= $rulevalue['DAY'];
              $p    = $child->addChild( $rulelabel, $str );
            }
            else {
              foreach( $rulevalue as $valuePart ) {
                if( isset( $valuePart['DAY'] )) {
                  $str  = ( isset( $valuePart[0] )) ? $valuePart[0] : '';
                  $str .= $valuePart['DAY'];
                  $p    = $child->addChild( $rulelabel, $str );
                }
                else
                  $p    = $child->addChild( $rulelabel, $valuePart );
              }
            }
            break;
          }
          case 'freq':
          case 'count':
          case 'interval':
          case 'wkst':
          default:
            $p = $child->addChild( $rulelabel, $rulevalue );
            break;
        } // end switch( $rulelabel )
      } // end foreach( $content as $rulelabel => $rulevalue )
      break;
    case 'rstatus':
      $v = $child->addChild( 'code', number_format( (float) $content['statcode'], 2, '.', ''));
      $v = $child->addChild( 'description', htmlspecialchars( $content['text'] ));
      if( isset( $content['extdata'] ))
        $v = $child->addChild( 'data', htmlspecialchars( $content['extdata'] ));
      break;
    case 'text':
      if( !is_array( $content ))
        $content = array( $content );
      foreach( $content as $part )
        $v = $child->addChild( $type, htmlspecialchars( $part ));
      break;
    case 'time':
      break;
    case 'uri':
      $v = $child->addChild( $type, $content );
      break;
    case 'utc-offset':
      if( in_array( substr( $content, 0, 1 ), array( '-', '+' ))) {
        $str     = substr( $content, 0, 1 );
        $content = substr( $content, 1 );
      }
      else
        $str     = '+';
      $str .= substr( $content, 0, 2 ).':'.substr( $content, 2, 2 );
      if( 4 < strlen( $content ))
        $str .= ':'.substr( $content, 4 );
      $v = $child->addChild( $type, $str );
      break;
    case 'unknown':
    default:
      if( is_array( $content ))
        $content = implode( '', $content );
      $v = $child->addChild( 'unknown', htmlspecialchars( $content ));
      break;
  }
}
/**
 * parse xml string into iCalcreator instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.11.2 - 2012-01-31
 * @param  string $xmlstr
 * @param  array  $iCalcfg iCalcreator config array (opt)
 * @return mixed  iCalcreator instance or FALSE on error
 */
function & XMLstr2iCal( $xmlstr, $iCalcfg=array()) {
  libxml_use_internal_errors( TRUE );
  $xml = simplexml_load_string( $xmlstr );
  if( !$xml ) {
    $str    = '';
    $return = FALSE;
    foreach( libxml_get_errors() as $error ) {
      switch ( $error->level ) {
        case LIBXML_ERR_FATAL:   $str .= ' FATAL ';   break;
        case LIBXML_ERR_ERROR:   $str .= ' ERROR ';   break;
        case LIBXML_ERR_WARNING:
        default:                 $str .= ' WARNING '; break;
      }
      $str .= PHP_EOL.'Error when loading XML';
      if( !empty( $error->file ))
        $str .= ',  file:'.$error->file.', ';
      $str .= ', line:'.$error->line;
      $str .= ', ('.$error->code.') '.$error->message;
    }
    error_log( $str );
    if( LIBXML_ERR_WARNING != $error->level )
      return $return;
    libxml_clear_errors();
  }
  return xml2iCal( $xml, $iCalcfg );
}
/**
 * parse xml file into iCalcreator instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.11.2 - 2012-01-20
 * @param  string $xmlfile
 * @param  array$iCalcfg iCalcreator config array (opt)
 * @return mixediCalcreator instance or FALSE on error
 */
function & XMLfile2iCal( $xmlfile, $iCalcfg=array()) {
  libxml_use_internal_errors( TRUE );
  $xml = simplexml_load_file( $xmlfile );
  if( !$xml ) {
    $str = '';
    foreach( libxml_get_errors() as $error ) {
      switch ( $error->level ) {
        case LIBXML_ERR_FATAL:   $str .= 'FATAL ';   break;
        case LIBXML_ERR_ERROR:   $str .= 'ERROR ';   break;
        case LIBXML_ERR_WARNING:
        default:                 $str .= 'WARNING '; break;
      }
      $str .= 'Failed loading XML'.PHP_EOL;
      if( !empty( $error->file ))
        $str .= ' file:'.$error->file.', ';
      $str .= 'line:'.$error->line.PHP_EOL;
      $str .= '('.$error->code.') '.$error->message.PHP_EOL;
    }
    error_log( $str );
    if( LIBXML_ERR_WARNING != $error->level )
      return FALSE;
    libxml_clear_errors();
  }
  return xml2iCal( $xml, $iCalcfg );
}
/**
 * parse SimpleXMLElement instance into iCalcreator instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.11.2 - 2012-01-27
 * @param  object $xmlobj  SimpleXMLElement
 * @param  array  $iCalcfg iCalcreator config array (opt)
 * @return mixed  iCalcreator instance or FALSE on error
 */
function & XML2iCal( $xmlobj, $iCalcfg=array()) {
  $iCal = new vcalendar( $iCalcfg );
  foreach( $xmlobj->children() as $icalendar ) { // vcalendar
    foreach( $icalendar->children() as $calPart ) { // calendar properties and components
      if( 'components' == $calPart->getName()) {
        foreach( $calPart->children() as $component ) { // single components
          if( 0 < $component->count())
            _getXMLComponents( $iCal, $component );
        }
      }
      elseif(( 'properties' == $calPart->getName()) && ( 0 < $calPart->count())) {
        foreach( $calPart->children() as $calProp ) { // calendar properties
         $propName = $calProp->getName();
          if(( 'calscale' != $propName ) && ( 'method' != $propName ) && ( 'x-' != substr( $propName,0,2 )))
            continue;
          $params = array();
          foreach( $calProp->children() as $calPropElem ) { // single calendar property
            if( 'parameters' == $calPropElem->getName())
              $params = _getXMLParams( $calPropElem );
            else
              $iCal->setProperty( $propName, reset( $calPropElem ), $params );
          } // end foreach( $calProp->children() as $calPropElem )
        } // end foreach( $calPart->properties->children() as $calProp )
      } // end if( 0 < $calPart->properties->count())
    } // end foreach( $icalendar->children() as $calPart )
  } // end foreach( $xmlobj->children() as $icalendar )
  return $iCal;
}
/**
 * parse SimpleXMLElement instance property parameters and return iCalcreator property parameter array
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.11.2 - 2012-01-15
 * @param  object $parameters SimpleXMLElement
 * @return array  iCalcreator property parameter array
 */
function _getXMLParams( & $parameters ) {
  if( 1 > $parameters->count())
    return array();
  $params = array();
  foreach( $parameters->children() as $parameter ) { // single parameter key
    $key   = strtoupper( $parameter->getName());
    $value = array();
    foreach( $parameter->children() as $paramValue ) // skip parameter value type
      $value[] = reset( $paramValue );
    if( 2 > count( $value ))
      $params[$key] = html_entity_decode( reset( $value ));
    else
      $params[$key] = $value;
  }
  return $params;
}
/**
 * parse SimpleXMLElement instance components, create iCalcreator component and update
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.11.2 - 2012-01-15
 * @param  array  $iCal iCalcreator calendar instance
 * @param  object $component SimpleXMLElement
 * @return void
 */
function _getXMLComponents( & $iCal, & $component ) {
  $compName = $component->getName();
  $comp     = & $iCal->newComponent( $compName );
  $subComponents = array( 'valarm', 'standard', 'daylight' );
  foreach( $component->children() as $compPart ) { // properties and (opt) subComponents
    if( 1 > $compPart->count())
      continue;
    if( in_array( $compPart->getName(), $subComponents ))
      _getXMLComponents( $comp, $compPart );
    elseif( 'properties' == $compPart->getName()) {
      foreach( $compPart->children() as $property ) // properties as single property
        _getXMLProperties( $comp, $property );
    }
  } // end foreach( $component->children() as $compPart )
}
/**
 * parse SimpleXMLElement instance property, create iCalcreator component property
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.11.2 - 2012-01-27
 * @param  array  $iCal iCalcreator calendar instance
 * @param  object $component SimpleXMLElement
 * @return void
 */
function _getXMLProperties( & $iCal, & $property ) {
  $propName  = $property->getName();
  $value     = $params = array();
  $valueType = '';
  foreach( $property->children() as $propPart ) { // calendar property parameters (opt) and value(-s)
    $valueType = $propPart->getName();
    if( 'parameters' == $valueType) {
      $params = _getXMLParams( $propPart );
      continue;
    }
    switch( $valueType ) {
      case 'binary':
        $value = reset( $propPart );
        break;
      case 'boolean':
        break;
      case 'cal-address':
        $value = reset( $propPart );
        break;
      case 'date':
        $params['VALUE'] = 'DATE';
      case 'date-time':
        if(( 'exdate' == $propName ) || ( 'rdate' == $propName ))
          $value[] = reset( $propPart );
        else
          $value = reset( $propPart );
        break;
      case 'duration':
        $value = reset( $propPart );
        break;
//        case 'geo':
      case 'latitude':
      case 'longitude':
        $value[$valueType] = reset( $propPart );
        break;
      case 'integer':
        $value = reset( $propPart );
        break;
      case 'period':
        if( 'rdate' == $propName )
          $params['VALUE'] = 'PERIOD';
        $pData = array();
        foreach( $propPart->children() as $periodPart )
          $pData[] = reset( $periodPart );
        if( !empty( $pData ))
          $value[] = $pData;
        break;
//        case 'rrule':
      case 'freq':
      case 'count':
      case 'until':
      case 'interval':
      case 'wkst':
        $value[$valueType] = reset( $propPart );
        break;
      case 'bysecond':
      case 'byminute':
      case 'byhour':
      case 'bymonthday':
      case 'byyearday':
      case 'byweekno':
      case 'bymonth':
      case 'bysetpos':
        $value[$valueType][] = reset( $propPart );
        break;
      case 'byday':
        $byday = reset( $propPart );
        if( 2 == strlen( $byday ))
          $value[$valueType][] = array( 'DAY' => $byday );
        else {
          $day = substr( $byday, -2 );
          $key = substr( $byday, 0, ( strlen( $byday ) - 2 ));
          $value[$valueType][] = array( $key, 'DAY' => $day );
        }
        break;
//      case 'rstatus':
      case 'code':
        $value[0] = reset( $propPart );
        break;
      case 'description':
        $value[1] = reset( $propPart );
        break;
      case 'data':
        $value[2] = reset( $propPart );
        break;
      case 'text':
        $text = str_replace( array( "\r\n", "\n\r", "\r", "\n"), '\n', reset( $propPart ));
        $value['text'][] = html_entity_decode( $text );
        break;
      case 'time':
        break;
      case 'uri':
        $value = reset( $propPart );
        break;
      case 'utc-offset':
        $value = str_replace( ':', '', reset( $propPart ));
        break;
      case 'unknown':
      default:
        $value = html_entity_decode( reset( $propPart ));
        break;
    } // end switch( $valueType )
  } // end  foreach( $property->children() as $propPart )
  if( 'freebusy' == $propName ) {
    $fbtype = $params['FBTYPE'];
    unset( $params['FBTYPE'] );
    $iCal->setProperty( $propName, $fbtype, $value, $params );
  }
  elseif( 'geo' == $propName )
    $iCal->setProperty( $propName, $value['latitude'], $value['longitude'], $params );
  elseif( 'request-status' == $propName ) {
    if( !isset( $value[2] ))
      $value[2] = FALSE;
    $iCal->setProperty( $propName, $value[0], $value[1], $value[2], $params );
  }
  else {
    if( isset( $value['text'] ) && is_array( $value['text'] )) {
      if(( 'categories' == $propName ) || ( 'resources' == $propName ))
        $value = $value['text'];
      else
        $value = reset( $value['text'] );
    }
    $iCal->setProperty( $propName, $value, $params );
  }
}
