<?php
/*
Plugin Name: Testimonials Manager
Plugin URI: http://www.profitplugs.com/testimonials-manager-wordpress/
Description: Manage and display testimonials for your blog, product or service. Automatically rotate testimonials in your sidebar and create a full testimonials page. Supports Gravatars.
Author: Gobala Krishnan 
Version: 2.0.4
Author URI: http://www.profitplugs.com
*/

global $wp_version;

include_once('testimonial_widget.php');
include_once('testimonial_manager.php');

function filter_testimonial() {
    $fil_test .= " AND post_content LIKE '%[show_testimonials]%' AND post_status LIKE 'publish' AND post_type LIKE 'page'";
    return $fil_test;
}

function showTestimonial() {
    $x = 0;
    $data = get_option('testimonials_manager');
    $testimonialboxcount = count($data['data']);

    switch ($data['dorder']) {
        case 'random':
            shuffle($data['data']);
            ;
            break;
        case 'desc':
            $data['data'] = array_reverse($data['data']);
            ksort($data['data']);
            ;
            break;
        default: ;
    }
    echo <<<EOF
<style>
.clearfloat {
clear: both;
}
</style>
EOF;

    $p = new pagination;
    $p->items($testimonialboxcount);

    $p->limit($data['items']);
    if (empty($_GET['pg'])) {
        $page = 1;
    } else {
        $page = $_GET['pg'];
    }
    $p->currentPage($page);
    $p->target(get_permalink());

    $result = $p->getOutput();
    // print_r($data);
    if ($testimonialboxcount > $data['items']) {
        $testimonialboxcount = $data['items'];
        // now to make the array smaller
        $newarray = array_slice($data['data'], ($page - 1) * $data['items'], $data['items']);
        $data['data'] = $newarray;
        $testimonialboxcount = count($newarray);
    }
    if ($testimonialboxcount == 0) {
        $result .= '<div class="testimonial" style="text-align:center;">';
        $result .= '<strong>There are no testimonial yet</strong>';
        $result .= '</div>';
    } else {
        while ($x < $testimonialboxcount) {
            $url = $data['data'][$x]['url'];

            if (substr($url, 0, 7) != 'http://') {
                $url = 'http://' . $url;
            }
            $result .= '<div class="testimonial">';
            if ($data['data'][$x]['avatar']) {
                if ($data['data'][$x]['avatar'] == "gravatar") {
                    $result .= get_avatar($data['data'][$x]['email'], $size = $data['imagex']);
                } else {
                    $result .= '<img src="' . $data['data'][$x]['own_avatar'] . '" class="avatar" alt="avatar" width="' . $data['imagex'] . '" height="' . $data['imagey'] . '" />';
                }
            }
            $result .= stripslashes(nl2br($data['data'][$x]['text']));
            $result .= '<br /><br /><strong>' . stripslashes($data['data'][$x]['name']) . '</strong><br />';
            if ($data['data'][$x]['url']) {
                $result .= '<a href="' . stripslashes($url) . '">';
            }
            if ($data['data'][$x]['company']) {
                $result .= stripslashes($data['data'][$x]['company']);
            }
            if ($data['data'][$x]['url']) {
                $result .= '</a>';
            }
            $result .= '<div class="clearfloat"></div></div>';
            $x++;
        }
    }
    /*
    $s=  <<<EOF
<style>
.copyyy,.copyyy a {
font-size: 10px !important;
font-family; veranda !important;
color: #666666 !important;
}
</style>
EOF;
     * 
     */
    $result .= $p->getOutput();
    $result .= '<div class="testimonial" style="background: transparent; border:none; margin:0 0 20px 0; text-align:right;size:10px">Powered by the <a href="http://www.profitplugs.com/testimonials-manager-wordpress/" title="Testimonial Manager">Testimonial Manager Plugin for Wordpress.</a></div>';

    return $result;
}

function custom_excerpt($paragraph, $limit) {
    $tok = strtok($paragraph, " ");
    $words = 0;
    while ($tok !== false) {
        $text .= " " . $tok;
        $words++;
        if (($words >= $limit) && ((substr($tok, - 1) == "!") || (substr($tok, - 1) == ".") || ((substr($tok, - 1) == "?")))) {
            $text .= '...';
            break;
        }
        $tok = strtok(" ");
    }
    return ltrim($text);
}

function widgetcss($data, $wid='') {
    if (version_compare($wp_version, '2.8', '<')) {
        $data = get_option('testimonials_manager_widget');
    }
    echo '<style type="text/css">';
    if (empty($data['customcss'])) {
        echo "#{$wid}	.testimonials_manager_widget{
             margin: 10px 0;
             padding:10px;
             border: 1px dotted #dddddd;
             background: #f4f4f4;
            }

            #{$wid} .testimonials_manager_widget .avatar{
             background:#FFFFFF none repeat scroll 0 0;
             border:1px solid #DDDDDD;
             float:right;
             margin-right:-5px;
             margin-top:-5px;
			 margin-left: 5px;
             padding:2px;
             position:relative;
            }";
    } else {
        $data['customcss'] = str_replace('.testimonials_manager_widget', "#{$wid}	.testimonials_manager_widget", $data['customcss']);
        echo $data['customcss'];
    }
    echo '</style>';
}

