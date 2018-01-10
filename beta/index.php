<?php
/*

	CHANGE LOG
	=================================
	Version	Date				by							Comment
	1.0			2017-10-03									Initial version.
	1.1			2017-10-06	joakim.saettem	Titles with underscore converts to space.
	1.2			2017-12-15									Added a new logo.

*/

$time = time();
global $teamdefinition, $ahtteamdefinition;

$application_name = "Concentrix Global Online Leader Dashboard";
$application_copyright = "Copyright Concentrix Europe Ltd 2017";
$application_contact = "joakim.saettem@concentrix.com";

$time = $_SERVER[‘REQUEST_TIME’];

$application_version_major = "2017-12-19";
$application_version_minor = "10:27";

$bad_color = 'd7191c';
$good_color = 'ffffbf';
$great_color = 'a6d96a';

include "config.php";
include "zm-common.php";

$theme = gettheme();
echo "
<!DOCTYPE html>
<html>
	<head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
		<script src='{$path}sorttable.js'></script>
		<script src='{$path}gold.js'></script>
    <script src='https://use.fontawesome.com/77fac2bb57.js'></script>
		<link rel=stylesheet href='{$theme}.css''>
		<link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet'>
		<link href='https://afeld.github.io/emoji-css/emoji.css' rel='stylesheet'>
    <title>{$application_name}</title>
	</head>
	<body>";
