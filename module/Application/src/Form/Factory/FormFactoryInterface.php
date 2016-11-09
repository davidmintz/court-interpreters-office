<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Form\Factory;

/**
 *
 * @author david
 */
interface FormFactoryInterface {
    /**
     * 
     * @param object|string $entityObjectOrClassname entity instance or classname
     * @param array $options 
     */
    public function createForm($entityObjectOrClassname, Array $options);
}
