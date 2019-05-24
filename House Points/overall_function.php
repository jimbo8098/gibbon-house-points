<?php
class over {
    
    function __construct($guid, $connection2) {
        $this->dbh = $connection2;
        $this->guid = $guid;
        
        $this->yearID = $_SESSION[$guid]['gibbonSchoolYearID'];
        $this->teacherID = $_SESSION[$guid]['gibbonPersonID'];
        
        if (isset($_POST['submit'])) {
            $this->awardSave();
        }
        $this->pointsList = $this->readPointsList($this->dbh, $this->yearID);
    }

    function mainForm() {

        echo "<p>&nbsp;</p>";
        echo "<h3>Overall House Points</h3>";
        
        echo "<table style='width:100%;font-size:14pt'>";
            echo "<tr>";
                echo "<th style='width:15%'>Crest</th>";
                echo "<th style='width:35%'>House</th>";
                echo "<th style='width:30%'>Points</th>";
            echo "</tr>";
                
            while ($row = $this->pointsList->fetch()) {
                echo "<tr>";
                    echo "<td class='textCenter'>";
                    if (!empty($row['houseLogo'])) {
                        echo sprintf('<img src="%1$s" title="%2$s" style="width:auto;height:80px;">', $_SESSION[$this->guid]['absoluteURL'].'/'.$row['houseLogo'], $row['houseName'] );
                    }
                    echo "</td>";
                    echo "<td>".$row['houseName']."</td>";
                    echo "<td>".$row['total']."</td>";
                echo "</tr>";
            }
        echo "</table>";
    }
    
    function readPointsList() {
        $data = array(
            'yearID' => $this->yearID
        );
        $sql = "SELECT gibbonHouse.gibbonHouseID AS houseID,
            gibbonHouse.name AS houseName,
            gibbonHouse.logo as houseLogo,
            COALESCE(pointStudent.total + pointHouse.total, pointStudent.total, 
            pointHouse.total, 0) AS total
            FROM gibbonHouse
            LEFT JOIN 
            (
                SELECT gibbonPerson.gibbonHouseID AS houseID,
                SUM(hpPointStudent.points) AS total
                FROM hpPointStudent
                INNER JOIN gibbonPerson
                ON hpPointStudent.studentID = gibbonPerson.gibbonPersonID
                WHERE hpPointStudent.yearID=:yearID
                GROUP BY gibbonPerson.gibbonHouseID

            ) AS pointStudent
            ON pointStudent.houseID = gibbonHouse.gibbonHouseID
            LEFT JOIN 
            (
                SELECT hpPointHouse.houseID,
                SUM(hpPointHouse.points) AS total
                FROM hpPointHouse
                WHERE hpPointHouse.yearID=:yearID
                GROUP BY hpPointHouse.houseID
            ) AS pointHouse
            ON pointHouse.houseID = gibbonHouse.gibbonHouseID
            ORDER BY total DESC";
        $rs = $this->dbh->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
}
