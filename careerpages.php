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
	$templateini = array();
	$templateini["errors"] = array();
	
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
		
		function getTemplatedata() {
			global $templateini;

			if (empty($templateini["errors"])) {
				$templateini["templatedata"]["php"] = CareerpagesMain::webItemExists(plugins_url('templates/'.$templateini["template"].'/php/careerpagestemplategui.php' , __FILE__ ), array());
				$templateini["templatedata"]["js"] = CareerpagesMain::webItemExists(plugins_url('templates/'.$templateini["template"].'/js/careerpagestemplate.js' , __FILE__ ), array());
				$templateini["templatedata"]["css"] = CareerpagesMain::webItemExists($templateini["css"] ? $templateini["css"] : plugins_url('templates/'.$templateini["template"].'/css/careerpagestemplatedefault.css' , __FILE__ ), array());

				if ($templateini["templatedata"]["php"]["status"] && $templateini["templatedata"]["js"]["status"] && $templateini["templatedata"]["css"]["status"]) {
					$templateini["local"] = 1;
					$templateini["templateurl"] = plugins_url('templates/'.$templateini["template"].'/' , __FILE__ );
				} else {
					$file_headers = @get_headers('https://'.($templateini["subdir"] ? $templateini["subdir"].'.' : '') .'prodii.com/common/careerpages/templates/'.$templateini["template"].'/php/careerpagestemplategui.php');
					$templateini["remote"]["status"] = strrpos($file_headers[0], ' 404 Not Found') === false;
					if ($templateini["remote"]["status"]) {
						$templateini["local"] = 0;
						$templateini["templateurl"] = 'https://'.($templateini["subdir"] ? $templateini["subdir"].'.' : '').'prodii.com/common/careerpages/templates/'.$templateini["template"].'/';
						$templateini["templatepath"] = '/common/careerpages/templates/'.$templateini["template"].'/';
					} else {
						$templateini["errors"][] = 'We cannot find the template you are asking for. The '.$templateini["template"].' template is not locally in your company-presentation plugin nor on the Prodii server.';
					}
				}
			}

			if (empty($templateini["errors"])) {
				// Get template ini
				if ($templateini["local"]) {
					require_once($templateini["templateurl"].'php/careerpagestemplategui.php');
					$templateini["ini"]["styles"] = CareerpagesTemplateGui::getStyles();
					$templateini["ini"]["scripts"] = CareerpagesTemplateGui::getScripts();
					$templateini["ini"]["images"] = CareerpagesTemplateGui::getImages();
				} else {
					$cp_info = array(
						'action' => 'getIni',
						'key' => $templateini["key"],
						'templatepath' => $templateini["templatepath"]
					);
					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_URL, 'https://'.(isset($templateini["subdir"]) && $templateini["subdir"] ? $templateini["subdir"].'.' : '').'prodii.com/common/careerpages/php/careerpageshandler.php'); 
					curl_setopt($ch, CURLOPT_POST, count($cp_info));
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_info));
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					$templateini["ini"] = json_decode(curl_exec($ch), true);
					if($errno = curl_errno($ch)) {
						$templateini["errors"][] = "Get template initialisation cURL error ({$errno}):\n {$error_message}";
					} elseif ($templateini["ini"] == '') {
						$templateini["errors"][] = "No template initialisation returned from cURL";
					}
					curl_close($ch);
				}
			}

			if (empty($templateini["errors"])) {
				//Get data(Template on local server) or html(Template on Prodii server)
				$cp_data = array(
					'action' => 'get'.$templateini["level"].($templateini["local"] ? 'Data' : 'Html'),
					'key' => $templateini["key"],
					strtolower($templateini["level"]) => $templateini["ids"],
					'template' => $templateini["template"],
					'templatepath' => $templateini["templatepath"],
					'local' => $templateini["local"],
					'subdir' => $templateini["subdir"],
					'css' => isset($css) && $css ? $css : ''
				);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://'.(isset($templateini["subdir"]) && $templateini["subdir"] ? $templateini["subdir"].'.' : '').'prodii.com/common/careerpages/php/careerpageshandler.php'); 
				curl_setopt($ch, CURLOPT_POST, count($cp_data));
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_data));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				$response = json_decode(curl_exec($ch), true);
				if($errno = curl_errno($ch)) {
					$templateini["errors"][] = "Get data or html cURL error ({$errno}):\n {$error_message}";
				} elseif ($response == '') {
					$templateini["errors"][] = "No data or html returned from cURL";
				}
				curl_close($ch);
			}
			
			if (empty($templateini["errors"])) {
				if ($templateini["local"]) {
					switch ($atts["level"]) {
						case "Teams":
							$templateini["gui"] = CareerpagesTemplateGui::getTeamsGui($response);
							break;
						case "Team":
							$templateini["gui"] = CareerpagesTemplateGui::getTeamGui($response);
							break;
						case "Profile":
							$templateini["gui"] = CareerpagesTemplateGui::getProfileGui($response);
							break;
					}						
				} else {
					$templateini["gui"] = $response;
				}
			}
		}
		
		function conditionally_add_scripts_and_styles($posts){
			global $templateini;

			$error = array();
			
			if (empty($posts)) return $posts;
			
			foreach ($posts as $post) {
				$subdir = null;
				if (stripos($post->post_content, '[careerpages') !== false) {
					// Key
					if (stripos($post->post_content, 'key="') !== false) {
						$startpos = stripos($post->post_content, 'key="') + 5;
						$templateini["key"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					} else {
						$templateini["errors"][] = 'Key missing in shortcode';
					}
					
					// Template
					if (stripos($post->post_content, 'subdir="') !== false) {
						$startpos = stripos($post->post_content, 'template="') + 10;
						$templateini["template"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					} else {
						$templateini["errors"][] = 'Template missing in shortcode';
					}
					
					// Subdir
					$subdir = '';
					if (stripos($post->post_content, 'subdir="') !== false) {
						$startpos = stripos($post->post_content, 'subdir="') + 8;
						$templateini["subdir"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					}
					
					// Css
					if (stripos($post->post_content, 'css="') !== false) {
						$startpos = stripos($post->post_content, 'css="') + 5;
						$templateini["css"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					} else {
						$templateini["css"] = '';
					}
					
					// Level
					if (stripos($post->post_content, 'level="') !== false) {
						$startpos = stripos($post->post_content, 'level="') + 7;
						$templateini["level"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					} else {
						$templateini["errors"][] = 'Level missing in shortcode';
					}
					
					// Ids
					if (stripos($post->post_content, 'ids="') !== false) {
						$startpos = stripos($post->post_content, 'ids="') + 5;
						$templateini["ids"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					} else {
						$templateini["errors"][] = 'Ids missing in shortcode';
					}
					
					CareerpagesMain::getTemplatedata();
					
					if (!$templateini["errors"]) {
						// plugin specific files from plugin, IE10 viewport hack for Surface/desktop Windows 8 bug
						wp_register_script('careerpages_viewportbug', plugins_url('js/ie10-viewport-bug-workaround.js' , __FILE__ ), array(), '1.0');
						
						// plugin specific external files
						wp_register_script('careerpages_googlemap_infobox', plugins_url('js/infobox.js' , __FILE__ ), array(), '1.0');
						wp_register_script('careerpages_googlemap_places', 'https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places&amp;language=en', false, '3');

						// plugin specific files from Prodii server
						//wp_register_script('careerpages_ellipsis', 'https://'.($subdir ? $subdir.'.' : '').'prodii.com/assets/js/jquery.ellipsis.min.js', array(), '1.0');
						wp_register_script('careerpages_script', 'https://'.($subdir ? $subdir.'.' : '').'prodii.com/common/careerpages/js/careerpages.js', array(), '1.0');
						wp_register_script('careerpages_library', 'https://'.($subdir ? $subdir.'.' : '').'prodii.com/assets/js/library.js', array(), '1.0');
						
						foreach ($templateini["ini"]["styles"] as $name => $url) {
							wp_register_style($name, $templateini["templateurl"].$url);
						}
						foreach ($templateini["ini"]["scripts"] as $name => $url) {
							wp_register_script($name, $templateini["templateurl"].$url, array(), '1.0');
						}
						//wp_register_script('careerpages_template_script', $templateini["templateurl"].'js/careerpagestemplate.js', array(), '1.0');
						//wp_register_script('careerpages_awesomecloud', $templateini["templateurl"].'js/jquery.awesomeCloud-0.2.min.js', array(), '1.0');
						//wp_register_script('careerpages_googlemap_infobox', $templateini["templateurl"].'js/infobox.js', array(), '1.0');
						//wp_register_style('careerpages_awesomefonts', $templateini["templateurl"].'css/font-awesome.min.css');
						wp_register_style('careerpages_style', $templateini["css"] ? $templateini["css"] : $templateini["templateurl"].'css/careerpagestemplatedefault.css');
					}
					
					break;
				}
			}
		 
			return $posts;
		}
		
		function addHeaderCode() {
			global $templateini;
			
			if (!$templateini["errors"]) {
				if (function_exists('wp_enqueue_script')) {
					echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
					echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

					foreach ($templateini["ini"]["styles"] as $name => $url) {
						wp_enqueue_style($name);
					}

					//wp_enqueue_style('careerpages_awesomefonts');
					//wp_enqueue_style('careerpages_style');

					wp_enqueue_script('careerpages_viewportbug');
					echo	'
								<!--[if lt IE 9]>
									<script type="text/javascript" src="'.plugins_url('js/html5shiv.js' , __FILE__ ).'"></script>
									<script type="text/javascript" src="'.plugins_url('js/respond.min.js' , __FILE__ ).'"></script>
								<![endif]-->
								';

					foreach ($templateini["ini"]["scripts"] as $name => $url) {
						wp_enqueue_script($name);
					}

					//wp_enqueue_script('careerpages_awesomecloud');
					//wp_enqueue_script('careerpages_template_script');
					wp_enqueue_script('careerpages_googlemap_infobox');
					//wp_enqueue_script('careerpages_ellipsis');
					wp_enqueue_script('careerpages_script');
					wp_enqueue_script('careerpages_library');
					wp_enqueue_script('careerpages_googlemap_places');
				}
			}
		}

		function addContent($content = '') {
		}
		
		function careerpages_shortcut($atts) {
			global $templateini;

			if (empty($templateini["errors"])) {
				$content = 	'
										<input id="handler" type="hidden" value="'.plugins_url('php/careerpagespluginhandler.php' , __FILE__ ).'"/>
										<input id="local" type="hidden" value="'.(isset($templateini["local"]) ? $templateini["local"] : "").'"/>
										<input id="subdir" type="hidden" value="'.(isset($atts["subdir"]) && $atts["subdir"] ? $atts["subdir"] : "").'"/>
										<input id="template" type="hidden" value="'.$atts["template"].'"/>
										<input id="key" type="hidden" value="'.$atts["key"].'"/>
										<input id="teamids" type="hidden" value="'.($atts["level"] == "Teams" ? $atts["ids"] : "0").'"/>
										<input id="teamid" type="hidden" value="'.($atts["level"] == "Team" ? $atts["ids"] : "0").'"/>
										<input id="profileid" type="hidden" value="'.($atts["level"] == "Profile" ? $atts["ids"] : "0").'"/>
										'.(isset($atts["css"]) && $atts["css"] ? '<input id="css" type="hidden" value="'.$atts["css"].'"/>' : '<input id="css" type="hidden" value="careerpagestemplatedefault.css"/>').'
										<div id="careerpagescontent" class="prd-container">'.$templateini["gui"].'</div>
										';
			} else {
				$errors = '';
				foreach ($templateini["errors"] as $index => $error) {
					$errors .= ($index + 1).')&nbsp;'.$error.'<br>';
				}
				$content = $errors;
			}
			
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