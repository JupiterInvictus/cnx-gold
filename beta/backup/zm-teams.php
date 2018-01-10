<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
		if (isadmin()) {
			echo "<a href='?a=teams&b=add'>Add team</a>.<br><br>";
			if ($b=='add'){
				if ($_GET[name]){
					$sql = "INSERT INTO teams (team_name, team_admin_user_id, contract_id) VALUES('{$_GET[name]}','$uid','{$_GET[contract]}')";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}
				else {
					echo "<form>
					Team name: <input name='name'><br>
					Contract: <select name='contract'>";
					$sql = "SELECT contract_id,contract_name FROM contracts";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					while($row=$result->fetch_assoc()){
							echo "<option value={$row[contract_id]}>{$row[contract_name]}</option>";
					}
					echo "</select><br>
					<input name=a value=teams type=hidden><input name=b value=add type=hidden><input type=submit></form><br>";
				}
			}
		}
		$sql = "SELECT * FROM teams ORDER by team_name ASC";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "<ul>";
		while($row=$result->fetch_assoc()){
			echo "<li><a href='?a=teams&b=show_team&team={$row[id]}'>".$row[team_name]."</a>";
		}
		echo "</ul>";
		if ($b=='show_team'){
			$team=$_GET[team];
			if (isadmin()) {
				if ($c == 'delsurvey'){
					$sql = "DELETE FROM team_data_definitions WHERE id = '$team' AND raw_data_column = '{$_GET[column]}' AND raw_data_data = '{$_GET[data]}' LIMIT 1";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}
				if ($c == 'delaht'){
					$sql = "DELETE FROM team_aht_definitions WHERE id = '$team' AND ahtreport_data_column = '{$_GET[column]}' AND ahtreport_data_data = '{$_GET[data]}' LIMIT 1";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}
			}
			if ($_GET[adata]){
				$sql = "INSERT INTO team_aht_definitions (team_id,ahtreport_data_column,ahtreport_data_data) VALUES('$team','$column_name','{$_GET[adata]}')";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "Definition added.";
			}
			if ($_GET[vmdef]){
				$sql = "UPDATE teams SET vmdata_team = '{$_GET[vmdef]}' WHERE id = '{$team}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "Definition added.";
			}
			$sql = "SELECT * FROM teams WHERE id='$team' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			echo "<h1>{$row[team_name]}</h1>";
			$sql = "SELECT * FROM team_data_definitions WHERE team_id = '$team' ORDER by raw_data_column, raw_data_data";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			echo "<h3>Survey definitions: </h3><ul>";
			while($row=$result->fetch_assoc()){
				echo "<li>";
				if (isadmin()) {
					echo "<a href='?a=$a&b=$b&team=$team&c=delsurvey&column={$row[raw_data_column]}&data={$row[raw_data_data]}'>( x )</a> &nbsp; ";
				}
				echo $row[raw_data_column] . " = " . $row[raw_data_data] . "</li>";
			}
			echo "</ul>";
				echo "Add column and survey filters for this team.";
				$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='raw_data' AND table_schema='concentrix'";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "<form><br>Data column: <select name=column_name onChange='location.href=\"?a=teams&b=show_team&team=$team&column_name=\" + this.value;'>";
				echo "<option></option>";
				while($row=$result->fetch_assoc()){
					echo "<option";
					if ($_GET[column_name]==$row[column_name]){echo" selected";}
					echo ">".$row[column_name]."</option>";
				}
				echo "</select>";
				if ($_GET[column_name]){
					$column_name=$_GET[column_name];
					$sql = "SELECT distinct $column_name FROM raw_data ORDER by $column_name ASC";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					echo "<br>Data: <select name=data onChange='location.href=\"?a=teams&b=show_team&team=$team&column_name=$column_name&data=\" + this.value;'>";
					echo "<option></option>";
					while($row=$result->fetch_assoc()){
						echo "<option";
						if ($_GET[data]==$row[$column_name]){echo" selected";}
						echo ">".$row[$column_name]."</option>";
					}
					echo "</select>";
					echo "<input name=a type=hidden value=teams>
					<input name=b type=hidden value=show_team>
					<input name=team type=hidden value=$team>
					</form>";
					if ($_GET[data]){
						$sql = "INSERT INTO team_data_definitions (team_id,raw_data_column,raw_data_data) VALUES('$team','$column_name','{$_GET[data]}')";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						echo "Definition added.";
					}
				}
			$sql = "SELECT * FROM team_aht_definitions WHERE team_id = '$team' ORDER by ahtreport_data_column, ahtreport_data_data";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			echo "<h3>AHT definitions:</h3>";
			while($row=$result->fetch_assoc()){
				echo "<li>";
				if (isadmin()) {
					echo "<a href='?a=$a&b=$b&team=$team&c=delaht&column={$row[ahtreport_data_column]}&data={$row[ahtreport_data_data]}'>( x )</a> &nbsp; ";
				}
				echo $row[ahtreport_data_column] . " = " . $row[ahtreport_data_data] . "</li>";
			}
			echo "</ul>";
			echo "<br>Add column and AHT filters for this team.";
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='prt060data' AND table_schema='concentrix'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			echo "<form><br>Data column: <select name=acolumn_name onChange='location.href=\"?a=teams&b=show_team&team=$team&acolumn_name=\" + this.value;'>";
				echo "<option></option>";
				while($row=$result->fetch_assoc()){
					echo "<option";
					if ($_GET[acolumn_name]==$row[column_name]){echo" selected";}
					echo ">".$row[column_name]."</option>";
				}
				echo "</select>";
				if ($_GET[acolumn_name]){
					$column_name=$_GET[acolumn_name];
					$sql = "SELECT distinct $column_name FROM prt060data ORDER by $column_name ASC";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					echo "<br>Data: <select name=data onChange='location.href=\"?a=teams&b=show_team&team=$team&acolumn_name=$column_name&adata=\" + this.value;'>";
					echo "<option></option>";
					while($row=$result->fetch_assoc()){
						echo "<option";
						echo ">".$row[$column_name]."</option>";
					}
					echo "</select>";
					echo "<input name=a type=hidden value=teams>
					<input name=b type=hidden value=show_team>
					<input name=team type=hidden value=$team>
					</form>";
					echo "<br><br><br>";
				}
			echo "<h3>VM055p definition</h3>";
			echo "Select a team from VM055p: ";
			echo "<select id=vmdef onChange='location.href=\"?a=$a&b=$b&team=$team&vmdef=\" + this.value;'>";
			$sql = "SELECT vmdata_team FROM teams WHERE id = '$team' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			$vmdef = $row[vmdata_team];
			$sql = "SELECT DISTINCT team FROM vm055_data ORDER BY team asc";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			echo "<option></option>";
			while($row=$result->fetch_assoc()){
				echo "<option";
				if ($row[team]==$vmdef){echo " selected";}
				echo ">".$row[team]."</option>";
			}
			echo "</select>";
		}
	}
	// m3trics
