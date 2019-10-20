<?php
class ind {
    
    function __construct($guid, $connection2) {
        $this->dbh = $connection2;
        $this->guid = $guid;
        
        $this->yearID = $_SESSION[$guid]['gibbonSchoolYearID'];
        
        $this->studentList = readStudentList($this->dbh, $this->yearID);
        $this->categoryList = readCategoryList($this->dbh);
        
    }

    function mainForm() {
        echo "<p>&nbsp;</p>";
        echo "<h3>View house points for a student</h3>";
        echo "<table class='fullWidth'>";
            echo "<tr>";
                echo "<th style='width:20%;'>Select student</div>";
                echo "<td>";
                    selectStudent($this->studentList);
                echo "</td>";
            echo "</tr>";
        echo "</table>";
        
        echo "<div>&nbsp;</div>";
        
        echo "<div id='studentPoints'></div>";
        
        ?>
        <script>
            $('#studentID').change(function() {
                showIndividualPoint($('#studentID').val());
            });
        </script>
        <?php
    }
    
}