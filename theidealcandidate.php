<?php
/*
Plugin Name: Job Listing
Plugin URI: http://www.theidealcandidate.com
Description: Shows jobs in you content and your widgets from The Ideal Candidate
Author: The Ideal Candidate
Version: 2.1
Author URI: http://www.theidealcandidate.com
*/

/**
 * The Ideal Candidate Widget Class
 *
 * @copyright 2009 The Ideal Candidate
 * @license GPL v2.0
 * @author Steven Raynham
 * @version 2.1
 * @link http://www.theidealcandidate.com/
 * @since File available since Release 1.0
 */

/**
 * Base The Ideal Candidate Widget Class 
 *
 * @copyright 2009 The Ideal Candidate
 * @license GPL v2.0
 * @author Steven Raynham
 * @version 2.1
 * @link http://www.theidealcandidate.com/
 * @since File available since Release 1.0
 */
class TheIdealCandidate
{
    /**
     * Construct the plugin/widget
     *
     * @author Steven Raynham
     * @since 2.1
     *
     * @param void
     * @return null
     */
    function TheIdealCandidate()
    {
        if (is_admin()) {
            add_action('init',array(&$this,'adminInit'));
            add_action('admin_menu',array(&$this,'adminMenu'));
            add_action('admin_head',array(&$this,'adminHead'));
        }
        add_filter('the_content', array(&$this, 'content'));
        add_action('plugins_loaded',array(&$this,'widgetInit'));
        add_action('wp_head',array(&$this,'head'));
    }

    /**
     * Initiate the plugin
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return null
     */
    function adminInit()
    {
    }

    /**
     * Initiate admin menu
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return null
     */
    function adminMenu()
    {
        add_menu_page("Ideal Candidate", "Ideal Candidate", "level_7", __FILE__, array(&$this,'pluginAdmin'));
    }

    /**
     * Admin header
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return null
     */
    function adminHead()
    {
        echo '<link rel="stylesheet" href="' . get_option('siteurl') . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/style.css" type="text/css" />'."\r\n";
    }

    /**
     * Frontend header
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return null
     */
    function head()
    {
        echo '<link rel="stylesheet" href="' . get_option('siteurl') . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/tic-style.css" type="text/css" />'."\r\n";
    }

