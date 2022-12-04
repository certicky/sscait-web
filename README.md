### SSCAIT web

* This is an implementation of the web frontend for **Student StarCraft AI Tournament** (SSCAIT): http://sscaitournament.com/
* It only makes sense to use this project as an add-on to **SSCAIT Tournament Manager** from this repository: https://github.com/certicky/sscait-tournamentmanager
* This web frontend should typically be hosted on the Host machine of SSCAIT Tournament Manager, where its database also runs (see https://github.com/certicky/sscait-tournamentmanager).
* **Warning:** This implementation is a complete spaghetti code mess. It's a hack on top of a hack and quick patch on top of a quick patch. None of this was designed to be used for more than one semester, yet here we are, 12 years later, still running it. Fun!


![diagram-small](https://user-images.githubusercontent.com/3534507/204827784-32c94af4-0bee-4f2f-b2f8-c2d01b88e156.png)

*Fig.1: Project Architecture Diagram*

#### Installation

* Install web server of your choice and PHP 7 or newer compatible version.
* Make sure you have MySQL server up and running and that it contains the `sc` database and a user with all the required privileges.
  * You should already have the DB set up if you're using this in conjunction with https://github.com/certicky/sscait-tournamentmanager.
  * If you don't have the DB set up yet, create it and then run the `database.sql` to create required tables.
* Clone this repository and update your webserver's settings so that the `www/index.php` from the repo is served by it.
* Make a copy of `settings_server.php.template` and name it `settings_server.php`. Update the values inside like this:
  * `$GLOBALS["ADMIN_EMAIL"]`: email to which the info emails are sent (your email)
  * `$GLOBALS["BOTS_FOLDER_WITHOUT_SLASH"]`: folder containing the bots files - part of the set up of SSCAIT Tournament Manager
  * `$GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"]`: folder containing the saved replay files - part of the set up of SSCAIT Tournament Manager
  * `$GLOBALS["CACHE_FOLDER_WITHOUT_SLASH"]`: folder reserved for data caching - just create an empty folder and give web server user the read & write permissions
  * `$db_host`, `$db_username`, `$db_password`: database credentials
  * `$GLOBALS["SMTP_*"]`: credentials for whatever external SMTP service we're using to send emails to bot authors and to admin (e.g. https://www.sendinblue.com/)
* If everything went well, the web should be accessible at a location determined by your web server - usually `http://localhost/` or `http://localhost:8080`.


