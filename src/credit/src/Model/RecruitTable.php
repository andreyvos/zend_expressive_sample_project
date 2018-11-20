<?php

namespace Credit\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Credit\Model\CreditUser;
use Credit\Model\CreditUserMetaTable;

class RecruitTable
{
    /**
     * @var RecruitTableGateway
     */
    private $recruitTableGateway;

	private $creditUserTable;

	private $creditUserMetaTable;

    public function __construct(
        RecruitTableGateway $recruitTableGateway,
		CreditUserMetaTable $creditUserMetaTable
    )
    {
        $this->recruitTableGateway = $recruitTableGateway;
		$this->creditUserMetaTable = $creditUserMetaTable;
    }

    public function oneById(int $id)
    {
        $resultSet = $this->recruitTableGateway->getResultSetPrototype();

        $select = (new Select($this->recruitTableGateway->getTable()))->where(['id' => $id]);
        $statement = $this->recruitTableGateway->getSql()->prepareStatementForSqlObject($select);
        $result = $statement->execute();

		$recruit = $this->fetchById($id);

        if (!$result->count()) {
            return null;
        }
        $resultSet->initialize([
            [
                $result->current(),
                $this->creditUserMetaTable->fetchByUserId($recruit->getId())		
            ]
        ]);

        return $resultSet->current();
    }

    public function fetchById(int $id)
    {
		return $this->recruitTableGateway->select(['id' => $id])->current();
    }

    public function fetchByCreditUserId(int $id)
    {
        return $this->recruitTableGateway->select(['credit_user_id' => $id])->current();
	
    }

    public function fetchAll()
    {
        return $this->recruitTableGateway->select();
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $this->recruitTableGateway->update([
				'courses' => $data['courses'],
				'credit_courses' => $data['credit_courses'],
            ], ['id' => $data['id']]);
            return $data['id'];
        }

        $this->recruitTableGateway->insert([
				'credit_user_id' => $data['credit_user_id'],
				'courses' => $data['courses'],
				'credit_courses' => $data['credit_courses'],
				'created_at' => time()
        ]);

        return $this->recruitTableGateway->lastInsertValue;
    }

    public function delete(int $id): void
    {
        $this->recruitTableGateway->delete(['id' => $id]);
    }

}
