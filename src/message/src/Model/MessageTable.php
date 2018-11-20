<?php

namespace Message\Model;

use Rbac\Role\Administrator;
use Rbac\Role\Tutor;
use User\Model\UserMetaTable;
use User\Model\UserOnlineTable;
use User\Model\UserTableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Permissions\Rbac\Rbac;

class MessageTable
{
    /**
     * @var MessageTableGateway
     */
    private $tableGateway;

    /**
     * @var UserMetaTable
     */
    private $userMetaTable;

    /**
     * @var UserOnlineTable
     */
    private $userOnlineTable;

    /** @var UserTableGateway */
    private $userTableGateway;

    public function __construct(MessageTableGateway $tableGateway, UserOnlineTable $userOnlineTable,
                                UserMetaTable $userMetaTable, UserTableGateway $userTableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->userOnlineTable = $userOnlineTable;
        $this->userMetaTable = $userMetaTable;
        $this->userTableGateway = $userTableGateway;
    }

    public function fetchReceived(int $receiver, string $search)
    {
        $statement = $this->tableGateway->getAdapter()->query("
            SELECT
                um_first_name.value  as first_name,
                um_last_name.value as last_name,
                uo.user_id as status,
                m.*
            FROM `message` m
            LEFT JOIN `user_meta` um_first_name ON um_first_name.user_id = m.sender AND um_first_name.name = 'first_name'
            LEFT JOIN `user_meta` um_last_name ON um_last_name.user_id = m.sender AND um_last_name.name = 'last_name'
            LEFT JOIN `user_online` uo ON uo.user_id = m.sender
            WHERE m.receiver = '$receiver' AND m.text LIKE '%$search%'
            ORDER BY m.id DESC
        ");

        $resultSet = $this->tableGateway->getResultSetPrototype();
        return $resultSet->initialize($statement->execute());
    }

    public function fetchSent(int $sender, string $search)
    {
        $statement = $this->tableGateway->getAdapter()->query("
            SELECT
                um_first_name.value  as first_name,
                um_last_name.value as last_name,
                uo.user_id as status,
                m.*
            FROM `message` m
            LEFT JOIN `user_meta` um_first_name ON um_first_name.user_id = m.receiver AND um_first_name.name = 'first_name'
            LEFT JOIN `user_meta` um_last_name ON um_last_name.user_id = m.receiver AND um_last_name.name = 'last_name'
            LEFT JOIN `user_online` uo ON uo.user_id = m.receiver
            WHERE m.sender = '$sender' AND m.text LIKE '%$search%'
            ORDER BY m.id DESC
        ");

        $resultSet = $this->tableGateway->getResultSetPrototype();
        return $resultSet->initialize($statement->execute());
    }

    public function countUnread(int $receiver)
    {
        $select = $this->tableGateway->getSql()->select()
            ->columns(['total' => new Expression('COUNT(*)')])
            ->where([
                'receiver = ?' => $receiver,
                'viewed = ?' => 0,
                'hide_to_receiver = ?' => 0
            ]);
        $statement = $this->tableGateway->getSql()->prepareStatementForSqlObject($select);
        $data = $statement->execute()->current();
        return intval($data['total']);
    }

    private function countUnreadCoversation(int $receiver, array $messageIds)
    {
        $where = new Where();
        $where->equalTo('viewed', 0);
        $where->equalTo('receiver', $receiver);
        $where->equalTo('hide_to_receiver', 0);
        $where->in('id', $messageIds);

        $select = $this->tableGateway->getSql()->select()
            ->columns(['total' => new Expression('COUNT(*)')])
            ->where($where);

        $statement = $this->tableGateway->getSql()->prepareStatementForSqlObject($select);
        $data = $statement->execute()->current();
        return intval($data['total']);
    }
    public function viewed($id, $receiver)
    {
        return $this->tableGateway->update(['viewed' => 1], ['id' => $id, 'receiver' => $receiver]);
    }

    public function delete($currentUserId, $userId)
    {
        $this->tableGateway->update(['hide_to_receiver' => 1], ['receiver' => $currentUserId, 'sender' => $userId]);
        $this->tableGateway->update(['hide_to_sender' => 1], ['sender' => $currentUserId, 'receiver' => $userId]);
        $this->tableGateway->update(['viewed' => 1], ['receiver' => $currentUserId, 'sender' => $userId]);
    }

    public function insert(array $input)
    {
        $this->tableGateway->insert([
            'sender' => $input['sender'],
            'receiver' => $input['receiver'],
            'text' => $input['text'],
            'created_at' => time()
        ]);

        return $this->tableGateway->lastInsertValue;
    }

    /**
     * @param $text
     * @param \User\Model\User $owner
     * @param int $page
     * @return array
     */
    public function getContacts($text, $owner, $page)
    {
        $limit = 10;
        $start = ($page - 1) * $limit;

        $where = $this->constructContactAndMessageClause($text, $owner);

        $selectcount = $this->userTableGateway->getSql()
            ->select()
            ->columns(array('total' => new Expression('COUNT(DISTINCT user.id)')))
            ->join('message', "message.receiver = user.id OR message.sender = user.id", [], Select::JOIN_INNER)
            ->join('user_online', "user_online.user_id = user.id", [], Select::JOIN_LEFT)
            ->where($where);
        $total = $this->userTableGateway->getSql()->prepareStatementForSqlObject($selectcount)->execute()->current();
        $pages = ceil(intval($total['total']) / $limit);

        $sql = $this->userTableGateway->getSql()
            ->select()
            ->columns(array('user_id' => new Expression('DISTINCT user.id')))
            ->join('message', "message.receiver = user.id OR message.sender = user.id", [], Select::JOIN_INNER)
            ->join('user_online', "user_online.user_id = user.id", ['is_online' => 'id'], Select::JOIN_LEFT)
            ->where($where)
            ->limit($limit)->offset($start);

        $result = [];
        $statement = $this->userTableGateway->getSql()->prepareStatementForSqlObject($sql);
        $contacts = $statement->execute();
        foreach ($contacts as $contact) {
            $firstname = $this->userMetaTable->getMetaByName($contact['user_id'], 'first_name')->toArray();
            $lastname = $this->userMetaTable->getMetaByName($contact['user_id'], 'last_name')->toArray();

            $result[] = [
                "id" => $contact['user_id'],
                "first_name" => empty($firstname) ? '' : $firstname[0]['value'],
                "last_name" => empty($lastname) ? '' : $lastname[0]['value'],
                "is_online" => $contact['is_online'] != null,
                "link" => in_array($owner->getRole(), [Tutor::class, Administrator::class]) ? "/student/user/{$contact['user_id']}" : ""
            ];
        }

        return [
            'page' => $page,
            'pages' => $pages,
            'recordsTotal' => $total['total'],
            'recordsFiltered' => $total['total'],
            'data' => $result
        ];
    }

    /**
     * @param $text
     * @param \User\Model\User $owner
     * @param int $page
     * @return array
     */
    public function getMessages($text, $owner, $page)
    {
        $limit = 10;
        $start = ($page - 1) * $limit;

        $where = $this->constructContactAndMessageClause($text, $owner);

        $selectcount = $this->userTableGateway->getSql()
            ->select()
            ->columns(array('total' => new Expression('COUNT(DISTINCT user.id)')))
            ->join('message', "message.receiver = user.id OR message.sender = user.id", [], Select::JOIN_INNER)
            ->join('user_online', "user_online.user_id = user.id", [], Select::JOIN_LEFT)
            ->where($where);
        $total = $this->userTableGateway->getSql()->prepareStatementForSqlObject($selectcount)->execute()->current();
        $pages = ceil(intval($total['total']) / $limit);

        $sql = $this->userTableGateway->getSql()
            ->select()
            ->columns(array(
                'user_id' => new Expression('DISTINCT user.id'),
                'message_ids' => new Expression('GROUP_CONCAT(message.id ORDER BY message.created_at DESC)'),
                'message_text' => new Expression("SUBSTRING_INDEX(GROUP_CONCAT(message.text ORDER BY message.created_at DESC SEPARATOR '|||||||'), '|||||||', 1)"),
                'is_online' => new Expression('MAX(user_online.user_id)'),
                'created_date' => new Expression('MAX(message.created_at)')
            ))
            ->join('message', "message.receiver = user.id OR message.sender = user.id", [], Select::JOIN_INNER)
            ->join('user_online', "user_online.user_id = user.id", [], Select::JOIN_LEFT)
            ->where($where)
            ->group('user.id')
            ->order(['created_date' => 'DESC'])
            ->limit($limit)->offset($start);

        $result = [];
        $statement = $this->userTableGateway->getSql()->prepareStatementForSqlObject($sql);
        $messages = $statement->execute();
        foreach ($messages as $item) {
            $firstname = $this->userMetaTable->getMetaByName($item['user_id'], 'first_name')->toArray();
            $lastname = $this->userMetaTable->getMetaByName($item['user_id'], 'last_name')->toArray();

            $text = json_decode($item['message_text'], true);
            $result[] = [
                "user_id" => intval($item['user_id']),
                "first_name" => empty($firstname) ? '' : $firstname[0]['value'],
                "last_name" => empty($lastname) ? '' : $lastname[0]['value'],
                "message_ids" => $item['message_ids'],
                "message_text" => !empty($text['Message']) ? $this->truncate(strip_tags($text['Message'])) : '',
                "not_viewed" => $this->countUnreadCoversation($owner->getId(), explode(',', $item['message_ids'])),
                "is_online" => $item['is_online'] != null,
                "created_date" => date('M d, Y H:i', $item['created_date']),
                "link" => in_array($owner->getRole(), [Tutor::class, Administrator::class]) ? "/student/user/{$item['user_id']}" : ""
            ];
        }

        return [
            'page' => $page,
            'pages' => $pages,
            'recordsTotal' => $total['total'],
            'recordsFiltered' => $total['total'],
            'data' => $result
        ];
    }

    private function truncate($text, $chars = 100) {
        if (strlen($text) <= $chars) {
            return $text;
        }
        $text = $text." ";
        $text = substr($text,0,$chars);
        $text = substr($text,0,strrpos($text,' '));
        $text = $text."...";
        return $text;
    }

    /**
     * @param $text
     * @param \User\Model\User $owner
     * @return Where
     */
    public function constructContactAndMessageClause($text, $owner): Where
    {
        $where = new Where();
        $where->notEqualTo('user.id', $owner->getId());

        $orwhere1 = new Where();
        $orwhere1->equalTo('message.receiver', $owner->getId());
        $orwhere1->notEqualTo('message.sender', $owner->getId());
        $orwhere1->equalTo('message.hide_to_receiver', 0);

        $orwhere2 = new Where();
        $orwhere2->equalTo('message.sender', $owner->getId());
        $orwhere2->notEqualTo('message.receiver', $owner->getId());
        $orwhere2->equalTo('message.hide_to_sender', 0);

        $orexpr = new Where();
        $orexpr->addPredicates([$orwhere1, $orwhere2], PredicateSet::OP_OR);
        $where->addPredicate($orexpr, PredicateSet::OP_AND);

        if (!empty($text)) {
            $metawhere = new Where();
            $metawhere->in("name", ['first_name', 'last_name']);
            $metawhere->like("value", "%$text%");
            $meta = $this->userMetaTable->select($metawhere)->toArray();
            $userids = [-1];
            foreach ($meta as $item) {
                if ($item['user_id'] == $owner->getId())
                    continue;

                $userids[] = $item['user_id'];
            }

            $orwhere1->in("message.sender", $userids);
            $orwhere2->in("message.receiver", $userids);
        }
        return $where;
    }

    /**
     * @param array $messageIds
     * @param \User\Model\User $owner
     * @return array
     */
    public function getMessageDatails($messageIds, $owner)
    {
        $where = new Where();
        $where->in('message.id', $messageIds);

        $orwhere1 = new Where();
        $orwhere1->equalTo('message.receiver', $owner->getId());
        $orwhere1->notEqualTo('message.sender', $owner->getId());
        $orwhere1->equalTo('message.hide_to_receiver', 0);

        $orwhere2 = new Where();
        $orwhere2->equalTo('message.sender', $owner->getId());
        $orwhere2->notEqualTo('message.receiver', $owner->getId());
        $orwhere2->equalTo('message.hide_to_sender', 0);

        $orexpr = new Where();
        $orexpr->addPredicates([$orwhere1, $orwhere2], PredicateSet::OP_OR);
        $where->addPredicate($orexpr, PredicateSet::OP_AND);

        $sql = $this->tableGateway->getSql()
            ->select()
            ->where($where)
            ->order(['created_at' => 'DESC']);

        $result = [];
        $statement = $this->tableGateway->getSql()->prepareStatementForSqlObject($sql);
        $messages = $statement->execute();
        foreach ($messages as $item) {
            $senderFirstname = $this->userMetaTable->getMetaByName($item['sender'], 'first_name')->toArray();
            $senderLastname = $this->userMetaTable->getMetaByName($item['sender'], 'last_name')->toArray();
            $receiverFirstname = $this->userMetaTable->getMetaByName($item['receiver'], 'first_name')->toArray();
            $receiverLastname = $this->userMetaTable->getMetaByName($item['receiver'], 'last_name')->toArray();

            $result[] = [
                "id" => intval($item['id']),
                "sender_first_name" => empty($senderFirstname) ? '' : $senderFirstname[0]['value'],
                "sender_last_name" => empty($senderLastname) ? '' : $senderLastname[0]['value'],
                "receiver_first_name" => empty($receiverFirstname) ? '' : $receiverFirstname[0]['value'],
                "receiver_last_name" => empty($receiverLastname) ? '' : $receiverLastname[0]['value'],
                "message_text" => json_decode($item['text'], true),
                "viewed" => $item['receiver'] == $owner->getId() ? boolval($item['viewed']) : true,
                "created_date" => date('M d, Y H:i', $item['created_at']),
            ];
        }

        return [
            'data' => $result
        ];
    }
}