    /**
     * Admin menu
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return null
     */
    function pluginAdmin()
    {
        $options = get_option('tic_widget');
        $pluginBase = wp_create_nonce(plugin_basename(__FILE__));
        if ((isset($_POST['nonce'])) && ($_POST['nonce'] == $pluginBase)) {
            if (isset($_POST['action'])) {
                 switch ($_POST['action']) {
                    case 'update':
                        foreach ($_POST as $key => $post) {
                            if (substr($key,0,4)=='tic_') $options[substr($key,4)] = trim($post);
                        }
                        if (!is_numeric($options['numjobs'])) $options['numjobs'] = 25;
                        if (!is_numeric($options['summary'])) $options['summary'] = 100;
                        if (get_option('tic_widget')!==false) {
                            update_option('tic_widget', $options);
                        } else {
                            add_option('tic_widget', $options);
                        }
                        $this->postXmlRequest();
                        $this->retrieveXmlFile();
                        break;
                 }
            }
        }
        $checkAffiliate = $this->checkAffiliate($options['paypal'], $options['password']);
        if ($checkAffiliate->email==0) {
            if ((isset($options['paypal'])) && (trim($options['paypal'])!='')) {
                $this->registerAffiliate($options['paypal'], $options['password'], $_SERVER['HTTP_HOST']);
                $checkAffiliate = $this->checkAffiliate($options['paypal'], $options['password']);
            }
        }
        echo '<h2>The Ideal Candidate</h2>'."\r\n";
        echo '<h3>Login details</h3>'."\r\n";
        echo '<form method="post" action="'.clean_url($_SERVER['REQUEST_URI']).'">'."\r\n";
        echo '<div id="tic_admin">'."\r\n";
        echo '<input type="hidden" name="nonce" value="' . $pluginBase . '"/>'."\r\n";
        echo '<input type="hidden" name="action" value="update"/>'."\r\n";
        echo '<p>Enter the login details you use to access your affiliate account below.</p>'."\r\n";
        echo '<p>If you don\'t already have an affiliate account just enter your PayPal email address and a password <strong>(not your PayPal password)</strong> to sign up as an affiliate.</p>'."\r\n";
        echo '<p>We need your PayPal address because we would like to pay you if you sell a job posting through your affiliate account.</p>'."\r\n";
        echo '<label for="tic_paypal">Email (PayPal)</label>'."\r\n";
        echo '<input type="text" name="tic_paypal" id="tic_paypal" value="' . $options['paypal'] . '"/><br/>'."\r\n";
        echo '<label for="tic_password">Password</label>'."\r\n";
        echo '<input type="password" name="tic_password" id="tic_password" value="' . $options['password'] . '"/><br/>'."\r\n";
        if (($checkAffiliate->email==1) && ($checkAffiliate->password==1)) {
            echo '<h3>Display parameters</h3>'."\r\n";
            //echo '<p>Values entered below will return only jobs that contain these values.</p>'."\r\n";
            echo '<label for="tic_numjobs">Number of jobs per page</label>'."\r\n";
            echo '<input type="text" name="tic_numjobs" id="tic_numjobs" value="' . $options['numjobs'] . '"/><br/>'."\r\n";
            echo '<label for="tic_summary">Characters in summary</label>'."\r\n";
            echo '<input type="text" name="tic_summary" id="tic_summary" value="' . $options['summary'] . '"/><br/>'."\r\n";
            echo '<h3>Filter parameters</h3>'."\r\n";
            echo '<p>Values entered below will return only jobs that contain these values.</p>'."\r\n";
            echo '<label for="tic_jobtitle">Job title</label>'."\r\n";
            echo '<input type="text" name="tic_jobtitle" id="tic_jobtitle" value="' . $options['jobtitle'] . '"/><br/>'."\r\n";
            echo '<label for="tic_companyname">Company name</label>'."\r\n";
            echo '<input type="text" name="tic_companyname" id="tic_companyname" value="' . $options['companyname'] . '"/><br/>'."\r\n";
            echo '<label for="tic_location">Location</label>'."\r\n";
            echo '<input type="text" name="tic_location" id="tic_location" value="' . $options['location'] . '"/><br/>'."\r\n";
            echo '<label for="tic_country">Country</label>'."\r\n";
            echo '<input type="text" name="tic_country" id="tic_country" value="' . $options['country'] . '"/><br/>'."\r\n";
            echo '<label for="tic_category">Category</label>'."\r\n";
            echo '<input type="text" name="tic_category" id="tic_category" value="' . $options['category'] . '"/><br/>'."\r\n";
            echo '<label for="tic_description">Description</label>'."\r\n";
            echo '<input type="text" name="tic_description" id="tic_description" value="' . $options['description'] . '"/><br/>'."\r\n";
            echo '<label for="tic_apply">How to apply</label>'."\r\n";
            echo '<input type="text" name="tic_apply" id="tic_apply" value="' . $options['apply'] . '"/><br/>'."\r\n";
            echo '<label for="tic_about">About the company</label>'."\r\n";
            echo '<input type="text" name="tic_about" id="tic_about" value="' . $options['about'] . '"/><br/>'."\r\n";
            echo '<input type="submit" class="button-primary" value="Save changes"/>'."\r\n";
        } else if (($checkAffiliate->email==1) && ($checkAffiliate->password==0)) {
            if ((!empty($options['paypal'])) && (!empty($options['paypal']))) echo '<p>Incorrect password, either try again or visit <a href="http://www.theidealcandidate.com/jobs-affiliate/?forgot=1" target="_blank">The Ideal Candidate</a> to reset your password.</p>'."\r\n";
            echo '<input type="submit" class="button-primary" value="Save login details"/>'."\r\n";
        } else {
            echo '<input type="submit" class="button-primary" value="Save login details"/>'."\r\n";
        }
        echo '</div>'."\r\n";
        echo '</form>'."\r\n";
        echo '<p>&nbsp;</p>'."\r\n";
        echo '<h3>Instructions</h3>'."\r\n";
        echo '<p>';
        echo '<strong>Setup an account</strong><br/>';
        echo 'To setup your account you can either enter your details above or visit <a href="http://www.theidealcandidate.com/" target="_blank">The Ideal Candidate</a> and become an affiliate. To use the widget you will need to have setup the design on <a href="http://www.theidealcandidate.com/jobs-widget" target="_blank">The Ideal Candidate widget page</a>.';
        echo '<p>&nbsp;</p>'."\r\n";
        echo '<strong>Creating your datafeed</strong><br/>';
        echo 'The filter parameters allow you to customise the jobs your site will download to just those containing the terms you type.';
        echo '<p>&nbsp;</p>'."\r\n";
        echo '<strong>Showing your job list</strong><br/>';
        echo 'Enter the number of jobs you would like to display per page, and the number of characters you would like to show in the description summary.<br/><br/>';
        echo '<strong>Important:</strong> You will need to create a page or post with the following tag in it\'s content<br/>[tic-job-list]<br/>to display the job board.<br/><br/>';
        echo 'The way the job list is displayed can be customised further through the following templates, you will need some HTML/CSS knowledge to do this. The template files are as follows:';
        echo '<ul>';
        echo '<li>tic-joblist.php</li>';
        echo '<li>tic-jobdetail.php</li>';
        echo '<li>tic-style.css</li>';
        echo '</ul>';
        echo 'Fields that can be used in the tic-joblist.php file are as follows:<br/>';
        echo '$job->jobtile, $job->companyname, $job->location, $job->country, $job->category, $job->description, $job->summary, $job->apply, $job->about<br/><br/>';
        echo 'Fields that can be used in the tic-jobdetail.php file are as follows:<br/>';
        echo '$job->jobtile, $job->companyname, $job->location, $job->country, $job->category, $job->description, $job->apply, $job->about, $job->link<br/><br/>';
        echo '</p>';
    }

