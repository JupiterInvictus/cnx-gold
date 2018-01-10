<?php

/*

	CHANGE LOG
	=================================
	Version	Date				By							Comment
	1.3.1 	2017-10-03	joakim.saettem	Fixing upload of prt file. Did not upload all months. FIXED.
	1.3.2		2017-10-04									function vm now returns n/a instead of 0 if there is no value.
	1.3.3		2017-10-13									function vm will now return data from prt073 as backup.
	1.3.4		2017-11-27									added function showphoto to determine if photos should be shown.

*/

$timeout_duration = 432000;
define('CHARSET', 'ISO-8859-1');
define('REPLACE_FLAGS', ENT_COMPAT | ENT_XHTML);
set_time_limit(600);
session_start();
if (isset($_SESSION[‘LAST_ACTIVITY’]) && ($time - $_SESSION[‘LAST_ACTIVITY’]) > $timeout_duration) {
	session_unset();
	session_destroy();
	session_start();
}

$_SESSION[‘LAST_ACTIVITY’] = $time;
$month['01']="January";$month['02']="February";$month['03']="March";$month['04']="April";$month['05']="May";$month['06']="June";$month['07']="July";$month['08']="August";
$month['09']="September";$month['10']="October";$month['11']="November";$month['12']="December";
$month['1']="January";$month['2']="February";$month['3']="March";$month['4']="April";$month['5']="May";$month['6']="June";$month['7']="July";$month['8']="August";
$month['9']="September";
$monthy['01']='JAN';$monthy['02']='FEB';$monthy['03']='MAR';$monthy['04']='APR';$monthy['05']='MAY';$monthy['06']='JUN';
$monthy['07']='JUL';$monthy['08']='AUG';$monthy['09']='SEP';$monthy['10']='OCT';
$monthy['11']='NOV';$monthy['12']='DEC';
$monthy['1']='JAN';$monthy['2']='FEB';$monthy['3']='MAR';
$monthy['4']='APR';$monthy['5']='MAY';$monthy['6']='JUN';
$monthy['7']='JUL';$monthy['8']='AUG';$monthy['9']='SEP';
$days['1']='Monday';$days['2']='Tuesday';$days['3']='Wednesday';$days['4']='Thursday';$days['5']='Friday';$days['6']='Saturday';$days['0']='Sunday';
error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
$uid = $_SESSION[user_id];
include "$path../../zm-core-db.php"; $ddb = 'concentrix'; db_connect();
include "$path../../zm-core-login.php";
include "$path../../zm-core-functions.php";

global $team_id_def;

$sql = "SELECT team_name,id FROM teams";
if (!$result = $db->query($sql)) {
	cl($sql);
	cl($db->error);
}

$teams = 0;

while ($row = $result->fetch_assoc()) {
	$teams++;
	$team_id = $row[id];
	$sqla = "SELECT * FROM team_data_definitions WHERE team_id = $team_id";
	if (!$resulta = $db->query($sqla)) {
		cl($sqla);
		cl($db->error);
	}

	$definitions = 0;
	$team_id_def[$team_id] = '';

	while ($rowa = $resulta->fetch_assoc()) {
		if ($definitions) {
			$team_id_def[$team_id].=' OR ';
		}
		$team_id_def[$team_id] .= $rowa[raw_data_column] . "='{$rowa[raw_data_data]}'";
		$definitions++;
	}
	if ($team_id_def[$team_id]) {
		$team_id_def[$team_id]=" AND (".$team_id_def[$team_id] . ")";
	}
}

function html($string) {
	return htmlspecialchars($string, REPLACE_FLAGS, CHARSET);
}

function sq($q){global $db;if(!$s=$db->query($q)){echo $q;echo $db->error;}}
function sqr($q){global $db;if(!$s=$db->query($q)){cl($q);cl($db->error);}return $s->fetch_assoc();}

// Get database data
function g ($tablename, $columnname, $tmpid) {
	global $db;
	$sql = "SELECT $columnname FROM $tablename WHERE id = '$tmpid' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[$columnname];
}

// Get database data
function gg ($tablename, $columnname, $idcolumnname, $tmpid) {
	global $db;
	$sql = "SELECT $columnname FROM $tablename WHERE $idcolumnname = '$tmpid' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[$columnname];
}

// Get metric symbol
function gms($metric_id){
	global $db;
	$sql = "SELECT metric_symbol FROM metrics WHERE metric_id = '$metric_id' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[metric_symbol];
}

// Get metric rounding
function gmr($metric_id){
	global $db;
	$sql = "SELECT metric_rounding FROM metrics WHERE metric_id = '$metric_id' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[metric_rounding];
}

function getlastday($year,$month) {
	$ldm=cal_days_in_month(CAL_GREGORIAN,$month,$year);
	return $ldm;
}

function getlatest($teamdeff){
	global $startdate,$enddate, $db;
	$valuestartdate_year = substr($startdate,0,4);
	$valuestartdate_month = substr($startdate,5,2);
	$valuestartdate_day = substr($startdate,8,2);
	$valueenddate_year = substr($enddate,0,4);
	$valueenddate_month = substr($enddate,5,2);
	$valueenddate_day = substr($enddate,8,2);
	$valuestartdate_excel = unixdate_to_exceldate(mktime(0,0,0,$valuestartdate_month,$valuestartdate_day,$valuestartdate_year));
	$valueenddate_excel = unixdate_to_exceldate(mktime(23,59,59,$valueenddate_month,$valueenddate_day,$valueenddate_year));
	$valuesqldater = " WHERE Teammate_Contact_Date > $valuestartdate_excel";
	$valuesqldater .= " AND Teammate_Contact_Date < $valueenddate_excel";
	$sql = "SELECT Response_Date FROM raw_data $valuesqldater $teamdeff ORDER by Response_Date DESC LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[Response_Date]==''){return 'n/a';}
	return date("Y - m - d",exceldate_to_unixedate($row[Response_Date]));
}



function exceldate_to_unixedate ($exceldate) {
	return ($exceldate - 25569) * 86400;
}
function unixdate_to_exceldate ($unixdate) {
	return 25569 + ($unixdate / 86400);
}

