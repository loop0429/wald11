<?php
	require_once('constants.php');
	date_default_timezone_set('Asia/Tokyo'); 
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
	 * 今回はyyとmmとddを返すために使用している。
	 * @param string $val 文字列
	 * @param int $pos 文字切り抜き開始位置 
	 * @return string
	 */
	function getNum($val, $pos) {
		return substr($val, $pos, 2);
	}

	/**
	 * 「土曜・日曜・祝日・映画の日」用のclassを返す
	 * @param string $d 日付
	 * @param string $w 曜日
	 * @param boolean $h 祝日判定
	 * @return string
	 */
	function coloring($d, $w, $h) {
		$color = '';
		if(intval($d) === 1) {
			$color .= ' class="movie"'; //映画の日
		} else {
			if($w === 0 || $h) {
				$color .= ' class="sun"'; //日曜・祝日
			} else if($w === 6) {
				$color .= ' class="sat"'; //土曜
			}
		}

		return $color;
	}

	/**
	 * liタグを作成して返す
	 * @param array $ow 一週間分の配列
	 * @param array $hl 一週間分の祝日判定配列
	 * @return string
	 */
	function createLi($ow, $hl) {
		$val = explode('-', $ow[0]);
		$year = $ow[2];
		$month = $val[0];
		$day = $val[1];
		$week = intval($ow[1]);
		$text = $month . '/' . $day; //表示する文字
		$class = coloring($day, $week, $hl);
		$schedule = 'http://wald11.com/schedule/';
		$xml = 'http://wald11.com/schedule/xml/';
		$param = '?d=';
		$join = $year . $month . $day;
		$li = '';

		$result = file_get_contents($xml . $join . '.xml');

		if(!empty($result)) {
			$li .= '<li' . $class . '><a href="' . $schedule . $param . $join . '">' . $text . '</a></li>';
		} else {
			$li .= '<li' . $class . '>' . $text . '</li>';
		}

		return $li;
	}

	/**
	 * 祝日かどうか比較するための配列を返す
	 * 【Thanks】http://www.pahoo.org/e-soul/webtech/php06/php06-31-01.shtm
	 * @param array $ow 一週間分の配列
	 * @return array
	 */
	function getHolidayList($ow) {
		$calendar_id = urlencode('ja.japanese#holiday@group.v.calendar.google.com'); //日本の祝日@google

		$year = date('Y');
		
		//今日を作成
		$today = explode('-', $ow[0][0]);
		$month = $today[0];
		$day = $today[1];

		//一週間分
		$len = count($ow);

		$start = sprintf("%04d-{$month}-{$day}T00:00:00Z", $year); //検索開始日時
		$finish = sprintf("%04d-01-01T00:00:00Z", $year + 2); //検索終了日時。一応2年先まで。

		//googleカレンダーへ取得しに行く
		//【Doc】https://developers.google.com/google-apps/calendar/v3/reference/events/list
		$url = "https://www.googleapis.com/calendar/v3/calendars/{$calendar_id}/events?key=" . API_KEY . "&timeMin={$start}&timeMax={$finish}&maxResults={$len}&orderBy=startTime&singleEvents=true";
		$result = file_get_contents($url);
		$json = json_decode($result);

		//すべて平日と仮定しておく
		$holiday_list = array(0, 0, 0, 0, 0, 0, 0);

		if(!empty($json)) {
			//直近7件の祝日リスト
			$item = $json->items;
			if(!empty($item)) {
				//$one_weekごとに比較する
				for($i = 0; $i < $len; $i++) {
					$val = explode('-', $ow[$i][0]);
					$m = $val[0];
					$d = $val[1];
					//年越し
					if($m === '01' && $d === '01') {
						$year++;
					}
					$s = $year . '-' . $ow[$i][0];
					$tmp = false;

					//$holiday_listとの比較
					for($j = 0; $j < count($item); $j++) {
						$t = $item[$j]->start->date;

						//祝日と判別する
						if($t === $s) {
							$tmp = true;
						}
					}
					//祝日であれば1を代入
					if($tmp) {
						$holiday_list[$i] = 1;
					}
				}
			}
		}
		return $holiday_list;
	}

	/**
	 * 日付に関する情報を返す
	 * @param int $i 日付カウントアップ用
	 * @return string
	 */
	function createDate($i) {
		$date = date('ymdw', strtotime('+ ' . $i . ' days'));
		$year = getNum($date, 0);
		$month = getNum($date, 2);
		$day = getNum($date, 4);
		$week = getNum($date, 6);

		return array($month . '-' . $day, $week, $year);
	}
?>
<ul class="schedule">
<?php
	//1週間分の日付を作っておく
	$one_week = array();

	for($i = 0; $i < 7; $i++) {
		$one_week[$i] = createDate($i);
	}

	//祝日リストの作成
	$holiday_list = getHolidayList($one_week);
	$len = count($one_week);

	//描画
	for($i = 0; $i < $len; $i++) {
		$li = createLi($one_week[$i], $holiday_list[$i]);
?>
<?php echo $li; ?>
<?php
	}
?>
</ul>
</body>
</html>