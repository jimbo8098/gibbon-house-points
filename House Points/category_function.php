<?php
use Gibbon\Forms\Form;
use Gibbon\Module\HousePoints\Domain\HousePointsGateway;

$mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
$categoryID = isset($_POST['categoryID'])? $_POST['categoryID'] : '';
$categoryName = isset($_POST['categoryName'])? trim($_POST['categoryName']) : '';
$categoryType = isset($_POST['categoryType'])? $_POST['categoryType'] : '';
$categoryPresets = isset($_POST['categoryPresets'])? trim($_POST['categoryPresets']) : '';
$categoryOrder = isset($_POST['categoryOrder'])?trim($_POST['categoryOrder']) : '';
$returnTo = $_POST['returnTo'] ?? $_GET['returnTo'] ?? '';
$result = 1; //Default fail
if($categoryOrder > 0)
{
    $categoryOrder--; //Category order is zero based in dbso decrement the user-readable version
}

function cleanupCategoryOrder($conn)
{
    $sql = "SET @row_number = 0;
    UPDATE hpCategory c
    INNER JOIN (
        SELECT
            (@row_Number := @row_number + 1) - 1 as newCategoryOrder,
            categoryID
        FROM hpCategory
        ORDER BY categoryOrder ASC, categoryID ASC
    ) _c  ON _c.categoryID = c.categoryID
    SET
            c.categoryOrder = _c.newCategoryOrder
    ;";
    try
    {
        $stmnt = $conn->prepare($sql)->execute();
    }
    catch(PDOException $e)
    {
        throw $e;
    }
}

switch($mode)
{
    case "edit":
        if($categoryID != 0)
        {
            $hpGateway = $container->get(HousePointsGateway::Class);
            $criteria = $hpGateway->newQueryCriteria()
                ->filterBy('categoryID',$categoryID)
                ->sortBy('categoryOrder','ASC');
            $categories = $hpGateway->queryCategories($criteria,false,false)->toArray();
            switch(sizeof($categories))
            {
                case 0: $result = 3; break;
                case 1: 
                    switch(strtolower($categoryOrder))
                    {
                        case "top":
                            //Set the order to 0 then increment the others
                            $categoryOrder = 0;
                            $sql = "UPDATE hpCategory SET categoryOrder = categoryOrder + 1;";
                            try{
                                $connection2->prepare($sql)->execute();
                            }
                            catch(PDOException $e)
                            {
                                throw $e;
                            }
                            break;
                        
                        case "bottom":
                            $highestCatOrdinal = $hpGateway->queryUsedCategoryOrders('DESC')->toArray()[0]['value'];
                            $categoryOrder = $highestCatOrdinal + 1; //The ordinal becomes the next highest
                            break;

                        default:
                            cleanupCategoryOrder($connection2);
                            $cats = array_map(function($elem) {
                                return $elem['value'];
                            },$hpGateway->queryUsedCategoryOrders('ASC')->toArray());

                            //Must make space for the new ordinal, otherwise leave the others be
                            if(array_search($categoryOrder,$cats) != null)
                            {
                                echo "Something exists in this slot, moving up to make space";
                                //When the next category ordinal is one more than the new ordinal 
                                $sql = "UPDATE hpCategory SET categoryOrder = categoryOrder + 1 WHERE categoryOrder >= :categoryOrder";
                                try{
                                    $stmnt = $connection2->prepare($sql);
                                    $stmnt->execute(['categoryOrder' => $categoryOrder]);
                                }
                                catch(PDOException $e)
                                {
                                    throw $e;
                                }
                            }
                            break;
                    }
                    $sql = "
                        UPDATE hpCategory
                        SET
                            categoryName = :categoryName,
                            categoryType = :categoryType,
                            categoryPresets = :categoryPresets,
                            categoryOrder = :categoryOrder
                        WHERE
                            categoryID = :categoryID;";
                    $sqlresult = null;
                    try{
                        $stmnt = $connection2->prepare($sql);
                        $sqlresult = $stmnt->execute(array(
                            'categoryID' => $categoryID,
                            'categoryName' => $categoryName,
                            'categoryType' => $categoryType,
                            'categoryPresets' => $categoryPresets,
                            'categoryOrder' => $categoryOrder
                        ));
                    }
                    catch(PDOException $e)
                    {
                        throw $e;
                    }
                    
                    switch($sqlresult)
                    {
                        case true: $result = 0; break; //fine
                        case false: $result = 1; break; //error, couldn't find category
                        default: $result = 1; break; //error
                    }
                    cleanupCategoryOrder($connection2); //Cleanup again just in case this has caused any weird gaps, particularly with the nudging
                    break;
                default: $result = 4; break;
            }
        }
        else
        {
            $result = 2;
        }
        break;

    case "add":
        cleanupCategoryOrder($connection2);
        if($categoryType != "House" && $categoryType != "Student") $result = 2;
        $sql = "
            INSERT INTO hpCategory
            SET
                categoryName = :categoryName,
                categoryType = :categoryType,
                categoryPresets = :categoryPresets
            ";
        try
        {
            $stmnt = $connection2->prepare($sql);
            $stmnt->execute(array(
                "categoryName" => $categoryName,
                "categoryType" => $categoryType,
                "categoryPresets" => $categoryPresets
            ));
            $result = 0;
        }
        catch(PDOException $e)
        {
            $result = 2;
        }
        break;

    case "delete":
        $sql = 'DELETE FROM hpCategory WHERE categoryID = :categoryID';
        $stmnt = $connection2->prepare($sql);
        $data = null;
        try
        {
            $data = $stmnt->execute(array(
                'categoryID' => $categoryID
            ));
        }
        catch(PDOException $e)
        {
            $result = 2;
        }
        cleanupCategoryOrder($connection2);
        break;

    default:
        $result = 3;
        break;
}

switch($result)
{
    case 0: $resultTxt = "Success"; break;
    case 1: $resultTxt = "Error"; break;
    case 2: $resultTxt = "A database error occurred"; break;
    case 3: $resultTxt = "The provided category couldn't be found";break;
    case 4: $resultTxt = "Duplicate categories exist"; break; //Duplicate category IDs, should be covered by the DB PK
    default: $resultTxt = "An unknown error occurred (" . $result . ")"; break;
}
echo "Result " . $result;
//header('Location: ' . $returnTo . '&statusCode=' . $result . '&statusText=' . $resultTxt);
?>