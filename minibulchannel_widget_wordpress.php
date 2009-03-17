<?php

/*
Plugin Name: Minibul Channel for WordPress Widget
Version: 1.1
Plugin URI: http://www.minibul.com/
Description: Display the latest update from any public Minibul Channel hosted at any Minibul Community.
Author: Minibul.com
Author URI: http://www.minibul.com/
*/

/*  
		Check minibul.com, your Minibul Community or WordPress.org for new updates.

		Copyright 2009  Minibul.com | Godfried van Loo

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define('MAGPIE_CACHE_AGE', 120);

$minibulchannel_options['widget_fields']['minibulchannelid'] = array('label'=>'<b>Channel ID</b>:', 'type'=>'text', 'default'=>'');
$minibulchannel_options['widget_fields']['channeltype'] = array('label'=>'<b>Channel Type</b> (cx / cm):', 'type'=>'text', 'default'=>'cx');

$minibulchannel_options['prefix'] = 'minibulchannel';

// Display Minibul Channel Widget
function minibulchannel_show($minibulchannelid,$channeltype) {
	global $minibulchannel_options;
print '
<script type="text/javascript"><!-- //Minibul Gadget by minibul.com
var mnblch_channelid = "'.$minibulchannelid.'";
var mnblch_channeltype = "'.$channeltype.'";
//--></script>
<script src="http://broadcast.minibul.net/gadgets/bloggadgets/bloggadget1_minibulchannel.js"></script>';
}

// Minibul Channel widget stuff
function widget_minibulchannel_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;
	
	$check_options = get_option('widget_minibulchannel');
  if ($check_options['number']=='') {
    $check_options['number'] = 1;
    update_option('widget_minibulchannel', $check_options);
  }
  
	function widget_minibulchannel($args, $number = 1) {

		global $minibulchannel_options;
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widget_minibulchannel');
		
		// fill options with default values if value is not set
		$item = $options[$number];
		foreach($minibulchannel_options['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		

		// These lines generate our output.
		echo $before_widget;
  	minibulchannel_show($item['minibulchannelid'], $item['channeltype']);
  	echo $after_widget;
	}

	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function widget_minibulchannel_control($number) {
	
		global $minibulchannel_options;

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_minibulchannel');
		if ( isset($_POST['minibulchannel-submit']) ) {

			foreach($minibulchannel_options['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$field_name = sprintf('%s_%s_%s', $minibulchannel_options['prefix'], $key, $number);

				if ($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
				} elseif ($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$field_name]);
				}
			}

			update_option('widget_minibulchannel', $options);
		}

		foreach($minibulchannel_options['widget_fields'] as $key => $field) {
			
			$field_name = sprintf('%s_%s_%s', $minibulchannel_options['prefix'], $key, $number);
			$field_checked = '';
			if ($field['type'] == 'text') {
				$field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
			} elseif ($field['type'] == 'checkbox') {
				$field_value = 1;
				if (! empty($options[$number][$key])) {
					$field_checked = 'checked="checked"';
				}
			}
			
			printf('<p style="text-align:right;" class="minibulchannel_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
		}

		echo '<input type="hidden" id="minibulchannel-submit" name="minibulchannel-submit" value="1" />';
	}
	
	function widget_minibulchannel_setup() {
		$options = $newoptions = get_option('widget_minibulchannel');
		
		if ( isset($_POST['minibulchannel-number-submit']) ) {
			$number = (int) $_POST['minibulchannel-number'];
			$newoptions['number'] = $number;
		}
		
		if ( $options != $newoptions ) {
			update_option('widget_minibulchannel', $newoptions);
			widget_minibulchannel_register();
		}
	}
		
	function widget_minibulchannel_register() {
		
		$options = get_option('widget_minibulchannel');
		$dims = array('width' => 300, 'height' => 300);
		$class = array('classname' => 'widget_minibulchannel');

		for ($i = 1; $i <= 9; $i++) {
			$name = sprintf(__('Minibul Channel'), $i);
			$id = "minibulchannel-$i"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, $i <= $options['number'] ? 'widget_minibulchannel' : /* unregister */ '', $class, $i);
			wp_register_widget_control($id, $name, $i <= $options['number'] ? 'widget_minibulchannel_control' : /* unregister */ '', $dims, $i);
		}
		
		add_action('sidebar_admin_setup', 'widget_minibulchannel_setup');
	}

	widget_minibulchannel_register();
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_minibulchannel_init');
?>