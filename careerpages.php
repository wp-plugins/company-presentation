<?php

/**
 * @package careerpages
 */
/*
Plugin Name: Careerpages
Plugin URI: https://prodii.com/WpPluginInfo
Description: The ultimative easiest way to present you company.
Version: 3.0.0
Author: Prodii by Ralph Rezende Larsen
Author URI: https://prodii.com/view/Ralph+Rezende+Larsen
License:
*/

if (!class_exists("CareerpagesMain")) {
	$templatefiles = array();
	$cp_fix = array();
	
	class CareerpagesMain {
		function CareerpagesMain() {
		}
		
		function webItemExists($url) {
			$response = wp_remote_head($url, array());
			$accepted_status_codes = array(200, 301, 302);
			if (!is_wp_error($response) && in_array(wp_remote_retrieve_response_code($response), $accepted_status_codes)) {
				return array("file" => $url, "status" => true);
			}
			return array("file" => $url, "status" => false);
		}
		
		function templateEvaluate($template, $css, $subdir) {
			$output = array();
			
			$output["php"] = CareerpagesMain::webItemExists(plugins_url('templates/'.$template.'/careerpagestemplategui.php' , __FILE__ ), array());
			$output["js"] = CareerpagesMain::webItemExists(plugins_url('templates/'.$template.'/careerpagestemplate.js' , __FILE__ ), array());
			$output["css"] = CareerpagesMain::webItemExists($css, array());

			$output["error"] = 0;
			if ($output["php"]["status"] && $output["js"]["status"] && $output["css"]["status"]) {
				$output["local"] = 1;
				$output["templateurl"] = plugins_url('templates/'.$template.'/' , __FILE__ );
			} else {
				$file_headers = @get_headers('https://'.$subdir.'prodii.com/common/careerpages/templates/'.$template.'/careerpagestemplategui.php');
				$output["remote"]["status"] = strrpos($file_headers[0], ' 404 Not Found') === false;
				if ($output["remote"]["status"]) {
					$output["local"] = 0;
					$output["templateurl"] = 'https://'.$subdir.'prodii.com/common/careerpages/templates/'.$template.'/';
				} else {
					$output["error"] = 'The template '.$template.' is not locally in your company-presentation plugin nor on the Prodii server.';
				}
			}
		
			return $output;
		}
		
		function conditionally_add_scripts_and_styles($posts){
			global $templatefiles;
		
			if (empty($posts)) return $posts;
			
			foreach ($posts as $post) {
				$subdir = null;
				if (stripos($post->post_content, '[careerpages') !== false) {
					// Template
					$startpos = stripos($post->post_content, 'template="') + 10;
					$template = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					
					// Subdir
					$subdir = '';
					if (stripos($post->post_content, 'subdir="') !== false) {
						$startpos = stripos($post->post_content, 'subdir="') + 8;
						$subdir = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos).'.';
					}
					
					// Css
					if (stripos($post->post_content, 'css="') !== false) {
						$startpos = stripos($post->post_content, 'css="') + 5;
						$cssurl = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					} else {
						$cssurl = plugins_url('templates/'.$template.'/careerpagestemplatedefault.css' , __FILE__ );
					}
					
					$templatefiles = CareerpagesMain::templateEvaluate($template, $cssurl, $subdir);
					
					if (!$templatefiles["error"]) {
						// IE10 viewport hack for Surface/desktop Windows 8 bug
						wp_register_script('careerpages_viewportbug', plugins_url('js/ie10-viewport-bug-workaround.js' , __FILE__ ), array(), '1.0');

						wp_register_style('careerpages_awesomefonts', plugins_url('css/font-awesome.min.css' , __FILE__ ));
						wp_register_script('careerpages_googlemap_infobox', plugins_url('js/infobox.js' , __FILE__ ), array(), '1.0');
						wp_register_script('careerpages_googlemap_places', 'https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places&amp;language=en', false, '3');

						wp_register_script('careerpages_ellipsis', 'https://'.$subdir.'prodii.com/assets/js/jquery.ellipsis.min.js', array(), '1.0');
						wp_register_script('careerpages_awesomecloud', plugins_url('js/jquery.awesomeCloud-0.2.min.js' , __FILE__ ), array(), '1.0');
						wp_register_script('careerpages_script', 'https://'.$subdir.'prodii.com/common/careerpages/js/careerpages.js', array(), '1.0');
						wp_register_script('careerpages_library', 'https://'.$subdir.'prodii.com/assets/js/library.js', array(), '1.0');

						wp_register_script('careerpages_template_script', $templatefiles["templateurl"].'careerpagestemplate.js', array(), '1.0');
						wp_register_style('careerpages_style', $templatefiles["templateurl"].'careerpagestemplatedefault.css');
					}
					
					break;
				}
			}
		 
			return $posts;
		}
		
		function addHeaderCode() {
			if (function_exists('wp_enqueue_script')) {
				echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
				echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

				wp_enqueue_style('careerpages_awesomefonts');
				wp_enqueue_style('careerpages_style');

				wp_enqueue_script('careerpages_viewportbug');
				echo	'
							<!--[if lt IE 9]>
								<script type="text/javascript" src="'.plugins_url('js/html5shiv.js' , __FILE__ ).'"></script>
								<script type="text/javascript" src="'.plugins_url('js/respond.min.js' , __FILE__ ).'"></script>
							<![endif]-->
							';
				wp_enqueue_script('careerpages_ellipsis');
				wp_enqueue_script('careerpages_awesomecloud');
				wp_enqueue_script('careerpages_script');
				wp_enqueue_script('careerpages_template_script');
				wp_enqueue_script('careerpages_library');
				wp_enqueue_script('careerpages_googlemap_places');
				wp_enqueue_script('careerpages_googlemap_infobox');
			}
		}

		function addContent($content = '') {
		}
		
		function careerpages_shortcut($atts) {
			global $templatefiles;
			global $cp_fix;

			if ($templatefiles["error"]) {
				$output = $templatefiles["error"];
			} else {
				//set POST variables
				$cp_fix = array(
					'action' => 'get'.$atts["level"].($templatefiles["local"] ? 'Data' : 'Html'),
					'key' => $atts["key"],
					strtolower($atts["level"]) => $atts["ids"],
					'template' => $atts["template"],
					'local' => $templatefiles["local"],
					'subdir' => $atts["subdir"],
					'css' => isset($atts["css"]) && $atts["css"] ? $atts["css"] : ''
				);

				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, 'https://'.($atts["subdir"] ? $atts["subdir"].'.' : '').'prodii.com/common/careerpages/careerpageshandler.php'); 
				curl_setopt($ch, CURLOPT_POST, count($cp_fix));
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_fix));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				$response = curl_exec($ch);

				if($errno = curl_errno($ch)) {
					$output = "cURL error ({$errno}):\n {$error_message}";
				} else {
					if ($templatefiles["local"]) {
						require_once(__DIR__.'/templates/'.$cp_fix["template"].'/careerpagestemplategui.php');
						//require_once(__DIR__.'/php/careerpageslibrary.php');
						
						// Adding plugin url for further use when retrieving images
						$cp_fix["localpluginurl"] = plugins_url('' , __FILE__ ).'/';
						$cp_fix["companyimgplaceholder"] = 'image-placeholder900x600.png';
						$cp_fix["teamimgplaceholder"] = 'image-placeholder900x600.png';
						$cp_fix["profileimgplaceholder"] = 'image-placeholder310x310.png';
						switch ($atts["level"]) {
							case "Teams":
								$output = CareerpagesTemplateGui::getTeamsGui(json_decode($response, true));
								break;
							case "Team":
								$output = CareerpagesTemplateGui::getTeamGui(json_decode($response, true));
								break;
							case "Profile":
								$output = CareerpagesTemplateGui::getProfileGui(json_decode($response, true));
								break;
						}						
					} else {
						$output = json_decode($response);
					}
				}
				
				curl_close($ch);
			}

			$content = 	'
									<input id="handler" type="hidden" value="'.plugins_url('php/careerpagespluginhandler.php' , __FILE__ ).'"/>
									<input id="local" type="hidden" value="'.(isset($templatefiles["local"]) ? $templatefiles["local"] : "").'"/>
									<input id="subdir" type="hidden" value="'.(isset($atts["subdir"]) ? $atts["subdir"] : "").'"/>
									<input id="template" type="hidden" value="'.$atts["template"].'"/>
									<input id="teamids" type="hidden" value="'.($atts["level"] == "Teams" ? $atts["ids"] : "0").'"/>
									<input id="teamid" type="hidden" value="'.($atts["level"] == "Team" ? $atts["ids"] : "0").'"/>
									<input id="profileid" type="hidden" value="'.($atts["level"] == "Profile" ? $atts["ids"] : "0").'"/>
									'.(isset($atts["css"]) && $atts["css"] ? '<input id="css" type="hidden" value="'.$atts["css"].'"/>' : '<input id="css" type="hidden" value="careerpagestemplatedefault.css"/>').'
									<div id="careerpagescontent" class="prd-container">'.$output.'</div>
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
	add_action('wp_enqueue_scripts', array(&$careerpagesMain, 'addHeaderCode'), 111115);
	add_shortcode('careerpages', array('careerpagesMain', 'careerpages_shortcut'));
}

?>