function pagecss($data) {
    $data = get_option('testimonials_manager');
    echo '<style type="text/css">';
    if (!isset($data['customcss']) || $data['customcss'] == "") {
        echo "	.testimonial {
				margin: 10px 0;
				padding:10px;
				border: 1px dotted #dddddd;
				background: #f4f4f4;
			}
			.testimonial .avatar {
                background:#FFFFFF none repeat scroll 0 0;
				border:1px solid #dddddd;
				float:right;
				margin-right:-5px;
				margin-top:-5px;
				padding:2px;
				position:relative;
			}";
    } else {
        echo $data['customcss'];
    }
    echo '</style>';
}

function testimonial_install() {

    $page = <<<EOF
div.pagination {
	font-size:11px;
	font-family:Tahoma,Arial,Helvetica,Sans-serif;
	padding:2px;
	background-color: #F4F4F4;
}
div.pagination a {
	padding:2px 5px 2px 5px;
	margin:2px;
	background-color:#F4F4F4;
	text-decoration:none;
	
	color:#000;
}
div.pagination a:hover,div.pagination a:active {
	background-color:#c1c1c1;
	color:#000;
}
div.pagination span.current {
	padding:2px 5px 2px 5px;
	margin:2px;
	font-weight:bold;
	background-color:#fff;
	color:#303030;
}
div.pagination span.disabled {
	padding:2px 5px 2px 5px;
	margin:2px;
	background-color:#c1c1c1;
	color: #797979;
}
EOF;

    $data = get_option('testimonials_manager');
    if ($data['first_time'] != "not") {
        global $wpdb;

        $my_post = array();
        $my_post['post_title'] = 'Testimonials';
        $my_post['post_content'] = '[show_testimonials]';
        $my_post['post_status'] = 'publish';
        $my_post['post_type'] = 'page';
        $my_post['post_author'] = 1;

        // Insert the post into the database
        $data['page_id'] = wp_insert_post($my_post);
        if (empty($data['items'])) {
            $data['items'] = 10;
        }
        if (empty($data['imagex'])) {
            $data['imagex'] = 48;
        }
        if (empty($data['imagey'])) {
            $data['imagey'] = 48;
        }
        if (empty($data['dorder'])) {
            $data['dorder'] = 'asc';
        }
        if (empty($data['customcss'])) {
            $data['customcss'] = <<<EOF
.testimonial {
	margin:10px 0;
	padding:10px;
	border:1px dotted #f4f4f4;
	background:#F4F4F4;
}
.testimonial .avatar {
	background:#FFFFFF none repeat scroll 0 0;
	border:1px solid #DDDDDD;
	float:right;
	margin-right:-5px;
	margin-left:5px;
	margin-top:-5px;
	padding:2px;
	position:relative;
}

EOF;
            $data['customcss'] .= $page;
        }
    }
    // jal_install();
    if (stristr($data['customcss'], 'pagination') == false) {
        $data['customcss'] .= $page;
    }
    $data = update_option('testimonials_manager', $data);
}

function testimonial_uninstall() {
    delete_option('testimonials_manager_widget');
    $data = get_option('testimonials_manager');
    $data['first_time'] = "not";
    $data = update_option('testimonials_manager', $data);
}
$jal_db_version = "1.0";

function jal_install () {
    global $wpdb;
    global $jal_db_version;
    $data = get_option('testimonials_manager');

    file_put_contents('xxx', $data);
    $table_name = $wpdb->prefix . "testimonials";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
              `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT,
              `url` varchar(150) DEFAULT NULL,
              `company` varchar(80) DEFAULT NULL,
              `name` varchar(80) DEFAULT NULL,
              `testimonial` text,
              PRIMARY KEY (`id`)
	);";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        if (!empty($data['data'])) {
            foreach ($data['data'] as $k) {
                $sql = "INSERT INTO {$table_name} (`url`, `company`, `name`, `testimonial`) VALUES ('{$k['url']}', '{$k['company']}', '{$k['name']}', '{$k['text']}') ;";
            }
        }
    }
}

register_activation_hook(__FILE__, 'testimonial_install');
register_deactivation_hook(__FILE__, 'testimonial_uninstall');
if (version_compare($wp_version, '2.8', '<')) {
    add_action('wp_head', 'widgetcss', 1);
}

add_action('wp_head', 'pagecss', 1);
add_shortcode('show_testimonials', 'showTestimonial');
do_shortcode('show_testimonials');

?>