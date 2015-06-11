<?php

/**
 * @package careerpages
 */
/*
Plugin Name: Careerpages, 
Plugin URI: https://prodii.com/WpPluginInfo
Description: The ultimative easiest way to present your company.
Version: 3.0.3
Author: Prodii by Ralph Rezende Larsen
Author URI: https://prodii.com/view/ralphrezendelarsen
License:
*/

if (!class_exists("CareerpagesMain")) {

	class CareerpagesMain {
		public static $templateini;

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
			if (empty(self::$templateini["errors"])) {
				self::$templateini["templatedata"]["php"] = CareerpagesMain::webItemExists(plugins_url('templates/'.self::$templateini["template"].'/php/careerpagestemplategui.php' , __FILE__ ), array());
				//self::$templateini["templatedata"]["js"] = CareerpagesMain::webItemExists(plugins_url('templates/'.self::$templateini["template"].'/js/careerpagestemplate.js' , __FILE__ ), array());
				//self::$templateini["templatedata"]["css"] = CareerpagesMain::webItemExists(self::$templateini["css"] ? self::$templateini["css"] : plugins_url('templates/'.self::$templateini["template"].'/css/careerpagestemplatedefault.css' , __FILE__ ), array());
				self::$templateini["pluginurl"] = plugins_url('', __FILE__).'/';
				self::$templateini["pluginpath"] = plugin_dir_path(__FILE__);
				//if (self::$templateini["templatedata"]["php"]["status"] && self::$templateini["templatedata"]["js"]["status"] && self::$templateini["templatedata"]["css"]["status"]) {
				if (self::$templateini["templatedata"]["php"]["status"]) {
					self::$templateini["local"] = 1;
					self::$templateini["templateurl"] = plugins_url('templates/'.self::$templateini["urlencodedtemplate"].'/' , __FILE__ );
					self::$templateini["templatepath"] = plugin_dir_path(__FILE__).'templates/'.self::$templateini["template"].'/';
				} else {
					$file_headers = @get_headers('https://'.(self::$templateini["subdir"] ? self::$templateini["subdir"].'.' : '') .'prodii.com/common/careerpages/templates/'.self::$templateini["template"].'/php/careerpagestemplategui.php');
					self::$templateini["remote"]["status"] = strrpos($file_headers[0], ' 404 Not Found') === false;
					if (self::$templateini["remote"]["status"]) {
						self::$templateini["local"] = 0;
						self::$templateini["templateurl"] = 'https://'.(self::$templateini["subdir"] ? self::$templateini["subdir"].'.' : '').'prodii.com/common/careerpages/templates/'.self::$templateini["urlencodedtemplate"].'/';
						self::$templateini["templatepath"] = '';
					} else {
						self::$templateini["errors"][] = 'We cannot find the template you are asking for. The '.self::$templateini["template"].' template is not locally in your company-presentation plugin nor on the Prodii server.';
					}
				}
			}

			if (empty(self::$templateini["errors"])) {
				require_once(self::$templateini["pluginpath"].'/php/careerpagespluginlibrary.php');
				
				// Get template ini
				if (self::$templateini["local"]) {
					require_once(self::$templateini["templatepath"].'php/careerpagestemplategui.php');
					self::$templateini["ini"] = CareerpagesTemplateGui::getIni();
				} else {
					$cp_info = array(
						'action' => 'getIni',
						'key' => self::$templateini["key"],
						'template' => self::$templateini["template"]
					);
					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_URL, 'https://'.(isset(self::$templateini["subdir"]) && self::$templateini["subdir"] ? self::$templateini["subdir"].'.' : '').'prodii.com/common/careerpages/php/careerpageshandler.php'); 
					curl_setopt($ch, CURLOPT_POST, count($cp_info));
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_info));
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					self::$templateini["ini"] = json_decode(curl_exec($ch), true);
					if($errno = curl_errno($ch)) {
						self::$templateini["errors"][] = "Get template initialisation cURL error ({$errno}):\n {$error_message}";
					} elseif (self::$templateini["ini"] == '') {
						self::$templateini["errors"][] = "No template initialisation returned from cURL";
					}
					curl_close($ch);
				}
			}

			if (empty(self::$templateini["errors"])) {
				//Get data(Template on local server) or html(Template on Prodii server)
				$cp_data = array(
					'action' => 'get'.self::$templateini["level"].(self::$templateini["local"] ? 'Data' : 'Html'),
					'key' => self::$templateini["key"],
					strtolower(self::$templateini["level"]) => self::$templateini["ids"],
					'template' => self::$templateini["template"],
					'local' => self::$templateini["local"],
					'subdir' => self::$templateini["subdir"],
					'css' => isset($css) && $css ? $css : ''
				);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://'.(isset(self::$templateini["subdir"]) && self::$templateini["subdir"] ? self::$templateini["subdir"].'.' : '').'prodii.com/common/careerpages/php/careerpageshandler.php'); 
				curl_setopt($ch, CURLOPT_POST, count($cp_data));
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_data));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				$response = json_decode(curl_exec($ch), true);
				if($errno = curl_errno($ch)) {
					self::$templateini["errors"][] = 'Get '.(self::$templateini["local"] ? 'data' : 'html').' cURL error ({$errno}):\n {$error_message}';
				} elseif ($response == '') {
					self::$templateini["errors"][] = 'No '.(self::$templateini["local"] ? 'data' : 'html').' returned from cURL';
				}
				curl_close($ch);
			}

			if (empty(self::$templateini["errors"])) {
				//Set server data
				$cp_data = array(
					'action' => 'setLogistics',
					'level' => self::$templateini["level"],
					'ids' => self::$templateini["ids"],
					'method' => 'wpplugin',
					'template' => self::$templateini["template"],
					'key' => self::$templateini["key"],
					'clientip' => CareerpagesLibrary::get_client_ip(),
					'server' => json_encode($_SERVER)
				);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://'.(isset(self::$templateini["subdir"]) && self::$templateini["subdir"] ? self::$templateini["subdir"].'.' : '').'prodii.com/common/careerpages/php/careerpageshandler.php'); 
				curl_setopt($ch, CURLOPT_POST, count($cp_data));
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_data));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_exec($ch);
				curl_close($ch);
			}
			
			if (empty(self::$templateini["errors"])) {
				if (self::$templateini["local"]) {
					switch (self::$templateini["level"]) {
						case "Teams":
							self::$templateini["gui"] = CareerpagesTemplateGui::getTeamsGui($response);
							break;
						case "Team":
							self::$templateini["gui"] = CareerpagesTemplateGui::getTeamGui($response);
							break;
						case "Profile":
							self::$templateini["gui"] = CareerpagesTemplateGui::getProfileGui($response);
							break;
					}						
				} else {
					self::$templateini["gui"] = $response;
				}
			}
		}
		
		function conditionally_add_scripts_and_styles($posts){
			self::$templateini = array();
			self::$templateini["errors"] = array();

			$error = array();
			
			if (empty($posts)) return $posts;

			$shortcode_found = false;
			foreach ($posts as $post) {
				if (stripos($post->post_content, '[careerpages ') !== false) {
					if ($post->post_type == 'page') {
						$shortcode_found = true;
						break;
					} else {
						$post->post_content = 'Sorry but Career pages only works with pages';
					}
				}
			}
			
			if ($shortcode_found) {
				// Key
				if (stripos($post->post_content, ' key="') !== false) {
					$startpos = stripos($post->post_content, ' key="') + 6;
					self::$templateini["key"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
				} else {
					self::$templateini["errors"][] = 'Key missing or Key is misspelled in shortcode';
				}
				
				// Template
				if (stripos($post->post_content, ' template="') !== false) {
					$startpos = stripos($post->post_content, ' template="') + 11;
					self::$templateini["template"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
					self::$templateini["urlencodedtemplate"] = rawurlencode(substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos));
				} else {
					self::$templateini["template"]= 'copenhagen';
					self::$templateini["urlencodedtemplate"]= 'copenhagen';
				}
				
				// Subdir
				$subdir = '';
				if (stripos($post->post_content, ' subdir="') !== false) {
					$startpos = stripos($post->post_content, ' subdir="') + 9;
					self::$templateini["subdir"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
				} else {
					self::$templateini["subdir"] = '';
				}
				
				// Css
				if (stripos($post->post_content, ' css="') !== false) {
					$startpos = stripos($post->post_content, ' css="') + 6;
					self::$templateini["css"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
				} else {
					self::$templateini["css"] = '';
				}
				
				// Level
				if (stripos($post->post_content, ' level="') !== false) {
					$startpos = stripos($post->post_content, ' level="') + 8;
					self::$templateini["level"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
				} else {
					self::$templateini["errors"][] = 'Level missing or Level is misspelled in shortcode';
				}
				
				// Ids
				if (stripos($post->post_content, ' ids="') !== false) {
					$startpos = stripos($post->post_content, ' ids="') + 6;
					self::$templateini["ids"] = substr($post->post_content, $startpos, stripos($post->post_content, '"', $startpos) - $startpos);
				} else {
					self::$templateini["errors"][] = 'Ids missing or Ids is misspelled in shortcode';
				}

				CareerpagesMain::getTemplatedata();
				if (empty(self::$templateini["errors"])) {
					foreach (self::$templateini["ini"]["styles"] as $name => $url) {
						wp_register_style($name, self::$templateini["templateurl"].$url);
					}
					foreach (self::$templateini["ini"]["scripts"] as $name => $url) {
						wp_register_script($name, self::$templateini["templateurl"].$url);
					}
				}

				// plugin specific files from plugin, IE10 viewport hack for Surface/desktop Windows 8 bug
				wp_register_script('careerpages_viewportbug', plugins_url('js/ie10-viewport-bug-workaround.js' , __FILE__ ));
				
				wp_register_script('careerpages_googlemap_places', 'https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places&amp;language=en', false, '3');
				wp_register_script('careerpages_script', plugins_url('js/careerpages.js' , __FILE__ ));
				wp_register_script('careerpages_library', plugins_url('js/library.js' , __FILE__ ));
			}
			
			return $posts;
		}
		
		function addHeaderCode() {
			if (empty(self::$templateini["errors"])) {
				if (function_exists('wp_enqueue_script')) {
					echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
					echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

					// plugin specific files from plugin, IE10 viewport hack for Surface/desktop Windows 8 bug
					wp_enqueue_script('careerpages_viewportbug');

					echo	'
								<!--[if lt IE 9]>
									<script type="text/javascript" src="'.plugins_url('js/html5shiv.js' , __FILE__ ).'"></script>
									<script type="text/javascript" src="'.plugins_url('js/respond.min.js' , __FILE__ ).'"></script>
								<![endif]-->
								';
					if (!empty(self::$templateini["ini"]["styles"])) {
						foreach (self::$templateini["ini"]["styles"] as $name => $url) {
							wp_enqueue_style($name);
						}
					}
					if (!empty(self::$templateini["ini"]["scripts"])) {
						foreach (self::$templateini["ini"]["scripts"] as $name => $url) {
							wp_enqueue_script($name);
						}
					}
					wp_enqueue_script('careerpages_script');
					wp_enqueue_script('careerpages_library');
					wp_enqueue_script('careerpages_googlemap_places');
				}
			}
		}

		function addContent($content = '') {
		}
		
		static function careerpages_shortcut($atts) {
			if (empty(self::$templateini["errors"])) {
				$content = 	'
										<input id="handler" type="hidden" value="'.plugins_url('php/careerpagespluginhandler.php' , __FILE__ ).'"/>
										<input id="local" type="hidden" value="'.(isset(self::$templateini["local"]) ? self::$templateini["local"] : "").'"/>
										<input id="subdir" type="hidden" value="'.(isset($atts["subdir"]) && $atts["subdir"] ? $atts["subdir"] : "").'"/>
										<input id="template" type="hidden" value="'.$atts["template"].'"/>
										<input id="key" type="hidden" value="'.$atts["key"].'"/>
										<input id="teamids" type="hidden" value="'.($atts["level"] == "Teams" ? $atts["ids"] : "0").'"/>
										<input id="teamid" type="hidden" value="'.($atts["level"] == "Team" ? $atts["ids"] : "0").'"/>
										<input id="profileid" type="hidden" value="'.($atts["level"] == "Profile" ? $atts["ids"] : "0").'"/>
										'.(isset($atts["css"]) && $atts["css"] ? '<input id="css" type="hidden" value="'.$atts["css"].'"/>' : '<input id="css" type="hidden" value="careerpagestemplatedefault.css"/>').'
										<div id="careerpagescontent" class="prd-container">'.(isset(self::$templateini["gui"]) ? self::$templateini["gui"] : '').'</div>
										';
			} else {
				$errors = '';
				foreach (self::$templateini["errors"] as $index => $error) {
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