=== Plugin Name ===
Contributors: Lisa Armstrong
Tags: intrepid travel
Requires at least: 4.6
Tested up to: 5.1
Stable tag: 5.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows basic list of available tours from Intrepid Tours.  Displays list and details, passes selected tour to contact form.

== Description ==



== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

= HOW TO USE =
1) Add the shortcode [lastminutetours] to the page.
That will automatically display the list and the details.  You need to add the form yourself.

2) Add a form on the same page.

3) The form should have a field with an id of 'tripinfo'.
The trip information added there.

= TO GET THE DATA FEED =
Important!  You need to have your feed set up with Intrepid.  Contact them for details.  The URL for the feed is hard coded in  'get-feed.php'

Set up a cron job that runs once a day.  It takes about half an hour to process the info.
The cron job should look like:

wget -q -O /dev/null "https://{yourwebsite.com}/wp-content/plugins/intrepid-tours/get_feed.php"

== Frequently Asked Questions ==

= Customizing List and Form =

You can user your own custom HTML for the list and the details area.

1) In your theme directory, create a folder called 'intrepid-tours'.
2) Copy the folder: \wp-content\plugins\intrepid-tours\templates
into your theme folder intrepid-tours

It should look like:
\wp-content\themes\{your theme}\intrepid-tours\templates\lastminute_moreinfo.html
\wp-content\themes\{your theme}\intrepid-tours\templates\lastminute_table.html

You can edit those files.
Note:  there is some code in there, but if your comfortable with HTML, it will make sense.


== Screenshots ==


== Changelog ==

= 1.0 =
Initial release.


== Upgrade Notice ==

= 1.0 =
na
