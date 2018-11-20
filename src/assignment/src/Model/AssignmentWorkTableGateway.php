<?php

namespace Assignment\Model;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;

class AssignmentWorkTableGateway extends TableGateway
{
    public function __construct(AdapterInterface $adapter, $features = null, $resultSetPrototype = null, $sql = null)
    {
        parent::__construct('assignment_work', $adapter, $features, $resultSetPrototype, $sql);
    }

    public function sqlSelect()
    {
        return $this->getSql()
            ->select()
            ->columns([
                '*',
                new Expression('(' . time() . ' - updated_at) / 86400 as days_since_update'),
                new Expression('(' . time() . ' - created_at) / 86400 as days_since_create'),
                new Expression("(SELECT user_meta.value FROM user_meta RIGHT JOIN assignment_work ON (user_meta.name = 'marking_days' AND user_meta.user_id = assignment_work.tutor) LIMIT 1) as marking_days")
            ]);
    }

    /**
     * @param int $tutor
     * @return int
     */
    public function countOverdueByTutor(int $tutor)
    {
        $select = $this->sqlSelect()
            ->where(['status = ?' => AssignmentWork::STATUS_WAIT, 'tutor' => $tutor])
            ->having(['days_since_create > marking_days']);
        $resultSet = $this->selectWith($select);
        return count($resultSet);
    }

    /**
     * @param int $tutor
     * @return int
     */
    public function countUnmarkedByTutor(int $tutor)
    {
        $select = $this->sqlSelect()
            ->where(['status = ?' => AssignmentWork::STATUS_WAIT, 'tutor' => $tutor]);
        $resultSet = $this->selectWith($select);
        return count($resultSet);
    }
}
