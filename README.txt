ABOUT LITTLESIS

LittleSis (http://littlesis.org) is a software project of Public Accountability Initiative, Inc., (http://public-accountability.org). 

LittleSis is open source software licensed under the GNU Public License (http://www.gnu.org/copyleft/gpl.html).

For more information see http://code.littlesis.org, or write to info@littlesis.org.



INSTALL NOTES 

(See http://code.littlesis.org/wiki/Install%20Notes for the latest.)

LittleSis requires PHP 5.2.3+ with cURL, GD, PDO and Memcache extensions. The application requires that version 1.1.7 of the [http://symfony-project.org Symfony framework] and [http://sphinxsearch.com Sphinx] are installed on the server.


== Checkout LS using Subversion ==

First you must checkout the source code from the LittleSis repository. You must have [http://subversion.tigris.org/ Subversion] installed and have a user account. Email admin@littlesis.org to obtain an account.

Create a directory in your web root and perform the checkout:

  mkdir /var/www/littlesis
  cd /var/www/littlesis
  svn co http://code.littlesis.org/subversion/ls-open/trunk .

Enter your account password when prompted. The LS app will download the source code (roughly 42 MB) to your machine.


== Create and Load Databases ==

Create two databases, one for the application and one for raw parsed & scraped data, for example:

  mysql> CREATE DATABASE littlesis;
  mysql> GRANT ALL PRIVILEGES ON littlesis.* TO 'littlesis'@'localhost' IDENTIFIED BY 'fakepassword';
  mysql> CREATE DATABASE littlesis_raw;
  mysql> GRANT ALL PRIVILEGES ON littlesis_raw.* TO 'littlesis'@'localhost' IDENTIFIED BY 'fakepassword';

Edit config/databases.yml.dist, enter the connection parameters for the two databases you created, and save as config/databases.yml:

  all:
    raw:
      class:      sfDoctrineDatabase
      param:
        dsn:      mysql://littlesis:fakepassword@localhost/littlesis_raw
  
    main:
      class:      sfDoctrineDatabase
      param:
        dsn:      mysql://littlesis:fakepassword@localhost/littlesis


== LittleSis Configuration ==

Edit config/ProjectConfiguration.class.php.dist, fill in the path to the symfony 1.1 libraries, and save as config/ProjectConfiguration.class.php:

  <?php
  require_once '/usr/share/php/symfony11/lib/autoload/sfCoreAutoload.class.php';

Edit web/.htaccess.dist, enter the web path to your index.php front controller (usually /index.php or /littlesis/index.php, and save as web/.htaccess:

    # no, so we redirect to our front web controller
    RewriteRule ^(.*)$ /littlesis/index.php [QSA,L]
  </IfModule>


To enable the API, API account registration and API documentation:
 * Edit apps/api/config/app.yml.dist, enter the ReCAPTCHA keys and admin API key, and save as apps/api/config/app.yml.
 * Edit apps/api/config/settings.yml.dist, enter a CRSF token secret string, and save as apps/api/config/settings.yml.

To enable the LittleSis frontend, user account registration, and map pages: 
 * Edit apps/frontend/config/app.yml.dist, enter the Google Maps and ReCAPTCHA keys, and save as apps/frontend/config/app.yml. 
 * Edit apps/frontend/config/settings.yml.dist, enter a CRSF token secret string, and save as apps/frontend/config/settings.yml. 
 * Edit apps/frontend/config/routing.yml.dist and save as apps/frontend/config/routing.yml. 
 * There are other keys you may need to set if you want to use some of the scrapers...

Now you need to make the cache directory recursively writable, and create a log directory with an empty log file. This can be done with a symfony command:

  cd /var/www/littlesis
  symfony fix-perms


== Configure Apache ==

Now you need to create Apache aliases pointing the "/littlesis" and "/littlesis/sf" URL paths to the proper locations on your machine. This can be tricky if you haven't created Apache aliases before. An example is below.

  Alias /littlesis/sf /usr/share/php/symfony11/data/web/sf/
  <Directory "/usr/share/php/symfony11/data/web/sf/">
    Options Indexes MultiViews FollowSymLinks
    AllowOverride All
    Allow from All
  </Directory>
  
  Alias /littlesis/ "/var/www/littlesis/web/"
  <Directory "/var/www/littlesis/web/">
      Options Indexes Multiviews FollowSymLinks
      AllowOverride All
      Order allow,deny
      Allow from all
  </Directory>

Restart Apache.


== Load Initial Data ==

If you have a database dump, import it: 

  mysql -u littlesis -p littlesis < some_dump.sql

Otherwise, you need to create the necessary database tables and import data from a fixtures file:

  symfony doctrine:build-sql frontend
  symfony doctrine:insert-sql frontend
  symfony doctrine:data-load frontend

Now clear the cache and you're good to go!

  symfony cc

If you ever get a permissions error while attempting to clear the cache, try this first:

  sudo symfony fix-perms

You can check that LittleSis is up and running by visiting http://localhost/littlesis/frontend_dev.php (or whatever web path you've parked your LittleSis installation at) in your web browser. You should see a welcome screen. By default LittleSis comes with three pre-existing user accounts, "System", "Bot", and "Admin", which are used by the software in various situations. Now that you have the application running, you can login to LittleSis as any of them in order to gain access to the backend tools:

 * '''System''' [username: system@example.org, password: system]
 * '''Bot''' [username: bot@example.org, password: bot1]
 * '''Admin''' [username: admin@example.org, password: admin]


== Setup Search ==

However, at this point search on LittleSis will not work. For this you will need to install and configure [http://http://www.sphinxsearch.com/ Sphinx].

First, download and compile the latest stable version of Sphinx from its website (version 0.9.8.1 as of now).

Edit config/sphinx.conf.dist in the LittleSis application, replacing "[username]", "[password]" and other parts in [] brackets with your local settings, and save the modified file as config/sphinx.conf.

Create writable log files at the locations you specified in sphinx.conf (for example, /var/log/searchd/searchd.pid, etc):

  cd /var/www/littlesis/log
  mkdir searchd
  touch searchd.log
  touch query.log
  touch searchd.pid
  chmod a+w *

Generate indexes:

  indexer --config config/sphinx.conf --all

Start searchd:

  searchd --config config/sphinx.conf

Create the following cron jobs to update the Sphinx index so that edits are reflected in the search (within a minute):

  0 0 * * * indexer --config [path/to/littlesis]/config/sphinx.conf entities notes --rotate
  * * * * * indexer --config [path/to/littlesis]/config/sphinx.conf entities-delta notes-delta --rotate

The "entities" and "notes" index updates only at midnight, whereas the "delta" indexes update every minute. This is to minimize index-rebuilding time while keeping the index constantly fresh. For further explanation of this indexing strategy, see the [http://www.sphinxsearch.com/docs/manual-0.9.8.html#live-updates Live index updates] section of the Sphinx documentation.

Clear the Symfony cache and search on the site should now work:

  symfony cc
