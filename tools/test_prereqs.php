<?php declare(strict_types=1);

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../backends/mysqlpdo.inc');

use Atlas\Query\Delete;
use Atlas\Query\Insert;
use Atlas\Query\Select;
use Atlas\Query\Update;

// create_schema.php auto-seeds some things and not others.
// Build what we need.

$db = MyPDO::instance();

$delete = Delete::new($db);
$delete->from('Announcements')->perform();
$delete->from('Authentication')->perform();
$delete->from('MeetingAttendance')->perform();
$delete->from('OfficialMeetings')->perform();
$delete->from('ConComList')->perform();
$delete->from('Registrations')->perform();
$delete->from('BadgeTypes')->perform();
$delete->from('VolunteerHours')->perform();
$delete->from('HourRedemptions')->perform();
$delete->from('Events')->perform();
$delete->from('AnnualCycles')->perform();
$delete->from('AccountConfiguration')->perform();
$delete->from('Members')->perform();

$select = Select::new($db);
$select->columns('Value')->from('Configuration')->whereEquals(['Field' => 'ADMINACCOUNTS']);
$val = $select->fetchOne();

$val['Value'] = '1000,'.$val['Value'];

$update = Update::new($db);
$update->table('Configuration')->columns($val)->whereEquals(['Field' => 'ADMINACCOUNTS'])->perform();

$from = strtotime('-1 month');
$to = strtotime('+1 year');

$insert = Insert::new($db);
$insert->into('AnnualCycles')->columns([
    'DateFrom' => date('Y-m-d', $from),
    'DateTo' => date('Y-m-d', $to)
])->perform();
$cycleId = $insert->getLastInsertId();

$from = strtotime('-1 day');
$to = strtotime('+1 day');

$insert = Insert::new($db);
$insert->into('Events')->columns([
    'AnnualCycleID' => $cycleId,
    'DateFrom' => date('Y-m-d', $from),
    'DateTo' => date('Y-m-d', $to),
    'EventName' => 'AsgardCon'
])->perform();
$eventId = $insert->getLastInsertId();

$insert = Insert::new($db);
$insert->into('BadgeTypes')->columns([
    'AvailableFrom' => date('Y-m-d', $from),
    'AvailableTo' => date('Y-m-d', $to),
    'Cost' => 0,
    'Name' => 'A Badge',
    'EventID' => $eventId
])->perform();

$insert = Insert::new($db);
$insert->into('Members')->columns([
    'AccountID' => 1000,
    'FirstName' => 'Odin',
    'LastName' => 'Allfather',
    'Email' => 'allfather@oneeye.com',
    'Gender' => 'Allfather'
])->perform();

$auth = \password_hash('Sleipnir', PASSWORD_DEFAULT);

$insert = Insert::new($db);
$insert->into('Authentication')->columns([
    'AccountID' => 1000,
    'Authentication' => $auth,
    'LastLogin' => null,
    'Expires' => date('Y-m-d', strtotime('+1 year')),
    'FailedAttempts' => 0,
    'OneTime' => null,
    'OneTimeExpires' => null
])->perform();

$select = Select::new($db);
$select->columns('PositionID')->from('ConComPositions')->whereEquals(['Name' => 'Head']);
$val = $select->fetchOne();
$posId = $val['PositionID'];

$insert = Insert::new($db);
$insert->into('ConComList')->columns([
    'AccountID' => 1000,
    'DepartmentID' => 1,
    'EventID' => $eventId,
    'PositionID' => $posId
])->perform();
