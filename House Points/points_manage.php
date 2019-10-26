<?php

use Gibbon\Forms\Form;
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Forms\DatabaseFormFactory;

// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/points_manage.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    
    $page->breadcrumbs->add(__('Manage points'));

    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/moduleFunctions.php";
    include $modpath."/manage_function.php";

    $form = Form::create('student',$gibbon->session->get('absoluteURL') . '/index.php','GET');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('q','/modules/House Points/student_view.php');
    $row = $form->addRow();
        $row->addLabel('studentID',__('Select Student'));
        $row->addSelectStudent('studentID',$_GET['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID',''));
    $form->addRow()->addSubmit();
    echo $form->getOutput();

    $form = Form::create('house',$gibbon->session->get('absoluteURL') . '/index.php','GET');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('q','/modules/House Points/house_view.php');
    $row = $form->addRow();
        $row->addLabel('houseID',__('Select House'));
        $row->addSelectHouse('houseID');
    $form->addRow()->addSubmit();
    echo $form->getOutput();
}
