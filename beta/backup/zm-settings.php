<?php
if ($_GET[x] == 'clear073') {
	global $db;
	$sql = "DELETE from prt073data WHERE month = 'OCT-2017'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
}
else if ($_GET[x] == 'clear060') {
	$sql = "DELETE from prt060data WHERE month = 'OCT-2017'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
}

function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
		echo "<h1>Regions</h1>";
		if (isadmin()) {
			echo "<a href='?a=settings&b=add_region'>Add region</a>.<br><br>";
			if ($b=='add_region'){
				if ($_GET[region_name]){
					$sql = "INSERT INTO regions (region_name, region_admin_user_id) VALUES('{$_GET[region_name]}','$uid')";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}
				else {
					echo "<form>
					Region name: <input name='region_name'><br>
					<input name=a value=settings type=hidden><input name=b value=add_region type=hidden>
					<input type=submit></form><br>";
				}
			}
		}
		$sql = "SELECT * FROM regions ORDER by region_name ASC";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		while($row=$result->fetch_assoc()){
			echo "<div class='tbl_region'><a href='?a=settings&b=show_region&region={$row[region_id]}'>".$row[region_name]."</a></div>";
		}
		echo "<h1>Metrics</h1>";
		if (isadmin()) {
			echo "<a href='?a=settings&b=add_metric'>Add metric</a>.<br><br>";
			if ($b=='add_metric'){
				if ($_GET[metric_name]){
					$sql = "INSERT INTO metrics (metric_name, contract_id) VALUES('{$_GET[metric_name]}','{$_GET[contract_id]}')";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}
				else {
					echo "<form>
					Metric name: <input name='metric_name'><br>
					Contract: <select name='contract_id'>";
					$sql = "SELECT contract_id,contract_name FROM contracts";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					while($row=$result->fetch_assoc()){
							echo "<option value={$row[contract_id]}>{$row[contract_name]}</option>";
					}
					echo "</select><br>
					<input name=a value=settings type=hidden><input name=b value=add_metric type=hidden>
					<input type=submit></form><br>";
				}
			}
		}
		$sql = "SELECT * FROM metrics ORDER by metric_name ASC";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "<table class=tabler>";
		echo "<thead><th>Metric</th><th>Symbol</th><th>Rounding</th><th>Quality metric?</th></thead>";
		while($row=$result->fetch_assoc()){
			echo "<tr>";
			echo "<td><a href='?a=settings&b=show_metric&metric={$row[metric_id]}'>".$row[metric_name]."</a></td>";
			echo "<td>{$row[metric_symbol]}</td>";
			echo "<td>{$row[metric_rounding]}</td>";
			echo "<td>{$row[metric_quality]}</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "<h1>Users</h1>";
		if (isadmin()) {
			echo "<a href='?a=settings&b=add_user'>Add user</a>.<br><br>";
			if ($b=='add_user'){
				if ($_GET[user_name]){
					$sql = "INSERT INTO users (user_name) VALUES('{$_GET[user_name]}')";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					$sql = "SELECT user_id FROM users WHERE user_name = '{$_GET[user_name]}' LIMIT 1";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();$newuserid=$row[user_id];
					showerror("User {$_GET[user_name]} has been created. Send the user this link to create a password:<br> http://7612.uk/?n={$newuserid}");
				}
				else {
					echo "<form>
					user name: <input name='user_name'><br>
					<input name=a value=settings type=hidden><input name=b value=add_user type=hidden>
					<input type=submit></form><br>";
				}
			}
		}
		$sql = "SELECT * FROM users ORDER by user_name ASC";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		while($row=$result->fetch_assoc()){
			echo "<div class='tbl_user'><a href='?a=settings&b=show_user&user={$row[user_id]}'>".$row[user_name]."</a></div>";
		}

		if (isadmin()){
			echo "<h1>Data</h1>";
			echo "Upload a Medallia report.";
			echo "<form name=uploadform method=post enctype='multipart/form-data'>
			<input type=hidden name=a value=upload>
			<input type=file name='filedata' id='filedata'>
			<input type=submit></form>";

/*				echo "Upload a PRT060 report for individual AHT, volumes and RCR.";
			echo "<form name=uploadformaht method=post enctype='multipart/form-data'>
			<input type=hidden name=a value=uploadaht>
			<input type=file name='filedataaht' id='filedataaht'>
			<input type=submit></form>
			";
*/
			echo "Upload a VM055p report for team AHT & TR.";
			echo "<form name=uploadformvm method=post enctype='multipart/form-data'>
			<input type=hidden name=a value=uploadvm055p>
			<input type=file name='filedatavm' id='filedatavm'>
			<input type=submit></form>
			";
/*
			echo "Upload a PRT073 report for team RCR.";
			echo "<form name=uploadformprt073 method=post enctype='multipart/form-data'>
			<input type=hidden name=a value=uploadprt073>
			<input type=file name='filedataprt073' id='filedataprt073'>
			<input type=submit></form>
			";
*/
			echo "<hr><b>General PRT report uploader.</b> <a href='?a=settings&x=clear060'>Clear 060 data</a> |
			<a href='?a=settings&x=clear073'>Clear 073 data</a>";
			echo "<form name=uploadformprtgeneral method=post enctype='multipart/form-data'>
			<input type=hidden name=a value=uploadprtgeneral>
			<input type=file name='filedataprtgeneral' id='filedataprtgeneral'>
			<input type=submit></form>";
		}
	}
