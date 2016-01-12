<?php

namespace MacFJA\ChainConfig\Test;


use MacFJA\ChainConfig\Collection\OrderedGroup;

class OrderedGroupTest extends \PHPUnit_Framework_TestCase
{
    public function testAppendPathWithNoGroup()
    {
        $groupArray = new OrderedGroup();

        $groupArray->appendValue('Path1');
        $groupArray->appendValue('Path2');

        self::assertEquals(array('Path1', 'Path2'), $groupArray->getGroupValues());

        $groupArray->appendValue(array('Path3', 'Path4'));

        self::assertEquals(array('Path1', 'Path2', 'Path3', 'Path4'), $groupArray->getGroupValues());
    }

    public function testPrependPathWithNoGroup()
    {
        $groupArray = new OrderedGroup();

        $groupArray->prependValue('Path1');
        $groupArray->prependValue('Path2');

        self::assertEquals(array('Path2', 'Path1'), $groupArray->getGroupValues());

        $groupArray->prependValue(array('Path3', 'Path4'));

        self::assertEquals(array('Path3', 'Path4', 'Path2', 'Path1'), $groupArray->getGroupValues());
    }

    public function testGetGroupPaths()
    {
        $groupArray = new OrderedGroup();

        $groupArray->appendValue('Path1', 'group1');
        $groupArray->appendValue('Path2', 'group1');

        $groupArray->appendValue('Path3', 'group2');
        $groupArray->appendValue('Path4', 'group2');

        self::assertEquals(array('Path1', 'Path2'), $groupArray->getGroupValues('group1'));
        self::assertEquals(array('Path3', 'Path4'), $groupArray->getGroupValues('group2'));
    }

    public function testGetAllPaths()
    {
        $groupArray = new OrderedGroup();

        $groupArray->appendValue('Path1', 'group1');
        $groupArray->appendValue('Path2', 'group1');

        $groupArray->appendValue('Path3', 'group2');
        $groupArray->appendValue('Path4', 'group2');

        $groupArray->appendValue('Path5', 'group3');
        $groupArray->appendValue('Path6', 'group3');

        $groupArray->appendValue('Path7', 'group4');
        $groupArray->appendValue('Path8', 'group4');

        $groupArray->setGroupPosition('group4', 'group1');
        $groupArray->setGroupPosition('group2', null, 'group3');

        self::assertEquals(array(
            // group4
            'Path7', 'Path8',
            // group1
            'Path1', 'Path2',
            // group3
            'Path5', 'Path6',
            // group2
            'Path3', 'Path4',
        ), $groupArray->getAllValues());
    }

    public function testGetAllGroup()
    {
        $groupArray = new OrderedGroup();

        $groupArray->appendValue('Path1', 'group1');
        $groupArray->appendValue('Path3', 'group2');
        $groupArray->appendValue('Path5', 'group3');
        $groupArray->appendValue('Path7', 'group4');

        self::assertEquals(
            array('group1', 'group2', 'group3', 'group4'),
            $groupArray->getAllGroups()
        );
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage [OrderedGroup] You must define at least $before or $after.
     */
    public function testInvalidatePosition1()
    {
        $groupArray = new OrderedGroup();
        $groupArray->setGroupPosition('error');

        self::fail('Expecting an Exception');
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage [OrderedGroup] $before and $after must be different.
     */
    public function testInvalidatePosition3()
    {
        $groupArray = new OrderedGroup();
        $groupArray->setGroupPosition('error', 'nope','nope');

        self::fail('Expecting an Exception');
    }

    /**
     * Unit test for the case of the group already in the right position
     */
    public function testSort()
    {
        $groupArray = new OrderedGroup();
        $groupArray->appendValue('1');
        $groupArray->appendValue('2', 'after');
        $groupArray->appendValue('0', 'before');

        $groupArray->setGroupPosition('after', null, OrderedGroup::DEFAULT_GROUP);
        $groupArray->setGroupPosition('before', OrderedGroup::DEFAULT_GROUP);

        self::assertEquals(
            array(0,1, 2),
            $groupArray->getAllValues()
        );
    }

    public function testCount()
    {
        $groupArray = new OrderedGroup();
        $groupArray->appendValue('1');
        $groupArray->appendValue('2');
        $groupArray->appendValue('0');

        self::assertEquals(3, $groupArray->count());
        self::assertEquals(3, count($groupArray));
    }

    public function testIteratorSimple()
    {
        $groupArray = new OrderedGroup();
        $groupArray->appendValue('1');
        $groupArray->appendValue('2');
        $groupArray->appendValue('0');

        $array = array('1', 2, 0);
        foreach ($groupArray as $key => $value) {
            self::assertEquals($array[$key], $value);
        }
    }

    public function testIteratorGroup()
    {
        $groupArray = new OrderedGroup();
        $groupArray->appendValue('1');
        $groupArray->prependValue('4');
        $groupArray->appendValue('2', 'after');
        $groupArray->appendValue('0', 'before');
        $groupArray->appendValue('99', 'before');

        $groupArray->setGroupPosition('after', null, OrderedGroup::DEFAULT_GROUP);
        $groupArray->setGroupPosition('before', OrderedGroup::DEFAULT_GROUP);

        $array = array(0, 99, 4, 1, 2);
        foreach ($groupArray as $key => $value) {
            self::assertEquals($array[$key], $value);
        }
    }

    public function testSerialize()
    {
        $groupArray = new OrderedGroup();
        $groupArray->appendValue('1');
        $groupArray->appendValue('2');
        $groupArray->appendValue('0');

        $serialize = serialize($groupArray);
        self::assertContains(
            serialize(
                array('values' => array(
                    array('value' => '1', 'group' => '__main__'),
                    array('value' => '2', 'group' => '__main__'),
                    array('value' => '0', 'group' => '__main__'),
                ),
                'groups' => array())),
            $serialize
        );

        $unSerialized = unserialize($serialize);
        self::assertEquals($groupArray, $unSerialized);
    }
}