if ($_POST[a]=='upload'){
	if(isset($_FILES['filedata'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedata"]["name"]);
			if (move_uploaded_file($_FILES["filedata"]["tmp_name"], $target_file)) {
				echo "The file ". basename( $_FILES["filedata"]["name"]). " has been uploaded.";
				processreport($target_file);
				echo "<script>location.href='./';</script>";
			}
			else { echo "Sorry, there was an error uploading your file."; }
	}
}
if ($_POST[a]=='uploadprtgeneral'){
  if(isset($_FILES['filedataprtgeneral'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedataprtgeneral"]["name"]);
			if (move_uploaded_file($_FILES["filedataprtgeneral"]["tmp_name"], $target_file)) {
				echo "The PRT report ". basename( $_FILES["filedataprtgeneral"]["name"]). " has been uploaded.";
				processGeneralPrtReport($target_file);
				//echo "<script>location.href='./';</script>";
			}
			else {
				echo "Sorry, there was an error uploading your PRT file.";
			}
	}
}
if ($_POST[a]=='uploadprt'){
	if(isset($_FILES['filedataprt'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedataprt"]["name"]);
			if (move_uploaded_file($_FILES["filedataprt"]["tmp_name"], $target_file)) {
				echo "The file prt058p ". basename( $_FILES["filedataprt"]["name"]). " has been uploaded.";
				processprtreport($target_file);
				echo "<script>location.href='./';</script>";
			}
			else {
				echo "Sorry, there was an error uploading your PRT058P file.";
			}
	}
}
if ($_POST[a]=='uploadvm055p'){
	if(isset($_FILES['filedatavm'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedatavm"]["name"]);
			if (move_uploaded_file($_FILES["filedatavm"]["tmp_name"], $target_file)) {
				echo "The file ". basename( $_FILES["filedatavm"]["name"]). " has been uploaded.";
				processvmreport($target_file);
				echo "<script>location.href='./';</script>";
			}
			else {
				echo "Sorry, there was an error uploading your VM055p file.";
			}
	}
}

// Are we logged in?
if ($_POST['newpassword']) {
	$newhash = generatehash($_POST['newpassword']);
	$sql = "UPDATE users SET user_hash = '".$newhash."' WHERE user_id = '" . $_POST['userid'] . "'";
	$result = $db->query($sql);
	$sql = "UPDATE users SET user_new = '0' WHERE user_id = '" . $_POST['userid'] . "'";
	$result = $db->query($sql);
	echo "<script>location.href='?';</script>";
}
if ($_POST['username']) {
	if (validatepassword($_POST['username'],$_POST['password'])) {
		//$_SESSION['username'] = $_POST['username'];
    $_SESSION['user_id'] = getuserid($_POST['username']);
		$_SESSION['logged_in'] = true;
		echo "<script>location.href='?';</script>";
	}
	else {
		$login_error = "Incorrect username and/or password.";
	}
}
// Allow new users to set a password
if ($_GET['n']) {
	$sql = "SELECT user_new FROM users WHERE user_id = '".$_GET['n']."'  LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row = $result->fetch_assoc();
	if($row['user_new'] == '1') { showsetnewpassword(); }
	else { showlogin(); }
}
else {
	if (!$_SESSION['logged_in']) { // Not logged in
		showlogin();
	}
	else { // Logged in
    	// Left area
		echo "<div class='page-bar-top'>";
		$a=$_GET[a];
		$b=$_GET[b];
		$c=$_GET[c];
		$c2=$_GET[c2];
		$d=$_GET[d];
		$e=$_GET[e];
		$f=$_GET[f];
		$m=$_GET['m'];
		$m2=$_GET['m2'];
		$fullparams = "a=$a&b=$b&c=$c&c2=$c2&d=$d&e=$e&f=$f&m=$m&m2=$m2&team=$team";
    $un=getusername($uid);
    $sql = "SELECT user_showleftchoicetext FROM users WHERE user_id={$uid} LIMIT 1";
    if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
    $showleftchoicetext = $row[user_showleftchoicetext];
    $small = ''; if (!$showleftchoicetext) {$small = 'small';}
		$sql = "SELECT user_preferred_team FROM users WHERE user_id=$uid LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
		$prefteam = $row[user_preferred_team];
		if ($_GET[prefteam]){
			$prefteam = $_GET[prefteam];
			$sql = "UPDATE users SET user_preferred_team = '{$_GET[prefteam]}' WHERE user_id='$uid' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
		$sql = "SELECT user_startdate,user_enddate FROM users WHERE user_id=$uid";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
		$startdate=$row[user_startdate]; $startdate=substr($startdate,0,10);
		$enddate=$row[user_enddate]; $enddate=substr($enddate,0,10);
		if ($startdate>$enddate){$startdate=$enddate;}
		if ($_GET[startdate]){
			$startdate=$_GET[startdate]; $enddate=$_GET[enddate];
		}
		$team=$_GET[team];
		if ($team==''){$team=$prefteam;}
		$currentmonth = date("m"); $currentyear = date("Y"); $currentday = date("d");
		$lastday=cal_days_in_month(CAL_GREGORIAN,$currentmonth,$currentyear);
		if ($startdate==''){
			if ($currentday<10){
				$startdate_month = str_pad($currentmonth-1,2,"0",STR_PAD_LEFT);
				if ($enddate==''){$enddate="$currentyear-$startdate_month-$lastday";}
			}
			$startdate="$currentyear-$startdate_month-01";
		}
		if ($enddate==''){ $enddate="$currentyear-$currentmonth-$lastday"; }
		$startdate_year = substr($startdate,0,4); $startdate_month = substr($startdate,5,2); $startdate_day = substr($startdate,8,2);
		$enddate_year = substr($enddate,0,4); $enddate_month = substr($enddate,5,2); $enddate_day = substr($enddate,8,2);
		$startdate_excel = unixdate_to_exceldate(mktime(0,0,0,$startdate_month,$startdate_day,$startdate_year));
		$enddate_excel = unixdate_to_exceldate(mktime(23,59,59,$enddate_month,$enddate_day,$enddate_year));
		$sqldater = " WHERE Teammate_Contact_Date > $startdate_excel";
		$sqldater .= " AND Teammate_Contact_Date < $enddate_excel";

		$sql = "UPDATE users SET user_startdate = '$startdate' WHERE user_id='$uid'"; if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$sql = "UPDATE users SET user_enddate = '$enddate' WHERE user_id='$uid'"; if(!$result=$db->query($sql)){cl($sql);cl($db->error);}

		$lastmonth = date("Y-m-d", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
        $lastmonthyear = date("Y", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
        $lastmonthmonth = date("m", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
        $lastmonthday = date("d", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
		$lastend = date("Y-m-d", mktime(0, 0, 0, $lastmonthmonth, cal_days_in_month(CAL_GREGORIAN,$lastmonthmonth,$lastmonthyear), $lastmonthyear));

		$nextmonth = date("Y-m-d", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
      	$nextmonthyear = date("Y", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
        $nextmonthmonth = date("m", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
        $nextmonthday = date("d", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
		$nextend = date("Y-m-d", mktime(0, 0, 0, $nextmonthmonth, cal_days_in_month(CAL_GREGORIAN,$nextmonthmonth,$nextmonthyear), $nextmonthyear));

        $previous_week = strtotime("-1 week + 1 day");
        $start_week = strtotime("last monday midnight",$previous_week);
        $end_week = strtotime("next sunday",$start_week);
        $lastweekstart = date("Y-m-d",$start_week);
        $lastweekend = date("Y-m-d",$end_week);

		echo "<div title='Previous month' class='prevmonth$small'><a href='?$fullparams&startdate=$lastmonth&enddate=$lastend'><div class='fa fa-3x fa-arrow-left'></div></a></div>";

		echo "<div title='Next month' class='nextmonth$small'><a href='?$fullparams&startdate=$nextmonth&enddate=$nextend'><div class='fa fa-3x fa-arrow-right'></div></a></div>";

		// Pick preferred team
		echo "<div class='preferred-team-title'>Team:</div>";
		echo "<div class='preferred_team'><select onChange='location.href=\"?$fullparams$extraurl&startdate=$startdate&enddate=$enddate&team=\" + this.value + \"&prefteam=\" + this.value;' name='prefteam'>";
		echo "<option value='-1'>Belfast</option>";
		$sql = "SELECT id,team_name FROM teams LIMIT 50";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		while($row=$result->fetch_assoc()){
			echo "<option "; if ($prefteam==$row[id]){ echo "selected "; }
			echo "value={$row[id]}>{$row[team_name]}</option>";
		}
		echo "</select></div>";
		echo "<div class='lastsevendays'>";
		echo "<a href='?$fullparams&startdate=$lastweekstart&enddate=$lastweekend&sub=Last%20Week'>Last Week</a>";
		echo "</div>";
		echo "<div class='currentmonth'><a href='?$fullparams&startdate=$currentyear-$currentmonth-01&enddate=$currentyear-$currentmonth-$lastday&sub=Current%20Month'>Current Month</a></div>";

		echo "<div class='startdatepicker'>Start date: <input class=startdate id=startdate name=startdate type=date value='$startdate' onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d&team=$team&enddate=$enddate&startdate=\"+this.value;'></div>";
		echo "<div class='enddatepicker'>End date: <input class=enddate id=enddate name=enddate type=date value='$enddate' onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d&team=$team&startdate=$startdate&enddate=\"+this.value;'></div>";
		$teamdefinition='';
		if ($team){
			$sql = "SELECT * FROM team_data_definitions WHERE team_id = $team";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$definitions = 0;
			while($row=$result->fetch_assoc()){
				if ($definitions){$teamdefinition.=' OR ';}
				$teamdefinition .= $row[raw_data_column] . "='{$row[raw_data_data]}'";
				$definitions++;
			}
			if ($teamdefinition){$teamdefinition=" AND (".$teamdefinition . ")";}
			$sql = "SELECT * FROM team_aht_definitions WHERE team_id = $team";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$ahtdefinitions = 0;
			while($row=$result->fetch_assoc()){
				if ($ahtdefinitions){$ahtteamdefinition.=' OR ';}
				$ahtteamdefinition .= $row[ahtreport_data_column] . "='{$row[ahtreport_data_data]}'";
				$ahtdefinitions++;
			}
			if ($ahtteamdefinition){$ahtteamdefinition=" AND (".$ahtteamdefinition . ")";}


		}
		echo "<div class='new-logo'>
			<div class='logo-text'><a href='./?'>gold</a></div>
			<div class='logo-colors'>
				<div class='logo-green'></div>
				<div class='logo-amber'></div>
				<div class='logo-red'></div>
			</div>
		</div>";
		if ($a=='') { $a = 'dashboard'; }
		if ($a) {
			$converted_title = str_replace("_", " ", $a);
			echo "<div class='area_title$small'><div class='fa fa-".geticon($a)."'></div> <a href='?a=$a'>".ucwords($converted_title)."</a></div>";
		}
		echo "</div>";
		echo "<div class='div_left_area$small'>";
		addleftchoice("dashboard");
		addleftchoice("surveys");
		addleftchoice("bonus");
		addleftchoice("elite");
		//addleftchoice("masters");
		//addleftchoice("qds");
		addleftchoice("targets");
		addleftchoice("trends");
		addleftchoice("certification");
		addleftchoice("seating");
		addleftchoice("search");
		addleftchoice("settings");
		//addleftchoice("mystats");
		//addleftchoice("metrics");
		//addleftchoice("employees");
		//addleftchoice("teams");
		//addleftchoice("contracts");
		//addleftchoice("calendar");
		echo "</div>";
		echo "<div class='topleftcorner'>";
		echo "</div>";




		echo "<div class='div_right_area$small'>";
		if ($a) {
			echo "<div class='area_body'>";
			include "zm-$a.php";
			show_module();
			echo "<div class='leftinfo'>Updated:<br> ";
			$today = date("Y-m-d");
			$medalliaupdated = getsetting('medalliadata');
			$vm055updated = getsetting('vm055data');
			$prt060pupdated = getsetting('prt060');
			$prt085updated = getsetting('prt085');
			$prt073updated = getsetting('prt073');
			$medalliaupdated = getsetting('medalliadata');
			$sub = $_GET[sub];
			echo "Platform: " . dd("$application_version_major $application_version_minor") . "<br>";
			echo "Medallia: " . dd($medalliaupdated) . "<br>";
			//echo "VM055: " . dd($vm055updated) . "<br>";
			echo "PRT060p: " . dd($prt060pupdated) . "<br>";
			echo "PRT073: " . dd($prt073updated) . "<br>";
			//echo "PRT085: " . dd($prt085updated);
			echo "</div>";
			echo "</div>";
		}
	}
}

echo "</body></html>";
$db->close();
?>
