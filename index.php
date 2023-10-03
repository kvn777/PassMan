<?php

include('config.php');

#defaults
	$title='PassMan';
	$action='do_auth';
	$http='http';	

	class PassmanDB extends SQLite3
	{
		function __construct()
		{
			#Create database file and tables if not exist
			$this->open('passman.db');
			$this->exec('CREATE TABLE IF NOT EXISTS users
						(id INTEGER PRIMARY KEY AUTOINCREMENT,info TEXT NOT NULL)');
			$this->exec('CREATE TABLE IF NOT EXISTS data
						(id INTEGER PRIMARY KEY AUTOINCREMENT,
						uid INTEGER NOT NULL,
						website TEXT NOT NULL,
						user TEXT NOT NULL,
						password TEXT NOT NULL,
						extra TEXT NOT NULL)');
		}
	}	

if (array_key_exists('action',$_GET)) $action=$_GET['action'];
	
if ($action=='login') {
	if ($_POST['passman_key']!=md5(passman_key . date('Y-m-d H:i'))) $action='do_auth';
	else {
		setcookie("passman", md5($_POST['email'].passman_salt), time()+cookie_time);
		$db = new PassmanDB();
		$result = $db->query('SELECT * FROM users where info=\''.md5($_POST['email'].passman_salt).'\'');
		if (!$result->fetchArray()) $db->query('INSERT INTO users (info) VALUES (\''.md5($_POST['email'].passman_salt).'\')');
		$result = $db->query('SELECT * FROM users where info=\''.md5($_POST['email'].passman_salt).'\'');
		if (!$result->fetchArray()) die('try again, reload page');
		$_COOKIE['passman']=md5($_POST['email'].passman_salt);
		$db->close();
	}
}

if (array_key_exists('passman',$_COOKIE)) {
	$action='auth';
	if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {    
		$action='api';
	}
}

if ($action=='auth') {
	$page=file_get_contents('./views/showdb.html');
}

if ($action=='api') {
	if (!array_key_exists('passman',$_COOKIE)) exit;
	$db = new PassmanDB();
	header('Content-Type: application/json; charset=utf-8');
	if (count($_POST)>0) {
		if (intval($_POST['editid'])>0) {
			//Edit data in DB
			$result = $db->query('SELECT * FROM users where info=\''.$db->escapeString($_COOKIE['passman']).'\'');
			$uid=$result->fetchArray()['id'];
			$query = $db->prepare('UPDATE data SET
									website=:website,
									user=:user,
									password=:password,
									extra=:extra
								WHERE 
									uid=:uid and id=:id');
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$query->bindValue(':id', intval($_POST['editid']), SQLITE3_INTEGER);
			$query->bindValue(':website', $_POST['website'], SQLITE3_TEXT);
			$query->bindValue(':user', $_POST['user'], SQLITE3_TEXT);
			$query->bindValue(':password', $_POST['password'], SQLITE3_TEXT);
			$query->bindValue(':extra', $_POST['extra'], SQLITE3_TEXT);
			$result = $query->execute();
		}
		elseif (intval($_POST['delid'])>0) {
			//Delete data in DB
			$result = $db->query('SELECT * FROM users where info=\''.$db->escapeString($_COOKIE['passman']).'\'');
			$uid=$result->fetchArray()['id'];
			$query = $db->prepare('Delete from data WHERE uid=:uid and id=:id');
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$query->bindValue(':id', intval($_POST['delid']), SQLITE3_INTEGER);
			$result = $query->execute();
		}
		else {
			//Add data to DB
			$result = $db->query('SELECT * FROM users where info=\''.$db->escapeString($_COOKIE['passman']).'\'');
			$uid=$result->fetchArray()['id'];
			$query = $db->prepare('INSERT INTO data (uid,website,user,password,extra) 
									VALUES (:uid,:website,:user,:password,:extra)');
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$query->bindValue(':id', intval($_POST['editid']), SQLITE3_INTEGER);
			$query->bindValue(':website', $_POST['website'], SQLITE3_TEXT);
			$query->bindValue(':user', $_POST['user'], SQLITE3_TEXT);
			$query->bindValue(':password', $_POST['password'], SQLITE3_TEXT);
			$query->bindValue(':extra', $_POST['extra'], SQLITE3_TEXT);
			$result = $query->execute();
		}
	}
	
	//read from DB
	$result = $db->query('SELECT data.id,data.uid,website,user,password,extra FROM data join users on users.id=data.uid where info=\''.$db->escapeString($_COOKIE['passman']).'\'');
	$response=[];
	while ($row = $result->fetchArray()) $response[]=$row;
	$db->close();
	echo json_encode($response);
	exit;
}

if ($action=='do_auth') {
	$title='Please auth to PassMan';
	
	#get yandex button
	if ($_SERVER['HTTPS']=='on') $http='https';
	$token_yandex='https://oauth.yandex.ru/authorize?response_type=code&redirect_uri='.urlencode($http.'://'.$_SERVER['HTTP_HOST'].'/oauth/yandex.php').'&client_id='.Yandex_client_id;
	$page=file_get_contents('./views/auth.html');
	$page=str_ireplace('{token_yandex}',$token_yandex,$page);
	
	#TODO: add more oAuth providers
}


$header=file_get_contents('./views/header.html');
$header=str_ireplace('{title}',$title,$header);
$footer=file_get_contents('./views/footer.html');



echo $header;
echo $page;
echo $footer;
