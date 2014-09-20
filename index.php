<?php
// settings
$password = 'pass';
$timeout  = '10';
$dir      = './files/';

// TODO more directories?
if (isset($_GET['dir'])) {
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
	header('Location: ./');
}

// permission
if (isset($_SESSION['pass']) && ($_SESSION['pass'] == $password)) {
	$perm = true;
} else {
	$perm = false;
}
?>
<!doctype html>

<html>
<head>

	<meta charset="utf-8">
	<title><?php echo (($perm) ? $dir : 'drop'); ?></title>
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
		<h1><?php echo $dir; ?></h1>
	</header>

	<input type="search" class="table-filter" data-table="order-table" placeholder="filter">
	<form action="upload.php" method="POST" enctype='multipart/form-data' id="uploadForm">
		<input type="hidden" name="dir" value="<?php echo $dir; ?>">
		<input type="file" name="upload[]" multiple="multiple" style="display:none" id="hiddenUp" onchange="this.form.submit()">
		<input type="button" onclick="document.getElementById('hiddenUp').click()" value="upload files">
	</form>

	<form action="./" method="POST" onsubmit="return confirm('Delete File?');">
	<table class="order-table table">
		<?php
		// delete
		if (isset($_POST['delete'])) {
			unlink($_POST['delete']);
			header('Location: ./');
		}

		$files = glob($dir.'*');

		// readable filesize
		array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_DESC, $files);
		function human_filesize($bytes, $decimals = 1) {
			$sz = "BKMG";
			$factor = floor((strlen($bytes) - 1) / 3);
			return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
		}

		// output
		for ($i = 0; $i < count($files); $i++) {
			$basename = explode('.', basename($files[$i]),2)[1];
			if (is_file($files[$i])) {
				echo '<tr><td>'.date('Y-m-d H:i', filemtime($files[$i])).'</td><td>'.human_filesize(filesize($files[$i])).'</td><td class="file"><a href="'.$files[$i].'" download="'.$basename.'">'.$basename.'</a></td><td><button class="trash" name="delete" value="'.$files[$i].'">&#xe001;</button></td></tr>';
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
