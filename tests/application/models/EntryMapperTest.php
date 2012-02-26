<?php

class Application_Model_EntryMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application_Model_EntryMapper
     */
    protected $mapper;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $mock;

    protected $testData;

    /**
     * @var Application_Model_DbTable_Entry
     */
    protected $dbTable;


    protected function setUp()
    {
        //$this->_application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');

        $this->testData = array("id" => "1",
            "title" => "First Entry",
            "body" => "Long text",
            "created" => "2012-02-20 15:00:00",
        );

        $this->dbTable= new Application_Model_DbTable_Entry();

       /* $this->adapter   = new Zend_Test_DbAdapter();*/

        $this->mapper = new Application_Model_EntryMapper();
        $this->mock = $this->getMock("Application_Model_DbTable_Entry");


    }

    /*$this->mock->insert($this->testData);
   $this->mock->update($this->testData, array('id = ?' => "1"));

   $result = $this->mock->find(1);
   $row = $result->current();
   $this->assertEquals($this->testData, $row->toArray());*/

    static public function getTestRowSet($id)
    {
        $testData = array("id" => "1",
            "title" => "First Entry",
            "body" => "Long text",
            "created" => "2012-02-20 15:00:00",
        );

        switch ($id) {
            case 1:
                return new Zend_Db_Table_Rowset(array("table" => new Application_Model_DbTable_Entry(), "data" => array($testData)));
                break;
            case 0:
                return new Zend_Db_Table_Rowset(array("table" => new Application_Model_DbTable_Entry(), "data" => array()));
                break;
            default:
                return null;
        }
    }

    public function testDbTable()
    {
        $this->assertEquals($this->dbTable, $this->mapper->getDbTable());

        $this->mapper->setDbTable($this->mock);
        $this->assertEquals($this->mock, $this->mapper->getDbTable());

        $this->mapper->setDbTable((string) get_class($this->dbTable));
        $this->assertEquals($this->dbTable, $this->mapper->getDbTable());
    }

    /**
     * @expectedException Exception
     */
    public function testDbTableError()
    {
        $this->mapper->setDbTable(new StdClass);
    }

    public function testFind()
    {
        $this->mock->expects($this->any())
            ->method("find")
            ->will($this->returnCallback("Application_Model_EntryMapperTest::getTestRowSet"));

        $this->mapper->setDbTable($this->mock);
        $entry = new Application_Model_Entry();
        $result = $this->mapper->find(1, $entry);
        $this->assertTrue($result);
        $this->assertEquals(new Application_Model_Entry($this->testData), $entry);

        $entry = new Application_Model_Entry();
        $result = $this->mapper->find(0, $entry);
        $this->assertFalse($result);
    }

    public function testSave()
    {
        $this->mapper->setDbTable($this->mock);

        $testDataNew=$this->testData;
        unset($testDataNew["id"]);
        $this->mock->expects($this->any())
            ->method("insert")
            ->with($testDataNew)
            ->will($this->returnValue(true));
        $this->mock->expects($this->any())
            ->method("update")
            ->with($this->testData, array('id = ?' => "1"))
            ->will($this->returnValue(true));
        $this->mock->expects($this->any())
            ->method("find")
            ->will($this->returnCallback("getTestRowSet"));


        //test insert
        $entry = new Application_Model_Entry($testDataNew);
        $result = $this->mapper->save($entry);
        $this->assertTrue($result);

        //test update
        $testData=$this->testData;
        $entry = new Application_Model_Entry($testData);
        $result = $this->mapper->save($entry);
        $this->assertTrue($result);

    }

    public function testFetchAll()
    {
        $testData2 = $this->testData;
        $testData2["id"] = 3;
        $testData2["title"] = "33 korovi";
        $testRowset = new Zend_Db_Table_Rowset(array("table" => new Application_Model_DbTable_Entry(),
            "data" => array($this->testData, $testData2)));

        $this->mock->expects($this->any())
            ->method("fetchAll")
            ->will($this->returnValue($testRowset));
        $this->mapper->setDbTable($this->mock);

        $entris = array(new Application_Model_Entry($this->testData)
        , new Application_Model_Entry($testData2)
        );


        $this->assertEquals($entris, $this->mapper->fetchAll());
    }



}
