<?php
/*
Plugin Name: WordPress Easy Index
Version: 0.1
Plugin URI: http://crispijnverkade.nl/blog/wp-easy-index
Description: WordPress Easy Index will create an index for your WordPress blog posts
Author: Crispijn Verkade
Author URI: http://crispijnverkade.nl/

Copyright (c) 2009
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt

    This file is part of WordPress.
    WordPress is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	INSTALL: 
	Just install the plugin in your blog and activate
*/ 

//add the stylesheet to the blog's header
function wpei_header(){
	global $wpdb, $post, $table_prefix, $mootools12;

	echo PHP_EOL.'<link rel="stylesheet" type="text/css" media="screen" href="'.get_settings('siteurl').'/wp-content/plugins/wp-easy-index/wp_easy_index.css" />'.PHP_EOL;
}

//add the admin style and javascript to the header
function wpei_header_admin(){
	/*echo PHP_EOL.'<script type="text/javascript">
// English lang variables for WP2.5

tinyMCE.addI18n({en:{
EasyIndex:{	
desc : \'Add Easy Index\'
}}});

</script>'.PHP_EOL;*/

}

//break long headings
function wp_break_maxchar($val){
	$char = stripslashes(get_option('wpei_char'));
	
	if(strlen($val) > $char){
		return substr($val, 0, $char).'...'.PHP_EOL;
	}else{
		return $val;
	}
}

//nice urls
function nice_url($match){
	return strtolower(str_replace(' ', '-', $match));
}

//creates an places the index in the content
function wp_get_index($content){
	global $post;
	
	if(is_single()){	
		if(!preg_match('|<!--index-->|', $content)) {
			return $content;
		}else{
			//get the options
			$heading = stripslashes(get_option('wpei_title'));
			$element = stripslashes(get_option('wpei_element'));
			$char = stripslashes(get_option('wpei_char'));
			$top = stripslashes(get_option('wpei_top'));
			$add = explode("\r\n",get_option('wpei_add'));
			$float = stripslashes(get_option('wpei_float'));
			
			//scan the text for heading elements
			preg_match_all('#\<'.$element.'>(.+?)\</'.$element.'>#si', $content, $matches, PREG_SET_ORDER);
			
			//build the index
			$index = '<div id="easy_index"';
			
			if(count($matches) > $float){
				$index .= ' class="easylargeindex" '; 
			}else{
				$index .= ' class="easysmallindex" ';
			}
			
			$index .= '>'.PHP_EOL.'<a id="index" name="index"></a><span class="easyindexhead">'.$heading.'</span>'.PHP_EOL.'<ol>'.PHP_EOL;
			
			//add the headings to the index
			foreach ($matches as $i=>$val) {
				$index .= "\t".'<li><a href="'.$_SERVER['REQUEST_URI'].'#'.nice_url($val[1]).'" title="'.$val[1].'">'.wp_break_maxchar($val[1]).'</a></li>'.PHP_EOL;
			}
			foreach($add as $val){
				if(!empty($val)){
					$index .= "\t".'<li><a href="'.$_SERVER['REQUEST_URI'].'#'.nice_url($val).'" title="'.$val.'">'.wp_break_maxchar($val).'</a></li>'.PHP_EOL;
				}
			}
			
			$index .= '</ol>'.PHP_EOL.'</div>'.PHP_EOL.PHP_EOL;
			
			function niceUrl($var){
				global $post;
				
				$top = stripslashes(get_option('wpei_top'));
				$element = stripslashes(get_option('wpei_element'));
				
				$link = strtolower(str_replace(' ', '-', $var[1]));
			
				return sprintf('<'.$element.'><a href="'.$_SERVER['REQUEST_URI'].'#'.$top.'" id="%s" name="%s">'.$var[1].'</a></'.$element.'>',$link,$var[1]);
			}
			
			$content = preg_replace_callback('#\<'.$element.'>(.+?)\</'.$element.'>#si', 'niceUrl', $content);

			//replace the index tag
			return str_replace('<!--index-->', $index, $content);	
		}
	}else{
		return $content;
	}
}

//add a button to the default editor
function wpei_add_wysiwyg(){
	
}

