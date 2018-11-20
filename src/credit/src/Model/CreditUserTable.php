<?php

namespace Credit\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Credit\Model\RecruitTable;
use Credit\Model\CreditStudentTable;

class CreditUserTable
{
    /**
     * @var CreditUserTableGateway
     */
    private $creditUserTableGateway;

    /**
     * @var CreditUserMetaTable
     */
    private $creditUserMetaTable;

    /**
     * @var RecruitTable
     */
	private $recruitTable;

	private $creditStudentTable;

    public function __construct(
		CreditUserTableGateway $creditUserTableGateway, 
		CreditUserMetaTable $creditUserMetaTable,
		RecruitTable $recruitTable,
		CreditStudentTable $creditStudentTable)
    {
        $this->creditUserTableGateway = $creditUserTableGateway;
        $this->creditUserMetaTable = $creditUserMetaTable;
		$this->recruitTable = $recruitTable;
		$this->creditStudentTable = $creditStudentTable;
    }

    /**
     * @param int $id
     *
     * @return array|\ArrayObject|User|null
     */
    public function oneById(int $id)
    {
        $resultSet = $this->creditUserTableGateway->getResultSetPrototype();

        $select = (new Select($this->creditUserTableGateway->getTable()))->where(['id' => $id]);
        $statement = $this->creditUserTableGateway->getSql()->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result->count()) {
            return null;
        }
        $resultSet->initialize([
            [
                $result->current(),
                $this->creditUserMetaTable->fetchByUserId($id),
				$this->recruitTable->fetchByCreditUserId($id),
				$this->creditStudentTable->fetchByCreditUserId($id)				
            ]
        ]);

