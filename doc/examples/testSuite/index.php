<?php
/**
 * iCalcreator class v2.10
 * copyright (c) 2007-2011 Kjell-Inge Gustafsson, kigkonsult
 * www.kigkonsult.se/iCalcreator/index.php
 * ical@kigkonsult.se
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
$action  = isset($_REQUEST['action'])  ? $_REQUEST['action']  : 'run-ical';
$fname   = isset($_REQUEST['fname'])   ? $_REQUEST['fname']   : null;
$output  = isset($_REQUEST['output'])  ? $_REQUEST['output']  : 'display';
$content = isset($_REQUEST['content']) ? $_REQUEST['content'] : null;
$message = isset($_REQUEST['message']) ? $_REQUEST['message'] : null;

if( '0' != ini_get ( 'magic_quotes_gpc' )) {
  $content = stripcslashes ( $content );
}

$files = array( 0 => '   ' );
if ($dir = @opendir( '.' )) {
  while( FALSE !== ( $theFile = readdir( $dir ))) {
    if(( '.' == $theFile ) || ( '..' == $theFile ))
      continue;
    if(( 'index.php' == $theFile ) || ( 'README.txt' == $theFile ))
      continue;
    if(( 'file' == substr( $theFile, 0, 4 )) ||
       ( 'test' == substr( $theFile, 0, 4 )) ||
          ( '~' == substr( $theFile, -1 )))
      continue;
    $files[] = $theFile;
  }
  closedir($dir);
}
asort( $files );
$files[0] = 'SELECT FILE';

if ( 'save' == $action ) {
  $msg = null;
  if ( !$fname )
    $msg = 'No file is selected!';
  if ( 0 < strlen( $content ))
    $msg = 'The file '.$files[$fname].' has no content!';
  if ( is_writable( $files[$fname] )) {
    if ( !$fp = fopen( $files[$fname], 'w+' ))
      $msg = 'Cannot open file ( '.$files[$fname].' )';
    if ( !fwrite( $fp, $content ))
      $msg = 'Cannot write to file ( '.$files[$fname].' )';
    $msg = 'Success, save content to file ( '.$files[$fname].' )';
    fclose($fp);
  }
  else
    $msg = 'The file '.$files[$fname].' is not writable';
  $url  = 'index.php?fname='.$fname.'&action=edit&message='.$msg;
  if (headers_sent()) {
    echo "<script>document.location.href='$url';</script>\n";
  }
  else {
    @ob_end_clean(); // clear output buffer
    header( "Location: $url" );
  }
}
elseif( !empty( $fname ) && ( 'run-' == substr( $action, 0, 4 ))) {
  $lines  = file ( $files[$fname] );
  $lines2 = array();
  foreach( $lines as $row ) {
    if( "\n" == trim( $row ))
      continue;
    $pos1 = strpos( $row, '->setConfig( "format"' ); // remove format setting
    $pos2 = strpos( $row, '$str = str_replace' );    // remove display adjustment
    $pos3 = strpos( $row, '$c->setConfig( "filename", "t' );
    if(( $pos1 === FALSE ) && ( $pos2 === FALSE ) && ( $pos3 === FALSE ))
      $lines2[] = $row;
  }
  $content = null;
  $cnt = 0;
  foreach( $lines2 as $row ) {
    $pos = strpos( $row, '$c->saveCalendar');
    if(( $pos !== false ) &&      // if run-xcal, set format
       ( ++$cnt > 1 )     &&      // after 2nd occurence of saveCal.. .
       ( 'run-xcal' == $action )) {
      $content .= $row;
      $content .= substr( $row, 0, $pos ).'$c->setConfig( "format", "xcal" );'."\n";
      $content .= substr( $row, 0, $pos ).'$c->setConfig( "filename", "t e s t .xml" );'."\n";
      continue;
    }
    if( 'display' != $output ) { // redirect output to browser
      if(( false !== strpos( $row, '$str = $' )) &&
          ( '// ' != substr( $row, 0, 3 )))
        $row = '// '.$row; // add comment-mark if not exist
      elseif(( false !== strpos( $row, 'echo $str' )) &&
          ( '// ' != substr( $row, 0, 3 )))
        $row = '// '.$row; // add comment-mark if not exist
      elseif((( false !== strpos( $row, '->returnCalendar(' )) ||
              ( false !== strpos( $row, '->useCachedCalendar(' ))) &&
         ( '// $' == substr( $row, 0, 4 )))
        $row = substr( $row, 3); // remove comment-mark if exist
    }
    else { // display output
      if( false !== strpos( $row, '$str = $' )) {
        if( '// ' == substr( $row, 0, 3 ))
          $row = substr( $row, 3); // remove comment-mark if exist
        if( 'run-xcal' == $action ) { // if run-xcal.. .
          $row .= '$str = str_replace( "<", "&lt;", $str );'."\n";
          $row .= '$str = str_replace( ">", "&gt;", $str );'."\n";
        }
      }
      elseif( false !== strpos( $row, 'echo $str' )) {
        if( '// ' == substr( $row, 0, 3 ))
          $row = substr( $row, 3); // remove comment-mark if exist
        if( 'run-xcal' == $action ) // change output filenames to xml-format
          $row = str_replace( '.ics', '.xml', $row );
        elseif( 'run-ical' == $action ) // change output filenames to ical-format
          $row = str_replace( '.xml', '.ics', $row );
      }
      elseif((( false !== strpos( $row, '->returnCalendar(' )) ||
              ( false !== strpos( $row, '->useCachedCalendar(' ))) &&
             ( '// $' != substr( $row, 0, 4 )))
        $row = '// '.$row; // add comment-mark if not exist
    }
    $content .= $row;
  }
  if( 0 < ( substr_count( $content, "\n\n" )))
    $content = str_replace( "\n\n", "\n", $content );
  if( is_writable( $files[$fname] ) && ( $fp = fopen( $files[$fname], 'w+' ))) {
    fwrite( $fp, $content );
    fclose($fp);
  }
  if( 'display' != $output ) { // redirect output to browser
    header( "Location: ".$files[$fname] );
    exit();
  }
}
// echo "action=$action file=$fname output=$output message=$message <br />\n"; // test ###
if( $fname ) {
  $pos = @strpos( $files[$fname], '_');
  if ($pos !== false) {
    $property = substr( $files[$fname], 0, $pos );
  }
}
else
  $property = null;
/*
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
*/
?>
<HTML>
<HEAD>
<TITLE>iCalcreator 2.10 testsuite - <?php echo $property; ?></TITLE>
<META name="author"      content="Kjell-Inge Gustafsson - kigkonsult" />
<META name="copyright"   content="2007-2011 Kjell-Inge Gustafsson - kigkonsult" />
<META name="keywords"    content="ical, calendar, calender, xcal, xml, icalender, rfc2445, vcalender, php, create" />
<META name="description" content="iCalcreator" />
<LINK href="../images/favicon.ico" rel="shortcut icon"/>
</HEAD>
<BODY<?php if( 'edit' == $action ) {?> onLoad="document.getElementById('content').focus()"<?php } ?>>
<a name="top"></a>
<FORM ID="form" NAME="form" ACTION="index.php" METHOD="post">
<TABLE border=0><TR><TD>
<?php
echo "<SELECT NAME='fname'>\n";
foreach( $files as $ix => $theFile ) {
  echo '<OPTION VALUE="'.$ix.'"';
  if( $fname == $ix )
    echo ' SELECTED="selected"';
  echo '>'.$theFile.'</option>'."\n";
}
echo '</SELECT>';
$actions = array( 'run-ical', 'run-xcal', 'edit' );
if( in_array( $action, array( 'edit', 'save' )))
  $actions[] = 'save';
