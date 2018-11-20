<?php

namespace Customer\Model;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Hydrator;

class CustomerTableGateway extends TableGateway
{
    /**
     * CustomerTableGateway constructor.
     *
     * @param AdapterInterface $adapter
     * @param null $features
     * @param null $resultSetPrototype
     * @param null $sql
     */
    public function __construct(AdapterInterface $adapter, $features = null, $resultSetPrototype = null, $sql = null)
    {
        $resultSetPrototype = $resultSetPrototype ?? new HydratingResultSet(new Hydrator\ClassMethods, new Customer);
        parent::__construct('customer', $adapter, $features, $resultSetPrototype, $sql);
    }
}
