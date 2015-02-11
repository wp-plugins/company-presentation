<?php
error_reporting(E_ALL);

header("content-type: text/html; Charset: utf-8");
setlocale(LC_ALL, "en_GB");

$action = $_POST['action']; 

$cp_fix = array();
$cp_fix["key"] = $_POST['key'];
$cp_fix["local"] = $_POST['local'];
$cp_fix["subdir"] = $_POST['subdir'];
$cp_fix["template"] = $_POST['template'];

if ($action == "getTeamsHtml") {
	echo json_encode(careerpages($action, "teams", $_POST['teams'], $_POST['breadcrumbs']));

} elseif ($action == "getTeamHtml") {
	echo json_encode(careerpages($action, "team", $_POST['team'], $_POST['breadcrumbs']));

} elseif ($action == "getProfileHtml") {
	echo json_encode(careerpages($action, "profile", $_POST['profile'], $_POST['breadcrumbs']));
}

// Depricated, future removal, but is still here to ensure back compatibility
function careerpages($action, $level, $ids, $breadcrumbs) {
	global $cp_fix;
	
	//add POST variables
	$cp_fix["action"] = 'get'.ucfirst($level).($cp_fix["local"] ? 'Data' : 'Html');
	$cp_fix[strtolower($level)] = $ids;
	$cp_fix["breadcrumbs"] = $breadcrumbs;
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, 'https://'.($cp_fix["subdir"] ? $cp_fix["subdir"].'.' : '').'prodii.com/common/careerpages/careerpageshandler.php'); 
	curl_setopt($ch, CURLOPT_POST, count($cp_fix));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($cp_fix));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$response = curl_exec($ch); 

	if($errno = curl_errno($ch)) {
		$output = "cURL error ({$errno}):\n {$error_message}";
	} else {
		if ($cp_fix["local"]) {
			require_once(dirname(dirname(__FILE__)).'/templates/oslo/careerpagestemplategui.php');
			
			// Adding more info for further use when retrieving images
			$cp_fix["localpluginurl"] = dirname(dirname($_SERVER['PHP_SELF'])).'/';
			$cp_fix["companyimgplaceholder"] = 'image-placeholder900x600.png';
			$cp_fix["teamimgplaceholder"] = 'image-placeholder900x600.png';
			$cp_fix["profileimgplaceholder"] = 'image-placeholder310x310.png';

			switch (ucfirst($level)) {
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

	return $output;     
}?>