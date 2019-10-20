<?php
use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Module\HousePoints\Domain\HousePointsGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;

// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/student_view.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    
    $form = Form::create('students',$gibbon->session->get('absoluteURL') . '/index.php','GET');
    $form->addHiddenValue('q',$gibbon->session->get('address'));
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $row = $form->addRow();
        $row->addLabel('studentID',__('Select Student'));
        $row
            ->addSelectStudent('studentID',$_GET['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID'))
            ->selected($_GET['studentID'] ?? '')
            ->placeholder();
    $form->addRow()->addSubmit();
    echo $form->getOutput();

    $hpGateway = $container->get(HousePointsGateway::class);
    $criteria = $hpGateway->newQueryCriteria()
        ->filterBy('studentID',$_GET['studentID'] ?? '');
    $hp = $hpGateway->queryStudents($criteria,$_GET['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID'));

    $table = DataTable::createPaginated('studentpoints',$criteria);
    $table->addHeaderAction('add',__('Add'))
        ->addParam('studentID',$_GET['studentID'] ?? '')
        ->setURL('/modules/House Points/student_award.php');
    $table->addExpandableColumn('reason')
        ->format(function($row){
            return "<p><strong>Reason:</strong> " . nl2brr($row['reason']) . "</p>";
        });

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

    echo $table->render($hp);
    
}
