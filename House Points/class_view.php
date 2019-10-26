<?php

use Gibbon\Forms\Form;
use Gibbon\Module\HousePoints\Domain\HousePointsGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Forms\DatabaseFormFactory;

// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/class_view.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    
    $page->breadcrumbs->add(__('View points class'));

    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    $gibbonYearId = $gibbon->session->get('gibbonSchoolYearID');
    include $modpath."/moduleFunctions.php";
   
    $form = Form::create('rollgroup',$gibbon->session->get('absoluteURL') . '/index.php?q=/modules/House Points/class_view.php','POST');
    
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('q',$gibbon->session->get('address'));

    $row = $form->addRow();
        $row->addLabel('rollGroupID',__('Select Class'));
        $row->addSelectRollGroup('rollGroupID',$gibbonYearId);
    
        
    $form->addRow()->addSubmit();
    echo $form->getOutput();
    
    $hpGateway = $container->get(HousePointsGateway::class);
    $criteria = $hpGateway
        ->newQueryCriteria()
        ->filterBy('rollGroupID',$_POST['rollGroupID'] ?? '');

    $hp = $hpGateway->queryStudentPoints($criteria,$gibbonYearId);

    $table = DataTable::createPaginated('classpoints',$criteria);
    $table->addColumn('student','Name')
        ->format(Format::using('name',['','firstname','surname','Student',false,true]));
    $table->addColumn('points','Points');
    $table->addColumn('house','House');

    echo $table->render($hp);
}
