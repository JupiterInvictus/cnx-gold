<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	echo "<a href='?a=$a'>View</a> | ";
	echo "<a href='?a=$a&b=$b&x=1'>Edit</a>";
	$x = $_GET[x];
	$latestsurvey = getlatest($teamdefinition);
	echo "<div id='message'>";
	$date1 = new DateTime($today);
	echo "Hi Dave";
	//$date2 = new DateTime($latestsurvey);
	$interval = $date1->diff($date2);
	echo "<br><br><b>Call Outs</b>";
	echo "<ul>";
	echo "<li>In {$month[$startdate_month]} $startdate_year, team ".getteamname($team);
	if ($interval->d <3) { echo " has so far"; }
	echo " received ".surveycount()." surveys. ";
	if ($interval->d <3) {
		$prevmonth=$currentmonth-1; if($prevmonth<1){$prevmonth=12;}
		$twomonthsago=$prevmonth-1; if($twomonthsago<1){$twomonthsago=12;}
		echo " (" . round(surveycount()/surveycount("previous month")*100,0) . "% vs $month[$prevmonth] and ";
		echo " " . round(surveycount()/surveycount("two months ago")*100,0) . "% vs $month[$twomonthsago]). ";
		echo " The latest survey has been received on ";
	}
	else {
		echo "The last survey was received on ";
	}
	echo $latestsurvey . '.</li> ';
	// Metrics
	$aht = vm(2,$team,$startdate);
	$ahttarget = gettarget(5,2,$team,$startdate,"low");

	$kdi = getvalue(5,$startdate,$enddate);;
	$kditarget = gettarget(5,5,$team,$startdate,"high");

	$crrr = getvalue(3,$startdate,$enddate);;
	$crrrtarget = gettarget(5,3,$team,$startdate,"high");

	$nps = getvalue(4,$startdate,$enddate);;
	$npstarget = gettarget(5,4,$team,$startdate,"high");

	$pvol = vm("pvol",$team,$startdate); $evol = vm("evol",$team,$startdate);
	$ptr = vm("ptr",$team,$startdate); $etr = vm("etr",$team,$startdate);
	$tr = round((($pvol*$ptr + $evol*$etr)/($pvol + $evol))*100,1);
	$trtarget = gettarget(5,6,$team,$startdate,"low");
	$ahtmet = -1;$kdimet = -1;$crrrmet = -1;$npsmet = -1;$trmet = -1;
	if ($aht <= $ahttarget) { $ahtmet = 1; }
	if ($kdi >= $kditarget) { $kdimet = 1; }
	if ($crrr >= $crrrtarget) { $crrrmet = 1; }
	if ($nps >= $npstarget) { $npsmet = 1; }
	if ($tr <= $trtarget) { $trmet = 1; }
	if ($c == "removeaction") {
		if ($d) {
			$sql = "DELETE FROM monthlyactions WHERE id = '$d'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	$sql = "SELECT * FROM calloutactions";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while ($row=$result->fetch_assoc()){
		$actions[$row[id]] = $row[actiontext];
	}
	if ($c=="addactiondone"){
		$sql = "SELECT id FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND calloutactionid = '$d' AND metricid = '{$_GET[m]}' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[id]==''){
			$sql = "INSERT INTO monthlyactions (calloutactionid,teamid,year,month,metricid) VALUES('{$d}','{$team}','{$startdate_year}','{$startdate_month}','{$_GET[m]}')";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}

	echo "<li>AHT "; if ($ahtmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($aht-$ahttarget,0)."s / ".round((($aht-$ahttarget)/$ahttarget)*100,0)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==2)){
			echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($ahtmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=2&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '2'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }

	echo "<li>KDI "; if ($kdimet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($kdi-$kditarget,1)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==5)){
			echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($kdimet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=5&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '5'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }
	echo "<li>CrRR "; if ($crrrmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($crrr-$crrrtarget,1)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==3)){
			echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($crrrmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=3&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '3'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }
	echo "</li>";
	echo "<li>NPS "; if ($npsmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($nps-$npstarget,1)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==4)){
			echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($npsmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=4&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '4'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }

	echo "</li>";
	echo "<li>TR "; if ($trmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($tr-$trtarget,1)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==6)){
			echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($trmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=6&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '6'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }
	echo "</li>";
	echo "</li>";
	echo "</ul>";
	echo "<br><b>Focus Areas</b>";
	echo "<hr>";
	echo "<h1>Top 5 contact reasons</h1>";
	echo "<h1>Top 5 detractors</h1>";
	echo "<h1>Underperformers<h1>";
}
