<?php

namespace ApplicationTest;

use Prophecy\Prophet;

use PHPUnit\Framework\TestCase;

class Shit
{
    public function someMethod() {
        $this->otherMethod();
        printf("\notherMethod() was called by %s\n",__FUNCTION__);
        return $this;
    }
    public function otherMethod() {

    }
}

class ShitTest extends TestCase
{
    public function testTest()
    {
        $prophet = $this->prophesize(Shit::class);
        $prophecy = $prophet->someMethod(\Prophecy\Argument::type(Shit::class))->shouldBeCalled();
        //$prophet->otherMethod()->shouldBeCalled();
        $object = $prophet->reveal();
        // $shit = new Shit();  $shit->someMethod();
        printf("\n\$object is a %s",get_class($object));
        print_r(get_class_methods($object));
        $object->someMethod($object);
        //$otherProphecy = $prophet->otherMethod()->shouldHaveBeenCalled();

        //$this->assertTrue(true);
    }

    public function testTestTwo()
    {
        $this->assertTrue(true);
    }
}
