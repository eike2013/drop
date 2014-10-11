<?php
// settings
$password_md5 = ''; // md5sum($PASSWORD)
$timeout  = '10';
$dir      = './files/';

// Check if dir is set and if it's in the root dir (./files/*)
// Otherwise it'll point to it
if (isset($_GET['dir']) && strpos($_GET['dir'],$dir) !== false) {
	$dir = $_GET['dir'];
}

// session
session_start();

// timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout * 60)) {
	session_unset();
	session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

// session cookie and reload
if (isset($_POST['pass'])) {
	$_SESSION['pass'] = $_POST['pass'];
	header('Location:./?dir='.$dir);
}

// permission
if (isset($_SESSION['pass']) && (md5($_SESSION['pass']) == $password_md5)) {
	$perm = true;
} else {
	$perm = false;
}
?>
<!doctype html>

<html>
<head>

	<meta charset="utf-8">
	<title>
	<?php 
	echo (($perm) ? $dir : 'drop'); 
	?>
	</title>
	<link rel="stylesheet" href="assets/style.css">

</head>
<body><?php if (!$perm) { ?>

	<form enctype="multipart/form-data" action="./" method="POST" id="login">
		<input name="pass" type="password">
	</form>

</body>
</html><?php } else { ?>

	<script>
		before = (new Date()).getTime();
	</script>
	<script src="assets/filter.js"></script>

	<header>
		<h1>
		<?php
		$title = explode("/", $dir);
		$combined_path = "";		
		for($i=0; $i<count($title)-1; $i++){		
		$combined_path .= $title[$i] . "/"; 
		echo '<a href="index.php?dir='.$combined_path.'">' .$title[$i].'</a>';
		echo "/";
		}
		?>
		</h1>
	</header>

	<input type="search" class="table-filter" data-table="order-table" placeholder="filter">
   <?php
     // After upload, stay in current dir
	  echo'<form action="./?dir=' .$dir. '" method="POST" onsubmit="return confirm(\'Delete File?\');">';
	?>
	<table class="order-table table">
		<?php
		// delete
		if (isset($_POST['delete'])) {
			unlink($_POST['delete']);
			header('Location:./?dir='.$dir);
		}

		$files = glob($dir.'*');
		$files_lowercase = array_map('strtolower', $files);
		array_multisort($files_lowercase, SORT_ASC, SORT_STRING, $files);

		// readable filesize
		//array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_DESC, $files);
		function human_filesize($bytes, $decimals = 1) {
			$sz = "BKMG";
			$factor = floor((strlen($bytes) - 1) / 3);
			return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
		}
		// output statics
		// first row: link to root (.)
		if($dir != './files/') {
			echo '<tr><td></td><td>Rootdir</td><td class="file"><a href="index.php?dir=./files/">.</a></td></tr>';
		}
		
		// second row: link to parent dir (..)
		// ...yes i know the code looks ugly...iterating two times the same function doesn't appeal to be maximum aesthetic! but it works :)
		if($dir != './files/') {
			$stringposition = strrpos ( $dir, '/');
			$parentfolder = substr($dir,0,$stringposition);
			$stringposition = strrpos ( $parentfolder, '/');
			$parentfolder = substr($parentfolder,0,$stringposition);
			echo '<tr><td></td><td>Parentdir</td><td class="file"><a href="index.php?dir='.$parentfolder.'/">..</a></td></tr>';
		}

		// output dir's
		for ($i = 0; $i < count($files); $i++) {
			$stringposition = strrpos ($files[$i], '/');
			$foldername = substr($files[$i],$stringposition+1);
			if (!is_file($files[$i])) {
				echo '<tr><td>'.date('Y-m-d H:i', filemtime($files[$i])).'</td><td>Folder</td><td class="file"><a href="index.php?dir='.$files[$i].'/">'.$foldername.'</a></td></tr>';
	   	}
	   }				
				
		// output files
		for ($i = 0; $i < count($files); $i++) {
			//$basename = explode('.', basename($files[$i]),2)[1];
			$basename = basename($files[$i]);
			if (is_file($files[$i])) {
				//echo '<tr><td>'.date('Y-m-d H:i', filemtime($files[$i])).'</td><td>'.human_filesize(filesize($files[$i])).'</td><td class="file"><a href="'.$files[$i].'" download="'.$basename.'">'.$basename.'</a></td><td><button class="trash" name="delete" value="'.$files[$i].'">&#xe001;</button></td></tr>';
				echo '<tr><td>'.date('Y-m-d H:i', filemtime($files[$i])).'</td><td>'.human_filesize(filesize($files[$i])).'</td><td class="file"><a href="'.$files[$i].'" download="'.$basename.'">'.$basename.'</a></td></tr>';
		   }
		}
		?>

	</table>
	</form>

	<script>
		var after = (new Date()).getTime();
		var sec = (after-before)/1000;
		document.body.innerHTML += '<footer>' + sec.toFixed(3) + 'sec</footer>';
	</script>

</body>
</html><?php } ?>
