<?php

namespace Tests\Unit\Models;

use App\Models\Articles;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\MockPDO;
use Tests\Mocks\MockPDOStatement;
use Tests\Mocks\TestableModel;

// Testable Articles class that uses TestableModel's getDB()
class TestableArticles extends Articles
{
    protected static function getDB()
    {
        return TestableModel::$mockDb;
    }
}

class ArticlesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Set up a mock database connection for each test
        TestableModel::$mockDb = new MockPDO();
    }

    public function testGetAllReturnsArray()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['id' => 1, 'name' => 'Article 1', 'seller_id' => 1, 'seller_username' => 'user1', 'seller_email' => 'user1@example.com'],
            ['id' => 2, 'name' => 'Article 2', 'seller_id' => 2, 'seller_username' => 'user2', 'seller_email' => 'user2@example.com'],
        ];

        // Override the query method of MockPDO to return our specific mock statement
        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['query'])
                                     ->getMock();
        TestableModel::$mockDb->method('query')->willReturn($mockStmt);

        $articles = TestableArticles::getAll('');
        $this->assertIsArray($articles);
        $this->assertCount(2, $articles);
        $this->assertArrayHasKey('name', $articles[0]);
    }

    public function testGetOneReturnsArray()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['id' => 1, 'name' => 'Article 1', 'seller_id' => 1, 'seller_username' => 'user1', 'seller_email' => 'user1@example.com'],
        ];

        // Override the prepare method of MockPDO to return our specific mock statement
        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $article = TestableArticles::getOne(1);
        $this->assertIsArray($article);
        $this->assertCount(1, $article);
        $this->assertEquals(1, $article[0]['id']);
    }

    public function testAddOneViewExecutesStatement()
    {
        $mockStmt = $this->getMockBuilder(MockPDOStatement::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['execute'])
                                     ->getMock();
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->with([1]);

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        TestableArticles::addOneView(1);
    }

    public function testGetByUserReturnsArray()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['id' => 1, 'name' => 'Article 1', 'user_id' => 1],
            ['id' => 2, 'name' => 'Article 2', 'user_id' => 1],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $articles = TestableArticles::getByUser(1);
        $this->assertIsArray($articles);
        $this->assertCount(2, $articles);
        $this->assertEquals(1, $articles[0]['user_id']);
    }

    public function testGetSuggestReturnsArray()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['id' => 1, 'name' => 'Suggested Article 1'],
            ['id' => 2, 'name' => 'Suggested Article 2'],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $articles = TestableArticles::getSuggest();
        $this->assertIsArray($articles);
        $this->assertCount(2, $articles);
    }

    public function testSaveReturnsLastInsertId()
    {
        $mockStmt = $this->getMockBuilder(MockPDOStatement::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['bindParam', 'execute'])
                                     ->getMock();
        $mockStmt->expects($this->exactly(4))
                 ->method('bindParam');
        $mockStmt->expects($this->once())
                 ->method('execute');

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare', 'lastInsertId'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);
        TestableModel::$mockDb->method('lastInsertId')->willReturn('100');

        $data = [
            'name' => 'New Article',
            'description' => 'Description of new article',
            'user_id' => 1
        ];
        $id = TestableArticles::save($data);
        $this->assertEquals('100', $id);
    }

    public function testAttachPictureExecutesStatement()
    {
        $mockStmt = $this->getMockBuilder(MockPDOStatement::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['bindParam', 'execute'])
                                     ->getMock();
        $mockStmt->expects($this->exactly(2))
                 ->method('bindParam');
        $mockStmt->expects($this->once())
                 ->method('execute');

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        TestableArticles::attachPicture(1, 'picture.jpg');
    }

    public function testGetWithOwnerByIdReturnsArray()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['article_id' => 1, 'article_name' => 'Article with Owner', 'user_username' => 'owner'],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $article = TestableArticles::getWithOwnerById(1);
        $this->assertIsArray($article);
        $this->assertEquals(1, $article['article_id']);
    }

    public function testSearchByNameReturnsArray()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['id' => 1, 'name' => 'Search Result 1'],
            ['id' => 2, 'name' => 'Search Result 2'],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $articles = TestableArticles::searchByName('test');
        $this->assertIsArray($articles);
        $this->assertCount(2, $articles);
    }
}