        return $resultSet->current();
    }

    public function byRole(string $role)
    {
        return $this->creditUserTableGateway->select(['role' => $role]);
    }

    public function byIdentity(string $identity)
    {
        return $this->creditUserTableGateway->select(['identity' => $identity]);
    }

    public function byPlainpin(string $pin)
    {
        return $this->creditUserTableGateway->select(['plainpin' => $pin]);
    }

    public function byToken(string $token)
    {
        $where = new Where();
        $where->equalTo('token', $token);
        $where->greaterThanOrEqualTo('token_time',time() - 7200);
        return $this->creditUserTableGateway->select($where);
    }

    public function byUsername(string $username)
    {
        return $this->creditUserTableGateway->select(['username' => $username]);
    }

    public function usingSearch(?string $role, array $pairs = [])
    {
        $where = new Where();

        if ($role) {
            $where->equalTo('role', $role);
        }
        foreach ($pairs as $identifier => $value) {
            $where
                ->nest()
                ->like($identifier, '%' . $value . '%')
                ->or
                ->like($this->creditUserMetaTable->getTableName() . '.value', '%' . $value . '%')
                ->unnest();
        }

        $resultSet = $this->creditUserTableGateway->getResultSetPrototype();
        $statement = $this->creditUserTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->creditUserTableGateway->getSql()
                    ->select()
                    ->join(
                        $this->creditUserMetaTable->getTableName(),
                        $this->creditUserTableGateway->getTable() . '.id = ' . $this->creditUserMetaTable->getTableName() . '.user_id',
                        [],
                        Select::JOIN_LEFT
                    )
                    ->where($where)
                    ->group($this->creditUserTableGateway->getTable(). '.id')
            );
        $userCollection = $statement->execute();
        foreach ($userCollection as $user) {
            $result[] = [
                $user,
                $this->creditUserMetaTable->fetchByUserId($user['id']),
                $this->courseUserTable->fetchByUserId($user['id'])
            ];
        }

        $resultSet->initialize($result ?? []);
        return $resultSet;
    }

    public function save(array $data): string
    {
        if (!empty($data['identity'])) {
            $data['identity'] = strtolower($data['identity']);
        }
        if (!empty($data['username'])) {
            $data['username'] = strtolower($data['username']);
        }
        if (! empty($data['identity']) && $this->duplicateIdentity($data['id'], $data['identity'])) {
            return 'duplicate_identity';
        }
        if (! empty($data['username']) && $this->duplicateUsername($data['id'], $data['username'])) {
            return 'duplicate_username';
        }
        if (! empty($data['pin']) && $this->duplicatePin($data['id'], $this->oldHashPassword($data['pin']))) {
            return 'duplicate_pin';
        }
        if (!$data['id']) {
            return $this->insert($data);
        }
        return $this->update($data['id'], $data);
    }

    public function duplicateIdentity($id, $identity)
    {
        $where = new Where();
        if ($id) {
            $where->notEqualTo('id', $id);
        }
        $where->EqualTo('identity', $identity);
        $statement = $this->creditUserTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->creditUserTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $userCollection = $statement->execute();
        if ($userCollection->count() > 0) {
            return true;
        }
        return false;
    }

    public function duplicateUsername($id, $username)
    {
        $where = new Where();
        if ($id) {
            $where->notEqualTo('id', $id);
        }
        $where->EqualTo('username', $username);
        $statement = $this->creditUserTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->creditUserTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $userCollection = $statement->execute();
        if ($userCollection->count() > 0) {
            return true;
        }
        return false;
    }

    public function duplicatePin($id, $pin)
    {
        $where = new Where();
        if ($id) {
            $where->notEqualTo('id', $id);
        }
        $where->EqualTo('pin', $pin);
        $statement = $this->creditUserTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->creditUserTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $userCollection = $statement->execute();
        if ($userCollection->count() > 0) {
            return true;
        }
        return false;
    }

    protected function insert(array $data): int
    {
        $this->creditUserTableGateway->insert([
            'role' => $data['role'],
            'username' => $data['username'],
            'identity' => $data['identity'],
            'password' => $this->hashPassword($data['password']),
            'pin' => $this->oldHashPassword($data['pin']),
            'plainpin' => $data['pin'],
            'created_at' => time()
        ]);

        $lastInsertValue = $this->creditUserTableGateway->lastInsertValue;

        foreach ($data['meta'] ?? [] as $name => $value) {
            $this->creditUserMetaTable->add($lastInsertValue, $name, $value);
        }

        return $lastInsertValue;
    }

    protected function update(int $userId, array $data): int
    {
        $updateData = [
            'username' => $data['username'],
            'identity' => $data['identity'],
        ];

        if (isset($data['password'])) {
            $updateData['password'] = $this->hashPassword($data['password']);
        }
        if (isset($data['role'])) {
            $updateData['role'] = $data['role'];
        }
        if (isset($data['pin'])) {
            $updateData['pin'] = $this->oldHashPassword($data['pin']);
            $updateData['plainpin'] = $data['pin'];
        }
        $affectedRows = $this->creditUserTableGateway->update($updateData, ['id' => $userId]);
        foreach ($data['meta'] ?? [] as $name => $value) {
            $oldMeta = $this->creditUserMetaTable->getMetaByName($userId, $name);
            if ($oldMeta->count()) {
                $updateMeta = $this->creditUserMetaTable->update($userId, $name, $value);
            } else {
                $updateMeta = $this->creditUserMetaTable->add($userId, $name, $value);
            }
            $affectedRows += $updateMeta;
        }

        return $affectedRows;
    }

    public function generateToken($userId)
    {
        return sha1(md5($userId . '_lms_salt_' . time()));
    }

    public function updateToken($userId, $token)
    {
        $token_time = time();
        if (empty($token)) {
            $token_time = '';
        }
        $this->creditUserTableGateway->update(['token' => $token, 'token_time' => $token_time], ['id' => $userId]);
    }

    public function updatePassword($userId, $password)
    {
        $this->creditUserTableGateway->update(['password' => $this->hashPassword($password)], ['id' => $userId]);
    }

    /**
     * @deprecated used to ensure backward compatible
     * @param $password
     * @return string
     */
    public function oldHashPassword($password)
    {
        return sha1(md5($password));
    }

    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function delete(int $id): void
    {
        $this->creditUserTableGateway->delete(['id' => $id]);
    }

	public function fetchAll($role='')
    {
        $select = $this->creditUserTableGateway->select();
		$user = array();
		foreach($select as $one) {
			if($role==$one->getRole() || $role == '') {
				$user[] = $this->oneById($one->getId());
			}
		}
		return $user;
    }
}