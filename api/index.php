<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ini_set("display_errors", "On");

// require_once 'vendor/autoload.php';
// print_r($_REQUEST);

$script_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}";

session_start();

require_once("config.php");
require_once("include.php");
require_once("Mysql_connector.class.php");

$Action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
$DefaultImageFolder = "images";

$DB = new Mysql_connector($DB_HOST, $DB_USERNAME, $DB_PASSWORD);
$DB->select_db($DB_NAME);

$mysqli = $DB->connection;

$_SESSION['Options'] = loadOptions();
if (!isset($_SESSION['Options'])) {
    $_SESSION['Options'] = loadOptions();
}
$Options = $_SESSION['Options'];

require_once("update.php");

$Langs = array();
foreach (glob("lang/*.txt") as $filename) {
    $path_parts = pathinfo($filename);
    $Langs[] = $path_parts['filename'];
}

if (isset($Options["defaultlang"]) && $Options["defaultlang"] && !isset($_SESSION["Lang"])) {
    $_SESSION["Lang"] = caricaLang($Options["defaultlang"]);
}
$Lang = $_SESSION["Lang"];

/* $google_client = new Google_Client();
$google_client->setClientId($Options['googleClientId']);
$google_client->setClientSecret($Options['googleClientSecret']);
$google_client->setRedirectUri($script_uri . "?action=generateGoogle");

$google_client->addScope('email');*/

$ret = array();

