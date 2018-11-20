<?php

namespace Recruit\Model;

class RecruitTable
{
    /**
     * @var RecruitTableGateway
     */
    private $tableGateway;

    public function __construct(
        RecruitTableGateway $tableGateway
    )
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchById(int $id)
    {
        return $this->tableGateway->select(['id' => $id])->current();
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $this->tableGateway->update([
                'name' => $data['name'],
                'email' => $data['email'],
				'courses' => $data['courses'],
				'credit_courses' => $data['credit_courses'],
            ], ['id' => $data['id']]);
            return $data['id'];
        }

        $this->tableGateway->insert([
                'name' => $data['name'],
                'email' => $data['email'],
				'courses' => $data['courses'],
				'credit_courses' => $data['credit_courses'],
				'created_at' => time()
        ]);

        return $this->tableGateway->lastInsertValue;
    }

    public function delete(int $id): void
    {
        $this->tableGateway->delete(['id' => $id]);
    }

}
