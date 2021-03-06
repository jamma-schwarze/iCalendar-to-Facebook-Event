<?php
include 'headerInclude.php';
?>


<h2>About</h2>
<p>With this app you can subscribe to a public <a href="http://en.wikipedia.org/wiki/ICalendar">iCalendar</a> URL which then gets regularly checked for updates. When new events in the calendar become available, it will create a facebook event in your name. This works also for Facebook pages and groups where you are an administrator.</p>

<p>This app requires permissions to create events in your name and manage pages. The latter is neccessary because Facebook does not support a special "create events for pages" permission.</p>

<p>Note: You do not add the app itself to your page, but access the app and create a subscription there and configure it to create events on your page.</p>

<h4>Example: Google Calendar</h4>
<p>Most calendars export to the iCalendar format, usually a file with the suffix .ics. For example, to get your Google Calendar URL: in <a href="http://calendar.google.com/">Google Calendar</a> go to Settings -> Calendars -> choose a calendar -> public ICAL.</p>

<h4>Migrating from Calendar to Event</h4>

<p>If you have been using the original "Calendar to Event" by Mauro Bieg, there are two ways to migrate:</p>
<ol>
<li>Deactivate your subscription at the <a href="http://calendartofacebook.project21.ch/">Calendar to Event</a>, then <a href="index.php?action=showSubscriptionList">add the subscription here</a>. You will get duplicate events - be sure to delete the event which has been created earliert (by Mauro's app) or delete all future events before adding/updating the subscription.</li>
<li>Delete your subscription at the <a href="http://calendartofacebook.project21.ch/">Calendar to Event</a> - this will <strong>DELETE</strong> all events created by that app. Afterwards, <a href="index.php?action=showSubscriptionList">add a new subscription here</a>.</li>
</ol>
<p>There's no easier way.</p>

<h4 id="images">Images</h4>
<p>If some of the events in your iCalendar file have a special <a href="http://en.wikipedia.org/wiki/ICalendar#Calendar_extensions" target="_blank">X-field</a> that cointains an URL which points to an image file, you can enter the name of that field in the 'Picture' field in the advanced options of this App. For example if your ics file looks like the following</a>
<p><code>
BEGIN:VEVENT<br/>
DTSTART;VALUE=DATE:20060704<br/>
DTEND;VALUE=DATE:20060705<br/>
SUMMARY:Independence Day<br/>
ATTACH:http://www.google.com/logos/july4th06.gif<br/>
END:VEVENT<br/>
</code></p>
then you would write ATTACH in the Picture field in the <i>advanced options</i> of a new subscription or click <i>edit</i> for an existing one. Or you can write the name of any other header found in your ICS file which contains an URL.</p>

<p>If you're using Google Calendar you can do the following:
	<ol>
		<li>in the iCalendar-to-Event app, in Advanced options (or Edit for an existing subscription), write 'ATTACH' (without the quotations marks) in the Picture field</li>
		<li>in Google Calendar click on the Labs icon in the upper right corner (the green potion)</li>
		<li>enable the Event attachments feature.</li>
		<li>now, when you create a new event you can add an attachement. Choose an image.</li>
	</ol>
</p>

<p>If the image is at least 400x150 pixels, it will be added as a cover image automatically.</p>

<h4 id="reactivate">Subscription was deactivated</h4>
<p>If the app always encounters an error when updating a subscription for an extended period of time, that subscription will be deactivated. It will say 'deactivated' in the iCalendar-to-Event app subscriptions list. You can try to reactivate it there which will only work if the error has been fixed. This is done as to not bother my server with checking lots of ics-files that don't work and aren't used anymore.</p>


<h4 id="faq">Miscellaneous/FAQ</h4>
<ul class="list">
<li>Don't add a calendar with lots of event, remove it again, add it again etc. Facebook imposes certain <a href="http://www.facebook.com/help/?page=1052" target="_blank">limits</a> and they don't like adding/removing lots of event too fast. When they decide you posted too much too quick the App will simply fail to create new events for you and usually there is a cryptic message in your Log.</li>
<li>Facebook doesn't support adding events that have a starttime in the past. So currently I've set the App to post only events from now until 3 months into the future.</li>
<li>If you change events in the ical file the facebook events will get updated but not the other way around. Also if you delete an event in your calendar it will remain on facebook.</li>
<li>This application has only been tested with Google Calendar for now. Please test with only one or two events of your choice if you choose another Calendard provider and take care that the times are copied correctly.</li>
<li>If you see post of events on your wall it is Facebook's own 'Events' App that is posting to your wall and not my app. While I currently don't know of any way to disable that behaviour for your personal page, the following works for pages:
<ol>
<li>Go to your page and click 'Edit page'</li>
<li>Select 'Apps' in the list on the left</li>
<li>At the Events app click 'Edit settings' -> 'Additional Permissions' and deselect 'Publish content to my wall'</li>
</ol>
</li>
<li>Internet Explorer might have some problems with this app. Get a decent browser like <a href="http://www.getfirefox.com">Firefox</a> or <a href="http://www.google.com/chrome/">Chrome</a>.</li>
</ul>

<br/>
<h2 id="development">Future development</h2>
<h4>Planned Features</h4>
<p>Here is a list of planned features and options that are not yet supported:</p>
<ul class="list">
<li>associate a picture with a subscription which then will be added to every event created</li>
<li>set your default RSVP: not attending / attending</li>
<li>support cover images (currently profile picture only)</li>
<li>support deleting events</li>
<li>remind the user to renew his/her access token once in a while (not neccessary for pages)</li>
</ul>

<h4>Contribute</h4>
<p>This is free and open source software written in PHP. Feel free to <a href="https://github.com/jamma-schwarze/iCalendar-to-Facebook-Event">contribute</a>!</p>

<h4>Support</h4>
<p>I'm working on this app in my spare time and running it on my private server. Please feel free to donate some money. Thank you!</p>
<p>For questions regarding the application, please visit the application's Facebook page: <a href="https://www.facebook.com/pages/ICalendar-to-Events-Importer-Community/536493229767772">iCalendard to Events Importer Community</a>.</p>

<h4>Credits</h4>
<p>This is currently 95% based on the work of Mauro Bieg who was so kind as to share his development with the world.</p>

<?php
include 'footerInclude.php';
?>
