<?php

namespace Credit\Model;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Hydrator;

class RecruitTableGateway extends TableGateway
{
    /**
     * RecruitTableGateway constructor.
     *
     * @param AdapterInterface $adapter
     * @param null $features
     * @param null $resultSetPrototype
     * @param null $sql
     */
    public function __construct(AdapterInterface $adapter, $features = null, $resultSetPrototype = null, $sql = null)
    {
        $resultSetPrototype = $resultSetPrototype ?? new HydratingResultSet(new Hydrator\ClassMethods, new Recruit);
        parent::__construct('recruit', $adapter, $features, $resultSetPrototype, $sql);
    }
}
