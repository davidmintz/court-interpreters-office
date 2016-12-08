<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace InterpretersOffice\Service\Factory;

/**
 * Description of LogFactory
 *
 * @author david
 */
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Log\Filter\Priority as Filter;


class LogFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        
        $log = new Logger();
        $path = getcwd().'/data/log/app.log';
        $writer = new Stream($path,'a');
        $filter = new Filter(Logger::DEBUG);
        $writer->addFilter($filter);
        // I think the 2nd argument 'priority' means the order
        // in which writers write, not the filter
        $log->addWriter($writer);
        // for now, mark the (approximate) beginning of each request cycle
        $log->debug("\n================================\n");
        return $log;
               
    }
}
