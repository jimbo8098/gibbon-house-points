<?php

namespace Gibbon\Module\HousePoints\Domain;
use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class HousePointsDomain extends QueryableGateway
{
    use TableAware;

    private $searchableColumns = [];

    public function queryCatergories(QueryCriteria $criteria)
    {
        $this->searchableColumns = ['h.categoryName'];
        $query = $this
            ->newQuery()
            ->from('hpCategory as h')
            ->cols([
                'h.catergoryID as categoryID',
                'h.categoryName as categoryName',
                'h.categoryOrder as categoryOrder',
                'h.categoryType as categoryType',
                'h.categoryPresets as categoryPresets'
            ]);
        
        $criteria->addFilterRules([
            'categoryType' => function($query,$needle)
            {
                return $query
                    ->where('h.categoryType = :categoryType')
                    ->bindValue('categoryType',$needle);
                
            }
        ]);

        $this->runQuery($query,$criteria);
    }

    public function queryOverallPoints(QueryCriteria $criteria)
    {
        $this->searchableColumns = ['h.name'];

        $query = $this
            ->newQuery()
            ->from('gibbonHouse as h')
            ->cols([
                'h.logo as houseLogo',
                'h.name as houseName',
                'COALESCE(pointStudent.total + pointHouse.total, pointStudent.total, pointHouse.total, 0) AS total'
            ]);

        $criteria->addFilterRules([
            'gibbonHouseId' => function($query,$needle)
            {
                return $query
                    ->where('h.gibbonHouseID = :gibbonHouseId')
                    ->bindValue('gibbonHouseId',$needle);
            }
        ]);
            
    }

    public function queryStudents(QueryCriteria $criteria)
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
                'rg.rollGroupID as rollGroupID',
                'rg.name as rollGroupName',
                'h.gibbonHouseID as houseID',
                'h.name as house',
                's.categoryID as categoryID',
                's.points as points',
                's.reason as reason',
                's.yearID as yearID',
                's.awardedDate as awardedDate',
                's.awardedBy as awardedBy'
            ])
            ->innerJoin('gibbonPerson as p_s')
            ->innerJoin('gibbonHouse as h')
            ->innerJoin('gibbonRollGroup as rg');

        $criteria->addFilterRules([
            'studentId' => function($query,$needle)
            {
                return $query
                    ->where('s.studentID = :studentId')
                    ->bindValue('studentId',$needle);
            },
            'rollGroupID' => function($query,$needle)
            {
                return $query
                    ->where('rg.rollGroupID = :rollGroupID')
                    ->bindValue('rollGroupID',$needle);
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

        $this->runQuery($query,$criteria);
    }


}

?>