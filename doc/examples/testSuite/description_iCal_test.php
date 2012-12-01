<?php // description_iCal_test.php

require_once '../iCalcreator.class.php';
$c = new vcalendar( array( 'unique_id' => 'test.se' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', '--------------------------------------------------blanks 5          5 and now 5 additional blanks     ' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', "Born 1971? Groundhog's day! And congratulations Matthew, your birthday entry gets to be my testbed for all that is good and holy and PHP related. Stupid PHP and iCalendar. I could kick both of their asses. Yo.  " );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'This is a very long description of an EVENT component with no ACTION property set to AUDIO. The meaning of this very long description (with a number of meaningless words) is to test the function of line break after every 75 position and I hope that this is working properly.' );
$e->setProperty( 'description', 'Second description' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'The meaning of this veery long description (with a linebreak--:
:--here) is to test the function of line break after every 75 position and I hope that this is working properly and also a forced linebreak at char 74+1.' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'This is a
 description with
 single linebreaks and two ending blanks  ' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'This is another

 description with

 2*2 linebreaks' );

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', 'This is a another


description with


2*3 linebreaks' );

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'comment', "setLanguage( 'no' )  'set language test. A very long description of an ALARM component with ACTION property set to AUDIO. The meaning of this very long description (with a number of meaningless words) is to test the function of line break after every 75 position and I hope that this is working properly.' ");
$e->setProperty( 'comment', $e->getConfig( 'language' ));
$e->setProperty( 'Description', 'set language test. A very long description of an ALARM component with ACTION property set to AUDIO. The meaning of this very long description (with a number of meaningless words) is to test the function of line break after every 75 position and I hope that this is working properly.' );

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language', 'no' );
$e->setProperty( 'comment', "setLanguage( 'no' ) 'This is description2', array( 'altrep' => 'http://www.domain.net/doc.txt', 'hejsan', 'language' => 'da' )" );
$e->setProperty( 'description'
               , 'This is description2'
               , array( 'altrep' => 'http://www.domain.net/doc.txt'
                      , 'hejsan'
                      , 'language' => 'da' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'comment',     "char test  comma, semi; singlequote' ".' doublequote" ' );
$e->setProperty( 'description', "char test   , ;  ' ".' " ' );

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'language'
             , 'en' );
$e->setProperty( 'comment', "setLanguage( 'en' ), 'å i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt', array( 'altrep' => 'http://www.domain.net/doc.txt', 'hejsan', 'xparamKey' => 'xparamvalue' )" );
$e->setProperty( 'description'
               , 'å i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt'
               , array( 'altrep' => 'http://www.domain.net/doc.txt'
                      , 'hejsan'
                      , 'xparamKey' => 'xparamvalue' ));

$e = & $c->newComponent( 'vevent' );
$e->setProperty( 'description', "x-mas evening, apples, oranges;
pears
bananas" );

$e = & $c->newComponent( 'vevent' );
$e->setConfig( 'nl', '
' );
$e->setProperty( 'description', "x-mas evening, apples, oranges;
pears
bananas" );

$e = & $c->newComponent( 'vevent' );
$comment = <<<EOT
ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i åå>>>åå i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ää>>>ää i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i öö>>>öö i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÅÅ>>>ÅÅ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÄÄ>>>ÄÄ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÖÖ>>>ÖÖ i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i üü>>>üü i åa ä e ö sa Yngve Öst, Ärligt och Ångerfyllt---brytning mitt i ÜÜ>>>ÜÜ
EOT;
$e->setProperty( 'Comment', "ä e $comment" );
$e->setProperty( 'description', $comment );

// save calendar in file, get size, create new calendar, parse saved file, get size
$d   = 'file folder';
$f1  = 't e s t .ics';
$f2  = 't e s t 2 .ics';
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->saveCalendar();
$fs1 = $c->getConfig('filesize');
$df1 = $c->getConfig('dirfile');
$c = new vcalendar( array( 'unique_id' => 'test.se', 'nl' => "
" ));
$c->setConfig( 'directory', $d );
$c->setConfig( 'filename', $f1 );
$c->parse();
$c->setConfig( 'filename', $f2 );
$c->saveCalendar();
$fs2 = $c->getConfig('filesize');
$df2 = $c->getConfig('dirfile');
$d  = str_replace(' ', chr(92).' ', $d); // Backslash-character
$f1 = str_replace(' ', chr(92).' ', $f1);
$f2 = str_replace(' ', chr(92).' ', $f2);
$cmd = 'diff -b -H --side-by-side '.$d.'/'.$f1.' '.$d.'/'.$f2;
$c->saveCalendar();
$fs2 = $c->getConfig('filesize');
$str = $c->createCalendar();
echo $str; $a=array(); $n=chr(10); echo "$n 1 filezise=$fs1 dir/file='$df1'$n"; echo " 2 filezise=$fs2 dir/file='$df2'$n"; echo " cmd=$cmd$n"; exec($cmd, $a); echo " diff result:".implode($n,$a);

// $c->returnCalendar();
?>