    /**
     * Check the affiliate exists
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    function checkAffiliate($email, $password)
    {
        $data = array('u' => $email,
                      'p' => $password,
                      'authorized' => '1');
        list($header, $xml) = postRequest('http://www.theidealcandidate.com/xmldf/index.php', $_SERVER['HTTP_HOST'], $data);
        $xmlElements = simplexml_load_string($xml);
        return $xmlElements;
    }

    /**
     * Check the affiliate exists
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    function registerAffiliate($email, $password, $url)
    {
        $data = array('u' => $email,
                      'p' => $password,
                      'w' => $url,
                      'register' => '1');
        list($header, $xml) = postRequest('http://www.theidealcandidate.com/xmldf/index.php', $_SERVER['HTTP_HOST'], $data);
        $xmlElements = simplexml_load_string($xml);
        if ($xmlElements->response==1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Upload XML request
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return object
     */
    function postXmlRequest()
    {
        $options = get_option('tic_widget');
        $email = $options['paypal'];
        unset($options['paypal']);
        $password = $options['password'];
        unset($options['password']);
        unset($options['numjobs']);
        unset($options['summary']);
        $xml = '<?xml version=\'1.0\'?>'."\r\n";
        $xml .= '<search>'."\r\n";
        foreach ($options as $key => $value) {
            $xml .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>'."\r\n";
        }
        $xml .= '</search>'."\r\n";
        $data = array('u' => $email,
                      'p' => $password,
                      'xml' => urlencode($xml));
        list($header, $xmlResponse) = postRequest('http://www.theidealcandidate.com/xmldf/index.php', $_SERVER['HTTP_HOST'], $data);
        $xmlElements = simplexml_load_string($xmlResponse);
        return $xmlElements;
    }

