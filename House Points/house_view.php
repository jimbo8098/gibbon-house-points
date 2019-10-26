<?php

use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Module\HousePoints\Domain\HousePointsGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Forms\Form;

// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/house_view.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    
    $houseID = $_GET['houseID'] ?? '';

    $page->breadcrumbs->add(__('View points overall'));
    $hpGateway = $container->get(HousePointsGateway::Class);
    if($houseID != '')
    {
        $criteria = $hpGateway->newQueryCriteria()
            ->sortBy('awardedDate','DESC')
            ->filterBy('houseId',$_GET['houseID']);
        $points = $hpGateway->queryStudentPoints($criteria,$gibbon->session->get('gibbonSchoolYearID'));
        $table = DataTable::create('housepoints');
        $table->addHeaderAction('add',__('Add'))
            ->addParam('studentID',$_GET['studentID'] ?? '')
            ->setURL('/modules/House Points/student_award.php');
        /*
            TODO: Something not right here
        $table->addExpandableColumn('reason')
            ->format(function($row){
                return "<p><strong>Reason:</strong> " . nl2brr($row['reason']) . "</p>";
            });
        */
        $table->addColumn('student',__('Student'))->format(Format::using('name',[
                '',
                'firstname',
                'surname',
                'Student',
                false,
                true
            ]));
        $table
            ->addColumn('awardedDate',__('Awarded Date'))
            ->format(Format::using('date',['awardedDate']));
        $table->addColumn('points',__('Points'));
        $table->addColumn('categoryName',__('Category Name'));
        $table->addColumn('teacherName',__('Teacher Name'))
            ->format(Format::using('name',[
                'teacherTitle',
                'teacherFirstname',
                'teacherSurname',
                'Staff',
                false,
                false
            ]));

        $actions = $table->addActionColumn();
        $actions->addAction('delete',__('Delete'));
        echo $table->render($points);
    }
    else
    {
        $criteria = $hpGateway->newQueryCriteria()
            ->sortBy('total','DESC');
        $hp = $hpGateway->queryOverallPoints($criteria, $_GET['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID'));
        $table = DataTable::create('housepoints');
        
        $table->addColumn('houseLogo',__('Crest'))->format(Format::using('userPhoto',['houseLogo',75]))->width("150px");
        $table->addColumn('houseName',__('House Name'));
        $table->addColumn('total',__('Total'))->format(Format::using('number','total'));

        $actions = $table->addActionColumn();
            $actions->format(function($row,$actions) {
                $actions->addAction('add',__('Add'))
                    ->setURL('/modules/House Points/house_award.php')
                    ->addParam('houseID',$row['gibbonHouseID']);

                $actions->addAction('view',_('View'))
                    ->setURL('/modules/House Points/house_view.php')
                    ->addParam('houseID',$row['gibbonHouseID']);
            });
        echo $table->render($hp);
    }
}
