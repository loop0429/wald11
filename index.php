<?php
	require_once('constants.php');
?>
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
	/**
	 * 二文字返す
	 */
	function getNum($val, $pos) {
		return substr($val, $pos, 2);
	}

	/**
	 * 祝日かどうかの判定を行う
	 */
	function checkHoliday($m, $d) {
		//日本の祝日@googleカレンダー
		$calendar_id = urlencode('ja.japanese#holiday@group.v.calendar.google.com');
		$year = date('Y');
		$start = sprintf("%04d-{$m}-{$d}T00:00:00Z", $year); //検索開始日時
		$finish = sprintf("%04d-01-01T00:00:00Z", $year + 1); //検索終了日時

		$url = "https://www.googleapis.com/calendar/v3/calendars/{$calendar_id}/events?key=" . API_KEY . "&timeMin={$start}&timeMax={$finish}&maxResults=1&singleEvents=true";

		$result = file_get_contents($url);
		$json = json_decode($result);
		
		if(!empty($json)) {
			$item = $json->items;

			//年内に祝日がなくなると$itemsには何もなくなるため
			if(!empty($item)) {
				$target = $item[0]->start->date;
				$today = "{$year}-{$m}-{$d}";

				//今日が祝日か否か判定
				return ($target === $today) ? true : false;
			} else {
				return false;
			}
		} else {
			return false;
		}
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
		$is_holiday = checkHoliday($month, $day);

		//土曜・日曜の判定
		if($week === '0' || $is_holiday) {
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