foreach( $actions as $ix => $choice ) {
  echo '<span STYLE="border:thin dotted gray" onClick="rg1'.$ix.'.checked=true"><INPUT id="rg1'.$ix.'" NAME="action" VALUE="'.$choice.'"';
  if( $choice == $action )
    echo' CHECKED="checked"';
  echo ' TYPE="radio" />'.$choice."&nbsp;</span>&nbsp;\n";
}
?>
</TD>
<TD>
<INPUT TYPE="submit" VALUE="submit" />&nbsp;&nbsp;
<A TITLE="iCalDictionary, rfc2445 in HTML format" HREF="http://www.kigkonsult.se/iCalDictionary/index.html" target="_blank">iCalDictionary</a>
</TD>
<TD ALIGN="right">
<A TITLE="using iCalcreator" HREF="http://www.kigkonsult.se/downloads/index.php" TARGET="_blank">using iCalreator</a>
</TD></TR>
<?php
echo '<TR><TD align="right">run&nbsp;and&nbsp;';
echo '<span STYLE="border:thin dotted gray" onClick="rg21.checked=true">';
echo '<INPUT id="rg21" NAME="output" VALUE="display"';
if( 'display' == $output )
  echo' CHECKED="checked"';
echo ' TYPE="radio" />output=display&nbsp;</span>&nbsp;'."\n";
echo '<span STYLE="border:thin dotted gray" onClick="rg22.checked=true"><INPUT id="rg22" NAME="output" VALUE="redirect"';
if( 'display' != $output )
  echo' CHECKED="checked"';
