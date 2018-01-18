<?php

/*
	DASHBOARD
	===========================
	VERSION	DATE				TIME	BY								COMMENTS
	----------------------------
	1.1			2017-08-23	08:56	joakim.saettem
	1.2									13:53										Split out volumes.
	1.3			2017-10-05	16:21										Displays uncoloured N/A instead of 0 and colour when no value.
	1.4			2018-01-08	09:42										Fixed delta to target and 7d.

*/

function show_module() {

	global $db, $startdate, $sqldater, $currentyear, $currentmonth, $monthy, $contract, $team_id_def;

echo "<div class='pad'>";
echo "<table class='dashboard'>";

	$dksurveys = surveycount("",$team_id_def[5]);
	$nlsurveys = surveycount("",$team_id_def[7]);
	$nosurveys = surveycount("",$team_id_def[6]);
	$sesurveys = surveycount("",$team_id_def[4]);

	echo "
	<thead id='teams'>
		<th></th>
		<th class='dashboard-team-name'>
			<a href='?a=team&b=5'>DK</a>
			<span class='dashboard-team-surveys' title='Surveys received'>$dksurveys</span>
			<span class='dashboard-team-latestsurvey'>".getlatest($team_id_def[5])."</span>
		</th>

		<th class='dashboard-team-name'>
			<a href='?a=team&b=7'>NL</a>
			<span class='dashboard-team-surveys' title='Surveys received'>$nlsurveys</span>
			<span class='dashboard-team-latestsurvey'>".getlatest($team_id_def[7])."</span>
		</th>

		<th class='dashboard-team-name'>
			<a href='?a=team&b=6'>NO</a>
			<span class='dashboard-team-surveys' title='Surveys received'>$nosurveys</span>
			<span class='dashboard-team-latestsurvey'>".getlatest($team_id_def[6])."</span>
		</th>

		<th class='dashboard-team-name'>
			<a href='?a=team&b=4'>SE</a>
			<span class='dashboard-team-surveys' title='Surveys received'>$sesurveys</span>
			<span class='dashboard-team-latestsurvey'>".getlatest($team_id_def[4])."</span>
		</th>
	</thead>";
	echo "<tr><td></td><td colspan=11 class='big-title'>Core Metrics</td></tr>";
	echo "<tr>";
		echo "<td class='dashboard-metric-name'>AHT</td>";
		echo displayvmbox(5,2);
		echo displayvmbox(7,2);
		echo displayvmbox(6,2);
		echo displayvmbox(4,2);
	echo "</tr>";

	echo "<tr>";
		echo "<td class='dashboard-metric-name'>KDI</td>";
		echo displaydashboardbox(5,5);
		echo displaydashboardbox(7,5);
		echo displaydashboardbox(6,5);
		echo displaydashboardbox(4,5);
	echo "</tr>";

	echo "<tr>";
	echo "<td class='dashboard-metric-name'>TR</td>";
	$pvalue[dk]=vm("pvol",5,$startdate);
	$pvalue[nl]=vm("pvol",7,$startdate);
	$pvalue[no]=vm("pvol",6,$startdate);
	$pvalue[se]=vm("pvol",4,$startdate);

	$evalue[dk]=vm("evol",5,$startdate);
	$evalue[nl]=vm("evol",7,$startdate);
	$evalue[no]=vm("evol",6,$startdate);
	$evalue[se]=vm("evol",4,$startdate);

	$ptr[dk]=vm("ptr",5,$startdate);
	$ptr[nl]=vm("ptr",7,$startdate);
	$ptr[no]=vm("ptr",6,$startdate);
	$ptr[se]=vm("ptr",4,$startdate);

	$etr[dk]=vm("etr",5,$startdate);
	$etr[nl]=vm("etr",7,$startdate);
	$etr[no]=vm("etr",6,$startdate);
	$etr[se]=vm("etr",4,$startdate);

	$value=round((($etr[dk]*$evalue[dk])+($ptr[dk]*$pvalue[dk]))/($evalue[dk]+$pvalue[dk])*100,1);echo displayvmbox(5,6,$value);
	$value=round((($etr[nl]*$evalue[nl])+($ptr[nl]*$pvalue[nl]))/($evalue[nl]+$pvalue[nl])*100,1);echo displayvmbox(7,6,$value);
	$value=round((($etr[no]*$evalue[no])+($ptr[no]*$pvalue[no]))/($evalue[no]+$pvalue[no])*100,1);echo displayvmbox(6,6,$value);
	$value=round((($etr[se]*$evalue[se])+($ptr[se]*$pvalue[se]))/($evalue[se]+$pvalue[se])*100,1);echo displayvmbox(4,6,$value);

	echo "</tr>";

	echo "<tr>";
		echo "<td class='dashboard-metric-name'>";
		echo "RCR";
		echo "</td>";
		echo displayvmbox(5,17);
		echo displayvmbox(7,17);
		echo displayvmbox(6,17);
		echo displayvmbox(4,17);
	echo "</tr>";

	echo "<tr><td></td><td colspan=10 class='big-title'>Kickers</td></tr>";
	echo "<tr>";
		echo "<td class='dashboard-metric-name'>NPS</td>";
		echo displaydashboardbox(5,4);
		echo displaydashboardbox(7,4);
		echo displaydashboardbox(6,4);
		echo displaydashboardbox(4,4);
	echo "</tr>";

	// VOLUMES
	echo "<tr><td></td><td colspan=10 class='big-title'>Volumes</td></tr>";
	echo "<tr>";
	echo "<td class='dashboard-metric-name'>Phone</td>";
	echo displayvmbox(5,12,$pvalue[dk]);
	echo displayvmbox(7,12,$pvalue[nl]);
	echo displayvmbox(6,12,$pvalue[no]);
	echo displayvmbox(4,12,$pvalue[se]);
	echo "</tr>";

	echo "<tr>";
	echo "<td class='dashboard-metric-name'>Email</td>";
	echo displayvmbox(5,14,$evalue[dk]);
	echo displayvmbox(7,14,$evalue[nl]);
	echo displayvmbox(6,14,$evalue[no]);
	echo displayvmbox(4,14,$evalue[se]);
	echo "</tr>";

	echo "<tr>";
	echo "<td class='dashboard-metric-name'>Combined</td>";
	echo displayvmbox(5,14,$evalue[dk]+$pvalue[dk]);
	echo displayvmbox(7,14,$evalue[nl]+$pvalue[nl]);
	echo displayvmbox(6,14,$evalue[no]+$pvalue[no]);
	echo displayvmbox(4,14,$evalue[se]+$pvalue[se]);
	echo "</tr>";

	echo "<tr>";
	echo "<td class='dashboard-metric-name'>Email / Phone Ratio</td>";
	echo displayvmbox(5,19,round($evalue[dk]/$pvalue[dk]*100));
	echo displayvmbox(7,19,round($evalue[nl]/$pvalue[nl]*100));
	echo displayvmbox(6,19,round($evalue[no]/$pvalue[no]*100));
	echo displayvmbox(4,19,round($evalue[se]/$pvalue[se]*100));
	echo "</tr>";

	echo "<tr>";
	echo "<td class='dashboard-metric-name'>Surveys / Volume Ratio</td>";
	echo displayvmbox(5,20,round($dksurveys/($evalue[dk]+$pvalue[dk])*100));
	echo displayvmbox(7,20,round($nlsurveys/($evalue[nl]+$pvalue[nl])*100));
	echo displayvmbox(6,20,round($nosurveys/($evalue[no]+$pvalue[no])*100));
	echo displayvmbox(4,20,round($sesurveys/($evalue[se]+$pvalue[se])*100));
	echo "</tr>\n";

	// MISCELLANEOUS
	echo "<tr><td></td><td colspan=10 class='big-title'>Service levels</td></tr>";
	echo "<tr><td class='dashboard-metric-name'>Phone</td>\n";
	echo displayvmbox(5,8);
	echo displayvmbox(7,8);
	echo displayvmbox(6,8);
	echo displayvmbox(4,8);
	echo "</tr>\n";

	echo "<tr><td class='dashboard-metric-name'>Email</td>\n";
	echo displayvmbox(5,13);
	echo displayvmbox(7,13);
	echo displayvmbox(6,13);
	echo displayvmbox(4,13);
	echo "</tr>\n";

	// MISCELLANEOUS
	echo "<tr><td></td><td colspan=10 class='big-title'>Transfer rate</td></tr>";
	echo "<tr><td class='dashboard-metric-name'>Phone</td>\n";
	echo displayvmbox(5,6,round($ptr[dk]*100,1));
	echo displayvmbox(7,6,round($ptr[nl]*100,1));
	echo displayvmbox(6,6,round($ptr[no]*100,1));
	echo displayvmbox(4,6,round($ptr[se]*100,1));
	echo "</tr>\n";

	echo "<tr><td class='dashboard-metric-name'>Email</td>\n";
	echo displayvmbox(5,6,round($etr[dk]*100,1));
	echo displayvmbox(7,6,round($etr[nl]*100,1));
	echo displayvmbox(6,6,round($etr[no]*100,1));
	echo displayvmbox(4,6,round($etr[se]*100,1));
	echo "</tr>\n";
	echo "</table>\n";
	echo "</div></div>\n";
}