//add a optionspage to the settings list
function wpei_add_options_page(){
	add_options_page('Easy Index Options', 'Easy Index', 8, basename(__FILE__),'wpeasy_index_subpanel');
}

//create the optionpage
function wpeasy_index_subpanel(){
	load_plugin_textdomain('wpei',$path = $wpcf_path);
	$location = get_option('siteurl') . '/wp-admin/options-general.php?page=wp_easy_index.php'; // Form Action URI
	
	/*Lets add some default options if they don't exist*/
	add_option('wpei_title', __('Index', 'wpei'));
	add_option('wpei_element', __('h2', 'wpei'));
	add_option('wpei_char', __('35', 'wpei'));
	add_option('wpei_top', __('index', 'wpei'));
	add_option('wpei_add', __('Leave a Reply', 'wpei'));
	add_option('wpei_float', __('5', 'wpei'));
	
	/*check form submission and update options*/
	if ('process' == $_POST['stage']){
		update_option('wpei_title', $_POST['wpei_title']);
		update_option('wpei_element', $_POST['wpei_element']);
		update_option('wpei_char', $_POST['wpei_char']);
		update_option('wpei_top', $_POST['wpei_top']);
		update_option('wpei_add', $_POST['wpei_add']);
		update_option('wpei_float', $_POST['wpei_float']);
	}
	
	/*Get options for form fields*/
	$wpei_title = stripslashes(get_option('wpei_title'));
	$wpei_element = stripslashes(get_option('wpei_element'));
	$wpei_char = stripslashes(get_option('wpei_char'));
	$wpei_top = stripslashes(get_option('wpei_top'));
	$wpei_add = stripslashes(get_option('wpei_add'));
	$wpei_float = stripslashes(get_option('wpei_float'));
	?>
	
	<div class="wrap"> 
        <h2><?php _e('Easy Index Options', 'wpcf') ?></h2>
        <form name="form1" method="post" action="<?php echo $location ?>&amp;updated=true">
            <input type="hidden" name="stage" value="process" />
            <table class="form-table">
            <tr valign="top">
            <th scope="row"><label for="wpei_title">Index heading</label></th>
            <td><input name="wpei_title" type="text" id="wpei_title" value="<?php echo $wpei_title; ?>" class="regular-text code" /></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="wpei_element">Elements to add to index</label></th>
            <td><input name="wpei_element" type="text" id="wpei_element" value="<?php echo $wpei_element; ?>" size="40" class="regular-text code" /><br />
                <em>Specify the element. For example: h1, h2, h3 etc.</em></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="wpei_char"><?php _e('Shorten') ?></label></th>
            <td><input name="wpei_char" type="text" id="wpei_char" value="<?php echo $wpei_char; ?>" size="40" class="regular-text code" /><br />
                <em>Max characters to show in the index.</em></td>
            </tr>

            <tr valign="top">
            <th scope="row"><label for="wpei_char">Top anchor</label></th>
            <td><input name="wpei_top" type="text" id="wpei_top" value="<?php echo $wpei_top; ?>" size="40" class="regular-text code" /></td>
            </tr>

            <tr valign="top">
            <th scope="row"><label for="wpei_ignore">Add headings</label></th>
            <td><textarea name="wpei_add" id="wpei_add" rows="4" cols="50" class="regular-text code"><?php echo $wpei_add; ?></textarea><br />
                <em>Add each value of elements that have to be add on a new line.</em></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="wpei_float">Float</label></th>
            <td><input name="wpei_float" type="text" id="wpei_float" value="<?php echo $wpei_float; ?>" size="40" class="regular-text code" /></td>
                <em>If a index contains less then x items the index is aligned right.</em></td>
            </tr>

            </table>
            <p class="submit">
              <input type="submit" name="Submit" value="Update Options &raquo;" class="button-primary" />
            </p>
		</form>
	</div>
<?php
} //end wpcontactform_subpanel()

add_action('admin_head', 'wpei_header_admin');
add_action('admin_menu', 'wpei_add_options_page');

add_action('wp_head', 'wpei_header');
add_filter('the_content', 'wp_get_index', 7);
?>