<?php

use PDOException;

$studentId = $_POST['studentId'] ?? '';
$points = $_POST['points'] ?? '';
$reason = $_POST['reason'] ?? '';
$categoryId = $_POST['categoryId'] ?? '';
$yearId = $_POST['yearId'] ?? '';
$awardedDate = $_POST['awardedDate'] ?? '';
$awardedBy = $_POST['awardedBy'] ?? '';

if($studentId != '' && $points != '' && $reason != '' && $categoryId != '' && $yearId != '' && $awardedDate != '' && $awardedBy != '')
{
    try{
        $data = array(
            'gibbonPersonID' => $studentId,
            'categoryId' => $categoryId,
            'points' => $points,
            'reason' => $reason,
            ''
        );
        $sql = "INSERT INTO hpPointStudent
        SET studentID = :studentId,
        categoryID = :categoryId,
        points = :points,
        reason = :reason,
        yearID = :yearID,
        awardedDate = :awardedDate,
        awardedBy = :awardedBy";
    }
    catch(PDOException $e)
    {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }
}
else
{
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
}

?>