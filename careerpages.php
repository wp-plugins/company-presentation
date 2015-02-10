<?php
require_once("careerpagesgui.php");
require_once("careerpagesdata.php");

class Careerpages {

	function __construct() {
	}
	
	/*public static function getTeamsShowdata($teamids) {
		$teamsdata = CareerpagesData::getCareerpageTeams($teamids);
		
		return $teamsdata;
	}*/
	
	public static function getTeamsData($teamids, $breadcrumbs) {
		$teamsdata = CareerpagesData::getCareerpageTeams($teamids, CareerpagesData::getBreadcrumbData($breadcrumbs));
		
		return $teamsdata;
	}
	
	public static function getTeamData($teamid, $breadcrumbs) {
		$teamdata = CareerpagesData::getCareerpageTeam($teamid, CareerpagesData::getBreadcrumbData($breadcrumbs));

		return $teamdata;
	}
	
	public static function getProfileData($profileid, $breadcrumbs) {
		$profiledata = CareerpagesData::getCareerpageProfile($profileid, CareerpagesData::getBreadcrumbData($breadcrumbs));

		return $profiledata;
	}
	
	public static function getTeamsHtml($teamids, $breadcrumbs) {
		$teamsdata = CareerpagesData::getCareerpageTeams($teamids, CareerpagesData::getBreadcrumbData($breadcrumbs));

		return CareerpagesGui::getTeamsHtml($teamsdata);
	}
	
	public static function getTeamHtml($teamid, $breadcrumbs) {
		$teamdata = CareerpagesData::getCareerpageTeam($teamid, CareerpagesData::getBreadcrumbData($breadcrumbs));
		
		return CareerpagesGui::getTeamHtml($teamdata);
	}
	
	public static function getProfileHtml($profileid, $breadcrumbs) {
		$profiledata = CareerpagesData::getCareerpageProfile($profileid, CareerpagesData::getBreadcrumbData($breadcrumbs));

		return CareerpagesGui::getProfileHtml($profiledata);
	}
	
	public static function getTeamsEmbed($teamids, $breadcrumbs) {
		return CareerpagesGui::getTeamsEmbed($teamids, CareerpagesData::getBreadcrumbData($breadcrumbs));
	}
	
	public static function getTeamEmbed($teamid, $breadcrumbs) {
		return CareerpagesGui::getTeamEmbed($teamid, CareerpagesData::getBreadcrumbData($breadcrumbs));
	}
	
	public static function getProfileEmbed($profileid, $breadcrumbs) {
		return CareerpagesGui::getProfileEmbed($profileid, CareerpagesData::getBreadcrumbData($breadcrumbs));
	}

	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////                             /////////////////////////////////////////////////////////////////////////////
/////////     Dashboard functions     /////////////////////////////////////////////////////////////////////////////
/////////                             /////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public static function getDashboardCareerpages($teamownerid, $templateid, $css, $teamsid, $teamid, $profileid) {
		return CareerpagesGui::getDashboardCareerpagesGui($teamownerid, $templateid, $css, $teamsid, $teamid, $profileid);
	}
	
	/*public static function getDeveloperCareerpages($templateid = 0, $css = "", $teamsid = 0, $teamid = 0, $profileid = 0) {
		return CareerpagesGui::getDeveloperCareerpagesGui($templateid, $css, $teamsid, $teamid, $profileid);
	}*/
	
	public static function getTeamowners() {
		return CareerpagesData::getTeamowners();
	}
	
	public static function getTeamowner() {
		return CareerpagesData::getTeamowner();
	}
	
	public static function getTemplates() {
		return CareerpagesData::getTemplates();
	}
	
	public static function getTeams($teamownerid) {
		return CareerpagesData::getTeams($teamownerid);
	}
}
?>