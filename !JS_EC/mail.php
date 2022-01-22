<?php
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	
	$url_google_api = 'https://www.google.com/recaptcha/api/siteverify';
	$secret = '6LcAB9oZAAAAAMYv2hscrCwEQw8bDZ6Kvg67tlK6';
	$query = $url_google_api.'?secret='.$secret.'&response='.$_REQUEST['recaptcha_token'].'&remoteip='.$_SERVER['REMOTE_ADDR'];
	$data = json_decode(file_get_contents($query), true);
	
	if (isset($_REQUEST['cphone']) && empty($_REQUEST['cphone']) && empty($_REQUEST['company'])) {
		
		CModule::IncludeModule('iblock');
		$el = new CIBlockElement;
		
		$arrProp = Array();
		$arrProp[147] = $_REQUEST['name'];
		$arrProp[148] = $_REQUEST['lastname'];
		$arrProp[149] = $_REQUEST['company'];
		$arrProp[150] = $_REQUEST['phone'];
		$arrProp[151] = $_REQUEST['email'];
		$arrProp[152][0] = Array("VALUE" => Array ("TEXT" => $_REQUEST['message'], "TYPE" => "text"));
		$arrProp[153] = $_REQUEST['path'];
		
		$ELEMENT_ID = 0;
		
		$arLoadElementArray = Array(
			"MODIFIED_BY"    => $USER->GetID(),
			"IBLOCK_SECTION_ID" => false,
			"IBLOCK_ID"      => 57,
			"PROPERTY_VALUES"=> $arrProp,
			"NAME"           => $_REQUEST['form'].'-'.$ELEMENT_ID,
			"ACTIVE"         => "Y"
		);
		
		$uploaddir = __DIR__ . '/uploads/';

		foreach ($_FILES["files"]["error"] as $key => $error) {
			if ($error == UPLOAD_ERR_OK) {
				$uploadfile = basename($_FILES["files"]["name"][$key]);
				move_uploaded_file($_FILES['files']['tmp_name'][$key], $uploaddir . $uploadfile);
			}
		}
		$files = implode(", ", $_FILES["files"]["name"]);
		$email = 'info@original-group.ru, forms@traceway.ru, i.kapranov@original-group.ru, a.slobodyan@original-group.ru';
		$title = "Заявка с сайта TraceWay.ru";
		$body = "Форма: ${_REQUEST['form']}\r\n\r\nИмя: ${_REQUEST['name']}\r\n\r\nФамилия: ${_REQUEST['lastname']}\r\n\r\nКомпания: ${_REQUEST['company']}\r\n\r\nТелефон: ${_REQUEST['phone']}\r\n\r\nE-mail: ${_REQUEST['email']}\r\n\r\nСообщение: ${_REQUEST['message']}\r\n\r\nЗагруженные файлы: ${files}\r\n\r\nОтправлено со страницы: ${_REQUEST['path']}\r\n\r\n--------\r\n\r\nДополнительные значения\r\n\r\nHTTP_REFERER: ${_COOKIE['referer']}\r\n\r\nHTTP_USER_AGENT: ${_SERVER['HTTP_USER_AGENT']}\r\n\r\nIP: ${_SERVER['REMOTE_ADDR']}\r\n\r\nLANGUAGE: ${_SERVER['HTTP_ACCEPT_LANGUAGE']}\r\n\r\nURL_PATH: ${_COOKIE['query_string']}\r\n\r\nutm_campaign: ${_COOKIE['utm_campaign']}\r\n\r\nutm_medium: ${_COOKIE['utm_medium']}\r\n\r\nutm_term: ${_COOKIE['utm_term']}";
		$answer = "Ваш запрос принят. Мы свяжемся с вами в самое ближайшее время.";

		
		if ($data['success']) {
			if ($data['score'] > 0.5) {
				if($ELEMENT_ID = $el->Add($arLoadElementArray)) {
					$arLoadElementArray = Array(
						"MODIFIED_BY"    => $USER->GetID(),
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID"      => 57,
						"PROPERTY_VALUES"=> $arrProp,
						"NAME"           => $_REQUEST['form'].'-'.$ELEMENT_ID,
						"ACTIVE"         => "Y"
					);
					$res = $el->Update($ELEMENT_ID, $arLoadElementArray);
				}
				mail($email,$title,$body,"Content-type: text/plain; charset=utf-8\r\nFrom: info@original-group.ru", "-finfo@original-group.ru");
				echo $answer;
			} else {
				echo 'Извините, возникли сомнения, что вы человек, попробуйте еще раз.';
			}
		} else {
			echo 'Извините, что-то пошло не так, попробуйте еще раз.';
		}
	}
?>