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
    
    $page->breadcrumbs->add(__('View points individual'));

    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/moduleFunctions.php";
    include $modpath."/individual_function.php";
   
    ?>
    <script>
        var modpath = '<?php echo $modpath ?>';
    </script>
    <?php
    
    $ind = new ind($guid, $connection2);
    $ind->modpath = $modpath;
    
    $ind->mainform();

    $form = Form::create('students',$gibbon->session->get('absoluteURL') . '/index.php','GET');
    $form->addHiddenValue('q',$gibbon->session->get('address'));
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $row = $form->addRow();
        $row->addLabel('studentID',__('Select Student'));
        $row
            ->addSelectStudent('studentID',$_GET['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID'))
            ->placeholder();
    $form->addRow()->addSubmit();
    echo $form->getOutput();

    $table = DataTable::create('studentpoints');
    
}
