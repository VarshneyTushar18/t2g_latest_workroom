<?php
/**
 * CLI tests for Workroom 13h session pairing (no DB writes).
 * Run: php tests/wfh_overnight_session_test.php
 */

const ATTENDANCE_SESSION_WINDOW_HOURS = 13;

/**
 * Mirror of Timesheets_model::pair_workroom_cio_by_session_window for offline tests.
 *
 * @param array<string, array<int, array<string, mixed>>> $cio_by_date
 * @return array{cio_by_date: array, carryover_dates: array<string, string>}
 */
function pair_workroom_cio_by_session_window_test(array $cio_by_date, $window_hours = null)
{
	$window_hours = $window_hours ?? ATTENDANCE_SESSION_WINDOW_HOURS;
	$window_sec = (int) (max(1, (float) $window_hours) * 3600);

	$events = [];
	foreach ($cio_by_date as $d => $rows) {
		foreach ($rows as $row) {
			$full = trim((string) ($row['date'] ?? ''));
			if ($full === '' && !empty($row['d']) && !empty($row['t'])) {
				$full = $row['d'] . ' ' . $row['t'];
			}
			$ts = $full !== '' ? strtotime($full) : false;
			if ($ts === false) {
				continue;
			}
			$events[] = [
				'ts' => (int) $ts,
				'row' => $row,
				'type' => (int) ($row['type_check'] ?? 0),
				'cal' => date('Y-m-d', $ts),
			];
		}
	}
	usort($events, function ($a, $b) {
		return ($a['ts'] ?? 0) <=> ($b['ts'] ?? 0);
	});

	$paired = [];
	$moved_from_date = [];
	$n = count($events);
	$i = 0;

	while ($i < $n) {
		while ($i < $n && $events[$i]['type'] !== 1) {
			$ev = $events[$i];
			$paired[$ev['cal']][] = $ev['row'];
			$i++;
		}
		if ($i >= $n) {
			break;
		}

		$start_ts = $events[$i]['ts'];
		$start_date = date('Y-m-d', $start_ts);
		$end_ts = $start_ts + $window_sec;

		while ($i < $n) {
			$ev = $events[$i];
			if ($ev['ts'] > $end_ts) {
				break;
			}
			if ($ev['type'] === 1 && $ev['ts'] > $start_ts && $ev['cal'] !== $start_date) {
				break;
			}
			if ($ev['cal'] !== $start_date) {
				$moved_from_date[$ev['cal']] = $start_date;
			}
			$paired[$start_date][] = $ev['row'];
			$i++;
		}
	}

	foreach ($paired as $d => $rows) {
		usort($rows, function ($a, $b) {
			return strcmp((string) ($a['date'] ?? ''), (string) ($b['date'] ?? ''));
		});
		$paired[$d] = $rows;
	}

	$carryover_dates = [];
	foreach ($moved_from_date as $d => $parent) {
		if (empty($paired[$d])) {
			$carryover_dates[$d] = $parent;
		}
	}

	return [
		'cio_by_date' => $paired,
		'carryover_dates' => $carryover_dates,
	];
}

function cio_row($ymd, $hms, $type_check)
{
	return [
		'd' => $ymd,
		't' => $hms,
		'type_check' => $type_check,
		'date' => $ymd . ' ' . $hms,
	];
}

function assert_true($cond, $msg)
{
	if (!$cond) {
		throw new RuntimeException('FAIL: ' . $msg);
	}
	echo 'OK: ' . $msg . PHP_EOL;
}

$pair = pair_workroom_cio_by_session_window_test([
	'2025-10-22' => [cio_row('2025-10-22', '18:20:00', 1)],
	'2025-10-23' => [cio_row('2025-10-23', '03:28:00', 2)],
]);
assert_true(count($pair['cio_by_date']['2025-10-22']) === 2, 'Overnight IN+OUT on check-in date');
assert_true(empty($pair['cio_by_date']['2025-10-23'] ?? []), 'Carryover morning empty on OUT date');
assert_true(($pair['carryover_dates']['2025-10-23'] ?? '') === '2025-10-22', 'Carryover map for NC day');

$raw2 = [
	'2025-10-22' => [cio_row('2025-10-22', '18:20:00', 1)],
	'2025-10-23' => [
		cio_row('2025-10-23', '03:28:00', 2),
		cio_row('2025-10-23', '10:00:00', 1),
		cio_row('2025-10-23', '19:00:00', 2),
	],
];
$pair2 = pair_workroom_cio_by_session_window_test($raw2);
assert_true(count($pair2['cio_by_date']['2025-10-23']) === 2, 'Day 2 own IN+OUT session');
assert_true(!isset($pair2['carryover_dates']['2025-10-23']), 'No NC when day 2 has own IN');

$pair3 = pair_workroom_cio_by_session_window_test([
	'2025-10-15' => [
		cio_row('2025-10-15', '10:00:00', 1),
		cio_row('2025-10-15', '19:00:00', 2),
	],
]);
assert_true(count($pair3['cio_by_date']['2025-10-15']) === 2, 'Same-day shift');

$pair4 = pair_workroom_cio_by_session_window_test([
	'2025-10-16' => [cio_row('2025-10-16', '09:00:00', 1)],
]);
assert_true(count($pair4['cio_by_date']['2025-10-16']) === 1, 'IN only on check-in date');

echo PHP_EOL . 'All pairing tests passed.' . PHP_EOL;
