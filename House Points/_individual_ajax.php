<?php
include  '../../gibbon.php';

$dbh = $connection2;

$studentID = $_POST['studentID'];

$data = array(
    'studentID' => $studentID,
    'yearID' => $_SESSION[$guid]['gibbonSchoolYearID']
);
$sql = "SELECT hpPointStudent.hpID,
    DATE_FORMAT(hpPointStudent.awardedDate, '%d/%m/%Y') AS awardedDate,
    hpPointStudent.points,
    hpCategory.categoryName,
    hpPointStudent.reason,
    CONCAT(gibbonPerson.title, ' ', gibbonPerson.preferredName, ' ', gibbonPerson.surname) AS teacherName
    FROM hpPointStudent
    INNER JOIN hpCategory
    ON hpCategory.categoryID = hpPointStudent.categoryID
    INNER JOIN gibbonPerson
    ON gibbonPerson.gibbonPersonID = hpPointStudent.awardedBy
    WHERE hpPointStudent.studentID = :studentID
    AND hpPointStudent.yearID = :yearID
    ORDER BY hpPointStudent.awardedDate DESC";
$rs = $dbh->prepare($sql);
$rs->execute($data);
$points = $rs->fetchAll();

$res = array(
    'points' => $points
);
echo json_encode($res);
