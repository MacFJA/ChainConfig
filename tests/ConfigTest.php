<?php

namespace MacFJA\ChainConfig\Test;

use MacFJA\ChainConfig\Config;
use MacFJA\ChainConfig\Reader\IniReader;
use MacFJA\ChainConfig\Reader\JsonReader;
use MacFJA\ChainConfig\Collection\OrderedGroup;
use MacFJA\ChainConfig\Reader\PhpReader;
use MacFJA\ChainConfig\Reader\ReaderInterface;
use MacFJA\ChainConfig\Reader\XmlReader;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected static $fixturePath;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
        self::$fixturePath = __DIR__.DIRECTORY_SEPARATOR.'fixtures';
        parent::setUpBeforeClass();
    }


    public function testReaderChain() {
        $config = new Config();
        $config->appendPath(self::$fixturePath);
        $config->appendReader(new IniReader());
        $config->appendReader(new JsonReader());

        self::assertEquals(true, $config->get('a.lvl2.value1'));
        self::assertFalse($config->get('a.lvl2.value2'));
        self::assertNull($config->get('a.lvl2.value3'));
        self::assertNull($config->get('b.a'));
    }

    public function testReaderChain2() {
        $config = new Config();
        $config->appendPath(self::$fixturePath);
        $config->appendReader(new IniReader());
        $config->appendReader(new PhpReader());

        self::assertEquals(true, $config->get('a.lvl2.value1'));
        self::assertNull($config->get('a.lvl2.value2'));
    }

    /**
     *
     * @dataProvider readerProvider
     * @param string $readerClass
     */
    public function testReader($readerClass) {
        $config = new Config();
        $config->appendPath(self::$fixturePath.DIRECTORY_SEPARATOR.'reader');

        /** @var ReaderInterface $reader */
        $reader = new $readerClass;
        $config->appendReader($reader);

        foreach ($reader->getExtensions() as $ext) {
            self::assertEquals('working', $config->get($ext.'_reader.section.value'));
        }
    }

    public function testReaderPathChain()
    {
        $config = new Config();
        $config->appendPath(self::$fixturePath);
        $config->appendPath(self::$fixturePath.DIRECTORY_SEPARATOR.'after');
        $config->prependPath(self::$fixturePath.DIRECTORY_SEPARATOR.'before');
        $config->appendReader(new IniReader());
        $config->appendReader(new XmlReader());
        $config->appendReader(new PhpReader());
        $config->prependReader(new JsonReader());

        self::assertEquals('Hello World', $config->get('a.lvl2.value1'));
        self::assertEquals('Bonjour le monde', $config->get('a.lvl2.value10'));
        self::assertFalse($config->get('a.lvl2.value2'));
        self::assertEquals('hello', $config->get('a.lvl2.value3'));
        self::assertNull($config->get('b.a'));
    }

    public function testKeyCallback()
    {
        $config = new Config();
        $config->appendPath(self::$fixturePath);
        $config->appendReader(new IniReader());
        $config->appendKeyCallback(function($key) use ($config) {
            if (strpos($key, '{db_type}') === false) {
                return $key;
            }
            return str_replace('{db_type}', $config->get('a.db.type'), $key);
        });
        $config->prependKeyCallback(function($key) use ($config) {
            if (strpos($key, 'b.') === 0) {
                return 'a.'.substr($key, 2);
            }
            return $key;
        });

        self::assertEquals('mysql', $config->get('a.db.type'));
        self::assertEquals('pdo', $config->get('{db_type}.driver'));
        self::assertEquals('mysql', $config->get('b.db.type'));
    }

    public function testDefault()
    {
        $config = new Config();

        self::assertNull($config->get('a.db.type'));

        self::assertEquals('mysql', $config->get('a.db.type', 'mysql'));
        self::assertNull($config->get('a.db.type'));

        self::assertEquals('mysql', $config->get('a.db.type', 'mysql', true));
        self::assertEquals('mysql', $config->get('a.db.type'));
    }

    public function testValueCallbacks()
    {
        $config = new Config();
        $config->appendValueCallback(function($key, $value) {
            return strtoupper($value);
        });

        self::assertEquals('MYSQL', $config->get('a.db.type', 'mysql'));

        $config->prependValueCallback(function($key, $value) {
            return str_replace('y', 'o', $value);
        });

        self::assertEquals('MOSQL', $config->get('a.db.type', 'mysql'));
    }

    public function testWeirdKey()
    {
        $config = new Config();
        $config->appendPath(self::$fixturePath);
        $config->appendReader(new IniReader());
        $config->appendReader(new PhpReader());

        self::assertEquals(true, $config->get('a.lvl2.value1.'));
        self::assertEquals(array('value1' => 1), $config->get('a.lvl2.'));
        self::assertNull($config->get('a.lvl2.nope'));

        // Directory separator in first key part don't work
        self::assertNull($config->get('after'.DIRECTORY_SEPARATOR.'a.lvl2.value2'));
        self::assertEquals('Key will be cut!', $config->get('a.dot.section.key.with.dot'));
        self::assertEquals('cutted', $config->get('a.normal_section.with.a.dot'));
        self::assertEquals(array('dot' => 'cutted'), $config->get('a.normal_section.with.a'));
    }

    public function testConstructor()
    {
        $config = new Config(array('Hello' => 'World'));

        self::assertEquals('World', $config->get('Hello'));
    }

    public function testGetCurrentConfigurations()
    {
        $config = new Config(array('Hello' => 'World'));
        $config->appendPath(self::$fixturePath.DIRECTORY_SEPARATOR.'reader');
        $config->appendReader(new IniReader());

        self::assertEquals('working', $config->get('ini_reader.section.value'));
        self::assertEquals('added!', $config->get('not.existing', 'added!', true));

        self::assertEquals('World', $config->get('Hello'));

        self::assertEquals(array(
            'Hello' => 'World',
            'ini_reader' => array(
                'section' => array('value' => 'working')
            ),
            'not' => array(
                'existing' => 'added!'
            )
        ), $config->getCurrentConfigurations());
    }

    public function testWrongPath()
    {
        $config = new Config(array('Hello' => 'World'));
        $config->appendPath(__DIR__.DIRECTORY_SEPARATOR.'nope');

        self::assertNull($config->get('any.key.will.fail'));
    }

    public function testDist()
    {
        $config = new Config(array('Hello' => 'World'));
        $config->appendPath(self::$fixturePath.DIRECTORY_SEPARATOR.'dist');
        $config->appendReader(new IniReader());

        self::assertEquals('value', $config->get('test1.section.key'));
        self::assertEquals('Yes', $config->get('test2.section.value'));
    }

    public function testOrderPath()
    {
        $config = new Config();

        $config->appendPath(self::$fixturePath);
        $config->appendPath(self::$fixturePath.DIRECTORY_SEPARATOR.'after', 'after');
        $config->appendPath(self::$fixturePath.DIRECTORY_SEPARATOR.'before', 'before');
        $config->appendReader(new IniReader());
        $config->appendReader(new XmlReader());
        $config->appendReader(new PhpReader());
        $config->prependReader(new JsonReader());

        $config->orderGroupPath('after', null, OrderedGroup::DEFAULT_GROUP);
        $config->orderGroupPath('before', OrderedGroup::DEFAULT_GROUP);

        self::assertEquals('Hello World', $config->get('a.lvl2.value1'));
        self::assertEquals('Bonjour le monde', $config->get('a.lvl2.value10'));
        self::assertFalse($config->get('a.lvl2.value2'));
        self::assertEquals('hello', $config->get('a.lvl2.value3'));
        self::assertNull($config->get('b.a'));
    }

    public function testIterator()
    {
        $config = new Config();
        $config->appendPath(self::$fixturePath)
            ->appendReader(new IniReader());

        $config->get('a');

        $expectedArray = array(
            'a.lvl2.value1' => 1,
            'a.db.type' => 'mysql',
            'a.dot.section.key.with.dot' => 'Key will be cut!',
            'a.normal_section.with.a.dot' => 'cutted'
        );

        foreach ($config as $key => $value) {
            self::assertTrue(array_key_exists($key, $expectedArray));
            self::assertEquals($expectedArray[$key], $value);
        }
    }

    public function testCount()
    {
        $config = new Config();
        $config->appendPath(self::$fixturePath)
            ->appendReader(new IniReader());

        self::assertEquals(0, $config->count());
        self::assertEquals(0, count($config));

        $config->get('a');

        self::assertEquals(4, $config->count());
        self::assertEquals(4, count($config));

        $config->get('b', 'c');

        self::assertEquals(4, $config->count());
        self::assertEquals(4, count($config));

        $config->get('b', 'c', true);

        self::assertEquals(5, $config->count());
        self::assertEquals(5, count($config));

        $config->get('mysql.driver');

        self::assertEquals(6, $config->count());
        self::assertEquals(6, count($config));
    }

    public function readerProvider() {
        // Use the string FQCN instead of ClassName::class to keep PHP < 5.5 compatibility
        return array(
            array('MacFJA\ChainConfig\Reader\IniReader'),
            array('MacFJA\ChainConfig\Reader\JsonReader'),
            array('MacFJA\ChainConfig\Reader\NeonReader'),
            array('MacFJA\ChainConfig\Reader\PhpReader'),
            array('MacFJA\ChainConfig\Reader\XmlReader'),
            array('MacFJA\ChainConfig\Reader\YamlReader'),
        );
    }
}