    /**
     * Retrieve XML file
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return null
     */
    function retrieveXmlFile()
    {
        global $wpdb;
        $doRetrieveXmlFile = false;
        if (($update=get_option('tic_update'))!==false) {
            $timeDifference = time() - (int)$update;
            if ($timeDifference>=3600) {
                update_option('tic_update', time());
                $doRetrieveXmlFile = true;
            }
        } else {
            add_option('tic_update', time());
            $doRetrieveXmlFile = true;
        }
        if ($doRetrieveXmlFile) {
            $options = get_option('tic_widget');
            $data = array('u' => $options['paypal'],
                          'p' => $options['password'],
                          'download' => '1');
            list($header, $xmlResponse) = postRequest('http://www.theidealcandidate.com/xmldf/index.php', $_SERVER['HTTP_HOST'], $data);
            $cleanedXml = $this->cleanXml($xmlResponse);
            $xmlElements = simplexml_load_string($cleanedXml);
            if (count($xmlElements)>0) {
                $queries[] = "TRUNCATE " . $wpdb->prefix . "tic;";
                foreach ($xmlElements->job as $job) {
                    $queries[] = "INSERT INTO " . $wpdb->prefix . "tic (id, jobtitle, published, expires, link, companyname, location, country, category, description, salary, about, video, googlemap) VALUES ('" . mysql_real_escape_string($job->id) . "',
                            '" . mysql_real_escape_string($job->jobtitle) . "',
                            '" . mysql_real_escape_string($this->convertXmlMysqlDatetime($job->published)) . "',
                            '" . mysql_real_escape_string($this->convertXmlMysqlDatetime($job->expires)) . "',
                            '" . mysql_real_escape_string($job->link) . "',
                            '" . mysql_real_escape_string($job->companyname) . "',
                            '" . mysql_real_escape_string($job->location) . "',
                            '" . mysql_real_escape_string($job->country) . "',
                            '" . mysql_real_escape_string($job->category) . "',
                            '" . mysql_real_escape_string($job->description) . "',
                            '" . mysql_real_escape_string($job->salary) . "',
                            '" . mysql_real_escape_string($job->about) . "',
                            '" . mysql_real_escape_string($job->video) . "',
                            '" . mysql_real_escape_string($job->googlemap) . "');";
                }
                if ($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."tic'") != $wpdb->prefix."tic") ticActivate();
                foreach ($queries as $query) {
                    $wpdb->query($query);
                }
            }
        }
    }

    /**
     * Convert XML date to MySQL
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return object
     */
    function convertXmlMysqlDatetime($datetime)
    {
        return str_replace('T', ' ', $datetime);
    }

    /**
     * Cleans the XML file
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return object
     */
    function cleanXml($xml)
    {
        $return = trim($xml, " \t\n\r\0\x0B0123456789abcdefABCDEF");
        return $return;
    }

    /**
     * Filter content
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param void
     * @return object
     */
    function content($content)
    {
        $pattern = '/\[(\s*)(tic-job-list)(\s*)(.*)(\s*)\]/i';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        if (count($matches)>0) {
            foreach ($matches as $match) {
                if ($match[2]=='tic-job-list') {
                    $search[] = $match[0];
                    if (isset($_GET['ticj'])) {
                        $replace[] = $this->getJobDetail($_GET['ticj']);
                    } else {
                        $this->retrieveXmlFile();
                        $replace[] = $this->getJobTable($_GET);
                    }
                }
            }
        }
        $filtered = str_replace($search, $replace, $content);
        return $filtered;
    }

    /**
     * Generate job list template output
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param array $request
     * @return string
     */
    function getJobTable($request)
    {
        global $wpdb;
        $options = get_option('tic_widget');
        $where = '';
        $limits = '';
        if (isset($request['ticq'])) $where = $this->getSqlWhere(urldecode($_GET['ticq']));
        $sqlLimits = $this->getSqlLimits(urldecode($_GET['ticp']),$where);
        $limits = $sqlLimits['sql'];
        $totalNumberOfPages = $sqlLimits['pages'];
        $jobLink = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'],'?')?'&':'?') . 'ticj=';
        $sql = "SELECT *, CONCAT('" . $jobLink . "',id) as detail, CONCAT(SUBSTRING(description,1," . $options['summary'] . "),'...') as summary FROM " . $wpdb->prefix . "tic" . $where . " ORDER BY published DESC" . $limits . ";";
        $jobs = $wpdb->get_results($sql);
        if (count($jobs)>0) {
            $parseUrl = parse_url(get_bloginfo('stylesheet_directory') . '/tic-joblist.php');
            $templateFile = str_replace('//', '/', ($_SERVER['DOCUMENT_ROOT'] . $parseUrl['path']));
            if (!file_exists($templateFile)) {
                $templateFile = 'tic-joblist.php';
            }
            ob_start();
            include($templateFile);
            $return = ob_get_contents();
            ob_end_clean();
            $return .= $this->getPagination($request['ticp'], $totalNumberOfPages);
        } else {
            $return = 'No jobs';
        }
        return $return;
    }

    /**
     * Generate job detail template output
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param array $request
     * @return string
     */
    function getJobDetail($id = '')
    {
        global $wpdb;
        if (is_numeric($id)) {
            $sql = "SELECT * FROM " . $wpdb->prefix . "tic WHERE id = '" . $id . "';";
            $job = $wpdb->get_row($sql);
            if (count($job)>0) {
                $parseUrl = parse_url(get_bloginfo('stylesheet_directory') . '/tic-jobdetail.php');
                $templateFile = str_replace('//', '/', ($_SERVER['DOCUMENT_ROOT'] . $parseUrl['path']));
                if (!file_exists($templateFile)) {
                    $templateFile = 'tic-jobdetail.php';
                }
                ob_start();
                include($templateFile);
                $return = ob_get_contents();
                ob_end_clean();
            } else {
                $return = 'No job found';
            }
        } else {
            $return = 'No job found';
        }
        return $return;
    }

    /**
     * Generate pagination
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param array $request
     * @return string
     */
    function getPagination($pageNumber = 1, $totalNumberOfPages = 1)
    {
        global $wpdb;
        $options = get_option('tic_widget');
        $numberJobsPerPage = $options['numjobs'];
        if ($pageNumber <=0 ) $pageNumber = 1;
        if ($pageNumber>$totalNumberOfPages) $pageNumber = $totalNumberOfPages;
        if ($totalNumberOfPages>1) {
            $requestUri = explode('?', $_SERVER['REQUEST_URI']);
            $currentGetRequest = $_SERVER['QUERY_STRING'];
            if ($currentGetRequest!='') {
                $queryParts = explode('&',$currentGetRequest);
                $newGetRequest = '';
                foreach ($queryParts as $queryPart) {
                    if (strpos($queryPart,'ticp')===false) {
                        $newGetRequest .= '&' . $queryPart;
                    }
                }
                $newGetRequest = '?' . trim($newGetRequest,'&') . '&';
            } else {
                $newGetRequest = '?';
            }
            $return = '<div class="tic-pagination">';
            for ($i = 1; $i <= $totalNumberOfPages; $i++) {
                if (($i != $pageNumber) && ($i == 1)) {
                    $return .= '<span class="tic-page-previous"><a href="' . $requestUri[0] . $newGetRequest . 'ticp=' . ($pageNumber - 1 ) . '">&laquo; Previous</a></span>';
                }
                if ($i!=$pageNumber) {
                    $return .= '<span class="tic-page-number"><a href="' . $requestUri[0] . $newGetRequest . 'ticp=' . $i . '">'.$i.'</a></span>';
                } else {
                    $return .= '<span class="tic-page-current">'.$i.'</span>'."\r\n";
                }
                if (($i == $totalNumberOfPages) && ($i != $pageNumber)) {
                    $return .= '<span class="tic-page-next"><a href="' . $requestUri[0] . $newGetRequest . 'ticp=' . ($pageNumber + 1) .'">Next &raquo;</a>'."\r\n";
                }
            }
            $return .= '</div>';
        } else {
            $return = '';
        }
        return $return;
    }

    /**
     * Generate where for sql
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param string $query
     * @return string
     */
    function getSqlWhere($query)
    {
        $sql = '';
        $query = trim($query);
        if ($query!='') {
            $sql .= ' WHERE';
            $queries = explode(' ', $query);
            if (count($queries)>0) {
                foreach ($queries as $query) {
                    if ($sql != ' WHERE') $sql .= ' AND';
                    $sql .= " (jobtitle LIKE '%" . $query . "%' OR companyname LIKE '%" . $query . "%' OR location LIKE '%" . $query . "%' OR country LIKE '%" . $query . "%' OR category LIKE '%" . $query . "%' OR description LIKE '%" . $query . "%' OR about LIKE '%" . $query . "%')";
                }
            } else {
                $sql .= " jobtitle LIKE '%" . $query . "%' OR companyname LIKE '%" . $query . "%' OR location LIKE '%" . $query . "%' OR country LIKE '%" . $query . "%' OR category LIKE '%" . $query . "%' OR description LIKE '%" . $query . "%' OR about LIKE '%" . $query . "%'";
            }
        }
        return $sql;
    }

    /**
     * Generate limits for sql
     *
     * @author Steven Raynham
     * @since 2.0
     *
     * @param int $pageNumber
     * @return string
     */
    function getSqlLimits($pageNumber = 1,$where)
    {
        global $wpdb;
        $options = get_option('tic_widget');
        $numberJobsPerPage = $options['numjobs'];
        if ($pageNumber <=0 ) $pageNumber = 1;
        $sql = "SELECT COUNT(id) as jobcount FROM " . $wpdb->prefix . "tic" . $where;
        $result = $wpdb->get_row($sql);
        $totalNumberOfPages = ceil($result->jobcount / $numberJobsPerPage);
        if ($pageNumber>$totalNumberOfPages) $pageNumber = $totalNumberOfPages;
        $offset = $numberJobsPerPage * ($pageNumber - 1);
        $sql = ' LIMIT ' . $offset . ', ' . $numberJobsPerPage;
        $return['sql'] = $sql;
        $return['pages'] = $totalNumberOfPages;
        return $return;
    }

    /**
     * Initiate the widget
     *
     * @author Steven Raynham
     * @since 1.0
     *
     * @param void
     * @return null
     */
    function widgetInit()
    {
        register_sidebar_widget('The Ideal Candidate', array(&$this,'widgetSidebar'));
        register_widget_control('The Ideal Candidate', array(&$this,'widgetControl'));
    }

    /**
     * Output the widget to the sidebar
     *
     * @author Steven Raynham
     * @since 1.0
     *
     * @param void
     * @return stdout
     */
    function widgetSidebar()
    {
        $options = get_option("tic_widget");
        $output = '<script type="text/javascript" src="http://www.theidealcandidate.com/widget/display-'.$options['widget'].'.js"></script>'."\r\n";
        echo $output;
    }

    /**
     * Create the widget control in the admin
     *
     * @author Steven Raynham
     * @since 1.0
     *
     * @param void
     * @return stdout
     */
    function widgetControl()
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

        if ($widgets = $this->getAffiliateWidgets($options['paypal'])) {
            echo '<p>'."\r\n";
            echo '<label for="tic_widget">Widget to display:</label><br/>'."\r\n";
            echo '<select name="tic_widget">'."\r\n";
            foreach ($widgets as $widgetId => $widgetName) {
                echo '<option value="' . $widgetId . '"'. (($widgetId==$options['widget'])?' selected="selected"':'') .'>' . $widgetName . '</option>'."\r\n";
            }
            echo '</select>'."\r\n";
            echo '<input type="hidden" id="tic_widget_submit" name="tic_widget_submit" value="1"/>'."\r\n";
            echo '</p>'."\r\n";
        } else {
            echo '<p>You will need to have an affiliate account with <a href="http://www.theidealcandidate.com/" title="The Ideal Candidate" target="_blank">The Ideal Candidate</a>, just go to the <a href="admin.php?page=theidealcandidate/theidealcandidate.php" title="Setup page">setup page</a> to enter your details. An account will be automatically setup if you don\'t already have one. To setup your widget you currently need to visit <a href="http://www.theidealcandidate.com/jobs-widget" title="The Ideal Candidate" target="_blank">The Ideal Candidate widget page</a> and use the same PayPal address you used to signup.</p>';
            echo '<p>Once you have entered your PayPal address (your login email for The Ideal Candidate) on the setup page, just return here to see a list of your widgets.</p>';
        }
    }

    /**
     * Get the affiliate's widgets
     *
     * @author Steven Raynham
     * @since 1.0
     *
     * @param string $email
     * @return mixed
     */
    function getAffiliateWidgets($email)
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

