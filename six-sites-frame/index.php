<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">
<html lang="ru">
	<head>
		<meta HTTP-EQUIV="Refresh" Content="30" />
		<meta charset='utf-8'>
		<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta http-equiv="Expires" content="0"/>
		<title>МультиОкно</title>
	</head>
	<body>
		<frameset rows="50%, 50%">
		<?php
			$sites = explode("\n", file_get_contents('sites.list'));
			$rand = mt_rand();
			$index = 0;
			for ($row = 1; $row <= 2; $row++) {
	            echo "<frameset cols=\"33%,33%,33%\">";
	            for ($col = 0; $col < 3; $col++) {
	                if (isset($sites[$index]) && $sites[$index] !== '') {
	                    $site = $sites[$index];
	                } else {
					$site = "about:blank";
	                }
	                $index++;
	                echo "<frame src=\"" . $site . "?random=" . $rand . "\" scrolling=\"no\" noresize>";
	            }
				echo "</frameset>";
			}
		?>
		</frameset>
	</body>
</html>
