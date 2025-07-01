<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\MockPDO;
use Tests\Mocks\MockPDOStatement;
use Tests\Mocks\TestableModel;

// Testable User class that uses TestableModel's getDB()
class TestableUser extends User
{
    protected static function getDB()
    {
        return TestableModel::$mockDb;
    }
}

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestableModel::$mockDb = new MockPDO();
    }

    public function testCreateUserReturnsLastInsertId()
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
        TestableModel::$mockDb->method('lastInsertId')->willReturn('101');

        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'hashedpassword',
            'salt' => 'randomsalt'
        ];
        $id = TestableUser::createUser($data);
        $this->assertEquals('101', $id);
    }

    public function testGetByLoginReturnsUser()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['id' => 1, 'username' => 'testuser', 'email' => 'test@example.com'],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $user = TestableUser::getByLogin('test@example.com');
        $this->assertIsArray($user);
        $this->assertEquals('testuser', $user['username']);
    }

    public function testFindByIdReturnsUser()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['id' => 1, 'username' => 'testuser', 'email' => 'test@example.com'],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $user = TestableUser::findById(1);
        $this->assertIsArray($user);
        $this->assertEquals('testuser', $user['username']);
    }

    
}
