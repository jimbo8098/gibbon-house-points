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

    /**
     * Get a list of categories
     * 
     * @param QueryCriteria The criteria for the query
     * @param bool If true, outputs for use with fromArray in MultipleInputTrait
     *
     * @return DataSet 
     */
    public function queryCategories(QueryCriteria $criteria,$select = false)
    {
        $this->searchableColumns = ['h.categoryName'];
        $query = $this
            ->newQuery()
            ->from('hpCategory as h')
            ->cols([
                'h.categoryOrder as categoryOrder',
                'h.categoryType as categoryType',
                'h.categoryPresets as categoryPresets'
            ]);

        if($select == false)
        {
            $query->cols([
                'h.categoryID as categoryID',
                'h.categoryName as categoryName'
            ]);
        }
        else
        {
            $query->cols([
                'h.categoryID as value',
                'h.categoryName as name',
            ]);
        }
        
        $criteria->addFilterRules([
            'categoryType' => function($query,$needle)
            {
                return $query
                    ->where('h.categoryType = :categoryType')
                    ->bindValue('categoryType',$needle);
                
            }
        ]);

        return $this->runQuery($query,$criteria);
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

    public function queryStudents(QueryCriteria $criteria, $schoolYearID)
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
                's.categoryID as categoryID',
                'c.categoryName as categoryName',
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
            ->innerJoin('hpCategory as c','c.categoryID = s.categoryID')
            ->where('se.gibbonSchoolYearID = :schoolYearId')
            ->bindValue('schoolYearId',$schoolYearID);

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

        return $this->runQuery($query,$criteria);
    }


}

?>