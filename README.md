### SSCAIT web

* This is an implementation of the web frontend for **Student StarCraft AI Tournament** (SSCAIT): http://sscaitournament.com/
* It only makes sense to use this project as an add-on to **SSCAIT Tournament Manager** from this repository: https://github.com/certicky/sscait-tournamentmanager
* This web frontend should typically be hosted on the Host machine of SSCAIT Tournament Manager, where its database also runs (see https://github.com/certicky/sscait-tournamentmanager).
* **Warning:** This implementation is a complete spaghetti code mess. It's a hack on top of a hack and quick patch on top of a quick patch. None of this was designed to be used for more than one semester, yet here we are, 12 years later, still running it. Fun!


![diagram-small](https://user-images.githubusercontent.com/3534507/204827784-32c94af4-0bee-4f2f-b2f8-c2d01b88e156.png)

*Fig.1: Project Architecture Diagram*

#### Installation

* Install web server of your choice and PHP 7 or newer compatible version. PHP 8.2 seems to work. Ensure that php-zip is installed. For MySQL, ensure that php-mysqlnd is installed.
* Install composer.
* Make sure you have MySQL server up and running and that it contains the `sc` database and a user with all the required privileges.
  * You should already have the DB set up if you're using this in conjunction with https://github.com/certicky/sscait-tournamentmanager.
  * If you don't have the DB set up yet, create it and then run the `database.sql` to create required tables.
* Clone this repository and update your webserver's settings so that the `www/index.php` from the repo is served by it.
* Make a copy of `settings_server.php.template` to the `www` folder and name it `settings_server.php`. Update the values inside like this:
  * `$GLOBALS["ADMIN_EMAIL"]`: email to which the info emails are sent (your email)
  * `$GLOBALS["BOTS_FOLDER_WITHOUT_SLASH"]`: folder containing the bots files - part of the set up of SSCAIT Tournament Manager. **Warning**: there should not be a path to this directory within the directory (or directories) served by your webserver, otherwise everyone could download files from every bot's `read` and `write` folders etc without logging in.
  * `$GLOBALS["REPLAYS_FOLDER_WITHOUT_SLASH"]`: folder containing the saved replay files - part of the set up of SSCAIT Tournament Manager. Note: if there is no path to this directory within the directory (or directories) served by your webserver at relative path `Replays`, links in web pages to replay files won't work. E.g. if the directory is `/var/www/html/Replays` it would work.
  * `$GLOBALS["CACHE_FOLDER_WITHOUT_SLASH"]`: folder reserved for data caching - just create an empty folder and give web server user the read & write permissions
  * `$db_host`, `$db_username`, `$db_password`: database credentials
  * `$GLOBALS["SMTP_*"]`: credentials for whatever external SMTP service we're using to send emails to bot authors and to admin (e.g. https://www.sendinblue.com/)
* Change the working directory to the directory for the parts of the website that your webserver does not serve, then run composer to download the dependencies `phpmailer/phpmailer` and `league/oauth2-google` (which are necessary for the website to send emails using the Gmail API), e.g.:
  * `cd /var/www/sscait-web/`
  * `composer require phpmailer/phpmailer`
  * `composer require league/oauth2-google`
  * Note: `get_oauth_token.php` currently uses a hardcoded path to under `/var/www/sscait-web/` - if you are using a different path, edit the path in `get_oauth_token.php`.
* If everything went well, the web should be accessible at a location determined by your web server - usually `http://localhost/` or `http://localhost:8080`.
