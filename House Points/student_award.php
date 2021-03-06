<?php
include "./modules/".$_SESSION[$guid]["module"] . "/moduleFunctions.php";

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

    echo showResultAlert($_GET['result']);
    echo "<h3>Award house points to students</h3>";

    $form = Form::create('awardForm', $gibbon->session->get('absoluteURL') . '/index.php','get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('q','/modules/House Points/award_save.php');
    $form->addHiddenValue('return',$gibbon->session->get('address'));
    $form->addHiddenValue('mode','student');
    $form->addHiddenValue('teacherID', $gibbon->session->get('gibbonPersonID') ?? '');

    $row = $form->addRow();
        $row->addLabel('studentID', __('Student'));
        $row->addSelectStudent('studentID',$_GET['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID'))
            ->selected($_GET['studentID'] ?? '')
            ->required()
            ->placeholder();

    $highestAction = getHighestGroupedAction($guid, '/modules/House Points/student_award.php', $connection2);
    $unlimitedPoints = ($highestAction == 'Award student points_unlimited');

    $hpGateway = $container->get(HousePointsGateway::Class);
    $criteria = $hpGateway->newQueryCriteria()->filterBy('categoryType','Student');
    $categories = $hpGateway->queryCategories($criteria,false);

    $row = $form->addRow();
        $row->addLabel('categoryName', __('Category'));
        $row->addSelect('categoryID')
            ->fromDataSet($categories,'categoryID','categoryName')
            ->required()
            ->placeholder();

    //TODO: Dynamic input in some way. AJAX? Two inputs instead of one? Not sure yet.
    $row = $form->addRow();
        $row->addLabel('points', __('Points'));
        $row->addNumber('points')
            ->placeholder(__('Points to add'));

    $row = $form->addRow();
        $row->addLabel('reason', __('Reason'));
        $row->addTextArea('reason')->setRows(2)->required();
        
    $row = $form->addRow();
        $row->addSubmit('Submit', 'submit')
        ->addClass('right');

    echo $form->getOutput();

    echo "<div>&nbsp;</div>";
    echo "<p id='msg' style='color:blue;'></p>";
    
}
