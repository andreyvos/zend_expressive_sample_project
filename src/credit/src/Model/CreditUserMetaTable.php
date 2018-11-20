<?php

namespace Credit\Model;

use Zend\Db\ResultSet\AbstractResultSet;

class CreditUserMetaTable
{
    /**
     * @var CreditUserMetaTableGateway
     */
    private $creditUserMetaTableGateway;

    public function __construct(CreditUserMetaTableGateway $creditUserMetaTableGateway)
    {
        $this->creditUserMetaTableGateway = $creditUserMetaTableGateway;
    }

    public function fetchByUserId(int $userId): ?AbstractResultSet
    {
        return $this->creditUserMetaTableGateway->select(['user_id' => $userId]);
    }

    public function add(int $userId, string $name, string $value): void
    {
        $this->creditUserMetaTableGateway->insert([
            'user_id' => $userId,
            'name' => $name,
            'value' => $value
        ]);
    }

    public function update(int $userId, string $name, string $value): int
    {
        return $this->creditUserMetaTableGateway->update([
            'value' => $value
        ], [
            'user_id' => $userId,
            'name' => $name
        ]);
    }

    public function getMetaByName(int $userId, string $name): ?AbstractResultSet
    {
        return $this->creditUserMetaTableGateway->select(['user_id' => $userId, 'name' => $name]);
    }

    public function getTableName()
    {
        return $this->creditUserMetaTableGateway->getTable();
    }

    public function delete(int $userId): void
    {
        $this->creditUserMetaTableGateway->delete(['user_id' => $userId]);
    }
}
