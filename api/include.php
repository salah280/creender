<?php

// error_reporting(E_ALL & ~E_NOTICE);
// ini_set("display_errors", "On");

// session_start();

// $Action = $_REQUEST['action'];

// $mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD);
// if( !$mysqli ){
// 	die("Error: a related error message");
// }
// $mysqli->select_db($DB_NAME);

// $Options = array();
// $query = "SELECT * FROM options";
// $result = $mysqli->query($query);
// while ($obj = $result->fetch_object()) {
// 	$Options[$obj->id] = $obj->value;
// }

// unset($_SESSION["Lang"]);
// if (isset($Options["defaultlang"]) && $Options["defaultlang"] && !isset($_SESSION["Lang"])) {
// 	$_SESSION["Lang"] = caricaLang($Options["defaultlang"]);
// }

// Functions

function loadOptions() {
	global $mysqli;
	
	$Options = array();
	$query = "SELECT * FROM options";
	$result = $mysqli->query($query);
	while ($obj = $result->fetch_object()) {
		$Options[$obj->id] = $obj->value;
	}
	return $Options;
}

// function back($url, $message = "", $kind = "success") {
// 	header("Location: " . $url);
// 	if ($message) {
// 		$_SESSION['message'] = $message;
// 		$_SESSION['message_kind'] = $kind;
// 	}
// 	exit();
// }

function caricaLang($lang) {
	$ret = array();

	$LangFile = "lang/" . $lang . ".txt";
	if (file_exists($LangFile)) {
		if (($handle = fopen($LangFile, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
				if (count($data) > 1) {
					$ret[$data[0]] = $data[1];
				}
			}
			fclose($handle);
		}
	}

	return $ret;
}

function find($table, $id, $text) {
	global $mysqli;
	
	$stmt_up = $mysqli->prepare("SELECT * FROM {$table} WHERE id = ?");
	$stmt_up->bind_param("s", $id);
	$stmt_up->execute();
	$r = $stmt_up->get_result();
	if ($r->num_rows) {
		return $r->fetch_assoc();
	}

	$ret = array();
    $ret['result'] = "ERR";
    $ret['error'] = $text;
	echo json_encode($ret);
	exit();
}

function generaPassword() {
	$parole = array(
		"topolino",
		"pippo",
		"paperino",
		"gambadilegno",
		"macchianera",
		"bandabassotti",
		"nonnapapera",
		"paperina",
		"minnie",
		"pluto",
		"archimede",
		"clarabella",
		"orazio",
		"ziopaperone",
		"gastone",
		"paperoga",
		"paperina",
		"battista"
		);
	if (file_exists("random_words.txt")) {
		$fn = fopen("random_words.txt", "r");
		$parole = array();

		while(!feof($fn))  {
			$result = fgets($fn);
			$result = trim($result);
			if (strlen($result) > 0) {
				$parole[] = $result;
			}
		}

		fclose($fn);
	}
	$n = $parole[rand(0, count($parole) - 1)];
	$n .= str_pad(rand(1, 999), 3, "0", STR_PAD_LEFT);
	$n .= randomPassword();
	return $n;
}

function randomPassword($len = 1) {
	$alphabet = '!@#?%$';
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < $len; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
}
