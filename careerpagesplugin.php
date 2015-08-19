<?php

/**
 * @package careerpages
 */
/*
Plugin Name: Careerpages, 
Plugin URI: https://prodii.com/WpPluginInfo
Description: The ultimative easiest way to present your company.
Version: 4.0.2
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

////////////////////////////////////////////////////////////////////////////////////
//////////
//////////				Admin
//////////
////////////////////////////////////////////////////////////////////////////////////
if (!class_exists("ProdiiAdmin")) {
	class ProdiiAdmin {

		function ProdiiAdmin() { //constructor
			//require_once(self::$templateini["pluginpath"].'/php/careerpagespluginlibrary.php');
			//require_once(plugins_url('php/careerpagespluginlibrary.php' , __FILE__ ));
			include(plugin_dir_path(__FILE__).'php/careerpagespluginlibrary.php');
		}

		function set_admin_statistics($page) {
			//Set server data
			$cp_data = array(
				'action' => 'setLogistics',
				'key' => get_option("prodii_key"),
				'method' => 'wppluginadmin',
				'page' => $page,
				'clientip' => CareerpagesLibrary::get_client_ip(),
				'server' => json_encode($_SERVER)
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://prodii.com/common/careerpages/php/careerpagesadminhandler.php'); 
			curl_setopt($ch, CURLOPT_POST, count($cp_data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_data));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_exec($ch);
			curl_close($ch);
		}

		function addAdminHeaderCode($hook) {
			global $prodii_shortcode_page;
			
			//if ($hook != $prodii_shortcode_page) return;
			
			wp_register_style('careerpages_admin_prettify_style', 'https://google-code-prettify.googlecode.com/svn/trunk/src/prettify.css');
			wp_enqueue_style('careerpages_admin_prettify_style');
			
			wp_register_script('careerpages_admin_script', plugins_url('js/careerpagesadmin.js' , __FILE__ ), array('jquery'));
			wp_enqueue_script('careerpages_admin_script');
			wp_localize_script('careerpages_admin_script', 'prodii_vars', array(
				'prodii_nonce' => wp_create_nonce('prodii_nonce')
			));
			wp_enqueue_script('careerpages_admin_prettify_script', 'https://google-code-prettify.googlecode.com/svn/trunk/src/prettify.js', false, '3');
		}

		//Prints out the admin Prodii description page
		function prodii_description_page() {
			self::set_admin_statistics('description');

			echo 	'
						<div class="wrap">
							<h2>Prodii Description</h2>
							<table class="form-table">
								<tr valign="top">
									<th scope="row">About Us Pages Made Easy</th>
									<td>
										<p>With Prodii Company and career page Plugin you can create a professional "About Us" section for your company, team and people; a vibrant and professional "About Us Page" introducing the human face of your company, such as team profiles and individual profiles.</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">On Your Company Home page</th>
									<td>
										<p>Tell about your people and skills. Include employee profiles and showcase your organisation on your company home page. From a simple employee directory listing of your employees and/ or co-workers to an extended profile information with photos, skills and bio - all put together in a stylish design. No coders required.</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">HOW IT WORKS</h3></th>
									<td>
										<p>&nbsp;</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">1. Get an account on prodii.com</th>
									<td>
										<ol>
											<li>Install and activate the plugin</li>
											<li>Go to <a href="https://prodii.com" target="_blank">https://prodii.com</a> and register for an account</li>
											<li>Create your company, your team(s) and invite your co-workers</li>
										</ol>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">2. Prodii Puts it all together for you in a short code</th>
									<td>
										<p>You enter information about your company and team. Co-workers enter information about themselves.</p>
										<p>Prodii puts all the content and data together for you in a short code like this:</p>
										<br>
										<p><span class="pun">[</span><span class="pln">careerpages key</span><span class="pun">=</span><span class="str">"WjEK4UWFcApDLsFR"</span><span class="pln"> level</span><span class="pun">=</span><span class="str">"Teams"</span><span class="pln"> ids</span><span class="pun">=</span><span class="str">"56,68"</span><span class="pln"> </span><span class="kwd">template</span><span class="pun">=</span><span class="str">"helios"</span><span class="pun">]</span></p>
										<br>
										<p>(Please go ahead and copy/paste this sample short code into a full width page to see how it works)</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">3. Activate your key</th>
									<td valign="top">
										<p>Pay to activate your key on prodii.com.</p>
										<p>Then enter your key within Wordpress.</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">4. Create your short code</th>
									<td>
										<p>Within wordpress you can now generate your short code.</p>
										<p>Select template (prepaid on prodii.com)</p>
										<p>Select company/ team(s)</p>
										<p>Generate your short code and paste into a full width page on your Wordpress home page.</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">5. Updating your company career page</th>
									<td>
										<p>Update your company, team and profile content on prodii.com and your home page will be updated accordingly.</p>
									</td>
								</tr>
							</table>
						';
		}

		//Prints out the admin settings page
		function prodii_settings_page() {
			self::set_admin_statistics('settings');

			$cp_data = array(
				'action' => 'getKeyData',
				'key' => get_option("prodii_key")
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://prodii.com/common/careerpages/php/careerpagesadminhandler.php'); 
			curl_setopt($ch, CURLOPT_POST, count($cp_data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_data));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			$data = json_decode(curl_exec($ch), true);
			curl_close($ch);
			
			echo 	'
						<div class="wrap">
							<h2>Prodii Settings</h2>
						';
			if (isset($data["teamowner"])) {
				echo 	'
								<table class="form-table">
									<tr valign="top">
										<th scope="row">Teamowner</th>
										<td>
											'.$data["teamowner"]["name"].'
										</td>
									</tr>
								</table>
							';
			}
			echo 	'
							<form method="post" action="options.php">
						';	
							settings_fields("prodii-settings");
							do_settings_sections("prodii-settings");
			echo 	'
								<table class="form-table">
									<tr valign="top">
										<th scope="row">Key</th>
										<td>
											<input type="text" name="prodii_key" value="'.get_option("prodii_key").'"/>
										</td>
									</tr>
								</table>
							'.get_submit_button().'
							</form>
						</div>
						';
		}

		//Prints out the admin shortcode page
		//function prodii_shortcode_page($hook) {
		function prodii_shortcode_page() {
			self::set_admin_statistics('shortcode');

			echo 	'
						<div class="wrap">
							<h2>Prodii Plugin Shortcode</h2>
							<form id="prodii_shortcode_form" method="post" action="'.admin_url('admin.php').'?page=prodii-shortcode">
								'.self::prodii_shortcode_content().'
							</form>
						</div>
						';

		}
		
		function prodii_shortcode_content() {
			//if (is_array($_REQUEST)) $content = "From prodii_shortcode_content <pre>".print_r($_REQUEST, true)."</pre>";
			$cp_data = array(
				'action' => 'getShortcodeHtml',
				'key' => get_option("prodii_key"),
				'templateid' => isset($_REQUEST["prodii_templateid"]) ? $_REQUEST["prodii_templateid"] : '',
				'css' => isset($_REQUEST["prodii_css"]) ? $_REQUEST["prodii_css"] : '',
				'companyid' => isset($_REQUEST["prodii_companyid"]) ? $_REQUEST["prodii_companyid"] : 0,
				'teamids' => isset($_REQUEST["prodii_teamids"]) ? $_REQUEST["prodii_teamids"] : '',
				'teamid' => isset($_REQUEST["prodii_teamid"]) ? $_REQUEST["prodii_teamid"] : 0,
				'memberid' => isset($_REQUEST["prodii_memberid"]) ? $_REQUEST["prodii_memberid"] : 0,
				'view' => isset($_REQUEST["prodii_view"]) ? $_REQUEST["prodii_view"] : 'tab-company'
			);
			//$content = $content."From prodii_shortcode_content <pre>".print_r($cp_data, true)."</pre>";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://prodii.com/common/careerpages/php/careerpagesadminhandler.php'); 
			curl_setopt($ch, CURLOPT_POST, count($cp_data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_data));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_FAILONERROR, true); 
			$content = json_decode(curl_exec($ch), true);
			if(curl_errno($ch)) {
				$content = json_decode('cURL error ({$errno}):\n {$error_message}');
			}
			curl_close($ch);
			
			return $content;
		}

		function ajax_prodii_shortcode_content() {
			if (!isset($_POST["prodii_nonce"]) || !wp_verify_nonce($_POST["prodii_nonce"], 'prodii_nonce')) die('Permissions check failed');
			
			//echo "From ajax_prodii_shortcode_content <pre>".print_r($_POST, true)."</pre>";
			echo self::prodii_shortcode_content();
			
			die();
		}
	}
}


if (class_exists("ProdiiAdmin")) {
	$prodiiAdmin = new ProdiiAdmin();
}
		
if (!function_exists("update_prodii_settings")) {
	function update_prodii_settings() {
		register_setting('prodii-settings', 'prodii_key');
	}
}

//Initialize the admin panel
if (!function_exists("prodii_adminpanel")) {
	function prodii_adminpanel() {
		global $prodii_shortcode_page;
		global $prodiiAdmin;
		if (!isset($prodiiAdmin)) {
			return;
		}

		add_menu_page( 'Prodii', 'Prodii', 'administrator', 'prodii', array(&$prodiiAdmin, 'printAdminDescriptionPage'), plugins_url('img/menu-logo.png' , __FILE__ ), 21);
		add_submenu_page( 'prodii', 'Description', 'Description', 'administrator', 'prodii-description', array(&$prodiiAdmin, 'prodii_description_page'));
		add_submenu_page( 'prodii', 'Settings', 'Settings', 'administrator', 'prodii-settings', array(&$prodiiAdmin, 'prodii_settings_page'));
		$prodii_shortcode_page = add_submenu_page( 'prodii', 'Shortcode', 'Shortcode', 'administrator', 'prodii-shortcode', array(&$prodiiAdmin, 'prodii_shortcode_page'));
		remove_submenu_page('prodii', 'prodii');
	}	
}
 
//Actions and Filters	
if (isset($prodiiAdmin)) {
	//Actions
	add_action('admin_menu', 'prodii_adminpanel');
	add_action('admin_init', 'update_prodii_settings');
	add_action('admin_enqueue_scripts', array(&$prodiiAdmin, 'addAdminHeaderCode'), 1);
	add_action('wp_ajax_prodii_shortcode_content', array(&$prodiiAdmin, 'ajax_prodii_shortcode_content'), 1);
	//Filters
}


?>