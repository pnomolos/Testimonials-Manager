<?php
if (version_compare($wp_version, '2.8', '>=')) {
    class testimonials_manager_widget extends WP_Widget {
        // The widget construct. Mumbo-jumbo that loads our code.
        function testimonials_manager_widget() {
            $widget_ops = array('classname' => 'ww1231', 'description' => __("Display and rotate your testimonials"));
            $this->WP_Widget('ww123', __('Testimonials'), $widget_ops);
        }
        function widget($args, $instance) {
            extract($args, EXTR_SKIP);

            $data = $instance;
            if (!isset( $args['show_css']) || $args['show_css'] != FALSE ) {
              widgetcss($data, $widget_id);
            }
            $instanc = get_option('testimonials_manager');
            // print_r($instanc['data']);
            echo $before_widget;
            if ($data['display'] && $data['display'] < count($instanc['data'])) {
                $testimonialboxValue = $data['display'];
            } else {
                $testimonialboxValue = count($instanc['data']);
            }
            if ($data['title'] != "") {
                echo $args['before_title'] . $data['title'] . $args['after_title'];
            }

            $result_array = array();
            if(empty($instanc['data'])) {
                echo '<div class="testimonials_manager_widget" style="text-align:center;">';
                echo '<strong>There are no testimonial yet</strong>';
                echo '</div>';
            }else {
                shuffle($instanc['data']);

                /*

		while(count($result_array) < $testimonialboxValue){
		   $num = array_rand($instanc['data']);
		   if(!in_array($num,$result_array)){
			  $result_array[] = $num;
		   }
		}*/

                $result_array = array_slice($instanc['data'], 0, $testimonialboxValue);
                // print_r($result_array);
                if ($testimonialboxValue == 0) {
                    echo '<div class="testimonials_manager_widget" style="text-align:center;">';
                    echo '<strong>There are no testimonial yet</strong>';
                    echo '</div>';
                } else {
                    foreach ($result_array as $x) {
                        if ($x != - 1) {
                            $url = $x['url'];
                            if (substr($url, 0, 7) != 'http://') {
                                $url = 'http://' . $url;
                            }
                            $text = stripslashes($x['text']);

                            echo '<div class="testimonials_manager_widget"><blockquote>';
                            if ($x['avatar']) {
                                if ($x['avatar'] == "gravatar") {
                                    echo get_avatar($x['email'], $size = '48');
                                } else {
                                    echo '<img src="' . $x['own_avatar'] . '" class="avatar" alt="avatar" width="48" height="48" />';
                                }
                            }
                        }
                        echo '<p>' . nl2br($text) . '</p>';
                        echo '<cite>' . stripslashes($x['name']);
                          if ( $x['title'] ) { echo ', ' . stripslashes($x['title']); }
                          if ( $x['company_name'] ) { echo '<em>' . stripslashes($x['company_name']) . '</em>'; }
                        echo '</cite>';
                        if ($x['url']) {
                            echo '<a href="' . stripslashes($url) . '">';
                        }
                        if ($x['company']) {
                            echo stripslashes($x['company']);
                        }
                        if ($x['url']) {
                            echo '</a>';
                        }
                        echo '</blockquote></div>';
                    }
                }
                if ($data['page_link'] != "no_page") {
                    echo '<div style="width:100%;text-align:right; display:block;"><a href="';
                    if ($data['page_link'] == "") {
                        get_permalink($instance['page_id']);
                    } else {
                        echo $data['page_link'];
                    }
                    echo '"> Read more&rsaquo;&rsaquo; </a></div>';
                }
            }
            echo $after_widget;
        } // End function widget.
        // Updates the settings.
        function update($new_instance, $old_instance) {
            return $new_instance;
        } // End function update
        // The admin form.
        function form($instance) {
            /*
		echo '<div id="bareBones-admin-panel">';
		echo '<label for="' . $this->get_field_id("title") .'">BareBones Title:</label>';
		echo '<input type="text" class="widefat" ';
		echo 'name="' . $this->get_field_name("title") . '" ';
		echo 'id="' . $this->get_field_id("title") . '" ';
		echo 'value="' . $instance["title"] . '" />';
		echo '<p>This widget will display the title you choose above followed by a "Hello World!" statement.</p>';
		echo '</div>';
            */
            if (empty($instance['customcss'])) {
                $instance['customcss'] = "\n.testimonials_manager_widget{\n margin: 10px 0;\n padding:10px;\n border: 1px dotted #dddddd;\n background: #f4f4f4;\n}";
                $instance['customcss'] .= "\n\n .testimonials_manager_widget .avatar{\n background:#FFFFFF none repeat scroll 0 0;\n border:1px solid #DDDDDD;\n float:right;\n margin-right:-5px;margin-left:5px;\n\n margin-top:-5px;\n padding:2px;\n position:relative;\n}";
            }

            if (empty($instance['display'])) {
                $instance['display'] = "3";
            }
            if (empty($instance['title'])) {
                $instance['title'] = "Testimonials";
            }

            ?>

<p><label>Widget Title:<br /><input name="<?php echo $this->get_field_name("title") ?>" type="text" value="<?php echo htmlspecialchars($instance['title'], ENT_QUOTES); ?>" style="width:100%;" /></label></p>
<p><label>No. of items to rotate:<br /><input type="text" name="<?php echo $this->get_field_name("display") ?>" value="<?php echo htmlspecialchars($instance['display'], ENT_QUOTES); ?>" style="width:100%;" /></label></p>
<p><label>Custom CSS:<br /><textarea name="<?php echo $this->get_field_name("customcss") ?>" style="width:100%; height:200px;"><?php echo htmlspecialchars($instance['customcss'], ENT_QUOTES); ?></textarea></label></p>
<p><label>Full testimonials page:<br />
        <select name="<?php echo $this->get_field_name("page_link") ?>" style="width:100%">
            <?php

            add_filter('posts_where', 'filter_testimonial');
            query_posts($query_string);
                        // query_posts("post_content LIKE '%[show_testimonial]%'&post_status=publish&post_type=page");
                        if (have_posts()) : while (have_posts()) : the_post();

                                ?>
            <option value="<?php the_permalink(); ?>" <?php if ($data['page_link'] == "") {
                                    if (get_permalink($instance['page_id']) == get_permalink()) {
                                        echo "selected";
                                    }
                    } else {
                                    if ($data['page_link'] == get_permalink()) {
                                        echo "selected";
                                    }
                                }

                                ?>><?php the_title(); ?></option>
                            <?php
                            endwhile;
                        else:
                                    ?>
            <option value="no_page">No page with testimonial short code</option>
                                <?php
                                endif;
                                // Reset Query
            wp_reset_query();

                        ?>
        </select></label></p>	<?php
                    } // end function form
                } // end class WP_Widget_BareBones
                // Register the widget.
    add_action('widgets_init', create_function('', 'return register_widget("testimonials_manager_widget");'));
} else {
    add_action("widgets_init", array('testimonials_manager_widget', 'register'));
    register_activation_hook(__FILE__, array('testimonials_manager_widget', 'activate'));
    register_deactivation_hook(__FILE__, array('testimonials_manager_widget', 'deactivate'));

    class testimonials_manager_widget {
        function activate() {
            $data = array('title' => 'Testimonials' , 'display' => '3' ,);
            update_option('testimonials_manager_widget' , $data);
        }
        function deactivate() {
            delete_option('testimonials_manager_widget');
        }

        function control() {
            $data = get_option('testimonials_manager_widget');
            if (!isset($data['customcss']) || $data['customcss'] == "") {
                $data['customcss'] = "\n.testimonials_manager_widget{\n margin: 10px 0;\n padding:10px;\n border: 1px dotted #dddddd;\n background: #f4f4f4;\n}";
                $data['customcss'] .= "\n\n.testimonials_manager_widget .avatar{\n background:#FFFFFF none repeat scroll 0 0;\n border:1px solid #DDDDDD;\n float:right;\n margin-right:-5px;\n margin-top:-5px;\n padding:2px;\n position:relative;\n}";
            }
            if (!isset($data['display']) || $data['display'] == "") {
                $data['display'] = "3";
            }
            if (!isset($data['title']) || $data['title'] == "") {
                $data['title'] = "Testimonials";
            }

            ?>
<p><label>Widget Title:<br /><input name="title" type="text" value="<?php echo htmlspecialchars($data['title'], ENT_QUOTES); ?>" style="width:100%;" /></label></p>
<p><label>No. of items to rotate:<br /><input type="text" name="display" value="<?php echo htmlspecialchars($data['display'], ENT_QUOTES); ?>" style="width:100%;" /></label></p>
<p><label>Custom CSS:<br /><textarea name="customcss" style="width:100%; height:200px;"><?php echo htmlspecialchars($data['customcss'], ENT_QUOTES); ?></textarea></label></p>
<p><label>Full testimonials page:<br />
        <select name="page_link" style="width:100%">
            <?php

            add_filter('posts_where', 'filter_testimonial');
            query_posts($query_string);
            // query_posts("post_content LIKE '%[show_testimonial]%'&post_status=publish&post_type=page");
            if (have_posts()) : while (have_posts()) : the_post();

                                ?>
            <option value="<?php the_permalink(); ?>" <?php if ($data['page_link'] == "") {
                                    if (get_permalink($instance['page_id']) == get_permalink()) {
                                        echo "selected";
                                    }
                                } else {
                                    if ($data['page_link'] == get_permalink()) {
                                        echo "selected";
                        }
                                }

                                ?>><?php the_title(); ?></option>
                            <?php
                            endwhile;
                        else:
                            ?>
            <option value="no_page">No page with testimonial short code</option>
                        <?php
                                endif;
                                // Reset Query
                                wp_reset_query();

                                ?>
        </select></label></p>
                        <?php
                        if (isset($_POST['title'])) {
                            $data['title'] = attribute_escape($_POST['title']);
                            $data['display'] = attribute_escape($_POST['display']);
                            $data['customcss'] = attribute_escape($_POST['customcss']);
                            $data['page_link'] = $_POST['page_link'];
                update_option('testimonials_manager_widget', $data);
            }
        }

        function widget($args) {
            extract($args, EXTR_SKIP);
            $data = get_option('testimonials_manager_widget');
            $instance = get_option('testimonials_manager');

            echo $args['before_widget'];
            if ($data['display'] && $data['display'] < count($instance['data'])) {
                $testimonialboxValue = $data['display'];
            } else {
                $testimonialboxValue = count($instance['data']);
            }
            if ($data['title'] != "") {
                echo $args['before_title'] . $data['title'] . $args['after_title'];
            }

            $result_array = array();
            while (count($result_array) < $testimonialboxValue) {
                $num = array_rand($instance['data']);
                if (!in_array($num, $result_array)) {
                    $result_array[] = $num;
                }
            }
            if ($testimonialboxValue == 0) {
                echo '<div class="testimonials_manager_widget" style="text-align:center;">';
                echo '<strong>There are no testimonial yet</strong>';
                echo '</div>';
            } else {
                foreach ($result_array as $x) {
                    if ($x != - 1) {
                        $url = $instance['data'][$x]['url'];
                        if (substr($url, 0, 7) != 'http://') {
                            $url = 'http://' . $url;
                        }
                        $text = stripslashes($instance['data'][$x]['text']);
                        if (!isset($data['customcss']) || $data['customcss'] == "") {
                            $data['customcss'] = "\n.testimonials_manager_widget{\n margin: 10px 0;\n padding:10px;\n border: 1px dotted #dddddd;\n background: #f4f4f4;\n}";
                            $data['customcss'] .= "\n\n.testimonials_manager_widget .avatar{\n background:#FFFFFF none repeat scroll 0 0;\n border:1px solid #DDDDDD;\n float:right;\n margin-right:-5px;\n margin-top:-5px;\n padding:2px;\n position:relative;\n}";
                        }
                        echo '<div class="testimonials_manager_widget">';
                        if ($instance['data'][$x]['avatar']) {
                            if ($instance['data'][$x]['avatar'] == "gravatar") {
                                echo get_avatar($instance['data'][$x]['email'], $size = '48');
                            } else {
                                echo '<img src="' . $instance['data'][$x]['own_avatar'] . '" class="avatar" alt="avatar" width="48" height="48" />';
                            }
                        }
                        echo $text;
                        echo '<br /><br /><strong>' . stripslashes($instance['data'][$x]['name']) . '</strong><br />';
                        if ($instance['data'][$x]['url']) {
                            echo '<a href="' . stripslashes($url) . '">';
                        }
                        if ($instance['data'][$x]['company']) {
                            echo stripslashes($instance['data'][$x]['company']);
                        }
                        if ($instance['data'][$x]['url']) {
                            echo '</a>';
                        }
                        echo '</div>';
                    }
                }
                if ($data['page_link'] != "no_page") {
                    echo '<div style="width:100%;text-align:right; display:block;"><a href="';
                    if ($data['page_link'] == "") {
                        get_permalink($instance['page_id']);
                    } else {
                        echo $data['page_link'];
                    }
                    echo '"> Read more&rsaquo;&rsaquo; </a></div>';
                }
            }
            echo $args['after_widget'];
        }

        function register() {
            register_sidebar_widget('Testimonials Manager', array('testimonials_manager_widget', 'widget'));
            register_widget_control('Testimonials Manager', array('testimonials_manager_widget', 'control'));
        }
    }
}

?>