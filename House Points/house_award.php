<?php
use Gibbon\Forms\Form;

include __DIR__ . '/moduleFunctions.php';
// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/house_award.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    
    $page->breadcrumbs->add(__('Award house points'));

    global $pdo;
        
    echo "<p>&nbsp;</p>";
    echo "<h3>Award house points to house</h3>";

    $form = Form::create('awardForm', '');
    $form->addHiddenValue('submit', 'submit');
    $form->addHiddenValue('teacherID', $gibbon->session->get('gibbonPersonID') ?? '');

    $sql = "SELECT gibbonHouse.gibbonHouseID AS value, gibbonHouse.name FROM gibbonHouse ORDER BY gibbonHouse.name";
    $row = $form->addRow();
        $row->addLabel('houseID', __('House'));
        $row->addSelect('houseID')->fromQuery($pdo, $sql)->required()->placeholder()->selected($_GET['houseID'] ?? '');

    $highestAction = getHighestGroupedAction($guid, '/modules/House Points/house_award.php', $connection2);
    $unlimitedPoints = ($highestAction == 'Award house points_unlimited');

    $categories = array_reduce(readCategoryList($connection2, 'House')->fetchAll(), function($group, $item) use ($unlimitedPoints) { 
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
        $row->addButton('Submit', 'houseSave()', 'submit')->addClass('right');

    echo $form->getOutput();

    echo "<div>&nbsp;</div>";
    echo "<p id='msg' style='color:blue;'></p>";
    echo "<script>
        
    $('#awardForm #categoryID').change(function(){
        updateCategoryPoints();
    });

    </script>";
        
}
