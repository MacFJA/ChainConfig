<?php

namespace MacFJA\ChainConfig\Test;


use MacFJA\ChainConfig\Collection\MultiPartKeyArray;

class MultiPartKeyArrayTest extends \PHPUnit_Framework_TestCase
{
    protected $example;
    protected static $flattenExample = array(
        'section1.sub1.v1' => 'a',
        'section1.sub1.v2' => 'b',
        'section1.sub2.v1' => 'C',
        'section1.sub2.v3' => 12345,
        'section2.value' => 'Hello world',
    );

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->example = array(
            'section1' => array(
                'sub1' => array('v1' => 'a', 'v2' => 'b'),
                'sub2' => array('v1' => 'C', 'v3' => 12345),
            ),
            'section2' => array(
                'value' => 'Hello world'
            )
        );
    }


    public function testSeparator()
    {
        $array = new MultiPartKeyArray();
        self::assertEquals('.', $array->getKeySeparator());

        $array->setKeySeparator('/');
        self::assertEquals('/', $array->getKeySeparator());
    }

    public function testFlatten()
    {
        self::assertEquals(
            self::$flattenExample,
            MultiPartKeyArray::flattenArray($this->example)
        );

        $array = new MultiPartKeyArray();
        $array->addArray($this->example);
        self::assertEquals(
            self::$flattenExample,
            $array->flatten()
        );
    }

    public function testGetValue()
    {
        $array = new MultiPartKeyArray($this->example);

        foreach (self::$flattenExample as $key => $value)
        {
            self::assertEquals(
                $value,
                $array->getValue($key)
            );
        }

        self::assertNull($array->getValue('not.existing'));
        self::assertNull($array->getValue('not.existing.'));
        self::assertEquals('a', $array->getValue('section1.sub1.v1.'));
        self::assertEquals('a', $array->getValue('section1.sub1.v1..'));
        self::assertEquals(array('v1' => 'a', 'v2' => 'b'), $array->getValue('section1.sub1'));
    }

    public function testHasKey()
    {
        $array = new MultiPartKeyArray($this->example);

        foreach (self::$flattenExample as $key => $value)
        {
            self::assertTrue(
                $array->hasKey($key)
            );
        }

        self::assertFalse($array->hasKey('not.existing'));
        self::assertFalse($array->hasKey('not.existing.'));
        self::assertTrue($array->hasKey('section1.sub1.v1.'));
        self::assertTrue($array->hasKey('section1.sub1.v1..'));
    }

    public function testUnFlatten()
    {
        self::assertEquals(
            $this->example,
            MultiPartKeyArray::unFlattenArray(self::$flattenExample)
        );
    }

    public function testToArray()
    {
        $array = new MultiPartKeyArray($this->example);

        self::assertEquals($this->example, $array->toArray(false));
        self::assertEquals(self::$flattenExample, $array->toArray(true));
    }

    public function testSerialization()
    {
        $array = new MultiPartKeyArray($this->example);
        $serialized = serialize($array);

        $unSerialized = unserialize($serialized);

        self::assertEquals($array, $unSerialized);
    }

    public function testRemoveValue()
    {
        $array = new MultiPartKeyArray($this->example);
        self::assertTrue($array->hasKey('section1.sub1.v1'));
        self::assertEquals('a', $array->getValue('section1.sub1.v1'));

        self::assertTrue($array->removeValue('section1.sub1.v1'));

        self::assertFalse($array->hasKey('section1.sub1.v1'));
        self::assertNull($array->getValue('section1.sub1.v1'));

        self::assertFalse($array->removeValue('section1.sub1.v1'));
    }

    public function testArrayAccess()
    {
        $array = new MultiPartKeyArray($this->example);

        self::assertTrue(isset($array['section1.sub1.v1']));

        self::assertEquals('a', $array['section1.sub1.v1']);

        self::assertEquals(5, $array->count());
        self::assertEquals(5, count($array));

        $array['section1.sub1.v3'] = 'c';
        self::assertEquals('c', $array['section1.sub1.v3']);

        $array['section1.sub1.v1'] = 'c';
        self::assertEquals('c', $array['section1.sub1.v1']);

        $array['section1.sub1.v1'] = 'a';

        unset($array['section1.sub1.v3']);
        self::assertFalse(isset($array['section1.sub1.v3']));
        self::assertNull($array['section1.sub1.v3']);

        self::assertEquals(array('v1' => 'a', 'v2' => 'b'), $array['section1.sub1']);
    }

    public function testIterator()
    {
        $array = new MultiPartKeyArray($this->example);
        foreach ($array as $longKey => $value) {
            self::assertTrue(array_key_exists($longKey, self::$flattenExample));
            self::assertEquals(self::$flattenExample[$longKey], $value);
        }

        foreach ($array as $longKey => &$value) {
            $value = 'M';
        }
        unset($value);

        // The iterator is read-only, reference value don't work
        self::assertNotEquals(
            array(
                'section1.sub1.v1' => 'M',
                'section1.sub1.v2' => 'M',
                'section1.sub2.v1' => 'M',
                'section1.sub2.v3' => 'M',
                'section2.value' => 'M',
            ),
            $array->flatten()
        );
    }
}