switch ($Action) {
    case "lang":
    case "reloadLang":
        $_SESSION["Lang"] = caricaLang($Options["defaultlang"]);
        $Lang = $_SESSION["Lang"];
        $Lang['_langs'] = $Langs;
    // case "lang":
        $ret = $Lang;
        break;

    case "logout":
        unset($_SESSION['Login']);
        unset($_SESSION['Admin']);
        $ret['result'] = "OK";
        break;

    case "loginInfo":
        $ret['logged'] = false;
        $ret['admin'] = false;
        if (isset($_SESSION['Login'])) {
            $ret['logged'] = true;
            $ret['login'] = $_SESSION['Login'];
            $query = "SELECT i.*, COUNT(u.id) usercount
                FROM institutions i
                LEFT OUTER JOIN users u ON u.institution = i.id
                WHERE i.hidden = 0 AND u.id = '".$ret['login']['id']."'
                GROUP BY i.id
                ORDER BY i.name";
            $res = $mysqli->query($query);
            $allData = $res->fetch_all(MYSQLI_ASSOC);
            foreach ($allData as &$datum) {
                if ($datum['info']) {
                    $datum['info'] = unserialize($datum['info']);
                }
            }
            
            $ret['institution'] = $allData[0];

        }
        if (isset($_SESSION['Admin'])) {
            $ret['admin'] = true;
        }
        break;

    case "adminLogin":
        $password = $_REQUEST['password'];

        if (isset($Options['adminpassword']) && $Options['adminpassword'] === md5($password)) {
            $_SESSION['Admin'] = true;
            $ret['result'] = "OK";
            break;
        }

        $ret['result'] = "ERR";
        $ret['error'] = $Lang["login_failed"];
        break;

    case "generateGoogle":
        $R = find("institutions", $_SESSION['Institution'], $Lang['unknown_institutions']);

        if ($_GET['code']) {
            $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);
            if (!isset($token['access_token'])) {
                exit($token['error_description']);
            }

            $google_client->setAccessToken($token['access_token']);
            $google_service = new Google_Service_Oauth2($google_client);
            $data = $google_service->userinfo->get();

            $email = $data['email'];
            $LoginUser = false;

            $query = "SELECT * FROM users WHERE social_email = ? AND institution = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("si", $email, $_SESSION['Institution']);
            $stmt->execute();
            $r = $stmt->get_result();

            if (!$LoginUser && $data = $r->fetch_assoc()) {
                $LoginUser = $data;
            }

            $query = "SELECT * FROM `users` WHERE institution = ? AND logged = '0' AND usergroup != '0' ORDER BY id";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $_SESSION['Institution']);
            $stmt->execute();
            $r = $stmt->get_result();

            if (!$LoginUser && $data = $r->fetch_assoc()) {
                $update = array();
                $update['logged'] = 1;
                $update['social_email'] = $email;
                $DB->queryupdate("users", $update, array("id" => $data['id']));

                $query = "SELECT * FROM users WHERE id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("i", $data['id']);
                $stmt->execute();
                $r = $stmt->get_result();
                $data = $r->fetch_assoc();

                $LoginUser = $data;
            }

            if (!$LoginUser) {
                $ret['result'] = "ERR";
                $ret['error'] = $Lang["login_failed"];
                break;
            }

            $_SESSION['Login'] = $data;
            $_SESSION['Lang'] = caricaLang($data['language']);
            $Lang = $_SESSION["Lang"];

            $ret['result'] = "OK";
            $ret['data'] = $_SESSION['Login'];

            header("Location: " . dirname(dirname($script_uri)));
        }
        else {
            $ret['result'] = "ERR";
            $ret['error'] = $Lang["login_failed"];
        }

        break;

    case "login":
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        $institution = $_REQUEST['institution'];

        $useSocial = $_REQUEST['useGoogle'] === "true" ? true : false;

        if (!preg_match("/^([0-9]+)-([0-9]+)$/", $_REQUEST['institution'], $ris)) {
            $ret['result'] = "ERR";
            $ret['error'] = $Lang["no_institution_code"];
            break;
        }

        $institutionID = $ris[1];
        $institutionCode = $ris[2];

        if ($useSocial) {
            $toPrepare = "SELECT * FROM institutions
                    WHERE id = ? AND code = ? AND hidden = 0
                    AND allow_social_login = '1' AND confirmed_users = '1'";
            if ($stmt = $mysqli->prepare($toPrepare)) {
                $stmt->bind_param("is", $institutionID, $institutionCode);
                $stmt->execute();
                $r = $stmt->get_result();

                // Login ok
                $okLogin = false;
                if ($data = $r->fetch_assoc()) {
                    if ($data['allow_social_login']) {
                        $_SESSION['Institution'] = $institutionID;

                        $ret['result'] = "OK";
                        $ret['data'] = array("url" => $google_client->createAuthUrl());
                        $okLogin = true;
                    }
                    else {
                        $ret['result'] = "ERR";
                        $ret['error'] = "Impossibile eseguire il login social";
                        $okLogin = true;
                    }
                }
                $stmt->close();

                if ($okLogin) {
                    break;
                }
            }
        }

        $toPrepare = "SELECT u.* FROM users u
                LEFT JOIN institutions i ON u.institution = i.id
                WHERE u.username = ? AND u.password = MD5(?)
                    AND i.id = ? AND i.code = ? AND i.hidden = 0
                    AND confirmed_users = '1'";
        if ($stmt = $mysqli->prepare($toPrepare)) {
            // echo $toPrepare . "\n";
            // echo $username . "-" . $password . "-" . $institutionID . "-" . $institutionCode . "\n";
            $stmt->bind_param("ssis", $username, $password, $institutionID, $institutionCode);
            $stmt->execute();
            $r = $stmt->get_result();

            // Login ok
            $okLogin = false;
            if ($data = $r->fetch_assoc()) {
                $_SESSION['Login'] = $data;
                $_SESSION['Lang'] = caricaLang($data['language']);
                $Lang = $_SESSION["Lang"];

                $ret['result'] = "OK";
                $ret['data'] = $_SESSION['Login'];

                $query = "UPDATE users SET logged = 1 WHERE id = '{$data['id']}'";
                $DB->query($query);

                $okLogin = true;
            }
            $stmt->close();

            if ($okLogin) {
                break;
            }
        }

        $ret['result'] = "ERR";
        $ret['error'] = $Lang["login_failed"];
        break;

    case "statistics":
    case "submit":

        if (!isset($_SESSION['Login'])) {
            $ret['result'] = "ERR";
            $ret['error'] = $Lang["not_logged"];
            break;
        }

        switch ($Action) {
            case "submit":
                $stmt = $mysqli->prepare(
                    "SELECT * FROM clusters c
                    LEFT JOIN photos p ON c.photo_id = p.id
                    WHERE c.user_id=? AND c.value IS NULL AND c.photo_id=?
                    ORDER BY p.id"
                );
                $stmt->bind_param("ii", $_SESSION['Login']['id'], $_REQUEST['id']);
                $stmt->execute();
                $r = $stmt->get_result();
                if ($data = $r->fetch_assoc()) {
                    $value = $_REQUEST['value'];
                    $comment = $_REQUEST['comment'];
                    if ($_REQUEST['no'] == "true") {
                        $value = "0";
                        $comment = "";
                    }
                    $stmt_up = $mysqli->prepare(
                        "UPDATE clusters
                        SET value=?, comment=?
                        WHERE user_id=? AND photo_id=?"
                    );
                    $stmt_up->bind_param("isii", $value, $comment, $_SESSION['Login']['id'], $_REQUEST['id']);
                    $stmt_up->execute();
                    $stmt_up->close();
                    $ret['result'] = "OK";
                }
                else {
                    $ret['result'] = "ERR";
                    $ret['error'] = $Lang["error"];
                }
                $stmt->close();
                break;

            case "statistics":

                $ret['data'] = array();

                $stmt = $mysqli->prepare(
                    "SELECT * FROM clusters c
                    LEFT JOIN photos p ON c.photo_id = p.id
                    WHERE c.user_id=? AND c.value IS NOT NULL"
                );
                $stmt->bind_param("i", $_SESSION['Login']['id']);
                $stmt->execute();
                $r = $stmt->get_result();
                $ret['data']['annotatedPhotos'] = $r->num_rows;

                $stmt = $mysqli->prepare(
                    "SELECT * FROM clusters c
                    LEFT JOIN photos p ON c.photo_id = p.id
                    WHERE c.user_id=? AND c.value IS NULL"
                );
                $stmt->bind_param("i", $_SESSION['Login']['id']);
                $stmt->execute();
                $r = $stmt->get_result();
                $ret['data']['todoPhotos'] = $r->num_rows;
                $ret['data']['nextPhoto'] = array();

                $stmt = $mysqli->prepare(
                    "SELECT p.id, p.link FROM clusters c
                    LEFT JOIN photos p ON c.photo_id = p.id
                    WHERE c.user_id=? AND c.value IS NULL
                    ORDER BY p.id"
                );
                $stmt->bind_param("i", $_SESSION['Login']['id']);
                $stmt->execute();
                $r = $stmt->get_result();
                if ($data = $r->fetch_assoc()) {
                    $ret['data']['nextPhoto'] = $data;
                }
                $stmt->close();

                $ret['result'] = "OK";
                break;
        }
        
        break;

    case "getInstitutions":
    case "getInstitutionInfo":
    case "addInstitution":
    case "deleteInstitution":
    case "editInstitution":
    case "institutionInfo":
    case "populatePhoto":
    case "resetInstitution":
    case "lockUsers":
    case "exportCsv":

        if (!$_SESSION['Admin']) {
            $ret['result'] = "ERR";
            $ret['error'] = $Lang["not_logged"];
            break;
        }

        switch ($Action) {
            case "getInstitutions":
                $query = "SELECT i.*, COUNT(u.id) usercount
                    FROM institutions i
                    LEFT OUTER JOIN users u ON u.institution = i.id
                    WHERE i.hidden = 0
                    GROUP BY i.id
                    ORDER BY i.name";
                $res = $mysqli->query($query);
                $allData = $res->fetch_all(MYSQLI_ASSOC);
                foreach ($allData as &$datum) {
                    if ($datum['info']) {
                        $datum['info'] = unserialize($datum['info']);
                    }
                }
                $ret['result'] = "OK";
                $ret['values'] = $allData;
                break;

            case "getInstitutionInfo":
                $R = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);

                if ($R['info']) {
                    $R['info'] = unserialize($R['info']);
                }

                $Users = array();
                $query = "SELECT * FROM users WHERE institution = '{$R['id']}'";
                $res = $mysqli->query($query);
                while ($data = $res->fetch_assoc()) {
                    $Users[] = $data;
                }

                $ret['users'] = $Users;
                $ret['data'] = $R;
                $ret['result'] = "OK";
                break;

            case "exportCsv":
                $R = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);

                $basic_info = fopen("php://output", 'w');
                $headers = array("ID", "Code", "Username", "Password");
                fputcsv($basic_info, $headers);

                $query = "SELECT * FROM users WHERE institution = '{$R['id']}'";
                $res = $mysqli->query($query);
                while ($data = $res->fetch_assoc()) {
                    $useData = array();
                    $useData[] = $data['id'];
                    $useData[] = $R['id'] . "-" . $R['code'];
                    $useData[] = $data['username'];
                    $useData[] = $data['password'];
                    fputcsv($basic_info, $useData);
                }

                fclose($basic_info);

                header("Content-disposition: attachment; filename=users.csv");
                header("Content-Type: text/csv");
                header('Content-Description: File Transfer');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize("php://output"));
                // ob_clean();
                flush();

                readfile("php://output");
                exit();

                break;

            case "resetInstitution":
                $R = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);

                $mysqli->query("DELETE c FROM clusters c
                    LEFT JOIN users u ON u.id = c.user_id
                    WHERE u.institution = '{$R['id']}'");
                $mysqli->query("DELETE FROM photos WHERE institution = '{$R['id']}'");
                $mysqli->query("DELETE FROM users WHERE institution = '{$R['id']}'");

                $data = array("confirmed_users" => 0);
                $where = array("id" => $_REQUEST['id']);
                $DB->queryupdate("institutions", $data, $where);

                $ret['result'] = "OK";
                break;

            case "lockUsers":
                $R = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);

                if (!$R['info']) {
                    $ret['result'] = "ERR";
                    $ret['error'] = $Lang['lock_users_info'];
                    break;
                }

                $data = array("confirmed_users" => 1);
                $where = array("id" => $_REQUEST['id']);
                $DB->queryupdate("institutions", $data, $where);

                $query = "UPDATE users SET password = MD5(password) WHERE institution = '{$R['id']}'";
                $DB->query($query);

                $ret['result'] = "OK";
                break;

            case "populatePhoto":
                $R = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);

                // $ret['result'] = "ERR";
                // $ret['error'] = print_r($_REQUEST, true);
                // break;

                $UsersPerPhoto = $_REQUEST['users_per_photo'];
                $UserGroups = $_REQUEST['user_groups'];
                $PhotosToDebugUsers = $_REQUEST['debug_photos'];
                $Folder = $_REQUEST['files_folder'];

                if (!is_numeric($UsersPerPhoto) || !preg_match('/^[0-9]+$/', $UsersPerPhoto) || $UsersPerPhoto < 1) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "UsersPerPhoto not numeric ($UsersPerPhoto)";
                    break;
                }
                if (!is_numeric($UserGroups) || !preg_match('/^[0-9]+$/', $UserGroups) || $UserGroups < 1) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "UserGroups not numeric ($UserGroups)";
                    break;
                }
                if (!is_numeric($PhotosToDebugUsers) || !preg_match('/^[0-9]+$/', $PhotosToDebugUsers)) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "PhotosToDebugUsers not numeric ($PhotosToDebugUsers)";
                    break;
                }
                if (!preg_match("/^[a-zA-Z0-9-_]+$/", $Folder)) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "Folder $Folder can contains only letters and numbers";
                    break;
                }
                $Folder = $DefaultImageFolder . "/" . $Folder;
                if (!file_exists("../" . $Folder) || !is_dir("../" . $Folder)) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "Folder $Folder does not exist";
                    break;
                }

                $mysqli->query("DELETE c FROM clusters c
                    LEFT JOIN users u ON u.id = c.user_id
                    WHERE u.institution = '{$R['id']}'");
                $mysqli->query("DELETE FROM photos WHERE institution = '{$R['id']}'");
                $mysqli->query("DELETE FROM users WHERE institution = '{$R['id']}'");

                $values = [];
                $password = generaPassword();
                $values[] = "('debug', 'debug', '0', '$password', '{$R['id']}', '{$R['language']}')";
                $userid = 0;
                for ($i = 0; $i < $UserGroups; $i++) { 
                    for ($j = 0; $j < $UsersPerPhoto; $j++) { 
                        $userid++;
                        $group = $j + 1;
                        $user = "user{$userid}";
                        $password = generaPassword();

                        $values[] = "('$user', '$user', '$group', '$password', '{$R['id']}', '{$R['language']}')";
                    }
                }

                $query = "INSERT INTO users (name, username, usergroup, password, institution, language) VALUES ";
                $query .= implode(", ", $values);
                $mysqli->query($query);

                $Users = array();
                $query = "SELECT * FROM users WHERE institution = '{$R['id']}'";
                $res = $mysqli->query($query);
                while ($data = $res->fetch_assoc()) {
                    $Users[$data['username']] = $data['id'];
                }

                $photos = [];
                $files = glob("../" . $Folder . "/*.{jpg,jpeg,png}", GLOB_BRACE);
                foreach ($files as $file) {
                    $filename = substr($file, 3);
                    $photos[] = "('$filename', '{$R['id']}')";
                }

                $query = "INSERT INTO photos (link, institution) VALUES ";
                $query .= implode(", ", $photos);
                $mysqli->query($query);

                $clusters = [];
                $query = "SELECT * FROM photos WHERE institution = '{$R['id']}'";
                $res = $mysqli->query($query);
                $i = 0;
                while ($data = $res->fetch_assoc()) {
                    $i++;
                    if ($i <= $PhotosToDebugUsers) {
                        $userID = $Users['debug'];
                        $clusters[] = "('$userID', '{$data['id']}')";
                    }
                    else {
                        $usergroup = ($i - $PhotosToDebugUsers - 1) % $UserGroups;
                        for ($j = 1; $j <= $UsersPerPhoto; $j++) {
                            $idui = $UsersPerPhoto * $usergroup + $j;
                            $userID = $Users["user" . $idui];
                            // print("$i\t$usergroup\t$userID\t{$data['link']}\n");
                            $clusters[] = "('$userID', '{$data['id']}')";
                        }
                    }
                }

                $query = "INSERT INTO clusters (user_id, photo_id) VALUES ";
                $query .= implode(", ", $clusters);
                $mysqli->query($query);

                $ret['result'] = "OK";

                break;


            case "deleteInstitution":
                $r = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);
                if ($stmt = $mysqli->prepare("UPDATE institutions SET hidden=1 WHERE id=?")) {
                    $stmt->bind_param("s", $_REQUEST['id']);
                    $stmt->execute();
                    $stmt->close();
                }
                $ret['result'] = "OK";
                break;

            case "editInstitution":
                $r = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);
                if ($r['info']) {
                    $r['info'] = unserialize($r['info']);
                }
                $ret['result'] = "OK";
                $ret['values'] = $r;
                break;

            case "institutionInfo":
                $r = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);
                $info = json_decode($_REQUEST['info'], true);

                if (!$info['sent1']) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "Missing sent1";
                    break;
                }
                if (!$info['sent2']) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "Missing sent2";
                    break;
                }
                /*if (!$info['other'] && !$info['sent3']) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "Missing sent3";
                    break;
                }*/
                if (!is_array($info['choices']) || count($info['choices']) == 0) {
                    $ret['result'] = "ERR";
                    $ret['error'] = "Missing choices";
                    break;
                }

                $info['other'] = !!$info['other'];
                $info['use_yes'] = !!$info['use_yes'];

                $DB->queryupdate("institutions", ["info" => serialize($info)], ["id" => $_REQUEST['id']]);

                $ret['result'] = "OK";
                break;

            case "addInstitution":
                $fields = array("name", "language", "code", "allow_social_login");

                $r = NULL;
                if ($_REQUEST['id'] != 0) {
                    $r = find("institutions", $_REQUEST['id'], $Lang['unknown_institutions']);
                }
                $data = array();
                foreach ($fields as $field) {
                    $data[$field] = $_REQUEST[$field];
                }

                if (!trim($data['name'])) {
                    $ret['result'] = "ERR";
                    $ret['error'] = $Lang["must_name"];
                    break;
                }
                if (!in_array($_REQUEST['language'], $Langs)) {
                    $ret['result'] = "ERR";
                    $ret['error'] = $Lang["wrong_language"];
                    break;
                }
                if (!preg_match("/^[0-9]{5}$/", $_REQUEST['code'])) {
                    $ret['result'] = "ERR";
                    $ret['error'] = $Lang["wrong_code"];
                    break;
                }

                if ($r === NULL) {
                    $DB->queryinsert("institutions", $data);
                    $ret['result'] = "OK";
                    break;
                }
                else {
                    $where = array("id" => $_REQUEST['id']);
                    $DB->queryupdate("institutions", $data, $where);
                    $ret['result'] = "OK";
                    break;
                }

                break;
        }
        
        break;

}

echo json_encode($ret);