echo ' TYPE="radio" />output=redirect to browser&nbsp;</span'."\n";
echo "</TD>\n<TD colspan='2'></TD>\n</TR>";
if( $message ) {
  $text  = $message;
  $color = 'red';
}
elseif( $fname ) {
  $text  = @$files[$fname];
  $color = 'silver';
}
else {
  echo "</TABLE></TD></TR>\n";
  echo "<TR><TD>";
  echo '<A HREF="http://sourceforge.net/tracker/?group_id=174828&atid=870787" target="_blank">Tracker at Sourceforge.net</A> <- Feature Requests - Bugs';
  echo "</TD>\n<TD COLSPAN=\"2\" ALIGN=\"right\">";
  echo 'Forum&nbsp;->&nbsp;<A HREF="https://sourceforge.net/forum/?group_id=174828" target="_blank">Discussion&nbsp;at&nbsp;Sourceforge.net</A>';
  echo "</TD></TR>\n";
?>
</TABLE>
</FORM>
</BODY>
</HTML>
<?php
  exit;
}
echo "<TR><TD WIDTH=\"800px\" COLSPAN=\"3\">\n";
$style = ' font-size: 18px; padding: 5px;';
echo "<H1 STYLE=\"background-color: $color; $style\">".$text."</H1>\n";
switch( $action ) {
  case 'run-ical':
  case 'run-xcal':
    echo '<PRE STYLE="background-color:#ccccff; padding: 5px;">'."\n";
    $time_start = getmicrotime(true);
    include_once( @$files[$fname] );
    $time_end   = getmicrotime(true);
    $time       = $time_end - $time_start;
    echo "</PRE>\n";
    break;
  default:
  case 'edit':
    $lines = file ( $files[$fname] );
    $str   = null;
    $rows  = ( 50 < count( $lines )) ? count( $lines ) : 50;
    $cols  = 0;
    foreach( $lines as $line ) {
      if( $cols < strlen( $line ))
          $cols = strlen( $line );
      $str .= $line;
    }
    $cols = (  80 > $cols ) ?  80 : $cols;
    $cols = ( 100 < $cols ) ? 100 : $cols;
    echo '<TEXTAREA id="content" NAME="content" ';
    echo 'STYLE="border: black solid; font: 14px courier; padding: 5px; background-color: #ccccff"';
    echo 'ROWS="'.$rows.'" COLS="'.$cols.'">'."\n";
    echo $str;
    echo "</TEXTAREA>\n";
    break;
}
echo "</TD></TR>\n";
echo "<TR><TD COLSPAN=\"3\">\n";
echo "<H1 STYLE=\"background-color: $color; $style\">".$text."</H1>\n";
echo "</TD></TR>\n";
echo "<TR><TD COLSPAN='3'><TABLE width='100%'><TR>";
echo "<TD ALIGN=\"middle\" width='50%'><A HREF=\"#top\">[top]</A></TD>\n";
if( isset( $time )) {
 // echo "<TD WIDTH='30%' ALIGN=\"right\">"."start<TD ALIGN=\"right\">".number_format( $time_start, 4 )."<TR>";
 // echo "<TD><TD ALIGN=\"right\">end<TD ALIGN=\"right\">".number_format( $time_end, 4 )."<TR>";
  echo "<TD><TD ALIGN=\"right\">execute&nbsp;time<TD ALIGN=\"right\">".number_format( $time, 4 );
//  echo substr(microtime(), 2, 4);
}
echo "</TABLE></TD></TR>\n";
echo "<TR><TD>";
echo '<A HREF="http://sourceforge.net/tracker/?group_id=174828&atid=870787" target="_blank">Tracker at Sourceforge.net</A> <- Feature Requests - Bugs';
echo "</TD>\n<TD COLSPAN=\"2\" ALIGN=\"right\">";
echo 'Forum&nbsp;->&nbsp;<A HREF="https://sourceforge.net/forum/?group_id=174828" target="_blank">Discussion&nbsp;at&nbsp;Sourceforge.net</A>';
echo "</TD></TR>\n";
?>
</TABLE>
</FORM>
</BODY>
</HTML>
<?php
function getmicrotime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
}
?>