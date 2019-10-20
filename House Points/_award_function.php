<?php
use Gibbon\Forms\Form;

class pt {
    
    function __construct($guid, $connection2) {
        $this->dbh = $connection2;
        $this->guid = $guid;
        
        $this->yearID = $_SESSION[$guid]['gibbonSchoolYearID'];
        $this->teacherID = $_SESSION[$guid]['gibbonPersonID'];
        
        if (isset($_POST['submit'])) {
            $this->awardSave();
        }
        $this->studentList = readStudentList($this->dbh, $this->yearID);
        $this->categoryList = readCategoryList($this->dbh, 'Student');
    }

    function mainForm() {
        echo "<p>&nbsp;</p>";
        echo "<h3>Award house points to students</h3>";

        $form = Form::create('awardForm', '');
        $form->addHiddenValue('submit', 'submit');
        $form->addHiddenValue('teacherID', $this->teacherID);

        $students = array_reduce($this->studentList->fetchAll(), function($group, $item) { 
            $studentName = $item['surname'].', '.$item['preferredName'];
            $group[$item['studentID']] = $studentName.' ('.$item['className'].' - '.$item['house'].')';
            return $group;
        }, array());
        $row = $form->addRow();
            $row->addLabel('studentID', __('Student'));
            $row->addSelect('studentID')->fromArray($students)->required()->placeholder();

        $highestAction = getHighestGroupedAction($this->guid, '/modules/House Points/student_award.php', $this->dbh);
        $unlimitedPoints = ($highestAction == 'Award student points_unlimited');
        var_dump($highestAction);

        $categories = array_reduce($this->categoryList->fetchAll(), function($group, $item) use ($unlimitedPoints) { 
            if (empty($item['categoryPresets']) && !$unlimitedPoints) return $group; 
            
            $group[$item['categoryID']] = $item['categoryName'];
            return $group;
        }, array());

        $row = $form->addRow();
            $row->addLabel('categoryID', __('Category'));
            $row->addSelect('categoryID')->fromArray($categories)->required()->placeholder();

        $row = $form->addRow();
            $row->addLabel('points', __('Points'));
            $row->addTextField('points')->disabled()->placeholder(__('Select a category'));

        $row = $form->addRow();
            $row->addLabel('reason', __('Reason'));
            $row->addTextArea('reason')->setRows(2)->required();
            
        $row = $form->addRow();
            $row->addButton('Submit', 'awardSave()', 'submit')->addClass('right');

        echo $form->getOutput();

        echo "<div>&nbsp;</div>";
        echo "<p id='msg' style='color:blue;'></p>";
        
        ?>
        <script>
            $('#awardForm #categoryID').change(function(){
                updateCategoryPoints();
            });

            $('#awardForm').change(function() {
                $('#msg').html('');
                $('#submit').prop('disabled', false).css({'background-color': 'black', 'color': 'white'});
            });
        </script>
        <?php

    }
    
}
