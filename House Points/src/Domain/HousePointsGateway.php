<?php

namespace Gibbon\Module\HousePoints\Domain;
use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class HousePointsGateway extends QueryableGateway
{
    use TableAware;

    private $searchableColumns = [];
    private static $tableName = 'hpCategory';

    private function LeftJoinedSubCatQuery($isHumanReadable)
    {
        $this->searchableColumns = ['c.categoryName'];
        $query = $this
            ->newQuery()
            ->from('hpCategory as c')
            ->join('LEFT','hpSubCategory as sc','c.categoryID = sc.categoryID')
            ->cols([
                'c.categoryType as categoryType',
                'c.categoryID as categoryID',
                'c.categoryName as categoryName',
                'IFNULL(GROUP_CONCAT(concat(sc.name,\': \',sc.value)),\'\') as concatenatedSubCategory'

            ]);


        if($isHumanReadable)
        {
            $query->cols([
                'c.categoryOrder + 1 as categoryOrder'
            ]);
        }
        else
        {
            $query->cols([
                'c.categoryOrder as categoryOrder'
            ]);
        }
        return $query;
    }

    private function LeftJoinedSubCatCriteria(QueryCriteria $criteria)
    {
        $criteria->addFilterRules([
            'categoryType' => function($query,$needle)
            {
                return $query
                    ->where('c.categoryType = :categoryType')
                    ->bindValue('categoryType',$needle);
                
            },
            'categoryID' => function($query,$needle)
            {
                return $query
                    ->where('c.categoryID = :categoryID')
                    ->bindValue('categoryID',$needle);
            }
        ]);
        return $criteria;
    }

    public function queryGroupedSubCategories(QueryCriteria $criteria,$isHumanReadable = false)
    {
        $query = $this->LeftJoinedSubCatQuery($isHumanReadable)
            ->groupBy([
                'c.categoryID',
                'c.categoryType',
                'c.categoryName'
            ]);

        $criteria = $this->LeftJoinedSubCatCriteria($criteria);

        return $this->runQuery($query,$criteria);
    }

    public function queryLeftJoinedSubCategories(QueryCriteria $criteria)
    {
        $query = $this->LeftJoinedSubCatQuery(false)->cols([
            'sc.value as subCategoryValue',
            'sc.subCategoryID as subCategoryID',
            'sc.name as subCategoryName'
        ]);
        $criteria = $this->LeftJoinedSubCatCriteria($criteria);
        return $this->runQuery($query,$criteria);
    }

    public function querySubCategories(QueryCriteria $criteria)
    {
        $query = $this
            ->newQuery()
            ->from('hpCategory as c')
            ->innerJoin('hpSubCategory as sc','sc.categoryID = c.categoryID')
            ->cols([
                'sc.name as subCategoryName',
                'sc.value as subCategoryValue',
                'sc.subCategoryID as subCategoryID',
                'sc.categoryID as categoryID',
                'CONCAT(sc.name,\' (\',sc.value,\') \') as subCategoryCombinedName',
                'CONCAT(c.categoryName,\' - \',sc.name,\' (\',sc.value,\') \') as categoryAndSubCategoryNames',
                'c.categoryName as categoryName',
                'c.categoryType as categoryType'
            ]);
            

        $criteria->addFilterRules([
            'categoryID' => function($query,$needle)
            {
                return $query
                    ->where('sc.categoryID = :categoryID')
                    ->bindValue('categoryID',$needle);
            },
            'subCategoryID'=> function($query,$needle)
            {
                return $query
                    ->where('sc.subCategoryID = :subCategoryID')
                    ->bindValue('subCategoryID',$needle);
            },
            'subCategoryName' => function($query,$needle)
            {
                return $query
                    ->where('sc.subQueryName = :subQueryName')
                    ->bindValue('subQueryName', $needle);
            },
            'categoryType' => function($query,$needle)
            {
                return $query
                    ->where('c.categoryType = :categoryType')
                    ->bindValue('categoryType',$needle);
            }
        ]);

        return $this->runQuery($query,$criteria);
    }

    public function queryUsedCategoryOrders($order)
    {
        $categoryOrderSelect = $this
            ->newQuery()
            ->from('hpCategory as c')
            ->cols([
                'c.categoryOrder as value',
                'c.categoryOrder as name'
            ]);
        
        $criteria = $this->newQueryCriteria()->sortBy('c.categoryOrder',$order);
        return $this->runQuery($categoryOrderSelect,$criteria);
    }

    public function queryOverallPoints(QueryCriteria $criteria,$yearId)
    {
        $this->searchableColumns = ['h.name'];

        $pointStudentSelect = $this
            ->newQuery()
            ->from('hpPointStudent as ps')
            ->cols([
                'gibbonPerson.gibbonHouseID AS houseID',
                'SUM(ps.points) AS total'
            ])
            ->innerJoin('gibbonPerson','ps.studentID = gibbonPerson.gibbonPersonID')
            ->where('ps.yearID = :yearId')
            ->groupBy(['gibbonPerson.gibbonHouseID'])
            ->bindValue('yearID',$yearId)
            ->calcFoundRows(false);

        $pointHouseSelect = $this
            ->newQuery()
            ->from('hpPointHouse as ph')
            ->cols([
                'ph.houseID',
                'SUM(ph.points) AS total'
            ])
            ->where('ph.yearID = :yearId')
            ->groupBy(['ph.houseID'])
            ->bindValue('yearId',$yearId)
            ->calcFoundRows(false);

        $query = $this
            ->newQuery()
            ->from('gibbonHouse as h')
            ->cols([
                'h.gibbonHouseID as gibbonHouseID',
                'h.logo as houseLogo',
                'h.name as houseName',
                'COALESCE(pointStudent.total + pointHouse.total, pointStudent.total, pointHouse.total, 0) AS total'
            ])
            ->joinSubSelect('LEFT',$pointStudentSelect,'pointStudent','pointStudent.houseID = h.gibbonHouseID')
            ->joinSubSelect('LEFT',$pointHouseSelect,'pointHouse','pointHouse.houseID = h.gibbonHouseID')
            ->bindValue('yearID',$yearId)
            ->calcFoundRows(false);

        $criteria->addFilterRules([
            'gibbonHouseId' => function($query,$needle)
            {
                return $query
                    ->where('h.gibbonHouseID = :gibbonHouseId')
                    ->bindValue('gibbonHouseId',$needle);
            }
        ]);
        return $this->runQuery($query,$criteria);
    }

    public function queryStudentPoints(QueryCriteria $criteria, $schoolYearID)
    {

        $this->searchableColumns = [
            'p_s.firstname',
            'p_s.surname',
            'p_s.officialName',
            'p_s.preferredName',
            'rg.name',
            'h.name'
        ];

        $query = $this 
            ->newQuery()
            ->from('hpPointStudent as s')
            ->cols([
                's.hpID as hpID',
                's.studentID as studentID',
                'p_s.firstname as firstname',
                'p_s.surname as surname',
                'p_s.officialName as officialName',
                'p_s.preferredName as preferredName',
                'rg.gibbonRollGroupID as rollGroupID',
                'rg.name as rollGroupName',
                'h.gibbonHouseID as houseID',
                'h.name as house',
                's.subCategoryID as categoryID',
                'CONCAT(c.categoryName,\' - \',sc.name) as categoryName',
                's.points as points',
                's.reason as reason',
                's.yearID as yearID',
                's.awardedDate as awardedDate',
                's.awardedBy as awardedBy',
                'p_t.title as teacherTitle',
                'p_t.firstname as teacherFirstname',
                'p_t.surname as teacherSurname',
            ])
            ->innerJoin('gibbonPerson as p_s','p_s.gibbonPersonID = s.studentID')
            ->innerJoin('gibbonPerson as p_t','p_t.gibbonPersonID = s.awardedBy')
            ->innerJoin('gibbonHouse as h','h.gibbonHouseID = p_s.gibbonHouseID')
            ->innerJoin('gibbonStudentEnrolment as se','se.gibbonPersonID = s.studentID')
            ->innerJoin('gibbonRollGroup as rg','rg.gibbonRollGroupID = se.gibbonRollGroupID')
            ->innerJoin('hpSubCategory as sc','sc.subCategoryID = s.subCategoryID')
            ->innerJoin('hpCategory as c','c.categoryID = sc.categoryID')
            ->where('se.gibbonSchoolYearID = :schoolYearId')
            ->bindValue('schoolYearId',$schoolYearID);

        $criteria->addFilterRules([
            /*
                The individual points table uses a non-zeropadded studentID whereas the gibbonPersonID is.
            */
            'studentID' => function($query,$needle)
            {
                return $query
                    ->where('s.studentID = :studentID')
                    ->bindValue('studentID',$needle);
            },
            'rollGroupID' => function($query,$needle)
            {
                if($needle != '')
                {
                    return $query
                        ->where('rg.gibbonRollGroupID = :rollGroupID')
                        ->bindValue('rollGroupID',$needle);
                }
            },
            'gibbonSchoolYearId' => function($query,$needle)
            {
                return $query
                    ->where('rg.gibbonSchoolYearID = :gibbonSchoolYearID')
                    ->bindValue('gibbonSchoolYearID',$needle);
            },
            'houseId' => function($query,$needle)
            {
                return $query
                    ->where('h.gibbonHouseId = :houseId')
                    ->bindValue('houseId',$needle);
            },
            /*
                House points year ID is slightly different from the gibbonYearId. 
                Whilst it's the same integer value, the gibbon version is zeropadded 
                where the one in hpPointStudent is not.
            */
            'housePointsYearId' => function($query,$needle)
            {
                return $query
                    ->where('s.yearId = :housePointsYearId')
                    ->bindValue('housePointsYearId',$needle);
            }
        ]);

        return $this->runQuery($query,$criteria);
    }


}

?>