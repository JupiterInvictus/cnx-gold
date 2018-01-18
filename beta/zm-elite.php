<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	// if a team has been specified, only show elites in that team.
	if ($team > 0) {
		echo "<h1>Team " . getteamname($team) . "</h1>";
	}
	else { echo "<h1>Belfast</h1>"; }

	echo "<div class='pad players'>";

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
	$threshold = 0;
	foreach ($teammate['rank'] as $ntid => $level) {
		if ($teammate['surveys'][$ntid] > $threshold) {
			if (($team < 1) or (guessteam($ntid) == $team)) {
				if ($teammate['level'][$ntid] >= 0) {
					$r = round($teammate['rank'][$ntid],0);
					if (($r != $oldrank) && ($r < 11)) {
						echo "<div class='clearer'>".($r);
						$ri = "";
						if ($r == 10) { $ri = 'angel'; }
						if ($r == 9) { $ri = 'crown'; }
						if ($r == 8) { $ri = 'astonished'; }
						if ($r == 7) { $ri = 'trophy'; }
						if ($r == 6) { $ri = '--1'; }
						if ($r == 5) { $ri = 'male-factory-worker'; }
						if ($r == 4) { $ri = 'anguished'; }
						if ($r == 3) { $ri = 'cold_sweat'; }
						if ($r == 2) { $ri = '-1'; }
						if ($r == 1) { $ri = 'confounded'; }
						echo "</div>";
					}
					$oldrank = $r;
					$fgcolor = g("teams","team_fgcolor",$teammate['guild'][$ntid]);
					$bgcolor = g("teams","team_bgcolor",$teammate['guild'][$ntid]);
					$brcolor = g("teams","team_border",$teammate['guild'][$ntid]);
					$iso = '';
					if ($teammate['guildname'][$ntid] == 'Denmark') { $iso = 'flag-dk'; }
					if ($teammate['guildname'][$ntid] == 'Netherlands') { $iso = 'flag-nl'; }
					if ($teammate['guildname'][$ntid] == 'Norway') { $iso = 'flag-no'; }
					if ($teammate['guildname'][$ntid] == 'Sweden') { $iso = 'flag-se'; }
					if ($teammate['guild'][$ntid] == '15') { $iso = 'gb'; }
					echo "<div class='player' style='background: #$bgcolor; color: #$fgcolor;'>";
					echo "<div class='player-photo'>";
					echo getphoto($ntid, 32);
					echo "</div>";

					echo "<div class='player-row'>";
						echo "<div class='player-rank'>";
							echo "<i class='em em-$ri'></i>";
							echo "</div>";
							echo "<div class='player-guild'>";
							echo "<i class='em em-$iso'></i>";
							echo "</div>";

						echo "<div class='player-level'>";
							echo round($teammate['level'][$ntid], 0);
							echo "</div>";
							echo "<div class='player-name'>";
							echo $teammate['name'][$ntid];
							echo "</div>";
						echo "</div>";
					echo "</div>";
				}
			}
		}
	}
	echo "</div>";
}
