<?php
/*
Plugin Name: Job Listing
Plugin URI: http://www.theidealcandidate.com
Description: Widget display plugin for jobs on The Ideal Candidate
Author: The Ideal Candidate
Version: 1.0
Author URI: http://www.theidealcandidate.com
*/

/**
 * The Ideal Candidate Widget Class
 *
 * @copyright  2009 The Ideal Candidate
 * @license    GPL v2.0
 * @version    1.0
 * @link       http://www.theidealcandidate.com/
 * @since      File available since Release 1.0
 */

/**
 * Base The Ideal Candidate Widget Class 
 *
 * @copyright  2009 The Ideal Candidate
 * @license    GPL v2.0
 * @version    1.0
 * @link       http://www.theidealcandidate.com/
 * @since      File available since Release 1.0
 */
class TheIdealCandidate
{
    /**
     * Construct the widget
     *
     * @param void
     * @return null
     */
    public function __construct()
    {
        add_action('plugins_loaded',array(&$this,'init'));
    }

    /**
     * Initiate the widget
     *
     * @param void
     * @return null
     */
    public function init()
    {
        register_sidebar_widget('The Ideal Candidate', array(&$this,'sidebar'));
        register_widget_control('The Ideal Candidate', array(&$this,'control'));
    }

    /**
     * Output the widget to the sidebar
     *
     * @param void
     * @return stdout
     */
    public function sidebar()
    {
        $options = get_option("tic_widget");
        $output = '<script type="text/javascript" src="http://www.theidealcandidate.com/widget/display-'.$options['widget'].'.js"></script>'."\r\n";
        echo $output;
    }

    /**
     * Create the widget control in the admin
     *
     * @param void
     * @return stdout
     */
    public function control()
    {
        $options = get_option('tic_widget');
        if ($_POST['tic_widget_submit']) {
            $options['paypal'] = $_POST['tic_paypal'];
            $options['widget'] = $_POST['tic_widget'];
            if (get_option('tic_widget')!==false) {
                update_option('tic_widget', $options);
            } else {
                add_option('tic_widget', $options);
            }
        }

        echo '<p>'."\r\n";
        echo '<label for="tic_paypal">PayPal Address (your admin email):</label>'."\r\n";
        echo '<input type="text" name="tic_paypal" id="tic_paypal" value="' . $options['paypal'] . '"/>'."\r\n";
        echo '<input type="hidden" id="tic_widget_submit" name="tic_widget_submit" value="1"/>'."\r\n";
        echo '</p>'."\r\n";

        if ($widgets = $this->getAffiliateWidgets($options['paypal'])) {
            echo '<p>'."\r\n";
            echo '<label for="tic_widget">Widget to display:</label><br/>'."\r\n";
            echo '<select name="tic_widget">'."\r\n";
            foreach ($widgets as $widgetId => $widgetName) {
                echo '<option value="' . $widgetId . '"'. (($widgetId==$options['widget'])?' selected="selected"':'') .'>' . $widgetName . '</option>'."\r\n";
            }
            echo '</select>'."\r\n";
            echo '</p>'."\r\n";
        } else {
            echo '<p>You will need to have an affiliate account with <a href="http://www.theidealcandidate.com/" title="The Ideal Candidate" target="_blank">The Ideal Candidate</a>, if you don\'t currently have an <a href="http://www.theidealcandidate.com/jobs-widget/" title="The Ideal Candidate" target="_blank">affiliate account then why not sign up</a>, it\'s free and you\'ll get commission on every job posted though your affiliate links.</p>';
            echo '<p>Once you have entered your PayPal address (your login email for The Ideal Candidate), please click on the save changes button to see a list of your available widgets.</p>';
        }
    }

    /**
     * Get the affiliate's widgets
     *
     * @param string $email
     * @return mixed
     */
    public function getAffiliateWidgets($email)
    {
        $xml = file_get_contents('http://www.theidealcandidate.com/affxml.php?waemail=' . $email);
        $xmlElements = simplexml_load_string($xml);
        if (count($xmlElements->widget)>0) {
            foreach ($xmlElements->widget as $widget) {
                $return[(int)$widget->id] = (string)$widget->name;
            }
        } else {
            $return = false;
        }
        return $return;
    }
}
$theIdealCandidate = new TheIdealCandidate;