<?php
ini_set('error_log', 'logfile.txt');

////////////////////////////////////////////////////////////////////////////////
function getMode() {
    $mode = '';
    if (isset($_POST['mode'])) {
        $mode = $_POST['mode'];
    } else {
        if (isset($_GET['mode'])) {
            $mode = $_GET['mode'];
        }
    }
    return $mode;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readCategoryList($dbh, $type = null) {
    if (!empty($type)) {
        $data = array('type' => $type);
        $sql = "SELECT *
            FROM hpCategory
            WHERE categoryType=:type 
            ORDER BY categoryOrder";
    } else {
        $data = array();
        $sql = "SELECT *
            FROM hpCategory
            ORDER BY categoryOrder";
    }
    
    $rs = $dbh->prepare($sql);
    $rs->execute($data);
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////    
function readHouseList($dbh) {
    $sql = "SELECT gibbonHouse.gibbonHouseID AS houseID,
        gibbonHouse.name AS houseName
        FROM gibbonHouse
        ORDER BY gibbonHouse.name";
    $rs = $dbh->prepare($sql);
    $rs->execute();
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////    
    
////////////////////////////////////////////////////////////////////////////////
function readStudentList($dbh, $yearID) {
    $data = array(
        'yearID' => $yearID
    );
    $sql = "SELECT gibbonPerson.gibbonPersonID AS studentID,
        gibbonPerson.officialName,
        gibbonPerson.surname,
        gibbonPerson.preferredName,
        gibbonRollGroup.name AS className,
        gibbonHouse.gibbonHouseID AS houseID,
        gibbonHouse.name AS house
        FROM gibbonPerson
        INNER JOIN gibbonStudentEnrolment
        ON gibbonStudentEnrolment.gibbonPersonID = gibbonPerson.gibbonPersonID
        INNER JOIN gibbonRollGroup
        ON gibbonRollGroup.gibbonRollGroupID = gibbonStudentEnrolment.gibbonRollGroupID
        INNER JOIN gibbonHouse
        ON gibbonHouse.gibbonHouseID = gibbonPerson.gibbonHouseID
        WHERE gibbonStudentEnrolment.gibbonSchoolYearID = :yearID
        AND gibbonPerson.status='Full'
        ORDER BY gibbonPerson.surname, gibbonPerson.preferredName";
    $rs = $dbh->prepare($sql);
    $rs->execute($data);
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function selectStudent($studentList) {
    echo "<select name='studentID' id='studentID' style='float:left;' class='fullWidth'>";
        echo "<option value='0'>Please select</option>";
        while ($row = $studentList->fetch()) {
            $studentName = $row['surname'].', '.$row['preferredName'];
            echo "<option value='".$row['studentID']."'>".$studentName.' ('.$row['className']." - ".$row['house'].")</option>";
        }
    echo "</select>";
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function selectHouse($houseList) {
    echo "<select name='houseID' id='houseID' style='float:left;' class='fullWidth'>";
        echo "<option value='0'>Please select</option>";
        while ($row = $houseList->fetch()) {
            echo "<option value='".$row['houseID']."'>".$row['houseName']."</option>";
        }
    echo "</select>";
}
////////////////////////////////////////////////////////////////////////////////

function showResultAlert($res)
{
    if(!is_null($res))
    {
        $divHTML = "";
        switch($res)
        {
            case 0: $divHTML .= "<div class='success'>"; break;
            default: $divHTML .= "<div class='error'>"; break;
        }

        switch($res)
        {
            case 0: $divHTML .= "Points added successfully!"; break;
            default: $divHTML .= "An error occurred"; break;
        }

        $divHTML .= "</div>";
        return $divHTML;
    }
    else
    {
        return "";
    }
}