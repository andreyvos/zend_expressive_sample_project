<?php

namespace Customer\Model;

class CustomerTable
{
    /**
     * @var CustomerTableGateway
     */
    private $tableGateway;

    public function __construct(
        CustomerTableGateway $tableGateway
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
                'type' => $data['type'],
                'email' => $data['email'],
                'available_credits' => $data['available_credits'],
                'credit_type' => $data['credit_type'],
                'pricing_type' => $data['pricing_type'],
                'payment_type' => $data['payment_type']
            ], ['id' => $data['id']]);
            return $data['id'];
        }

        $this->tableGateway->insert([
            'type' => $data['type'],
            'email' => $data['email'],
            'available_credits' => $data['available_credits'],
            'credit_type' => $data['credit_type'],
            'pricing_type' => $data['pricing_type'],
            'payment_type' => $data['payment_type']
        ]);

        return $this->tableGateway->lastInsertValue;
    }

    public function delete(int $id): void
    {
        $this->tableGateway->delete(['id' => $id]);
    }

    public function generateToken($customerId)
    {
        return sha1(md5($customerId . '_lms_salt_' . time()));
    }

    public function updateToken($customerId, $token)
    {
        $token_time = time();
        if (empty($token)) {
            $token_time = '';
        }
        $this->tableGateway->update(['token' => $token, 'token_time' => $token_time], ['id' => $customerId]);
    }
}
