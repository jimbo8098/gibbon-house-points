<?php

use Gibbon\Module\HousePoints\Domain\HousePointsGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Forms\Form;
use Gibbon\Forms\Prefab\DeleteForm;

//include '../gibbon.php';

$mode = $_GET['mode'] ?? '';
$categoryID = $_GET['categoryID'] ?? '';
$returnTo = $_GET['returnTo'] ?? '';
$absoluteURL = $_SESSION[$guid]['absoluteURL'];
$statusText = $_GET['statusText'] ?? null;
$statusCode = $_GET['statusCode'] ?? null;

// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/category_manage.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
        print "You do not have access to this action." ;
    print "</div>" ;
} else {

    if($statusText != null || $statusCode != null)
    {
        $statusText = $statusText ?? "Error not provided";
        $statusCode = $statusCode ?? "500"; //Dummy error so that it shows as an error box but provides the error text
        echo "<div class=" . ($_GET['statusCode'] == 0 ? 'success' : 'error') . " >" . $_GET['statusText'] . "</div>";
    }
    //Common
    $page->breadcrumbs->add(__('Categories'));
    $hpGateway = $container->get(HousePointsGateway::Class);

    switch($mode)
    {
        case 'add':
        case 'edit':
            echo showAddEdit($hpGateway,$categoryID,$mode,$absoluteURL);
            break;

        case 'delete':
            echo showDelete($categoryID,$absoluteURL);
            break;

        case 'deleteconfirmed':
            deleteCategory($categoryID);
            break;

        default:
            echo showViewAll($hpGateway,$absoluteURL);
            break;
    }
}

function deleteCategory($categoryID)
{
    try
    {
        $sql = "DELETE FROM hpCategories WHERE categoryID = :categoryID";
        $stmnt = $connection2->prepare($sql);
        $stmnt->execute(array(
            'categoryID' => $categoryID
        ));
        $returnTo .= '&return=error2';
        header("Location: {$returnTo}");
        exit();
    }
    catch(PDOException $e)
    {
        $returnTo .= '&return=error2';
        header("Location: {$returnTo}");
        exit();
    }
}

function showDelete(HousePointsGateway $hpGateway,$absoluteURL)
{
    $catFunctions = $absoluteURL.'index.php?q=/modules/House Points/category_functions.php';
    $form = DeleteForm::createForm($catFunctions . '7mode=deleteconfirmed&categoryID='.$categoryID."&returnTo=" . $absoluteURL.'/modules/'.$_SESSION[$guid]['module']. '/category_manage.php');
    return $form->getOutput();
}

function showViewAll(HousePointsGateway $hpGateway,$absoluteURL)
{
    $criteria = $hpGateway->newQueryCriteria()
        ->sortBy('categoryOrder','ASC');
    $categories = $hpGateway->queryCategories($criteria,false,true);
    $table = DataTable::create('categories');
    $table->addHeaderAction('add',__('Add'))
        ->addParam('mode','add')
        ->addParam('q','/modules/House Points/category_manage.php');
    $table->addColumn('categoryName',__('Category'));
    $table->addColumn('categoryOrder',__('Category Order'));
    $table->addColumn('categoryType',__('Category Type'));
    $actions = $table->addActionColumn('actions',__('Actions'));
        $actions->format(function($row,$actions){

            $actions->addAction('edit',__('Edit'))
                ->addParam('categoryID',$row['categoryID'])
                ->addParam('mode','edit')
                ->setURL('/modules/House Points/category_manage.php');

            $actions->addAction('delete',__('Delete'))
                ->addParam('categoryID',$row['categoryID'])
                ->addParam('mode','delete')
                ->setURL('/modules/House Points/category_functions.php');
        });
    
    return $table->render($categories);
}

function showAddEdit(HousePointsGateway $hpGateway, $categoryID, $mode,$absoluteURL)
{
    $criteria = $hpGateway->newQueryCriteria()
        ->filterBy('categoryID',$categoryID);
    $categories = $hpGateway->queryCategories($criteria,false,true);

    //Get the used category ordinals. These are ordered
    $highestOrder = $hpGateway->queryUsedCategoryOrders('DESC')->toArray()[0]['value'];
    $availableCategories = [];
    array_push($availableCategories,'Top');
    foreach(range(1,$highestOrder + 1) as $existingOrdinal)
    {
        array_push($availableCategories,$existingOrdinal);
    }
    array_push($availableCategories,'Bottom');
        
    if($categories->count() > 1) throw new Exception("There are duplicate category IDs");
    else
    {
        $category = $categories->toArray()[0];
        
        $form = Form::create('catform', $absoluteURL.'/index.php?q=/modules/House Points/category_function.php','POST');
        $form->addHiddenValue('categoryID', $categoryID ?? 0);
        $form->addHiddenValue('returnTo',$absoluteURL . '?q=/modules/House Points/category_manage.php');
        $form->addHiddenValue('mode', $mode);    

        $row = $form->addRow();
            $row->addLabel('categoryName', __('Category Name'));
            $row->addTextField('categoryName')->required()->maxLength(45)->setValue($category['categoryName'] ?? '');

        $row = $form->addRow();
            $row->addLabel('categoryType', __('Type'));
            $row->addSelect('categoryType')->fromArray(array('House', 'Student'))->selected($category['categoryType'] ?? '');

        $row = $form->addRow();
            $row->addLabel('categoryOrder',__('Category Order'));
            $row->addSelect('categoryOrder')->fromArray($availableCategories);

        $row = $form->addRow();
            $row->addLabel('categoryPresets', __('Presets'))
                ->description(__('Add preset comma-separated increments as Name: PointValue. Leave blank for unlimited.'))
                ->description(__(' eg: ThingOne: 1, ThingTwo: 5, ThingThree: 10'));
            $row->addTextArea('categoryPresets')->setRows(2)->setValue($category['categoryPresets'] ?? '');

        $row = $form->addRow();
            $row->addSubmit(__('Save'));

        return $form->getOutput();
    }
}