function displayvmbox ($dvm_team, $dvm_metric, $dvm_value) {
	global $startdate;

	if (isset($dvm_value)){ $value = $dvm_value; }
	else { $value = vm($dvm_metric, $dvm_team, $startdate); }

	list($bg,$fg) = targetcolor($value, 5, $dvm_metric, $dvm_team, $startdate);
	echo "<td class='dashboardtd'>";

	if ($value == "n/a") {
		echo "<span class='valuearea'>n/a</span>";
	}
	else {
		// Value area
		echo "<span class='valuearea' style='background-color:#$bg;'>";
		echo "<a style='color:#$fg;' href='?a=metric_details&b=$dvm_metric&c=$dvm_team'>";
		echo  round($value,gmr($dvm_metric)). gms($dvm_metric)."</a></span>";
	}

	// Display target area if there is a target.
	$tmptarget = gettarget(5,$dvm_metric,$dvm_team,$startdate,"low");
	if ($tmptarget) {

		// Target title
			echo "<span class='targettitle xtoggle' style='color:#$fg'>&ofcir;</span>";

		// Target area
			echo "<span class='targetarea xtoggle' style='color:#$fg'>";
			echo $tmptarget;
			echo "</span>";

		// Delta area
			if ($value != "n/a"){
				echo "<span class='targetdeltatitle xtoggle' style='color:#$fg'>&Delta;</span>";
				echo "<div class='targetdelta xtoggle";
				echo "' style='color:#$fg;background-color:#$bg";

				// If the metric is AHT, calculate delta in one way.
				if ($dvm_metric == '2') {
					$tmpdelta = ($value / $tmptarget) - 1;
				}
				else {
					$tmpdelta = $value - $tmptarget;
					$tmpdelta = $tmpdelta / 100;
				}
				echo "'>";
				echo round($tmpdelta*100,1);
				echo "%</div>";
			}
	}
	echo "</td>\n\n";
}
function displaydashboardbox($ddb_team, $ddb_metric, $submetric){
	global $startdate, $enddate, $team_id_def, $today, $contract;
	$teamdefinition = $team_id_def[$ddb_team];
	$value = getvalue($ddb_metric,$startdate,$enddate, $teamdefinition);
	echo "<td class='dashboardtd'>";

	// Value, contract, metric, team, date, submetric)
	list($bg,$fg) = targetcolor($value, 5, $ddb_metric, $ddb_team, $startdate, $submetric);
	$rounding = gmr($ddb_metric);
	$symbol = gms($ddb_metric);

	if ($value == "n/a") { echo "<span class='valuearea'>n/a</span>"; }
	else {
	// Value area
		echo "<span class='valuearea' style='background:#$bg'>";
		echo "<a style='color:#$fg;' href='?a=metric_details&b=$ddb_metric&c=$ddb_team'>";
		echo round($value,$rounding) . $symbol;
		echo "</a>";
		echo "</span>";
	}


	// Display target areas if there is a target.
	$highorlow='high';if ($ddb_metric=='2'){$highorlow='low';}
	$tmptarget = gettarget(5,$ddb_metric,$ddb_team,$startdate,$highorlow,$submetric);
	if ($tmptarget) {
		// Target title
		echo "<span class='targettitle' style='color:#$fg'>&ofcir;</span>";
		// Target area
		echo "<span class='targetarea' style='color:#$fg;background-color:#$bg'>";
		echo $tmptarget;
		echo "</span>";
		echo "<span class='targetdeltatitle' style='color:#$fg'>&Delta;</span>";
		echo "<div class='targetdelta' style='color:#$fg;background-color:#$bg";
		$tmpdelta = $value - $tmptarget;

		// NPS has a range from -100 to 100. The rest has a range of 100 (AHT is not displayed in this function).
		if ($ddb_metric==4) { $tmpdelta=$tmpdelta/200; }
		else { $tmpdelta=$tmpdelta/100; }
		echo "'>";
		echo round($tmpdelta*100,1);
		echo "%</div>";
	}

	$tmpstartdate = date("Y-m-d",strtotime("-1 week +1 day"));
	$tmpenddate = $today;
	$sevendays = getvalue($ddb_metric,$tmpstartdate,$tmpenddate);
	list($tbg,$tfg) = targetcolor($sevendays, 5, $ddb_metric, $ddb_team, $startdate, $submetric);

	// 7 days rolling title
	echo "<span class='sevendaystitle xtoggle'";
	echo " style='color:#{$fg};'>";
	echo "7d";
	echo "</span>";

	// Last week value area
	echo "<span class='sevendaysarea xtoggle'";
	echo " style='color:#{$tfg};'";
	echo ">";
	echo round($sevendays,$rounding) . $symbol;
	echo "</span>";

	if ($value != "n/a") {
		// Last week delta area
		echo "<div class='sevendaysdelta xtoggle delta";
		$tmpdelta = $sevendays - $value;
		if ($tmpdelta<0){$goodbad = 'bad';}  else{$goodbad = 'good';}
		echo "$goodbad'";
		echo "><div class='{$goodbad}arrow'>
		</div>";
		echo round($tmpdelta,0);
		echo "%</div>";
	}
	echo "</td>";
}
