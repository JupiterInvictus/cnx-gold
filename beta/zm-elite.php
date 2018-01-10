<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	// if a team has been specified, only show elites in that team.
	if ($team > 0) {
		echo "<h2>Team " . getteamname($team) . "</h2>";
	}

	$statement = "SELECT id, title, left_or_right FROM titles";
	if (!$result = $db->query($statement)) {
		cl($statement);
		cl($db->error);
	}
	while ($row = $result->fetch_assoc()) {
		$titles[$row['id']] = $row;
	}

	$statement = "SELECT id, titleid FROM ranks";
	if (!$result = $db->query($statement)) {
		cl($statement);
		cl($db->error);
	}
	while ($row = $result->fetch_assoc()) {
		$ranks[$row['id']] = $row;
	}

	$statement = "SELECT teammate_nt_id, teammate_name, COUNT(external_survey_id) AS surveys FROM raw_data $sqldater $teamdefinition AND likely_to_recommend_paypal > 7 AND (kdi___email > 75 OR kdi___phone > 75) AND issue_resolved = 'Yes' GROUP BY teammate_name ORDER BY teammate_name ASC";
	if (!$result = $db->query($statement)) {
		cl($statement);
		cl($db->error);
	}
	while ($row = $result->fetch_assoc()) {
		$teammate['name'][$row['teammate_nt_id']] = $row['teammate_name'];
		$teammate['goodsurveys'][$row['teammate_nt_id']] = $row['surveys'];
	}

	$totalsurveys = 0;
	$totalgoodsurveys = 0;

	$xp_per_level = 500;


	$statement = "SELECT teammate_nt_id, teammate_name, COUNT(external_survey_id) AS surveys FROM raw_data $sqldater 	$teamdefinition AND (kdi___email <> '' OR kdi___phone <> '') GROUP BY teammate_name ORDER BY teammate_name ASC";
	if (!$result = $db->query($statement)) {
		cl($sqla);
		cl($db->error);
	}

	$total_contacts_handled = 0;

	while ($row = $result->fetch_assoc()) {
		$teammate['contacts_handled'][$row['teammate_nt_id']] = 0;
		$statementx = "SELECT SUM(contacts_handled) as ch FROM prt060data WHERE ntid = '{$row['teammate_nt_id']}'";
		if (!$resultx = $db->query($statementx)) {
		}
		while ($rowx = $resultx->fetch_assoc()) {
			$teammate['contacts_handled'][$row['teammate_nt_id']] += $rowx['ch'];
		}
		$total_contacts_handled += $teammate['contacts_handled'][$row['teammate_nt_id']];


		$teammate['surveys'][$row['teammate_nt_id']] = $row['surveys'];

		// Rank signifies what your skill level is. This is based on the selected date range.
		$teammate['rank'][$row['teammate_nt_id']] = round($teammate['goodsurveys'][$row['teammate_nt_id']] / $teammate['surveys'][$row['teammate_nt_id']] * 10, 0);

		// Level is based on the experience.
		$teammate['level'][$row['teammate_nt_id']] = $teammate['contacts_handled'][$row['teammate_nt_id']] / $xp_per_level;

		$totalsurveys = $totalsurveys + $teammate['surveys'][$row['teammate_nt_id']];
		$totalgoodsurveys = $totalgoodsurveys + $teammate['goodsurveys'][$row['teammate_nt_id']];

		$teammate['guild'][$row['teammate_nt_id']] = guessteam($row['teammate_nt_id']);

		$teammate['guildname'][$row['teammate_nt_id']] = g("teams","team_name",$teammate['guild'][$row['teammate_nt_id']]);

	}

arsort($teammate['rank']);
$threshold = 1;
foreach ($teammate['rank'] as $ntid => $level) {
	if ($teammate['surveys'][$ntid] > $threshold) {
		if (($team < 1) or (guessteam($ntid) == $team)) {
			if ($teammate['level'][$ntid] >= 0) {
				$r = round($teammate['rank'][$ntid],0);
				if ($r != $oldrank) { echo "<div class='clearer'></div>";}
				$oldrank = $r;
				//$surveyratio = round(($teammate['goodsurveys'][$ntid]/$totalgoodsurveys * 70), 0);
				$fgcolor = g("teams","team_fgcolor",$teammate['guild'][$ntid]);
				$bgcolor = g("teams","team_bgcolor",$teammate['guild'][$ntid]);
				$brcolor = g("teams","team_border",$teammate['guild'][$ntid]);
				echo "<div class='player' style='background: #$bgcolor; color: #$fgcolor; box-shadow: 0 0 15px #$bgcolor;'>";
				$title = "";
				$title = "<span class='player-rank' style='background: #$fgcolor; color: #$bgcolor;'>";
				$title .= $titles[$ranks[$r]['titleid']]['title'];
				$title .= "</span>\n";
			echo "<div class='player-name'>";
			if ($titles[$ranks[$r]['titleid']]['left_or_right'] == 'left') { echo $title; }
			//echo "<a href='?a=surveys&c=Teammate_NT_ID&d=$ntid'>";
			echo "{$teammate['name'][$ntid]}";
			//echo "</a>";
			if ($titles[$ranks[$r]['titleid']]['left_or_right'] == 'right') { echo ", " . $title; }
			echo "</div>";
			echo "<div class='player-guild' style='color: #$fgcolor;'>{$teammate['guildname'][$ntid]}</div>\n";
			echo "<div class='player-level'>" . round($teammate['level'][$ntid],0) . "</div>\n";
			echo "<div class='player-description'></div>\n";
			echo "</div>";
		}
		}
	}
}
//echo "$total_contacts_handled";
echo "<div class='clearer'></div>";

}
