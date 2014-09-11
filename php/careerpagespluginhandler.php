<?php
error_reporting(E_ALL);
header("content-type: text/html; Charset: utf-8");
setlocale(LC_ALL, "en_GB");

$action = $_POST['action']; 

if ($action == "getTeamsHtml") {
	echo json_encode(careerpages("Teams", $_POST['template'], $_POST['key'], $_POST['teams'], $_POST['subdir']));

} elseif ($action == "getTeamHtml") {
	echo json_encode(careerpages("Team", $_POST['template'], $_POST['key'], $_POST['team'], $_POST['subdir']));

} elseif ($action == "getProfileHtml") {
	echo json_encode(careerpages("Profile", $_POST['template'], $_POST['key'], $_POST['profile'], $_POST['subdir']));
}

function careerpages($level, $template, $key, $ids, $subdir) {
		// create curl resource 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, "https://".($subdir ? $subdir."." : "")."prodii.com/CareerPages/".$level."/Html/".$template."/".$key."/".$ids); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = json_decode(curl_exec($ch)); 
        curl_close($ch);

	return $output;     
}
?>