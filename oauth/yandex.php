<?php
$http='http';
if ($_SERVER['HTTPS']=='on') $http='https';
include('../config.php'); 
if (!empty($_GET['code'])) {
	// Отправляем код для получения токена (POST-запрос).
	$params = array(
		'grant_type'    => 'authorization_code',
		'code'          => $_GET['code'],
		'client_id'     => Yandex_client_id,
		'client_secret' => Yandex_client_secret,
	);
	
	$ch = curl_init('https://oauth.yandex.ru/token');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$data = curl_exec($ch);
	curl_close($ch);	
			 
	$data = json_decode($data, true);
	
	//echo 'data<pre>';
	//print_r($data);
	
	if (!empty($data['access_token'])) {
		// Токен получили, получаем данные пользователя.
		$ch = curl_init('https://login.yandex.ru/info');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('format' => 'json')); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $data['access_token']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$info = curl_exec($ch);
		curl_close($ch);
 
		$info = json_decode($info, true);
		//echo '</pre>INFO<pre>';
		//print_r($info);
		echo '<html>
				<head><meta charset="utf-8" /></head> 
				<body onload="oauth.submit()";>
					<form name="oauth" method="POST" action="'.$http.'://'.$_SERVER['HTTP_HOST'].'/index.php?action=login"  onload=submit();>  
						<input type="hidden" name="email" value="'.$info['default_email'].'">
						<input type="hidden" name="access_token" value="'.$data['access_token'].'">  
						<input type="hidden" name="oauth" value="yandex">
						<input type="hidden" name="passman_key" value="'.md5(passman_key . date('Y-m-d H:i')).'">
					</form>
				</body>
			</html>';		
	}
	
	//echo '</pre>';
	//else redirect to show message and register other way
	else {
		header('Location: '.$http.'://'.$_SERVER['HTTP_HOST'].'/index.php?action=noauth', true, 301);
		echo "<script>window.location.replace('".$http."://".$_SERVER['HTTP_HOST']."/index.php?action=noauth');</script>";
		echo 'Перенаправление… Перейдите по <a href="'.$http.'://'.$_SERVER['HTTP_HOST'].'/index.php?action=noauth">ссылке</a>.';
	}
}
