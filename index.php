<?php

    /*
    *  _________________________________________________
    *  $Id: index.php 74 2006-08-29 20:42:08Z gstewart $
    *  tv plan two point oh web corporate synergy buzzword
    *
    *  Gavin Gilmour (gavin(at)brokentrain.net)
    *  great dominions they don't come cheap
    *  _________________________________________________
    */

    /* check for server config */
    if (!is_file('lib/server_config.php')) {
        die(sprintf("Error: Cannot open database settings '%s', please make sure it exists.",'lib/server_config.php'));
    }

    /* essential includes */
    require('lib/server_config.php'); // server specific settings
    require('lib/util_lib.php'); // utility library
    require('lib/classes/User.class.php'); // users 
    require('lib/classes/User_lib.php'); // user management
    require('lib/classes/Show.class.php'); // show management 
    require('lib/classes/Show_lib.php'); // general show functions
    require('lib/classes/News.class.php'); // new tv.com listings
    require('lib/smarty/Smarty.class.php'); // smarty
    require('lib/lastRSS.php'); // rss class

    /* program info */
    define('TITLE', 'tvplan2');
    define('DESC', 'online show tracker');
    define('VER', '2.0');
    define('ADMINEMAIL', 'gavin(at)brokentrain.net'); // where mails come from, etc
    define('ENABLEEMAILS', '1'); // enable registration emails 

    /* global dirs */
    define('CONFIGDIR', 'cfg/'); // path to config files
    define('TMPDIR', 'tmp/'); // path to tmp files
    define('PROFILEDIR', 'profile/'); // path to user profiles

    /* global files */
    define('SHOWLIST', CONFIGDIR . 'shows.txt'); // epguide show list
    define('TIPFILE', CONFIGDIR . 'tips.txt'); // tip file
    define('LOGFILE', TMPDIR . 'access.log'); // access log

    /* default list constraints (hardcoded) */
    define('MAXFEEDS', '5'); // maximum feeds to show
    define('MAXTORRENTS', '4'); // maximum torrents to show
    define('MIN_USERNAME_LENGTH', '3'); // maximum length of usernames
    define('MAX_USERNAME_LENGTH', '30'); // maximum length of usernames
    define('MIN_PASSWORD_LENGTH', '3'); // minimum length of passwords
    define('MAX_PASSWORD_LENGTH', '30'); // maximum length of passwords

    /* connect to database */
    $mysqli = new mysqli(MYSQL_SERVER, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DB);

    /* check connection */
    if (mysqli_connect_errno()) {
        die(sprintf("Database Error: %s", $mysql_error));
        exit();
    }

    /* log the connection */
    if (!logEvent(LOGFILE)) {
        die(sprintf("Error: Unable to open '%s' for writing (logs connection attempts, usage, etc.)",LOGFILE));
    }

    /* check for epguide show list */
    if (!is_file(SHOWLIST)) {
        die(sprintf("Error: Cannot read show list from '%s', please make sure it exists.",SHOWLIST));
    }

    /* smarty template stuff */
    $smarty = new Smarty;
    $smarty->compile_check = false;
    $smarty->debugging = false;
    $smarty->force_compile = true;
    $smarty->load_filter('output','trimwhitespace');

    /* check our login stuff */
    session_start();

    /* assign constants */
    $smarty->assign('title', TITLE);
    $smarty->assign('desc', DESC);
    $smarty->assign('ver', VER);
    $smarty->assign('enable_registration', ENABLE_REGISTRATION);
    $smarty->assign('js_validate', 1);

    /* check our query string before everything for a new register */
    $ARGV = explode(',', urldecode($_SERVER['QUERY_STRING']));

    if (($ARGV[0] == "register") || (empty($ARGV[0])) 
        || ($ARGV[0] == "lostpassword")) {
            
            /* retrieve some site stats */
            $smarty->assign('sitestats', getSiteStats());
    }

    if ($ARGV[0] == "register") {

        if (isset($_POST['register_submit'])) {

            if (checkFormInput($_POST['register_username'], $_POST['register_password'], $_POST['register_email'], $msg)) {

                /* everything looks alright, create a new object */
                $user = new User($_POST['register_username'], $_POST['register_password'], $_POST['register_email']);

                /* add the user to the database if input is valid */
                if ($user->add()) {

                    /* write out the a default config and create the user index tables */
                    if (($user->write_default_config()) && ($user->createUserTables())) {

                        /* construct the location of tvplan on the server */
                        $site_path = "http://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER['PHP_SELF']) . "/";

                        $body = "Hello, this is just a confirmation email to say that you've successfully created an account with the following details:" . "\n\n";
                        $body .= "username: " . $_POST['register_username'] . "\n";
                        $body .= "password: " . $_POST['register_password'] . "\n\n";
                        $body .= "You can now login by pointing your browser at " . $site_path . "\n\n\n";
                        $body .= "Cheers," . "\n" . "tvplan admin";

                        /* send user confirmation email */
                        $user->sendMail(ADMINEMAIL, "tvplan admin", "tvplan: account confirmation", $body);

                        /* if the user wants to login immediately, do this */
                        if (isset($_POST['automatic_login'])) {

                            /* set up our session values so the check passes on redirect */
                            $_SESSION['username'] = $_POST['register_username'];
                            $_SESSION['password'] = $_POST['register_password'];

                            /* redirect the user to the same script executing */
                            redirect(dirname($_SERVER['PHP_SELF']));
                        } else {

                            /* friendly information message saying we've registered */
                            $msg[] = array("text"=>sprintf("Thank you for registering, %s. Please login using the form below.", $_POST['register_username']), "type"=>"info");

                            /* flag to tell us we've went through the register process */
                            $login_success = 1;
                        }
                    }
                }
            }
        }

        if (isset($msg)) {
            $smarty->assign('msg', $msg);
        }

        $smarty->display('header.tpl');
        $smarty->display('topmenu.tpl');
        $smarty->display('leftcolumn.tpl');
        $smarty->display((!isset($login_success)) ? 'register.tpl' : 'login.tpl');
        $smarty->display('footer.tpl');

        die;
    } elseif ($ARGV[0] == "lostpassword") {

        /* user has hit retrieve button */
        if (isset($_POST['lostpassword_submit']) && (!empty($_POST['details']))) {

            $user = new User();

            /* check email is valid */
            if (validEmail($_POST['details'])) {
                if (isEmailTaken($_POST['details'])) {
                    $user->setEmail($_POST['details']);
                    $user->setUsername($user->getEmailOrUsername($_POST['details']));
                    $sent_password = 1;
                }
            }

            /* check username is valid */
            elseif (validUsername($_POST['details'])) {
                if (isUsernameTaken($_POST['details'])) {
                    $user->setUsername($_POST['details']);
                    $user->setEmail($user->getEmailOrUsername($_POST['details']));
                    $sent_password = 1;
                }
            }

            /* if passed more than one test, we have valid details */
            if (isset($sent_password)) {

                /* reset the password */
                if ($user->resetPassword()) {

                    $body = "Hello, this is just a confirmation email to say that your details have been reset to:" . "\n\n";
                    $body .= "username: " . $user->getUsername() . "\n";
                    $body .= "password: " . $user->getPassword() . "\n\n";
                    $body .= "This can be changed to something more memorable by logging in and going to your config page." ."\n\n\n";
                    $body .= "Cheers," . "\n" . "tvplan admin";

                    /* send confirmation email */
                    $user->sendMail(ADMINEMAIL, "tvplan admin", "tvplan: password retrieval", $body);

                    $msg[] = array("text"=>"An email has been dispatched to the account on record.", "type"=>"info");
                } else {
                    $msg[] = array("text"=>"An error occurred when attempting to reset your password!", "type"=>"error");
                }
            } else {
                $msg[] = array("text"=>"Error: Invalid username/email or not found in the database.", "type"=>"error");
            }
        }

        if (isset($msg)) {
            $smarty->assign('msg', $msg);
        }

        $smarty->display('header.tpl');
        $smarty->display('topmenu.tpl');
        $smarty->display('leftcolumn.tpl');
        $smarty->display((!isset($sent_password)) ? 'lostpassword.tpl' : 'login.tpl');
        $smarty->display('footer.tpl');

        die;
    }

    /* neither registering or retrieving password, so check the login */
    $logged_in = checkLogin();

    /* already logged in! assign smarty variables */
    if ($logged_in) {
        $smarty->assign('logged_in', 1);
        $smarty->assign('username', $_SESSION['username']);
    }

    /* not logged in, print the login form or check submission */
    else {

        if (isset($_POST['login_submit'])) {

            if ((empty($_POST['username'])) || (empty($_POST['password']))) {
                $msg[] = array("text"=> "Error: You did not fill in a required field.", "type"=>"error");

            } else {

                $username = stripslashes($_POST['username']);
                $password = stripslashes($_POST['password']);

                $user = new User($username, $password);

                /* after our input has been validated, check our input against the database */
                $result = $user->confirmUser();

                /* error codes from confirmUser tell us whats up with the login */
                $msg[] = (($result == 1) ? array("text"=>sprintf("Error: Could not find the username '%s' in the database.",cutText($username)), "type"=>"error") :
                (($result == 2) ? array("text"=>sprintf("Error: Invalid password was given for username '%s'.", $username), "type"=>"error") : ""));

                /* else there has been no result, so everythings going ok */
                if (empty($result)) {

                    /* read our configuration value for timezone offset, showing tips, etc. */
                    $config_file = @parse_ini_file($user->getConfigFilename(), true);
                    $config['offset'] = (isset($config_file['main']['offset'])) ? $config_file['main']['offset'] : 0;
                    $config['enabletips'] = (isset($config_file['main']['enabletips'])) ? $config_file['main']['enabletips'] : 0;

                    /* use timezone offset to correct last login time */
                    $last_login = $user->getLastLogin(((int)$config['offset']));

                    if (is_null($last_login)) {

                        /* first time ever logged on */
                        $msg[] = array("text"=> sprintf("Welcome <strong>%s</strong>. To get started, visit the 'config' page (top right bar) and try adding some shows.", $username), "type"=>"info");
                    } else {

                        /* generate tip if enabled */
                        if (!empty($config['enabletips'])) {
                            $msg[] = array("text"=> sprintf("<strong>Tip of the day:</strong> %s",getTip(TIPFILE)), "type"=>"tip");
                        }

                        /* friendly logon message */
                        $msg[] = array("text"=> sprintf("Thank you for logging in, %s. (Last login: <strong>%s</strong> days ago.)",$username, $last_login), "type"=>"info");
                    }

                    /* update our last login time */
                    $user->updateLastLogin();

                    /* set username and password session variables */
                    $_SESSION['username'] = $username;
                    $_SESSION['password'] = $password;

                    /* if the 'remember' input tag has been checked then set rememberance cookies for the user */
                    if (!empty($_POST['remember'])) {
                        @setcookie("cookname", $_SESSION['username'], time() + 60 * 60 * 24 * 100, "/");
                        @setcookie("cookpass", $_SESSION['password'], time() + 60 * 60 * 24 * 100, "/");
                    }
                }
            }

        }

        /* recheck our login, cause we might of just submitted ok details! */
        $logged_in = checkLogin();

        if (isset($msg)) {
            $smarty->assign('msg', $msg);
        }

        if ($logged_in) {
            $smarty->assign('logged_in', 1);
            $smarty->assign('username', $_SESSION['username']);
        } else {

            $smarty->display('header.tpl');
            $smarty->display('topmenu.tpl');
            $smarty->display('leftcolumn.tpl');
            $smarty->display('login.tpl');
            $smarty->display('footer.tpl');

            die;
        }
    }

    /* create the user object */ 
    if (isset($_SESSION['username'])) {
        $user = new User($_SESSION['username']);
        if ($user->confirmAdmin()) {
            $smarty->assign('admin', 1);
            $admin = 1;
        }
    }

    /* destroy our sitestats since we're not interested when we login */
    $smarty->clear_assign('sitestats');

    /* sanity check for main config file */
    if (($_SESSION['username'] == "admin")) {
        if (!$user->write_default_config()) {
            logOut();
            die(sprintf("Error: Cannot write to '%s', please make sure the directory is writable.",
            !$user->getConfigFilename()));
        }
    } else {
        if (!is_file($user->getConfigFilename())) {
            logOut();
            die(sprintf("Error: Cannot read settings from '%s', please make sure it exists.",
            $user->getConfigFilename()));
        }
    }

    /* read our configuration values */
    $config_file = parse_ini_file($user->getConfigFilename(), true);

    /* main section of config */
    $config['checknews'] = (isset($config_file['main']['checknews'])) ? $config_file['main']['checknews'] : 0;
    $config['checkrss'] = (isset($config_file['main']['checkrss'])) ? $config_file['main']['checkrss'] : 0;
    $config['showsearchbox'] = (isset($config_file['main']['showsearchbox'])) ? $config_file['main']['showsearchbox'] : 0;
    $config['newzbinlinks'] = (isset($config_file['main']['newzbinlinks'])) ? $config_file['main']['newzbinlinks'] : 0;
    $config['enabletips'] = (isset($config_file['main']['enabletips'])) ? $config_file['main']['enabletips'] : 0;
    $config['calendarstyle'] = (isset($config_file['main']['calendarstyle'])) ? $config_file['main']['calendarstyle'] : "balloon";
    $config['offset'] = (isset($config_file['main']['offset'])) ? $config_file['main']['offset'] : 0;

    /* torrent section */
    $config['showtorrents'] = (isset($config_file['torrent']['showtorrents'])) ? $config_file['torrent']['showtorrents'] : 0;
    $config['torrentfile'] = (isset($config_file['torrent']['torrentfile'])) ? $config_file['torrent']['torrentfile'] : 0;
    $config['showseeding'] = (isset($config_file['torrent']['showseeding'])) ? $config_file['torrent']['showseeding'] : 0;

    /* rss section */
    $config['rssserver'] = (isset($config_file['rss']['server'])) ? $config_file['rss']['server'] : 0;
    $config['rssusername'] = (isset($config_file['rss']['username'])) ? $config_file['rss']['username'] : "null";
    $config['rsspassword'] = (isset($config_file['rss']['password'])) ? $config_file['rss']['password'] : "null";

    if ($ARGV[0] == "config") {

        /* boolean to load ac stuff (only needed for the config page) */
        $smarty->assign('loadac', 1);

        /* set up our initial checkbox states */
        $smarty->assign('showtorrents_state', $config['showtorrents']);
        $smarty->assign('enablerss_state', $config['checkrss']);
    }

    elseif ($ARGV[0] == "logout") {

        if ($logged_in) {

            /* logout and redirect back to main page */
            logOut();
            redirect(dirname($_SERVER['PHP_SELF']));
        }

    }

    /* do rss stuff if we're interested */
    if (!empty($config['checkrss'])) {

        /* check we have a server */
        if ((!empty($config['rssserver']))) {

            $corrected_feed = "http://" . $config['rssserver'];

            /* quick check if server is up */
            if (ping($corrected_feed)) {

                /* lastRSS.php script init */
                $rss = new lastRSS;
                $rss->cache_dir = './cache';
                $rss->cache_time = 3600; // one hour

                if ($rs = $rss->get('http://' . $config['rssusername'] . ':' . $config['rsspassword'] . '@' . $config['rssserver'])) {
                    foreach ($rs['items'] as $item) {

                        /* clean up our long feed names */
                        $rss_feed[] = array("link"=>$item['link'], "title"=>cutText($item['title'], 38));
                    }
                }
            } else {
                $msg[] = array("text"=>sprintf("Error: The specified RSS feed, '%s' is not available.", $corrected_feed), "type"=>"error");
            }
        }
    }

    /* do torrent stuff if we're interested */
    if (!empty($config['showtorrents'])) {

        /* sanity check for valid torrent file */
        if (!validUrl($config['torrentfile']) && (!file_exists($config['torrentfile']))) {
            $msg[] = array("text"=>sprintf("Error: The specified XML file, '%s' does not exist.", $config['torrentfile']), "type"=>"error");
        } else {

            $xml = simplexml_load_file($config['torrentfile']);
            $torrents = "";

            $global_download = $xml->GLOBAL->DOWNLOAD_SPEED->TEXT;
            $global_upload = $xml->GLOBAL->UPLOAD_SPEED->TEXT;

            foreach ($xml->DOWNLOADS->DOWNLOAD as $download) {

                /* trim torrent name */
                $name = cutText((string)$download->TORRENT->NAME,38);
                $size = (string)$download->TORRENT->SIZE->RAW;

                $status = (string)$download->DOWNLOAD_STATUS;
                $eta = (string)$download->ETA;
                $target = (string)$download->TARGET_FILE;

                /* make sure we've got a link be it a file or a dir */
                (empty($target)) ? ($target = (string)$download->TARGET_DIR) : $target;

                $downloaded = (string)$download->DOWNLOADED->RAW;
                $downloaded_text = (string)$download->DOWNLOADED->TEXT;
                $uploaded = (string)$download->UPLOADED->RAW;
                $uploaded_text = (string)$download->UPLOADED->TEXT;

                switch ($status) {

                    case 'Seeding':

                        if (!empty($config['showseeding'])) {

                            /* set ratio if we're interested */
                            $ratio = round($uploaded/$downloaded,2);

                            /* set percent if we're interested */
                            $percent = 100;
                        }

                        break;

                    case 'Downloading':

                        if (!empty($downloaded)) {
                            $ratio = round($uploaded/$downloaded,2);
                            $percent = round((($downloaded/$size) * 100),2);
                        } else {
                            $percent = 0;
                        }

                        break;

                    case 'Waiting':
                        break;

                    case 'Queued':
                        break;

                    case 'Stopped':
                        break;

                }

                /* if percent is set, it means we're interested in this */
                /* if not, repeat! */
                if ((isset($name)) && (isset($percent))) {
                    $overlib = "href=\"file://$target\" onmouseover=\"return overlib('d:[$downloaded_text]/u:[$uploaded_text] ($ratio) $eta',BUBBLE,BUBBLETYPE,'quotation',ADJBUBBLE)\" onmouseout=\"return nd();\"";
                    $torrents[] = array("percent"=>$percent, "filename"=>$name, "overlib"=>$overlib);
                }

                /* clear up used variables */
                unset($percent);
                unset($name);
                unset($overlib);
            }

        }
    }

    if ((isset($torrents)) && (is_array($torrents))) {

        /* take slice of the torrents needed, and sort them */
        $torrents = array_slice($torrents,0,MAXTORRENTS);
        sort($torrents);

        $smarty->assign('torrents', $torrents);
        $smarty->assign('global_download', $global_download);
        $smarty->assign('global_upload', $global_upload);
    }
    if ((!empty($rss_feed)) && (is_array($rss_feed))) {
        $rss_feed = array_slice($rss_feed,0,MAXFEEDS);
        $smarty->assign('rss_feed', $rss_feed);
    }
    if (!empty($config['showsearchbox'])) {
        $smarty->assign('showsearchbox', $config['showsearchbox']);
    }

    /* draw site header */
    $smarty->display('header.tpl');

    /* draw site menu */
    $smarty->display('topmenu.tpl');

    /* helps us grab any updated months before the leftcolumn is drawn */
    switch($ARGV[0]) {

        case 'date':
            if ((isset($ARGV[1])) && (isset($ARGV[2]))) {

                $month = $ARGV[1];
                $year = $ARGV[2];

            }
            break;

    }

    /* if no dates are set just set them to this month/year (including offset) */
    if ((!isset($month)) || (!isset($year))) {
        $month = date('n', timeOffset((int)$config['offset']));
        $year = date('Y', timeOffset((int)$config['offset']));
    }

    $closest_date = $user->getCalendarDates($month, $year);
    $show_array = $user->getShowNames();
    
    $smarty->assign('calendar', calendar(
        $closest_date,
        $month,
        $year,
        $config['calendarstyle'],
        $config['newzbinlinks'],
        timeOffset((int)$config['offset']
    )));

    /* draw site left column */
    $smarty->display('leftcolumn.tpl');

    /* switch to whatever content section we need drawn */
    switch($ARGV[0]) {

        /* news section */
    case 'news':

        if ((isset($ARGV[1])) && ($ARGV[1] == "remove") && (isset($ARGV[2]))) {

            /* create dummy news object */
            $news = new News($user);

            if ($news->deleteShowFromNews($ARGV[2])) {
                $msg[] = array(
                    "text"=>sprintf("Successfully deleted the synopsis '%s' from your news database.", $ARGV[2]), 
                    "type"=>"info");

                    /* variable to make sure we don't just update it again in a second */
                    $show_just_deleted = $ARGV[2];
            } else {
                $msg[] = array("text"=>sprintf("Error: Could not delete the synopsis for '%s'!",$ARGV[2]), "type"=>"error");
            }
        }

        /* default case (main page) or date */
    case '':
    case 'date':

        if (!isset($show_just_deleted)) {
            $show_just_deleted = "silly_stub_filler";
        }

        /*
        * (make sure checking for news is turned on!), we have dates to check and
        * then search for a date that is today before downloading news
        */
        if ((!empty($config['checknews'])) &&
        (is_array($closest_date)) &&
        (array_search_r(date('Y-m-d', timeOffset((int)$config['offset'])),$closest_date))) {

            foreach($closest_date as $main) {
                if (is_array($main)) {
                    foreach($main as $date_list) {
                        if (is_array($date_list)) {
                            foreach($date_list as $date) {

                                /* check the date is today */
                                if (date('Y-m-d', timeOffset((int)$config['offset'])) == $date['date']) {

                                    $news = new News($user, $date['url'], $main['show'] , $date['name']);

                                    /* search for the shows news in the database making sure we've not just deleted it! */
                                    if ((!$news->hasNews()) && ($news->getTitle() != $show_just_deleted)) {

                                        /* if none, look for some and add it using tv.com_lib.php's addNews! */
                                        if (!$news->add()) {

                                            /* failure occurred, suitable error message */
                                            $msg[] = array("text"=>sprintf("Error: Could not retrieve the news for '%s' - '%s' for some weird reason :(",$main['show'], $date['name']), "type"=>"error");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        /* fill our body with the users episode synopsis */
        $posts = $user->getNews();

        /* check for no news and display accordingly */
        if (!is_array($posts)) {
            $msg[] = array("text"=>"(There are no recent show synopses to display!)", "type"=>"info");
        }

        /* add any msg output to the page */
        if (isset($msg)) {
            $smarty->assign('msg', $msg);
        }

        /* finally assign and output site body (content) */
        $smarty->assign('posts', $posts);
        $smarty->display('body.tpl');

        break;

        /* admin section */
    case 'admin':

        /* if the admin flag is set then we are considered an admin */
        if (!empty($admin)) {

            /* delete user button has been pressed */
            if (isset($_POST['deleteuser_submit'])  && (isset($_POST['username']))) {

                /* make sure we can't delete ourself (!) */
                if ($_POST['username'] != $_SESSION['username']) {

                    $user = new User($_POST['username']);

                    $msg[] = $user->delete() ? array("text"=>sprintf("Successfully deleted the username '%s', please refresh the page to see the desired changes.",$_POST['username']), "type"=>"info") : 
                    array("text"=>sprintf("Error: Could not delete the user '%s' for some weird reason (!)",$_POST['username']), "type"=>"error");
                } else {
                    $msg[] = array("text"=>sprintf("Error: Cannot delete yourself. (%s)",$_SESSION['username']), "type"=>"error");
                }

            }

            /* add user button has been pressed */
            if (isset($_POST['adduser_submit'])) {

                if (checkFormInput($_POST['adduser_username'], 
                $_POST['adduser_password'], $_POST['adduser_email'], $msg)) {

                    /* determine whether or not we are adding an admin */
                    $newadmin = (isset($_POST['adduser_admin'])) ? 1 : 0;

                    $user = new User($_POST['adduser_username'], 
                    $_POST['adduser_password'], $_POST['adduser_email'], $newadmin);

                    /* add the new account to the database */
                    if ($user->add()) {

                        /* write out the a default config and create the user index tables */
                        if (($user->write_default_config()) && 
                        ($user->createUserTables())) {
                            $msg[] = array("text"=>sprintf("The user '%s' with the email '%s' has been added" . ((!empty($newadmin)) ? " with admin status." : "."), $_POST['adduser_username'], $_POST['adduser_email']), "type"=>"info");
                        } else {
                            $msg[] = array("text"=>sprintf("Error: The user has been added to the database, but could not create a default config!",$_POST['adduser_username']), "type"=>"error");
                        }
                    } else {
                        $msg[] = array("text"=>sprintf("Error: Could not add the user '%s' for some weird reason (!)",$_POST['adduser_username']), "type"=>"error");
                    }

                }
            }


            /* create list of all users registered */
            $user_list = listUsers();

            /* ensure that we cant keep deleting nothing */
            if ((isset($user_list)) && (!empty($user_list))) {

                /* order it to make alphabetical */
                sort($user_list);
                $smarty->assign('user_list', $user_list);
            }

            if (isset($msg)) {
                $smarty->assign('msg', $msg);
            }

            $smarty->display('admin.tpl');
        }

        /* denied page for non-admins */
        else {
            $smarty->display('denied.tpl');
        }
        break;

        /* user configuration page */
    case 'config':

        /* save changes button been pressed */
        if (isset($_POST['submit'])) {

            $user = new User($_SESSION['username']);

            $config['checknews'] = (isset($_POST['checknews'])) ? 1 : 0;
            $config['checkrss'] = (isset($_POST['checkrss'])) ? 1 : 0;
            $config['showsearchbox'] = (isset($_POST['showsearchbox'])) ? 1 : 0;
            $config['calendarstyle'] = $_POST['calendarstyle'];
            $config['offset'] = (!empty($_POST['offset'])) ? $_POST['offset'] : ((!empty($_POST['offset_hidden'])) ? $_POST['offset_hidden'] : 0);
            $config['newzbinlinks'] = (isset($_POST['newzbinlinks'])) ? 1 : 0;
            $config['enabletips'] = (isset($_POST['enabletips'])) ? 1 : 0;

            $config['torrentfile'] = (!empty($_POST['torrentfile'])) ? $_POST['torrentfile'] : ((!empty($_POST['torrentfile_hidden'])) ? $_POST['torrentfile_hidden'] : 0);
            $config['showtorrents'] = (isset($_POST['showtorrents'])) ? 1 : 0;
            $config['showseeding'] = (isset($_POST['showseeding'])) ? 1 : 0;

            $config['rssserver'] = (!empty($_POST['rssserver'])) ? $_POST['rssserver'] : ((!empty($_POST['rssserver_hidden'])) ? $_POST['rssserver_hidden'] : 0);
            $config['rssusername'] = (!empty($_POST['rssusername'])) ? $_POST['rssusername'] : ((!empty($_POST['rssusername_hidden'])) ? $_POST['rssusername_hidden'] : 0);
            $config['rsspassword'] = (!empty($_POST['rsspassword'])) ? $_POST['rsspassword'] : ((!empty($_POST['rsspassword_hidden'])) ? $_POST['rsspassword_hidden'] : 0);

            /* merge new values with the existing config giving a updated config */
            $updated_config_file = multimerge($config_file,
            array(
                "main"=>
                array(
                    "checknews"=>$config['checknews'],
                    "checkrss"=>$config['checkrss'],
                    "showsearchbox"=>$config['showsearchbox'],
                    "calendarstyle"=>$config['calendarstyle'],
                    "offset"=>$config['offset'],
                    "newzbinlinks"=>$config['newzbinlinks'],
                    "enabletips"=>$config['enabletips'],
                ),
                "torrent"=>
                array(
                    "showtorrents"=>$config['showtorrents'],
                    "torrentfile"=>$config['torrentfile'],
                    "showseeding"=>$config['showseeding'],
                ),
                "rss"=>
                array(
                    "server"=>$config['rssserver'],
                    "username"=>$config['rssusername'],
                    "password"=>$config['rsspassword'],
                )
            ));

            /* check if we're updating passwords and whether or not they are valid/match */
            if ((!empty($_POST['new_password'])) && (!empty($_POST['confirm_password']))) {
                if (($_POST['new_password']) == ($_POST['confirm_password'])) {
                    if (validPassword($_POST['new_password'])) {

                        /* update the password for the user and reset the session variable */
                        $user->updatePassword($_POST['new_password']);
                        $_SESSION['password'] = $_POST['new_password'];
                    } else {
                        $msg[] = array("text"=>"Error: password contains invalid characters.", "type"=>"error");
                        $form_error = 1;
                    }
                } else {
                    $msg[] = array("text"=>"Error: passwords did not match!", "type"=>"error");
                    $form_error = 1;
                }
            }

            /* check for flag indicating form failure */
            if (!isset($form_error)) {

                /* write our final settings out to file */
                if (!$user->write_updated_config($updated_config_file)) {

                    /* an error occurred writing to the file */
                    $msg[] = array("text"=>sprintf("Error: Cannot write to '%s', please make sure the directory is writable.",
                    $user->getConfigFilename()), "type"=>"error");
                } else {

                    /* update the array since things have been changed! */
                    $config_file = parse_ini_file($user->getConfigFilename(), true);

                    /* friendly confirmation message */
                    $msg[] = array("text"=>"The changes have been applied!", "type"=>"info");
                }
            }
        }

        /* add show button has been pressed */
        if (!empty($_POST['show'])) {

            $show = new Show(True, $user, $_POST['show']);

            if ($show->add()) {

                /* get more readable name */
                $real_name = $show->getRealName();

                $msg[] = array("text"=>sprintf("Successfully added the show '%s', please refresh the page to see the desired changes.", $real_name), "type"=>"info");

                /* we've added a series so we might as well repopulate the combobox */
                $show_array = $user->getShowNames();

            } else {
                $msg[] = array("text"=>sprintf("Error: Could not add the show '%s' for some weird reason :(",$_POST['show']), "type"=>"error");
            }

        }

        /* delete button has been pressed */
        if ((isset($_POST['delete_submit'])) && (isset($_POST['show_name']))) {

            $show = new Show(False, $user, $_POST['show_name']);

            /* if we successfully removed the show */
            if ($show->delete()) {

                $msg[] = array("text"=>sprintf("Successfully deleted the show '%s', please refresh the page to see the desired changes.",$_POST['show_name']), "type"=>"info");

                /* delete from the show array */
                unset_by_val($_POST['show_name'],$show_array);

                /* reindex our array up */
                array_unshift($show_array, array_shift($show_array));

            } else {
                $msg[] = array("text"=>sprintf("Error: Could not delete the show '%s' for some weird reason :(",$_POST['show_name']), "type"=>"error");
            }

        }

        /* update button has been pressed */
        if ((isset($_POST['update_submit'])) && (isset($_POST['show_name']))) {

            $show = new Show(False, $user, $_POST['show_name']);

            /* if we successfully updated the show */
            $msg[] = ($show->add()) ? array("text"=>sprintf("The show '%s' was successfully updated, please refresh the page to see the desired changes.", $_POST['show_name']), "type"=>"info")
            : $msg[] = array("text"=>sprintf("Error: Could not update the show '%s' for some weird reason :(",$_POST['show_name']), "type"=>"error");

        }

        /* update ALL button has been pressed */
        if (isset($_POST['updateall_submit'])) {

            /* get the shows the user watches */
            $allshows = $user->getShowNames();

            /* loop through users shows */
            foreach($allshows as $show_name) {

                $show = new Show(False, $user, $show_name);

                /* nasty hack since we can't use $msg, but we can't wait and this has to be done now! */
                print ($show->update()) ? "<div class=\"box infoBox\">$show_name was successfully updated.</div>" :
                "<div class=\"box errorBox\">Could not update $show_name for some weird reason :(</div>";

                /* sleep for 4 seconds and flush the page */
                @ob_flush();
                flush();
                sleep(4);
            }
        }

        /* for showing main section */
        $config['checkrss'] = (isset($config_file['main']['checkrss'])) ? $config_file['main']['checkrss'] : 0;
        if (!empty($config['checkrss'])) $smarty->assign('checkrss', $config['checkrss']);

        $config['checknews'] = (isset($config_file['main']['checknews'])) ? $config_file['main']['checknews'] : 0;
        if (!empty($config['checknews'])) $smarty->assign('checknews', $config['checknews']);

        $config['showsearchbox'] = (isset($config_file['main']['showsearchbox'])) ? $config_file['main']['showsearchbox'] : 0;
        if (!empty($config['showsearchbox'])) $smarty->assign('showsearchbox', $config['showsearchbox']);

        $config['newzbinlinks'] = (isset($config_file['main']['newzbinlinks'])) ? $config_file['main']['newzbinlinks'] : 0;
        if (!empty($config['newzbinlinks'])) $smarty->assign('newzbinlinks', $config['newzbinlinks']);

        $config['enabletips'] = (isset($config_file['main']['enabletips'])) ? $config_file['main']['enabletips'] : 0;
        if (!empty($config['enabletips'])) $smarty->assign('enabletips', $config['enabletips']);

        $config['calendarstyle'] = (isset($config_file['main']['calendarstyle'])) ? $config_file['main']['calendarstyle'] : 0;
        if (!empty($config['calendarstyle'])) $smarty->assign('calendarstyle', $config['calendarstyle']);

        $config['offset'] = (!empty($config_file['main']['offset'])) ? $config_file['main']['offset'] : 0;
        if (!empty($config['offset'])) $smarty->assign('offset', $config['offset']);

        /* for showing torrent section */
        $config['showtorrents'] = (isset($config_file['torrent']['showtorrents'])) ? $config_file['torrent']['showtorrents'] : 0;
        if (!empty($config['showtorrents'])) $smarty->assign('showtorrents', $config['showtorrents']);

        $config['torrentfile'] = (!empty($config_file['torrent']['torrentfile'])) ? $config_file['torrent']['torrentfile'] : 0;
        if (!empty($config['torrentfile'])) $smarty->assign('torrentfile', $config['torrentfile']);

        $config['showseeding'] = (isset($config_file['torrent']['showseeding'])) ? $config_file['torrent']['showseeding'] : 0;
        if (!empty($config['showseeding'])) $smarty->assign('showseeding', $config['showseeding']);

        /* for showing rss section */
        $config['rssserver'] = (!empty($config_file['rss']['server'])) ? $config_file['rss']['server'] : 0;
        if (!empty($config['rssserver'])) $smarty->assign('rssserver', $config['rssserver']);

        $config['rssusername'] = (!empty($config_file['rss']['username'])) ? $config_file['rss']['username'] : 0;
        if (!empty($config['rssusername']))    $smarty->assign('rssusername', $config['rssusername']);

        $config['rsspassword'] = (!empty($config_file['rss']['password'])) ? $config_file['rss']['password'] : 0;
        if (!empty($config['rsspassword'])) $smarty->assign('rsspassword', $config['rsspassword']);

        /* ensure that we cant keep deleting nothing */
        if (isset($show_array) && is_array($show_array)) {

            /* order the shows */
            sort($show_array);
            $smarty->assign('shows', $show_array);
        }

        /* server date/time */
        $smarty->assign('server_date', date('h:i:s a', timeOffset((int)$config_file['main']['offset'])));

        /* nuke button has been pressed! */
        if (!empty($_POST['nuke_submit'])) {
            $news = new News($user);
            if ($news->nuke()) {
                $msg[] = array("text"=>"The news database has been nuked!", "type"=>"nuke");
            }
        }

        /* add any output to the page */
        if (isset($msg)) {
            $smarty->assign('msg', $msg);
        }

        /* draw site config page */
        $smarty->display('config.tpl');

        break;


        /* browse section */
    case 'browse':

        /* we have something to display */
        if (!empty($ARGV[1])) {

            $show = new Show(True, $user, $ARGV[1]);

            /* make sure show is a valid show in the database */
            if ($show->isShow()) {

                /* retrieve data for the show */
                $smarty->assign('specific_show',$ARGV[1]);
                $smarty->assign('specific_show_data', $show->getShowData());
                $smarty->assign('specific_show_closest_date', $show->getClosestDate());
            } else {
                $msg[] = array("text"=>sprintf("Error: Invalid show: '%s'", $ARGV[1]), "type"=>"error");
            }

            /* add any output to the page */
            if (isset($msg)) {
                $smarty->assign('msg', $msg);
            }

            /* draw the specific show details */
            $smarty->display('browse_specific_show.tpl');
        }

        /* nothing was given so just give an index of tracked shows */
        else {

            /* get active names */
            $show_index = $user->getActiveShowNames();

            if (!is_array($show_index)) {
                $msg[] = array("text"=>"(No shows found in the database!)", "type"=>"info");
            } else {
                $smarty->assign('show_index', $show_index);
            }

            /* add any output to the page */
            if (isset($msg)) {
                $smarty->assign('msg', $msg);
            }

            /* draw show index page */
            $smarty->display('browse.tpl');
        }


        break;

        /* any other unhandled cases just display the default body */
    default:

        $posts = $user->getNews();

        /* add any output to the page */
        if (isset($msg)) {
            $smarty->assign('msg', $msg);
        }

        /* draw site body */
        $smarty->assign('posts', $posts);
        $smarty->display('body.tpl');
    }

    $smarty->display('footer.tpl');

    /* close connection */
    $mysqli->close();
?>