/**
 * Plugin activation
 *
 * @param void
 * @return null 
 */
function ticActivate() {
    global $wpdb;
    if ($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."tic'") != $wpdb->prefix."tic") {
        $sql = "CREATE TABLE `".$wpdb->prefix."tic` (
                `id` INT(11) UNSIGNED NOT NULL,
                `jobtitle` VARCHAR(255) DEFAULT NULL,
                `published` DATETIME NOT NULL,
                `expires` DATETIME NOT NULL,
                `link` VARCHAR(255) DEFAULT NULL,
                `companyname` VARCHAR(255) NOT NULL,
                `location` VARCHAR(255) NOT NULL,
                `country` VARCHAR(255) NOT NULL,
                `category` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `salary` VARCHAR(255),
                `about` TEXT,
                `video` VARCHAR(255),
                `googlemap` VARCHAR(255),
                PRIMARY KEY (`id`),
                UNIQUE KEY `id` (`id`)
                );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    $options['numjobs'] = 25;
    $options['summary'] = 100;
    add_option('tic_widget', $options);
}
register_activation_hook(__FILE__,'ticActivate');

require_once "simplexml.class.php";

/**
 * Add simplexml function for PHP4 compatibility
 *
 * @author Taha Paksu, http://www.tahapaksu.com/
 * @since 1.0
 *
 * @param string $file
 * @return mixed
 */
