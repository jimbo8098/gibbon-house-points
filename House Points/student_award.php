<?php
use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Module\HousePoints\Domain\HousePointsGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;

// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/student_award.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {

    $page->breadcrumbs->add(__('Award student points'));
    
    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/moduleFunctions.php";
    include $modpath."/award_function.php";
   
    ?>
    <script>
        var modpath = '<?php echo $modpath ?>';
    </script>
    <?php
    
    $pt = new pt($guid, $connection2);
    $pt->modpath = $modpath;
    
    $pt->mainform();

    echo "<p>&nbsp;</p>";
        echo "<h3>Award house points to students</h3>";

        $form = Form::create('awardForm', '');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('submit', 'submit');
        $form->addHiddenValue('teacherID', $this->teacherID);

        $row = $form->addRow();
            $row->addLabel('studentID', __('Student'));
            $row->addSelectStudent('studentID',$_GET['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID'))
                ->required()
                ->placeholder();

        $highestAction = getHighestGroupedAction($guid, '/modules/House Points/student_award.php', $connection2);
        $unlimitedPoints = ($highestAction == 'Award student points_unlimited');

        $hpGateway = $container->get(HousePointsGateway::Class);
        $criteria = $hpGateway->newQueryCriteria()->filterBy('categoryType','Student');
        $categories = $hpGateway->queryCategories($criteria,false);

        $row = $form->addRow();
            $row->addLabel('categoryName', __('Category'));
            $row->addSelect('categoryName')
                ->fromDataSet($categories,'categoryID','categoryName')
                ->required()
                ->placeholder();

        $row = $form->addRow();
            $row->addLabel('points', __('Points'));
            $row->addTextField('points')
                ->disabled()
                ->placeholder(__('Select a category'));

        $row = $form->addRow();
            $row->addLabel('reason', __('Reason'));
            $row->addTextArea('reason')->setRows(2)->required();
            
        $row = $form->addRow();
            $row->addButton('Submit', 'awardSave()', 'submit')->addClass('right');

        echo $form->getOutput();

        echo "<div>&nbsp;</div>";
        echo "<p id='msg' style='color:blue;'></p>";
    
}
