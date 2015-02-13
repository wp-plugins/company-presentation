<?php
error_reporting(E_ALL);

header("content-type: text/html; Charset: utf-8");
setlocale(LC_ALL, "en_GB");

$action = $_POST['action']; 

$templateini = array();
$templateini["key"] = $_POST['key'];
$templateini["local"] = $_POST['local'];
$templateini["subdir"] = $_POST['subdir'];
$templateini["template"] = $_POST['template'];

if ($action == "getTeamsHtml") {
	echo json_encode(careerpages($action, "teams", $_POST['teams'], $_POST['breadcrumbs']));

} elseif ($action == "getTeamHtml") {
	echo json_encode(careerpages($action, "team", $_POST['team'], $_POST['breadcrumbs']));

} elseif ($action == "getProfileHtml") {
	echo json_encode(careerpages($action, "profile", $_POST['profile'], $_POST['breadcrumbs']));
}

// Depricated, future removal, but is still here to ensure back compatibility
function careerpages($action, $level, $ids, $breadcrumbs) {
	global $templateini;
	
	//add POST variables
	$templateini["action"] = 'get'.ucfirst($level).($templateini["local"] ? 'Data' : 'Html');
	$templateini[strtolower($level)] = $ids;
	$templateini["breadcrumbs"] = $breadcrumbs;
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, 'https://'.($templateini["subdir"] ? $templateini["subdir"].'.' : '').'prodii.com/common/careerpages/php/careerpageshandler.php'); 
	curl_setopt($ch, CURLOPT_POST, count($templateini));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($templateini));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$response = curl_exec($ch); 

	if($errno = curl_errno($ch)) {
		$output = "cURL error ({$errno}):\n {$error_message}";
	} else {
		if ($templateini["local"]) {
			require_once(dirname(dirname(__FILE__)).'/templates/oslo/careerpagestemplategui.php');
			
			// Adding more info for further use when retrieving images
			$templateini["localpluginurl"] = dirname(dirname($_SERVER['PHP_SELF'])).'/';
			$templateini["companyimgplaceholder"] = 'image-placeholder900x600.png';
			$templateini["teamimgplaceholder"] = 'image-placeholder900x600.png';
			$templateini["profileimgplaceholder"] = 'image-placeholder310x310.png';

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