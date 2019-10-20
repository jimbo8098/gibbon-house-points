<?php

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
    
    $page->breadcrumbs->add(__('View points overall'));
    
    $hpGateway = $container->get(HousePointsGateway::Class);
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
        });
    echo $table->render($hp);
}