if (!function_exists('simplexml_load_file'))
{
    function simplexml_load_file($file) {
        $sx = new simplexml;
        return $sx->xml_load_file($file);
    }
}

/**
 * Post request function
 *
 * @author Jonas John, http://www.jonasjohn.de/snippets/php/post-request.htm
 * @since 2.0
 *
 * @param string $url
 * @param string $referer
 * @param string $data
 * @return mixed
 */
function postRequest($url, $referer, $_data) {
 
    // convert variables array to string:
    $data = array();    
    while(list($n,$v) = each($_data)){
        $data[] = "$n=$v";
    }    
    $data = implode('&', $data);
    // format --> test1=a&test2=b etc.
 
    // parse the given URL
    $url = parse_url($url);
    if ($url['scheme'] != 'http') { 
        die('Only HTTP request are supported !');
    }
 
    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];
 
    // open a socket connection on port 80
    $fp = fsockopen($host, 80);
 
    // send the request headers:
    fputs($fp, "POST $path HTTP/1.1\r\n");
    fputs($fp, "Host: $host\r\n");
    fputs($fp, "Referer: $referer\r\n");
    fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    fputs($fp, "Content-length: ". strlen($data) ."\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    fputs($fp, $data);
 
    $result = ''; 
    while(!feof($fp)) {
        // receive the results of the request
        $result .= fgets($fp, 4096);
    }
 
    // close the socket connection:
    fclose($fp);
 
    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);
 
    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';
    // return as array:
    return array($header, $content);
}