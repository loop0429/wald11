<!DOCTYPE html>
<html lang="jp">
<head>
<meta charset="UTF-8">
<title>Wald11 Schedule</title>
<link rel="stylesheet" href="css/normalize.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
	//作法不明だけど、とりあえずここに関数を貯める

	//二つ文字返す
	function getNum($val, $pos) {
		return substr($val, $pos, 2);
	}
?>
<ul>
<?php
	//とりあえず1週間分やっとけみたいな
	for($i = 0; $i < 7; $i++) {
		$date = date('ymd', strtotime('+ ' . $i . ' days'));
		$month = getNum($date, 2);
		$day = getNum($date, 4);
		$str = $month . '/' . $day;
		$week = date('w', strtotime('+ ' . $i . ' days'));
		$class = '';

		//土曜・日曜の判定
		//Todo:祝日の判定もしたい。
		if($week === '0') {
			$class .= ' class="sun"';
		} else if($week === '6') {
			$class .= ' class="sat"';
		}
?>
<li<?php echo $class; ?>><a href="http://wald11.com/schedule/index.php?d=<?php echo $date; ?>"><?php echo $str; ?></a></li>
<?php
	}
?>
</ul>
</body>
</html>