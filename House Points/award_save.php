<?php

$dbh = $connection2;
$highestAction = getHighestGroupedAction($guid, '/modules/House Points/award.php', $dbh);

$returnTo = $_GET['return'] ?? "";
$mode = $_GET['mode'] ?? "";
$houseID = $_GET['houseID'] ?? 0;
$teacherID = $_GET['teacherID'] ?? 0;
$studentID = $_GET['studentID'] ?? 0;
$categoryID = $_GET['categoryID'] ?? 0;
$points = $_GET['points'] ?? 0;
$reason = $_GET['reason'] ?? "";
$yearID = $_SESSION[$guid]['gibbonSchoolYearID'] ?? 0;
$status = "";

//Ensures the script always receives a teacher id, awardedBy in the table 
//MUST be populated for the other parts to inner join properly
if($teacherID == 0) { $status = "A Teacher ID wasn't provided"; } 
switch($mode)
{
    case "house": if($houseID == 0) $status = "Please select a house"; break;
    case "student": if($studentID == 0) $status = "Please select a student"; break;
    default: $status = "Please select an award mode"; break;
}

if ($categoryID == 0) $status = "Please select a category";
if (empty($reason)) $status = "Please provide a detailed reason";
if ($points == 0) $status = "Please enter a valid point value";
if ($highestAction != 'Award student points_unlimited') {
    if ($points<1 || $points>20) {
        $status = "Please award between 1 and 20 points<br />"; 
    }
}
//If an error occurs, don't continue
if($status != "") header("Location: " .$gibbon->session->get('absoluteURL') . "?q=". $returnTo . "&result=0&status=" . $status);

$data = array(
    'categoryID' => $categoryID,
    'points' => $points,
    'reason' => $reason,
    'yearID' => $_SESSION[$guid]['gibbonSchoolYearID'],
    'awardedDate' => date('Y-m-d'),
    'awardedBy' => $teacherID
);

$sql = "";
switch($mode)
{
    case "student":
        $data['studentID'] = $studentID;
        $sql = "INSERT INTO hpPointStudent
            SET studentID = :studentID,
            categoryID = :categoryID,
            points = :points,
            reason = :reason,
            yearID = :yearID,
            awardedDate = :awardedDate,
            awardedBy = :awardedBy";
        break;

    case "house":
        $data['houseID'] = $houseID;
        $sql = "INSERT INTO hpPointHouse 
            SET 
                houseID = :houseID,
                categoryID = :categoryID,
                points = :points,
                reason = :reason,
                yearID = :yearID,
                awardedDate = :awardedDate,
                awardedBy = :awardedBy
            ;";
        break;
}


if($sql != "")
{
    $rs = $dbh->prepare($sql);
    $ok = $rs->execute($data);
    if ($ok) {
        $status = "Points successfully added";
    } else {
        $status = "Problem - contact system adminstrator";
    }
}


if($returnTo != "")
{
    header("Location: " .$gibbon->session->get('absoluteURL') . "?q=". $returnTo . "&result=0&status=" . $status);
}
else
{
    return json_encode(array(
        "status" => $status,
        "result" => $ok ? "ok" : "fail"
    ));
}