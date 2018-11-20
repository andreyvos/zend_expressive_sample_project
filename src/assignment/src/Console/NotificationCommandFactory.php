<?php
namespace Assignment\Console;

use Assignment\Model\AssignmentWorkTable;
use Cake\Chronos\MutableDate;
use Options\Model\Options;
use Options\Model\OptionsTable;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use User\Model\UserTable;
use Zend\Mail as ZendMail;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class NotificationCommandFactory extends Command
{
    public function __invoke(ContainerInterface $container)
    {
        return new NotificationCommand(
            $container->get(AssignmentWorkTable::class),
            $container->get(OptionsTable::class),
            $container->get(UserTable::class),
            $container->get(TransportInterface::class),
            $container->get('config')
        );
    }
}
