<?php

namespace Credit\Model;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Hydrator;
use Zend\Db\TableGateway\TableGateway;

class CreditUserMetaTableGateway extends TableGateway
{
    public function __construct(AdapterInterface $adapter, $features = null, $resultSetPrototype = null, $sql = null)
    {
        $resultSetPrototype = $resultSetPrototype ?? new HydratingResultSet(new Hydrator\ClassMethods, new CreditUserMeta);
        parent::__construct('credit_user_meta', $adapter, $features, $resultSetPrototype, $sql);
    }
}
