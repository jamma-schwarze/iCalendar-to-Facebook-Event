#!/bin/bash
#

egrep 'DTSTART|DTEND|:VEVENT|TIMEZONE' ical2fb_html/tests/cross-day.ics  | sed -e 's/DTSTART/S/;s/DTEND/E/;s/T\([0-9][0-9]\)\([0-9][0-9]\).*/ \1:\2/'