function countsurveys($username){
	global $db, $teamdefinition,$sqldater;
	$surveys=0;
	$sql = "SELECT COUNT(*) as id FROM raw_data  $sqldater $teamdefinition AND teammate_nt_id='$username' ";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row = $result->fetch_assoc();
	$surveys = $row[id];
	return $surveys;
}
function processreport($filename) {
	global $db;
	echo " Processing Medallia report... this can take a few minutes...";
	require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$rownumber = 0;
	foreach ($objWorksheet->getRowIterator() as $row) {
		$columnnumber = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);
		foreach ($cellIterator as $cell) {
			$columnnumber++;
			if ($rownumber==2) {
				$column[$columnnumber] = str_replace(" ","_",$cell->getValue());
				$column[$columnnumber] = substr($column[$columnnumber],0,30);
				$column[$columnnumber] = str_replace("(","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace(")","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace("/","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace("?","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace("'","",$column[$columnnumber]);
				$column[$columnnumber] = str_replace(",","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace("-","_",$column[$columnnumber]);
			}
			elseif($rownumber>2) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
		}
		$rownumber++;
	}

	// Add the columns to the database table.
	for($a = 1; $a<=$columnnumber; $a++){
		$sql = "alter table raw_data add {$column[$a]} text";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	}

	// Add the data.
	for ($a = 1; $a<$rownumber; $a++){

		// Try to find out if there data already exists.
		$sql = "INSERT INTO raw_data (";
		for ($b=1;$b<=$columnnumber;$b++){
			$sql .=	"{$column[$b]},";
		}
		$sql = substr($sql,0,-1);
		$sql .= ") VALUES(";
		for ($b=1;$b<=$columnnumber;$b++){
			$data[$a][$b] = $db->real_escape_string($data[$a][$b]);
			$sql .=	"'{$data[$a][$b]}',";
		}
		$sql = substr($sql,0,-1);
		$sql .= ')';
		if(!$result=$db->query($sql)){
			if (substr($db->error,0,15)!='Duplicate entry') {
				cl($sql);cl($db->error);
			}
		}
	}
	setsetting('medalliadata',date("Y-m-d H:i:s"));
}
function processahtreport($filename) {
	global $db;
	echo " Processing AHT report...";
	require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	for($x=0;$x<$objPHPExcel->getSheetCount();$x++){
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($x);
		unset($column);
		unset($columnnumber);
		unset($rownumber);
		unset($data);
		$rownumber = 0;
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnnumber = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);
			foreach ($cellIterator as $cell) {
				$columnnumber++;
				if ($rownumber==6) {
					$column[$columnnumber] = str_replace(" ","_",$cell->getValue());
					$column[$columnnumber] = substr($column[$columnnumber],0,30);
					$column[$columnnumber] = str_replace("(","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(")","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("/","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("?","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("'","",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(",","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(".","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("-","_",$column[$columnnumber]);
					if ($column[$columnnumber]=='Month'){$monthcolumn = $columnnumber;}
					if ($column[$columnnumber]=='NTID'){$ntidcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Name'){$queuenamecolumn = $columnnumber;}
				}
				elseif($rownumber>6) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
			}
			$rownumber++;
		}

		// Add the columns to the database table.
		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table prt060data add {$column[$a]} text";	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}	}

		// Add the data.
		for ($a = 7; $a<$rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id FROM prt060data WHERE month = '{$data[$a][$monthcolumn]}' AND ntid = '{$data[$a][$ntidcolumn]}' AND queue_name = '{$data[$a][$queuenamecolumn]}' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM prt060data WHERE id = '{$row[id]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			$sql="INSERT INTO prt060data (";for ($b=1;$b<=$columnnumber;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
			for ($b=1;$b<=$columnnumber;$b++){$data[$a][$b]=$db->real_escape_string($data[$a][$b]);$sql.="'{$data[$a][$b]}',";}$sql=substr($sql,0,-1);$sql.=')';
 			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	echo "Done.";
	setsetting('prt060pdata',date("Y-m-d H:i:s"));
}

function processGeneralPrtReport($reportFile) {
	global $db, $path;
	$reportType = substr($reportFile, 8, 6);
	$reportType = strtolower($reportType);
	$reportFrom = substr($reportFile, -13, 8);
	// check if last char is ), then split the string and do the -13 8 substr on the left.
	if (substr($reportFrom, -1) == ')') {
		list($reportFrom, $bla) = explode(" ", $reportFile);
		$reportFrom = substr($reportFrom, -8, 8);
	}

	echo "<hr /></hgr>Processing <b>$reportType</b> report '$reportFile' from $reportFrom... ";

	// Load the required Excel stuff.
	require_once dirname(__FILE__) . "/{$path}Classes/PHPExcel/IOFactory.php";
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($reportFile);

	echo "//";

	// Loop through all sheets in the file.
	for($sheetNumber = 0; $sheetNumber < $objPHPExcel->getSheetCount(); $sheetNumber++){

		# Set the current sheet.
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($sheetNumber);

		# Delete the values in the variables used.
		unset($column); $rowNumber[$sheetNumber] = 0;

		// Loop through all the rows of the sheet.
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnNumber[$sheetNumber] = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);

			// Loop through all the columns in the sheet.
			foreach ($cellIterator as $cell) {
				$columnNumber[$sheetNumber]++;

				// In a PRT report, row 6 is the table header row.
				if ($rowNumber[$sheetNumber] == 6) {
					$column[$columnNumber[$sheetNumber]] = $cell->getValue();
					$column[$columnNumber[$sheetNumber]] = fixColumnName($column[$columnNumber[$sheetNumber]]);
				}
				elseif ($rowNumber > 6) {
					$data[$sheetNumber][$rowNumber[$sheetNumber]][$columnNumber[$sheetNumber]] = $cell->getValue();
				}
			}
			$rowNumber[$sheetNumber]++;
		}
	}
	if ($_GET[x] == 'override') {
	// Add columns to the database table.
	for($a = 1; $a <= $columnNumber; $a++){
		$sql = "alter table {$reportType}data add {$column[$a]} text";
		if (!$result = $db->query($sql)){
			cl($sql);
			cl($db->error);
		}
	}
}

$table_deleted = false;
	// Add the data.
	for ($sheetLoop = 0; $sheetLoop <= $sheetNumber; $sheetLoop++) {
		for ($a = 7; $a<$rowNumber[$sheetLoop]; $a++){
			$dataFilter = "";
			for ($b = 1; $b <= $columnNumber[$sheetLoop]; $b++) { $dataFilter .= "{$column[$b]} = '" . $data[$sheetLoop][$a][$b] . "' AND "; }
			$dataFilter = substr($dataFilter, 0, -5);

			$sql = "INSERT INTO {$reportType}data (";
			for ($b=1;$b<=$columnNumber[$sheetLoop];$b++){
				$sql.="{$column[$b]},";
			}
			$sql = substr($sql,0,-1);
			$sql .= ") VALUES(";
			for ($b=1; $b <= $columnNumber[$sheetLoop]; $b++) {
				$data[$sheetLoop][$a][$b] = $db->real_escape_string($data[$sheetLoop][$a][$b]);
				$sql .= "'{$data[$sheetLoop][$a][$b]}',";
			}
			$sql = substr($sql,0,-1);
			$sql .= ')';
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	echo "Done.";
	setsetting($reportType, date("Y-m-d H:i:s"));
	setsetting($reportType . "from", $reportFrom);
}

function fixColumnName($columnName) {
	// Limit to 64 characters due to mysql limitation.
	$columnName = substr($columnName, 0, 64);

	// Replace unsupported characters.
	$columnName = str_replace(" ", "_", $columnName);
	$columnName = str_replace("(", "_", $columnName);
	$columnName = str_replace(")", "_", $columnName);
	$columnName = str_replace("/", "_", $columnName);
	$columnName = str_replace("?", "_", $columnName);
	$columnName = str_replace("%", "_", $columnName);
	$columnName = str_replace(":", "_", $columnName);
	$columnName = str_replace("'", "",  $columnName);
	$columnName = str_replace(",", "_", $columnName);
	$columnName = str_replace(".", "_", $columnName);
	$columnName = str_replace("-", "_", $columnName);

	return $columnName;
}
/*function processprt073report($filename) {
	global $db;
	echo " Processing prt073 report...";
	require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	for($x=0;$x<$objPHPExcel->getSheetCount();$x++){
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($x);
		unset($column);
		unset($columnnumber);
		unset($rownumber);
		unset($data);
		$rownumber = 0;
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnnumber = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);
			foreach ($cellIterator as $cell) {
				$columnnumber++;
				if ($rownumber==6) {
					$column[$columnnumber] = str_replace(" ","_",$cell->getValue());
					$column[$columnnumber] = substr($column[$columnnumber],0,30);
					$column[$columnnumber] = str_replace("(","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(")","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("/","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("?","_",$column[$columnnumber]);
          $column[$columnnumber] = str_replace("%","_",$column[$columnnumber]);
          $column[$columnnumber] = str_replace(":","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("'","",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(",","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(".","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("-","_",$column[$columnnumber]);
					if ($column[$columnnumber]=='Month'){$monthcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Skillset'){$ntidcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Name'){$queuenamecolumn = $columnnumber;}
				}
				elseif($rownumber>6) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
			}
			$rownumber++;
		}

		// Add the columns to the database table.
		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table prt073data add {$column[$a]} text";	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}	}

		// Add the data.
		for ($a = 7; $a<$rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id FROM prt073data WHERE month = '{$data[$a][$monthcolumn]}' AND queue_skillset = '{$data[$a][$ntidcolumn]}' AND queue_name = 'Total' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM prt073data WHERE id = '{$row[id]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			if ($data[$a][$queuenamecolumn] == 'Total') {
				$sql="INSERT INTO prt073data (";
				for ($b=1;$b<=$columnnumber;$b++){
					$sql.="{$column[$b]},";
				}
				$sql = substr($sql,0,-1);
				$sql .= ") VALUES(";
				for ($b=1; $b <= $columnnumber; $b++) {
          $data[$a][$b] = $db->real_escape_string($data[$a][$b]);
          $sql .= "'{$data[$a][$b]}',";
        }
        $sql = substr($sql,0,-1);
        $sql .= ')';
   			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
      }
		}
	}
	echo "Done.";
}
*/

function processprtreport($filename) {
	global $db;
	echo " Processing PRT report...";
	require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	for($x=0;$x<$objPHPExcel->getSheetCount();$x++){
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($x);
		unset($column);
		unset($columnnumber);
		unset($rownumber);
		unset($data);
		$rownumber = 0;
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnnumber = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);
			foreach ($cellIterator as $cell) {
				$columnnumber++;
				if ($rownumber==6) {
					$column[$columnnumber] = str_replace(" ","_",$cell->getValue());
					$column[$columnnumber] = substr($column[$columnnumber],0,30);
					$column[$columnnumber] = str_replace("(","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(")","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("/","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("?","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("'","",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(",","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(".","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("-","_",$column[$columnnumber]);
					if ($column[$columnnumber]=='Date'){$monthcolumn = $columnnumber;}
					if ($column[$columnnumber]=='NTID'){$ntidcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Name'){$queuenamecolumn = $columnnumber;}
				}
				elseif($rownumber>6) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
			}
			$rownumber++;
		}

		// Add the columns to the database table.
		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table prt058data add {$column[$a]} text";	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}	}

		// Add the data.
		for ($a = 7; $a<$rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id FROM prt058data WHERE date = '{$data[$a][$monthcolumn]}' AND ntid = '{$data[$a][$ntidcolumn]}' AND queue_name = '{$data[$a][$queuenamecolumn]}' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM prt058data WHERE id = '{$row[id]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			$sql="INSERT INTO prt058data (";for ($b=1;$b<=$columnnumber;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
			for ($b=1;$b<=$columnnumber;$b++){$data[$a][$b]=$db->real_escape_string($data[$a][$b]);$sql.="'{$data[$a][$b]}',";}$sql=substr($sql,0,-1);$sql.=')';
 			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	echo "Done.";
}
function processvmreport($filename) {
	global $db; echo " Processing VM055p report..."; require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007(); $objReader->setReadDataOnly(true); $objPHPExcel = $objReader->load($filename);
	for($x=0;$x<$objPHPExcel->getSheetCount();$x++){
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($x);
		$teamo = $objWorksheet->getTitle();
		unset($column); unset($columnnumber); unset($rownumber); unset($data);
		$months = 0; $monthdata = []; $rownumber = 0;	 $inverted_col = 0; $inverted_row = 0;
		$column[0] = "team";
		$column[1] = "month";

		$nom = 0; // Number of months.
		$rownumber = 0;
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnnumber = 0; $cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(FALSE);
			foreach ($cellIterator as $cell) {
				$columnnumber++;
				$rawdata[$columnnumber][$rownumber] = $cell->getValue();
			}
			$rownumber++;
		}
		$savedskillgroup = "";
		$colos = 1;
		$startcol = 0;
		$endcol = 0;
		for ($a = 9; $a < $rownumber; $a++){
			if ($rawdata[1][$a]!=''){$savedskillgroup = $rawdata[1][$a];}
			if ($rawdata[1][$a]==''){$rawdata[1][$a]=$savedskillgroup;}
			if ($rawdata[1][$a] == 'Total'){
				if ($startcol == 0){$startcol = $a;}
				$endcol = $a;
				$colos++;
				$column[$colos]=$rawdata[3][$a];
				$column[$colos] = str_replace(" ","_",$column[$colos]);
				$column[$colos] = substr($column[$colos],0,30);
				$column[$colos] = str_replace("(","_",$column[$colos]);
				$column[$colos] = str_replace(")","_",$column[$colos]);
				$column[$colos] = str_replace("/","_",$column[$colos]);
				$column[$colos] = str_replace("?","_",$column[$colos]);
				$column[$colos] = str_replace("'","",$column[$colos]);
				$column[$colos] = str_replace(",","_",$column[$colos]);
				$column[$colos] = str_replace("-","_",$column[$colos]);
				$column[$colos] = str_replace(":","_",$column[$colos]);
				$column[$colos] = str_replace("%","perc",$column[$colos]);
			}
		}

		// Add the columns to the database table.
		for($a = 0; $a<=$colos; $a++){
			$sql = "alter table vm055_data add {$column[$a]} text";
			if(!$result=$db->query($sql)){
				cl($sql);cl($db->error);
			}
		}
		for ($a = 5;$a <=$columnnumber; $a++){
			if (($rawdata[$a][5]!='') && ($rawdata[$a][5]!='Total')){
				// find the data column for this month
				for($b=$a;$b<$a+7;$b++){if ($datacolumn[$teamo][$rawdata[$a][5]]==''){if($rawdata[$b][6]=='Total'){$datacolumn[$teamo][$rawdata[$a][5]]=$b;}}}

				// Try to find out if the data already exists.
				$sql = "SELECT id FROM vm055_data WHERE month = '{$rawdata[$a][5]}' AND team = '$teamo' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				$row=$result->fetch_assoc();
				if ($row[id]>0){
					$sql = "DELETE FROM vm055_data WHERE id = '{$row[id]}' LIMIT 1"; if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}
				$sql="INSERT INTO vm055_data (";for ($b=0;$b<=$colos;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
				$sql .= "'$teamo','{$rawdata[$a][5]}',";
				for ($c = $startcol;$c <= $endcol; $c++) { $ddb = $db->real_escape_string($rawdata[$datacolumn[$teamo][$rawdata[$a][5]]][$c]);$sql.="'$ddb',";}
				$sql=substr($sql,0,-1);
				$sql.=")";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
		}
	}
	setsetting('vm055data',date("Y-m-d H:i:s"));
	echo "<script>location.href='?';</script>";
}

function addleftchoice($name) {
  global $_GET, $showleftchoicetext, $path;
  echo "<div id='{$name}' onmousedown=\"location.href='?a={$name}';\" ";
  if ($showleftchoicetext) {
    echo "class='leftchoice";
    if ($_GET[a]==$name) {echo " leftchoiceselected";}
    echo "'><img src='{$path}images/{$name}.png'>";
    echo ucfirst($name);
  }
  else {
    echo "class='leftchoicesmall";
    if ($_GET[a]==$name) {echo " leftchoiceselectedsmall";}
    echo "' title='" . ucfirst($name) . "'>";
		$name = geticon($name);
    echo "<div class='fa fa-$name'></div>";
  }
  echo "</div>";
}

function dd($datestring){
	$today = date("Y-m-d");
	$datepart = substr($datestring,0,10);
	$timepart = substr($datestring,10,6);
	if ($datepart == $today) { $datepart = "<b>today</b>";}
	else { $datepart = "<i>$datepart</i>"; }
	return $datepart . " @ ". $timepart;
}
function getsetting($setting) {
	global $db;
	$sql = "SELECT settingvalue FROM systemsettings WHERE settingname = '$setting' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[settingvalue];
}
function setsetting($setting,$value) {
	global $db;
	$sql = "UPDATE systemsettings SET settingvalue = '$value' WHERE settingname = '$setting' LIMIT 1";
	if(!$result=$db->query($sql)){
		cl($sql);cl($db->error);
		$sql = "INSERT INTO systemsettings (settingname, settingvalue) VALUES('$setting', '$value')";
		if(!$result=$db->query($sql)){
			cl($sql);cl($db->error);
		}
	}
}
function isadmin() {
  global $db, $uid;
  $sql = "SELECT user_admin FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row[user_admin];
}
function gettheme(){
	global $db, $uid;
	if (!$_SESSION['logged_in']){return;}
	$sql = "SELECT user_theme FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row[user_theme];
}
function ismgr() {
  global $db, $uid;
  $sql = "SELECT user_manager FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row[user_manager];
}
function getmanager($username){
	global $db;
	$sql = "SELECT team_leader_name FROM raw_data WHERE teammate_nt_id = '{$username}' ORDER by response_date DESC LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  $row=$result->fetch_assoc();
	return $row[team_leader_name];
}


// Let's try to guess the team of a user. Argument: NT ID. Returns the team ID if successful, -1 if not.
function guessteam($username){
	global $db;

	//echo $username . "<br>";
	$sql = "SELECT team_id FROM users_teams WHERE teammate_nt_id = '$username' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[team_id]=="")	{
		$sql = "SELECT queue_source_name, count(queue_source_name) as qc FROM raw_data WHERE teammate_nt_id = '$username' GROUP by queue_source_name ORDER by qc DESC LIMIT 1";

		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		$sql = "SELECT team_id FROM team_data_definitions WHERE raw_data_column = 'Queue_Source_Name' AND raw_data_data = '{$row['queue_source_name']}' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();

		$sql = "INSERT INTO users_teams (team_id, teammate_nt_id) VALUES('{$row[team_id]}','$username')";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	}
	return $row['team_id'];
}


function getteamname($team_id){
	global $db;
	$sql = "SELECT team_name FROM teams WHERE id = '$team_id'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[team_name];
}
function targetcolor($value, $contract, $metric, $team, $date, $submetric) {
	if ($value==='--'){return '';}
	if ($metric===''){return '';}
	if ($team){
		global $db, $bad_color;
		// There should only be one match.
		$sub=" AND submetric = '$submetric'";
		$sql = "SELECT target_color,target_textcolor,target_value_low,target_value_high FROM targets WHERE target_value_low <= '$value' AND target_value_high >= '$value-0.01' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND target_start_date <= '$date' AND target_stop_date >= '$date' $sub LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[target_color]===''){
			$date = "0000-00-00 00:00:00";
			$sql = "SELECT target_color,target_textcolor,target_value_low,target_value_high FROM targets WHERE target_value_low <= $value AND target_value_high >= $value-0.01 AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND target_start_date <= '$date' AND target_stop_date >= '$date' $sub LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
		}
		if (($metric==2) or ($metric==6) or ($metric==17)) {
			$targetdiff = $row[target_value_low] - $value;
		}
		else {
			$targetdiff = $row[target_value_high] - $value;
		}
		$targetdiff = $targetdiff / 100;
		if (($metric==2) or ($metric==6) or ($metric==17)) {
			if (($row[target_color]==$bad_color) && ($targetdiff > -0.05)) {
				return array("fdae61","dd8e41");
			}
		}
		else {
			if (($row[target_color]==$bad_color) && ($targetdiff < 0.05)) {
				return array("fdae61","dd8e41");
			}
		}
		return array($row[target_color],$row[target_textcolor]);
	}
	else {return '';}
}

function gettarget($contract,$metric,$team,$date,$highorlow,$submetric){
	if($team){
		global $db;
		if ($submetric){ $submetric = "AND submetric='$submetric'"; }
		else { $submetric = "AND submetric=''"; }
		$tvhol = 'target_value_' . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '$bad_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){
			$sql = "SELECT $tvhol FROM targets WHERE target_color = '$bad_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_start_date = '0000-00-00 00:00:00' AND target_stop_date = '0000-00-00 00:00:00' AND target_team_id='$team' $submetric LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[$tvhol]==''){return 0;}
		}
		return $row[$tvhol];
	}
	return 0;
}
function getgoodtarget($contract,$metric,$team,$date,$highorlow){
	if($team){
		global $db, $bad_color, $good_color;
		$submetric = "AND submetric=''";
		$tvhol = 'target_value_' . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '$good_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric ORDER by $tvhol DESC LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){
			$sql = "SELECT $tvhol FROM targets WHERE target_color = '$good_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_start_date = '0000-00-00 00:00:00' AND target_stop_date = '0000-00-00 00:00:00' AND target_team_id='$team' $submetric LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[$tvhol]==''){return 0;}
		}
		return $row[$tvhol];
	}
	return 0;
}
function getgreattarget($contract,$metric,$team,$date,$highorlow){
	if($team){
		global $db;
		$submetric = "AND submetric=''";
		$tvhol = "target_value_$highorlow";// . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '009030' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric ORDER by $tvhol DESC LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		return $row[$tvhol];
	}
	return 0;
}
function getsubtarget($contract,$metric,$team,$date,$submetric){
	global $db, $good_color;
	$hol='low';
	if ($metric==2){$hol='high';}
	$tvhol = "target_value_$hol";
	$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '$good_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND submetric='$submetric' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
 	$row=$result->fetch_assoc();
	if ($row[$tvhol]==''){
		$sql = "SELECT $tvhol FROM targets WHERE target_color = '$good_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND submetric='$submetric' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	 	$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){ return 0; }
	}
	return $row[$tvhol];
}
function surveycount($optionalmonth, $teamdefinition){
	global $db,$sqldater, $currentyear, $currentmonth;
	$surveys=0;
  if ($optionalmonth == "previous month") {
    $tmpyear = $currentyear;
    $tmpmonth = $currentmonth-1;
    if ($tmpmonth < 1){$tmpmonth = 12; $tmpyear--; }
    $tmpdate = unixdate_to_exceldate(mktime(0,0,0,$tmpmonth,1,$tmpyear));
    $lastday = getlastday($tmpyear,$tmpmonth);
    $tmpdater = unixdate_to_exceldate(mktime(23,59,59,$tmpmonth,$lastday,$tmpyear));
    $tmpsqldater = "WHERE Teammate_Contact_Date > $tmpdate AND Teammate_Contact_Date < $tmpdater";
  }
  elseif ($optionalmonth == "two months ago") {
    $tmpyear = $currentyear;
    $tmpmonth = $currentmonth-1;
    if ($tmpmonth < 1){$tmpmonth = 12; $tmpyear--; }
    $tmpmonth = $tmpmonth-1;
    if ($tmpmonth < 1){$tmpmonth = 12; $tmpyear--; }
    $tmpdate = unixdate_to_exceldate(mktime(0,0,0,$tmpmonth,1,$tmpyear));
    $lastday = getlastday($tmpyear,$tmpmonth);
    $tmpdater = unixdate_to_exceldate(mktime(23,59,59,$tmpmonth,$lastday,$tmpyear));
    $tmpsqldater = "WHERE Teammate_Contact_Date > $tmpdate AND Teammate_Contact_Date < $tmpdater";
  }
  if ($tmpsqldater=='') {$tmpsqldater = $sqldater; }
	$sql = "SELECT COUNT(*) as id FROM raw_data $tmpsqldater $teamdefinition";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	$surveys=$row[id];
	return $surveys;
}

function backupvm ($vm_metric, $vm_team, $vm_startdate) {
	global $db, $monthy;
	if ($vm_startdate == '') { echo "No startdate picked."; return "n/a"; }
	if ($vm_metric == '') { echo "No metric picked."; return "n/a"; }
	if ($vm_team == '') { echo "No team picked."; return "n/a"; }
	$vm_startdate_year = substr($vm_startdate,0,4);
	$vm_startdate_month = substr($vm_startdate,5,2);
	$vm_startdate_month = $monthy[$vm_startdate_month];
	$vm_date = $vm_startdate_month.'-'.$vm_startdate_year;
	$mul = 1;

	    if ($vm_metric == "2"){ $vm_metric='aht'; }
	elseif ($vm_metric == "8"){ $vm_metric='psl'; }
	elseif ($vm_metric == "13"){ $vm_metric='esl'; }
	elseif ($vm_metric == "12"){ $vm_metric='pvol'; }
	elseif ($vm_metric == "14"){ $vm_metric='evol'; }

	    if ($vm_metric == "aht"){ $selector = 'email_aht_secs, phone_aht_secs, phone_answered, email_worked'; }
	elseif ($vm_metric == "psl"){ return "n/a"; } // Not available in prt073
	elseif ($vm_metric == "esl"){ $selector = 'email_sl_'; }
	elseif ($vm_metric == "pvol"){ $selector = 'Phone_Answered'; }
	elseif ($vm_metric == "evol"){ $selector = 'Email_Worked'; }
	elseif ($vm_metric == "ptr"){ $selector = 'Phone_Transfer_Rate'; }
	elseif ($vm_metric == "etr"){ $selector = 'Email_Transfer_Rate'; }

	if ($selector == '') { return "n/a"; }
		if($vm_metric == 'esl') { $mul = 100; }

		$sql = "SELECT team_prt073 FROM teams WHERE vmdata_team = '$vm_team' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		$vm_team = $row['team_prt073'];

		$sql = "SELECT $selector FROM prt073data WHERE queue_skillset = '{$vm_team}' AND month = '{$vm_date}' AND Queue_Name = 'Total' LIMIT 1";
		if(!$result=$db->query($sql)){
			cl($sql);
			cl($db->error);
		}
		$row=$result->fetch_assoc();

		if ($vm_metric == 'aht') {
			$total_aht = ($row['email_aht_secs'] * $row['email_worked'] + $row['phone_aht_secs'] * $row['phone_answered']) / ($row['email_worked'] + $row['phone_answered']);
			return $total_aht;
		}
		elseif ($vm_metric == 'ptr') {
		}
		if ($row[$selector]==''){ return "n/a"; }
		return $row[$selector]*$mul;
}

/*
	VM (metric, team, start date)
*/

function vm($vm_metric, $vm_team, $vm_startdate) {
	global $db, $monthy;
	 $vm_startdate_year = substr($vm_startdate,0,4);
	$vm_startdate_month = substr($vm_startdate,5,2);
	$vm_startdate_month = $monthy[$vm_startdate_month];
	$vm_date = $vm_startdate_month.'-'.$vm_startdate_year;
	$mul = 1;

	    if ($vm_metric == "2"){$vm_metric='aht';}
	elseif ($vm_metric == "8"){$vm_metric='psl';}
	elseif ($vm_metric == "13"){$vm_metric='esl';}
	elseif ($vm_metric == "12"){$vm_metric='pvol';}
	elseif ($vm_metric == "14"){$vm_metric='evol';}

	    if ($vm_metric == "aht"){$selector = 'Total_AHT_secs';}
	elseif ($vm_metric == "psl"){$selector = 'Phone_SLperc';}
	elseif ($vm_metric == "esl"){$selector = 'Email_SLperc';}
	elseif ($vm_metric == "pvol"){$selector = 'Phone_Answered';}
	elseif ($vm_metric == "evol"){$selector = 'Email_Worked';}
	elseif ($vm_metric == "ptr"){$selector = 'Phone_Transfer_Rate';}
	elseif ($vm_metric == "etr"){$selector = 'Email_Transfer_Rate';}
  if ($vm_metric == 'psl') { $mul = 100; }
  else if($vm_metric == 'esl') { $mul = 100; }

  if ($vm_metric == "17") {
    $sql = "SELECT team_prt073 FROM teams WHERE id = '$vm_team' LIMIT 1";
    if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  	$row=$result->fetch_assoc();
    $vm_team = $row['team_prt073'];
    $selector = 'phone_rcr';
    $mul = 100;
    $sql = "SELECT $selector FROM prt073data WHERE queue_skillset = '{$vm_team}' AND month = '{$vm_date}' AND Queue_Name = 'Total' LIMIT 1";
  }
  else {
    $sql = "SELECT vmdata_team FROM teams WHERE id = '$vm_team' LIMIT 1";
    if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  	$row=$result->fetch_assoc();
    $vm_team = $row[vmdata_team];
    $sql = "SELECT $selector FROM vm055_data WHERE team = '{$vm_team}' AND month = '{$vm_date}' LIMIT 1";
  }
  if(!$result=$db->query($sql)){
	  cl($sql);
	  cl($db->error);
  }
  $row=$result->fetch_assoc();
	$returno = '';
  if ($row[$selector]==''){ $returno = "n/a"; }
	$returno = $row[$selector]*$mul;

	// If no value is returned by the vm file, try prt073.
	if ($returno == 'n/a') {
		$returno = backupvm($vm_metric, $vm_team, $vm_startdate);
	}

  return $returno;

}

// Get combined values for a specific date interval.
function getvalue($metric,$valuestartdate,$valueenddate, $teamdefinition){
	global $db,$contract;
	//echo $teamdefinition . '<br>';
	$valuestartdate_year = substr($valuestartdate,0,4);
	$valuestartdate_month = substr($valuestartdate,5,2);
	$valuestartdate_day = substr($valuestartdate,8,2);
	$valueenddate_year = substr($valueenddate,0,4);
	$valueenddate_month = substr($valueenddate,5,2);
	$valueenddate_day = substr($valueenddate,8,2);
	$valuestartdate_excel = unixdate_to_exceldate(mktime(0,0,0,$valuestartdate_month,$valuestartdate_day,$valuestartdate_year));
	$valueenddate_excel = unixdate_to_exceldate(mktime(23,59,59,$valueenddate_month,$valueenddate_day,$valueenddate_year));
	$valuesqldater = " WHERE Teammate_Contact_Date >= $valuestartdate_excel";
	$valuesqldater .= " AND Teammate_Contact_Date <= $valueenddate_excel";
	$sql = "SELECT external_survey_id FROM raw_data".$valuesqldater . $teamdefinition;
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$surveys = 0;
  while($row=$result->fetch_assoc()){ $surveys++;}
  $topperformer=0;
	$bottomperformer=0;
	$crrr_yes=0;$crrr_inc=0;
	$kdi_sum=0;$kdi_phone=0;$kdi_phone_sum=0;
	$kdi_email=0;$kdi_email_sum=0;
	if($metric=='4'){$co='likely_to_recommend_paypal';}
	elseif($metric=='3'){$co='issue_resolved';}
	elseif($metric=='5'){$co='kdi___email,kdi___phone,Handled_professionally,Showed_genuine_interest,Took_ownership,Knowledge_to_handle_request,Valued_customer,Was_professional,Easy_to_understand,Provided_accurate_info,Helpful_response,Answered_concisely,Sent_in_timely_manner';}
	elseif($metric=='15'){$co='workitem_phone_talk_time';}
  elseif($metric=='16'){$co='customer_contact_count,issue_resolved';}
  else{return 0;} // Unsupported metric
	$sql = "SELECT $co FROM raw_data $valuesqldater ". $teamdefinition;
  $contra = 0;
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){
    $contra++;
    if($metric=='4'){
			if ($row[likely_to_recommend_paypal]>8){$topperformer++;}
			if ($row[likely_to_recommend_paypal]<7){$bottomperformer++;}
		}
    elseif($metric=='3'){
			if ($row[issue_resolved]=='Yes'){$crrr_yes++;}
			if ($row[issue_resolved]!='') {$crrr_inc++;}
		}
    elseif($metric=='5'){
			if (($row[kdi___phone] != '') || ($row[kdi___email] != '')) {
				$k='Handled_professionally';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Showed_genuine_interest';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Took_ownership';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Knowledge_to_handle_request';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Valued_customer';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Was_professional';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Easy_to_understand';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Provided_accurate_info';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Helpful_response';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Answered_concisely';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				//$k='Sent_in_timely_manner';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
			}
		}
    elseif($metric=='15'){
			if ($row[workitem_phone_talk_time]!=''){$att++;$att_sum+=$row[workitem_phone_talk_time];}
		}
    elseif($metric=='16'){
      if ($row[customer_contact_count]!=''){
        $ccc++;
        if (($row[customer_contact_count]==1) && ($row[issue_resolved]=='Yes')){
          $ccc_sum++;
        }
      }
    }
  	}
  if($metric=='4'){
    $value = round((100*$topperformer/$surveys)-(100*$bottomperformer/$surveys),2);
    //echo "100 * $topperformer / $surveys) - (100 * $bottomperformer / $surveys) = $value<br>";
  }
	//elseif($metric=='3'){ $value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metric=='5'){ $value = round(($kdi_top/$kdi*100),2);}
	elseif($metric=='15'){$value = round(($att_sum/$att),0);}
  elseif($metric=='16'){$value = round(($ccc_sum/$ccc*100),2);}
	else { $value = 0; }
	if ($contra>0){return $value;}
	else{return 'n/a';}
}

// Get any values with a free filter.
/*
  Metric:
    - NPS

*/
function sv($metric,$filter,$surveys,$sqldaterextra){
	global $db,$teamdefinition,$sqldater;
  if ($sqldaterextra==''){$sqldaterextra = $sqldater;}
	$topperformer=0;
	$bottomperformer=0;
	$crrr_yes=0;$crrr_inc=0;
	$kdi_sum=0;$kdi_phone=0;$kdi_phone_sum=0;
	$kdi_email=0;$kdi_email_sum=0;
	if($metric=='4'){$co='likely_to_recommend_paypal';}
	elseif($metric=='3'){$co='issue_resolved';}
	elseif($metric=='5'){$co='kdi___email,kdi___phone,Handled_professionally,Showed_genuine_interest,Took_ownership,Knowledge_to_handle_request,Valued_customer,Was_professional,Easy_to_understand,Provided_accurate_info,Helpful_response,Answered_concisely,Sent_in_timely_manner';}
	elseif($metric=='15'){$co='workitem_phone_talk_time';}
  elseif($metric=='16'){$co='customer_contact_count,issue_resolved';}
  else { return "error with metric '$metric'"; }
	$sql = "SELECT $co FROM raw_data $sqldaterextra $teamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$contra = 0;
  $npssurveys=0;
	while($row=$result->fetch_assoc()){
		$contra++;
		if($metric=='4'){
			if ($row[likely_to_recommend_paypal]>8){$topperformer++;}
			if ($row[likely_to_recommend_paypal]<7){$bottomperformer++;}
      if ($row[likely_to_recommend_paypal]!=''){$npssurveys++;}
		}
		elseif($metric=='3'){
			if ($row[issue_resolved]=='Yes'){$crrr_yes++;}
			if ($row[issue_resolved]!='') {$crrr_inc++;}
		}
		elseif($metric=='5'){
			if (($row[kdi___phone] != '') || ($row[kdi___email] != '')) {
				$k='Handled_professionally';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Showed_genuine_interest';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Took_ownership';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Knowledge_to_handle_request';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Valued_customer';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Was_professional';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Easy_to_understand';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Provided_accurate_info';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Helpful_response';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Answered_concisely';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				//$k='Sent_in_timely_manner';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
			}
		}
		elseif($metric=='15'){
			if ($row[workitem_phone_talk_time]!=''){$att++;$att_sum+=$row[workitem_phone_talk_time];}
		}
    elseif($metric=='16'){
      if ($row[customer_contact_count]!=''){
        $ccc++;
        if (($row[customer_contact_count]==1) && ($row[issue_resolved]=='Yes')){
          $ccc_sum++;
        }
      }
    }
	}
	if($metric=='4'){$value = round((100*$topperformer/$npssurveys)-(100*$bottomperformer/$npssurveys),2);}
	elseif($metric=='3'){ $value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metric=='5'){ $value = round(($kdi_top/$kdi*100),2);}
	elseif($metric=='15'){$value = round(($att_sum/$att),0);}
  elseif($metric=='16'){$value = round(($ccc_sum/$ccc*100),2);}
	else { $value = 0; }
	if ($contra>0){return $value;}
	else{return '--';}
}
// Get any values with a free filter and surveys.
/*
  Metric:
    - NPS

*/
function svs($metric,$filter,$surveys,$sqldaterextra){
	global $db,$teamdefinition,$sqldater;
  if ($sqldaterextra==''){$sqldaterextra = $sqldater;}
	$topperformer=0;
	$bottomperformer=0;
	$crrr_yes=0;$crrr_inc=0;
	$kdi_sum=0;$kdi_phone=0;$kdi_phone_sum=0;
	$kdi_email=0;$kdi_email_sum=0;
	if($metric=='4'){$co='likely_to_recommend_paypal';}
	elseif($metric=='3'){$co='issue_resolved';}
	elseif($metric=='5'){$co='kdi___email,kdi___phone,Handled_professionally,Showed_genuine_interest,Took_ownership,Knowledge_to_handle_request,Valued_customer,Was_professional,Easy_to_understand,Provided_accurate_info,Helpful_response,Answered_concisely,Sent_in_timely_manner';}
	elseif($metric=='15'){$co='workitem_phone_talk_time';}
  elseif($metric=='16'){$co='customer_contact_count,issue_resolved';}
  else { return "error with metric '$metric'"; }
	$sql = "SELECT $co FROM raw_data $sqldaterextra $teamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$contra = 0;
  $surveys = 0;
  $npssurveys=0;
	while($row=$result->fetch_assoc()){
    $surveys++;
		$contra++;
		if($metric=='4'){
			if ($row[likely_to_recommend_paypal]>8){$topperformer++;}
			if ($row[likely_to_recommend_paypal]<7){$bottomperformer++;}
      if ($row[likely_to_recommend_paypal]!=''){$npssurveys++;}
		}
		elseif($metric=='3'){
			if ($row[issue_resolved]=='Yes'){$crrr_yes++;}
			if ($row[issue_resolved]!='') {$crrr_inc++;}
		}
		elseif($metric=='5'){
			if (($row[kdi___phone] != '') || ($row[kdi___email] != '')) {
				$k='Handled_professionally';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Showed_genuine_interest';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Took_ownership';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Knowledge_to_handle_request';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Valued_customer';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Was_professional';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Easy_to_understand';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Provided_accurate_info';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Helpful_response';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Answered_concisely';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				//$k='Sent_in_timely_manner';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
			}
		}
		elseif($metric=='15'){
			if ($row[workitem_phone_talk_time]!=''){$att++;$att_sum+=$row[workitem_phone_talk_time];}
		}
    elseif($metric=='16'){
      if ($row[customer_contact_count]!=''){
        $ccc++;
        if (($row[customer_contact_count]==1) && ($row[issue_resolved]=='Yes')){
          $ccc_sum++;
        }
      }
    }
	}
	if($metric=='4'){$value = round((100*$topperformer/$npssurveys)-(100*$bottomperformer/$npssurveys),2);}
	elseif($metric=='3'){ $value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metric=='5'){ $value = round(($kdi_top/$kdi*100),2);}
	elseif($metric=='15'){$value = round(($att_sum/$att),0);}
  elseif($metric=='16'){$value = round(($ccc_sum/$ccc*100),2);}
	else { $value = 0; }
	if ($contra>0){return array($value,$contra);}
	else{return '0';}
}

// Get any PRT60p value based on month and filter.
// Options:
//    answered = phone calls answered
//    worked = emails worked
//    eaht = email aht
//    paht = phone aht
//
function av($what,$monthdate,$filter){
	global $db,$sqldater,$monthy,$ahtteamdefinition;
	$answered=0;$worked=0;$eaht=0;$paht=0;$montha = substr($monthdate,5,2);$montha=$monthy[$montha];$yeara = substr($monthdate,0,4);
	$aht=0;
	if ($what==2){$what='aht';}
	if ($what==17){$what='rcr';}
	if ($what==6){$what='tr';}
	if ($what==14){$what='worked';}
	if ($what==12){$what='answered';}
	$sql = "SELECT phone_rcr,transfer_rate,total_aht_secs,contacts_handled,ntid,queue_name,phone_answered,phone_aht_secs,email_worked,email_aht_secs FROM prt060data WHERE month='$montha-$yeara' $ahtteamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}

	$contra = 0;
	$counto=0;
	$counta=0;
	while($row=$result->fetch_assoc()){
		$contra++;
		if ($what=='answered'){
			$counto+=$row[phone_answered];
			$counta+=$row[phone_answered];
		}
		elseif ($what=='worked'){
			$counto+=$row[email_worked];
			$counta+=$row[email_worked];
		}
		elseif ($what=='eaht'){
			$counto+=$row[email_aht_secs]*$row[email_worked];
			$counta+=$row[email_worked];
		}
		elseif ($what=='paht'){
			$counto+=$row[phone_aht_secs]*$row[phone_answered];
			$counta+=$row[phone_answered];
		}
		elseif ($what=='aht'){
			$counto+=$row[total_aht_secs]*$row[contacts_handled];
			$counta+=$row[contacts_handled];
		}
		elseif ($what=='tr'){
			$counto+=$row[transfer_rate]*$row[contacts_handled];
			$counta+=$row[contacts_handled];
		}
		elseif ($what == 'rcr') {
			$counto+=$row[phone_rcr]*$row[phone_answered];
			$counta+=$row[phone_answered];
		}
	}
	$counto = intval($counto);
	$counta = intval($counta);

	if (($what=='paht') or ($what=='eaht') or ($what=='aht') or ($what=='tr') or ($what=='rcr')) { $counto=$counto / $counta; }
//	if ($contra>0){
		if ($counta>0) {
			// TODO figure out why it becomes 00...
			//if ($counto == '00') { $counto = 0; }
			return $counto;
		}
//	}
	return '--';
}
function gethours($tm,$date){
	global $db;
	$month = substr($date,0,7);
	$sql = "SELECT worked_hours FROM hours WHERE teammate_nt_id = '$tm' AND date = '$month' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[worked_hours];
}
function getinstances($tm,$date){
	global $db;
	$month = substr($date,0,7);
	$sql = "SELECT sick_instances FROM hours WHERE teammate_nt_id = '$tm' AND date = '$month' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[sick_instances];
}

function geticon($name) {
	if ($name == 'dashboard') { $name = 'bar-chart'; }
	elseif ($name == 'surveys') { $name = 'commenting-o'; }
	elseif ($name == 'settings') { $name = 'cog'; }
	elseif ($name == 'bonus') { $name = 'money'; }
	elseif ($name == 'targets') { $name = 'dot-circle-o'; }
	elseif ($name == 'certification') { $name = 'certificate'; }
	elseif ($name == 'trends') { $name = 'line-chart'; }
	elseif ($name == 'seating') { $name = 'th-large'; }
	elseif ($name == 'elite') { $name = 'bolt'; }
	return $name;
}
function showphotos() {
  global $db, $uid;
  $sql = "SELECT user_showphotos FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row['user_showphotos'];
}
function getphoto($tm) {
	// Don't get photo if user does not have access to photos.
	if (showphotos()) {
		// TODO: check if tm has a photo
		// TODO: return the filename of photo
		return "/gold/photos/$tm.jpg";
	}
}
