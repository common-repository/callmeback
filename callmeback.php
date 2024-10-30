<?php
/**
 * Plugin Name: CallMeBack
 * Description: A Client Callback Plugin,  connected to your Google Calendar
 * Version: 0.3
 * Author: Adrian Gier
 * License: GPL2
 * Author URI: http://click3.de
 */

set_include_path(dirname(__FILE__));
require_once 'Google/Client.php';
require_once 'Google/Service/Calendar.php';


$client = new Google_Client();
$client->addScope('https://www.googleapis.com/auth/calendar');

add_action('admin_menu', 'show_options_page');
add_shortcode('cmb', 'show_shortcode');
add_action('init', 'init_handler');
add_action('admin_head', 'admin_register_head');

function admin_register_head()
{
    echo "<link rel='stylesheet' type='text/css' href='".plugins_url( 'css/backendstyle.css' , __FILE__ )."' />\n";
}

add_action('admin_head', 'admin_register_head');

//updating backend settings
if ($_POST) {
    if ($_POST['secret'] == 'Y') {
        update_option("cmb_clientid", $_POST['clientid']);
        update_option("cmb_clientsecret", $_POST['clientsecret']);
        update_option("cmb_calendarid", $_POST['calendarid']);
        update_option("cmb_tytext", $_POST['tytext']);
    }
}


function show_options_page()
{

    add_menu_page("CMB Settings", "CMB Settings", 'manage_options', 'callbacksettings', 'show_options', plugins_url('img/phone-icon.png',__FILE__));
}


function show_options()
{
    global $client;
    $clientid = get_option('cmb_clientid');
    $clientsecret = get_option('cmb_clientsecret');
    $calendarid = get_option('cmb_calendarid');
    $tytext = get_option('cmb_tytext');
    ?>
    <h1>CallMeBack Settings</h1>
    <form id="cmb_backend" method="post" action="">
        <fieldset>
            <legend>Google Developer Console <a target="_blank" href="http://console.developers.google.com">Link</a>
            </legend>
            <label for="clientid">ClientID</label><input type="text" name="clientid" placeholder="ClientID"
                                                         value="<?= $clientid ?>"
                                                         required/>
            <br/>
            <label for="clientsecret">ClientSecret</label><input type="text" name="clientsecret"
                                                                 placeholder="ClientSecret"
                                                                 value="<?= $clientsecret ?>" required/>
            <br/>
        </fieldset>
        <fieldset>
            <legend>Google Calendar <a target="_blank" href="https://www.google.com/calendar/render?hl=de">Link</a>
            </legend>
            <label for="calendarid">CalendarID</label><input type="text" name="calendarid" placeholder="CalendarID"
                                                             value="<?= $calendarid ?>"
                                                             required/>
        </fieldset>
        <label for="tytext">Thank You Popup-Message</label><textarea type="text" name="tytext"
                                                                     required><?= $tytext ? $tytext : "Thanks for your Request. We will call you back as requested" ?></textarea>

        <br/>

        <input type="hidden" name="secret" value="Y"/>
        <input type="submit" value="Speichern"/>
    </form>
    <?php
    //Show Authentication Link ?
    if ($clientid && $clientsecret && $calendarid) {
        if (get_option('access_token')) {
            $client->setAccessToken(get_option('access_token'));
        } else {
            //authenticate user
            $client->setClientId($clientid);
            $client->setClientSecret($clientsecret);
            $client->setRedirectUri(home_url());
            $client->setAccessType('offline');
            $auth_url = $client->createAuthUrl();
            echo "<a href='$auth_url' >Authenticate</a > ";
        }

    }
}

function show_shortcode()
{
    $tytext = get_option('cmb_tytext');
    ?>
    <script type="text/javascript">
        jQuery(function () {
            //jQuery('#cmb_date').datetimepicker({format: 'd.m.Y H:i'});
            jQuery('#cmb_date').datetimepicker();
            jQuery('#cmb_form').submit(function () {
                alert('<?= $tytext ?>');
            })
        })
    </script>
    <div id="cmb_wrapper">
        <div id="cmb_header">Callback Request</div>
        <div id="cmb_form">
            <form method="post" action=""/>
            <div class='fieldblock'>
                <label>Name / Company</label>
                <input type="text" name="name" placeholder="" required="">
            </div>
            <div class='fieldblock'>
                <label>Phone No</label>
                <input type="text" name="phone" placeholder='' required="">
            </div>
            <div class='fieldblock'>
                <label>Call Date/Time</label>
                <input type="text" id="cmb_date" name="time" placeholder="" required="">
            </div>
            <div class='fieldblock'>
                <input type="hidden" name="secret" value="form">
                <label>&nbsp;</label>
                <input type="submit" value="Request Callback">
            </div>
            </form>
        </div>
    </div>
<?php
}

//Handle Form Data
function init_handler()
{
    global $client;
    global $_POST;

    //Styles and Scripts
    wp_register_style('CMBStylesheet', plugins_url('css/style.css', __FILE__));
    wp_register_style('PickerStylesheet', plugins_url('css/jquery.datetimepicker.css', __FILE__));
    wp_register_script('PickerScript', plugins_url('js/jquery.datetimepicker.js', __FILE__), array('jquery'));
    wp_enqueue_style('PickerStylesheet');
    wp_enqueue_style('CMBStylesheet');
    wp_enqueue_script('PickerScript');

    if ($token = get_option('access_token')) {
        $client->setAccessToken($token);
    }

    $service = new Google_Service_Calendar($client);

    $client->setClientId(get_option('cmb_clientid'));
    $client->setClientSecret(get_option('cmb_clientsecret'));
    $client->setRedirectUri(home_url());
    $client->addScope('https://www.googleapis.com/auth/calendar');
    $client->setAccessType('offline');
    //$client->setApprovalPrompt('force');

    //got authentication code, log me in
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        try {
            $client->authenticate($code);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        update_option('access_token', $client->getAccessToken());
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }

    //received post message from form => insert event
    if (!empty($_POST) && $_POST['secret'] == 'form') {
        //is the user authenticated ?
        if (get_option('access_token')) {
            $string = "Callback Request, Page:" . $_SERVER['SERVER_NAME'] . ", from " . $_POST['name'] . ", PhoneNr: " . $_POST['phone'] . ' am ' . $_POST['time'];
            $service->events->quickadd(get_option('cmb_calendarid'), $string);
            wp_redirect(home_url());
            exit;
        } else {
            die('User is not authenticated');
        }
    }
}