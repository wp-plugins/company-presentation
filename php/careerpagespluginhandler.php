<?php
error_reporting(E_ALL);
header("content-type: text/html; Charset: utf-8");
setlocale(LC_ALL, "en_GB");

$action = $_POST['action']; 

if ($action == "getTeamsHtml") {
	//echo json_encode(careerpages($action, "teams", $_POST['template'], $_POST['key'], $_POST['teams'], $_POST['subdir'], array($_POST['teams'], 0, 0)));
	echo json_encode(careerpages($action, "teams", $_POST['template'], $_POST['key'], $_POST['teams'], $_POST['subdir'], $_POST['breadcrumbs']));

} elseif ($action == "getTeamHtml") {
	echo json_encode(careerpages($action, "team", $_POST['template'], $_POST['key'], $_POST['team'], $_POST['subdir'], $_POST['breadcrumbs']));

} elseif ($action == "getProfileHtml") {
	echo json_encode(careerpages($action, "profile", $_POST['template'], $_POST['key'], $_POST['profile'], $_POST['subdir'], $_POST['breadcrumbs']));
}

function careerpages($action, $level, $template, $key, $ids, $subdir, $breadcrumbs) {
	//set POST variables
	$fields = array(
		'action' => urlencode($action),
		'key' => urlencode($key),
		$level => urlencode($ids),
		'template' => urlencode($template),
		'breadcrumbs' => $breadcrumbs
	);
	
	// create curl resource 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "https://".($subdir ? $subdir."." : "")."prodii.com/common/careerpages/careerpageshandler.php"); 
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = json_decode(curl_exec($ch)); 
	curl_close($ch);

	return $output;     
}
?>