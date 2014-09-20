<?php
if(strtolower($_SERVER['REQUEST_METHOD']) != 'post')
	exit_status('Error! Wrong HTTP method!');

if (!is_dir($_POST['dir'])) {
	mkdir($_POST['dir'], 0777, true);
}

$randomString = substr(str_shuffle(md5(time())),0,10);

for($i=0; $i<count($_FILES['upload']['name']); $i++) {
	$tmpFilePath = $_FILES['upload']['tmp_name'][$i];
	if ($tmpFilePath != ""){
		$newFilePath = $_POST['dir'].$randomString .'.'. $_FILES['upload']['name'][$i];
		if(move_uploaded_file($tmpFilePath, $newFilePath)) {
			// file uploaded
		}
	}
}

// back to index
header('Location:./');
die();
?>
