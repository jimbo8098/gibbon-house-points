<?php
class mypt {
    
    function __construct($guid, $connection2) {
        $this->dbh = $connection2;
        $this->guid = $guid;
        
        $this->yearID = $_SESSION[$guid]['gibbonSchoolYearID'];
        
        $this->studentList = readStudentList($this->dbh, $this->yearID);
        //$this->categoryList = readCategoryList($this->dbh);
        
    }

    function mainForm() {
        echo "<p>&nbsp;</p>";
        echo "<h3>My house points</h3>";
        
        echo "<div>&nbsp;</div>";
        
        echo "<div id='studentPoints'></div>";
        
        ?>
        <script>
            showIndividualPoint(studentID);
        </script>
        <?php
    }
    
}