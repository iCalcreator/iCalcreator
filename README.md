# iCalcreator

is the PHP class package managing

> iCal (rfc2445, rfc5545, rfc6321, rfc7986) calendar information

operating on calendar and
calendar events, reports, todos and journaling data.

~~~~~~~~

iCalcreator supports systems like

* calendars
* CMS
* project management systems
* other applications...

~~~~~~~~

Please review 
- [demo] for a short demo 
- releaseNotes for release brief overview,
- docs/summary and docs/using for details.


###### Builds

c2.40 - *(master)* stable PHP8

v2.39 - PHP7 (tag)

Assert PHP compability using [PHPCompatibility] and [PHPStan].

###### Support

For support use [github.com iCalcreator]. Non-emergence support issues are, unless sponsored, fixed in due time.


###### Sponsorship

Donation using <a href="https://paypal.me/kigkonsult" rel="nofollow">paypal.me/kigkonsult</a> are appreciated.
For invoice, <a href="mailto:ical@kigkonsult.se">please e-mail</a>.

###### Installation

Composer

From the Command Line:

```
composer require kigkonsult/icalcreator
```

In your composer.json:

```
{
    "require": {
        "kigkonsult/icalcreator": ">=2.40"
    }
}
```
For PHP7 use 2.39.

###### License

iCalcreator is licensed under the LGPLv3 License.

[demo]:docs/demoUsage.md
[github.com iCalcreator]:https://github.com/iCalcreator/iCalcreator/issues
[PHPCompatibility]:https://github.com/PHPCompatibility/PHPCompatibility
[PHPStan]:https://github.com/phpstan/phpstan
