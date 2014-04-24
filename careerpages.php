<?php
/**
 * @package careerpages
 */
/*
Plugin Name: Careerpages
Plugin URI: http://prodii.com/wordpress/plugins/careerpages.php
Description: The ultimative easiest way to present you company.
Version: 1.0.0
Author: Prodii by Ralph Rezende Larsen
Author URI: http://prodii.com/view/Ralph+Rezende+Larsen
License:
*/
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

if (!class_exists("CareerpagesMain")) {
	class CareerpagesMain {
		function CareerpagesMain() {
		}

		function conditionally_add_scripts_and_styles($posts){
			if (empty($posts)) return $posts;
			foreach ($posts as $post) {
				$subdir = null;
				if (stripos($post->post_content, '[careerpages') !== false) {
					wp_register_style('careerpages_bootstrap', plugins_url('css/bootstrap.min.css' , __FILE__ ));
					wp_register_style('careerpages_bootstrap_theme', plugins_url('css/bootstrap-theme.min.css' , __FILE__ ));
					//wp_register_style('careerpages_bootstrap', plugins_url('css/bootstrap-namespace.css' , __FILE__ ));
					wp_register_script('careerpages_googlemap_infobox', plugins_url('js/infobox.js' , __FILE__ ), array(), '1.0');
					wp_register_script('careerpages_googlemap_places', 'https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places&amp;language=en', false, '3');
					if (stripos($post->post_content, 'subdir="') !== false) {
						$startpos = stripos($post->post_content, 'subdir="') + 8;
						$subdir = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					}
					if (stripos($post->post_content, 'css="') !== false) {
						$startpos = stripos($post->post_content, 'css="') + 5;
						$css = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
						wp_register_style('careerpages_style', $css);

					} else {
						$startpos = stripos($post->post_content, 'template="') + 10;
						$template = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
						wp_register_style('careerpages_style', 'http://'.($subdir ? $subdir."." : "").'prodii.com/careerpages/templates/template'.$template.'/careerpagestemplatedefault.css');
					}
					wp_register_script('careerpages_expander', plugins_url('js/jquery.expander.js' , __FILE__ ), array('jquery-ui-core'), '1.0');
					wp_register_script('careerpages_awesomecloud', plugins_url('js/jquery.awesomeCloud-0.2.min.js' , __FILE__ ), array(), '1.0');
					wp_register_script('careerpages_bootstrap', plugins_url('js/bootstrap.min.js' , __FILE__ ), array(), '1.0');
					wp_register_script('careerpages_library', 'http://'.($subdir ? $subdir."." : "").'prodii.com/common/library.js', array(), '1.0');
					wp_register_script('careerpages_script', 'http://'.($subdir ? $subdir."." : "").'prodii.com/careerpages/wordpress_plugin/js/careerpagesplugin.js', array(), '1.0');
					wp_register_script('careerpages_smti', 'http://'.($subdir ? $subdir."." : "").'prodii.com/smti/smti.js', array(), '1.0');
					wp_register_script('careerpages_followmeon', 'http://'.($subdir ? $subdir."." : "").'prodii.com/followmeon/followmeon.js', array(), '1.0');
					wp_register_script('careerpages_positions', 'http://'.($subdir ? $subdir."." : "").'prodii.com/positions/positions.js', array(), '1.0');
					wp_register_script('careerpages_geo', 'http://'.($subdir ? $subdir."." : "").'prodii.com/geo/geo.js', array(), '1.0');

					break;
				}
			}
		 
			return $posts;
		}

		function addHeaderCode() {
			if (function_exists('wp_enqueue_script')) {
				echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
				echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

				wp_enqueue_style('careerpages_bootstrap');
				wp_enqueue_style('careerpages_bootstrap_theme');
				wp_enqueue_style('careerpages_style');

				wp_enqueue_script('careerpages_expander');
				wp_enqueue_script('careerpages_awesomecloud');
				wp_enqueue_script('careerpages_bootstrap');
				echo	'
						<!--[if lt IE 9]>
							<script type="text/javascript" src="'.plugins_url('js/html5shiv.js' , __FILE__ ).'"></script>
							<script type="text/javascript" src="'.plugins_url('js/respond.min.js' , __FILE__ ).'"></script>
						<![endif]-->
						';
				wp_enqueue_script('careerpages_script');
				wp_enqueue_script('careerpages_library');
				wp_enqueue_script('careerpages_smti');
				wp_enqueue_script('careerpages_followmeon');
				wp_enqueue_script('careerpages_positions');
				wp_enqueue_script('careerpages_googlemap_places');
				wp_enqueue_script('careerpages_googlemap_infobox');
				wp_enqueue_script('careerpages_geo');
			}
		}

		function addContent($content = '') {
		}

		function careerpages_shortcut($atts) {
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, "http://".(isset($atts["subdir"]) ? $atts["subdir"]."." : "")."prodii.com/CareerPages/".$atts["level"]."/Html/".$atts["template"]."/".$atts["key"]."/".$atts["ids"]); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			$output = json_decode(curl_exec($ch)); 
			curl_close($ch);

			$content = 	'
						<input id="subdir" type="hidden" value="'.(isset($atts["subdir"]) ? $atts["subdir"] : "").'"/>
						<input id="template" type="hidden" value="'.$atts["template"].'"/>
						<input id="teamids" type="hidden" value="'.($atts["level"] == "Teams" ? $atts["ids"] : "0").'"/>
						<input id="teamid" type="hidden" value="'.($atts["level"] == "Team" ? $atts["ids"] : "0").'"/>
						<input id="profileid" type="hidden" value="'.($atts["level"] == "Profile" ? $atts["ids"] : "0").'"/>
						'.(isset($atts["css"]) && $atts["css"] ? '<input id="css" type="hidden" value="'.$atts["css"].'"/>' : '<input id="css" type="hidden" value="careerpagestemplatedefault.css"/>').'
						<div id="careerpagescontent" class="careerpagescontent container">'.$output.'</div>
						';
			
			return $content;
		}
	}
}

if (class_exists("CareerpagesMain")) {
	$careerpagesMain = new CareerpagesMain();
}

if (isset($careerpagesMain)) {
	// the_posts gets triggered before wp_head
	add_filter('the_posts', array(&$careerpagesMain, 'conditionally_add_scripts_and_styles'), 1);

	//add_action('wp_head', array(&$careerpagesMain, 'addHeaderCode'), 15);
	add_action('wp_enqueue_scripts', array(&$careerpagesMain, 'addHeaderCode'), 111115);
	
	add_shortcode('careerpages', array('careerpagesMain', 'careerpages_shortcut'));
}

?>