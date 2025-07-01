<?php

namespace Tests\Unit\Models;

use App\Models\UserRegister;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\MockPDO;
use Tests\Mocks\MockPDOStatement;
use Tests\Mocks\TestableModel;

// Testable UserRegister class that uses TestableModel's getDB()
class TestableUserRegister extends UserRegister
{
    protected static function getDB()
    {
        return TestableModel::$mockDb;
    }
}

class UserRegisterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestableModel::$mockDb = new MockPDO();
    }

    public function testCreateUserReturnsLastInsertIdOnSuccess()
    {
        $mockStmt = $this->getMockBuilder(MockPDOStatement::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['bindParam', 'execute'])
                                     ->getMock();
        $mockStmt->expects($this->exactly(4))
                 ->method('bindParam');
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare', 'lastInsertId'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);
        TestableModel::$mockDb->method('lastInsertId')->willReturn('101');

        $data = [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'hashedpass',
            'salt' => 'somesalt'
        ];
        $id = TestableUserRegister::createUser($data);
        $this->assertEquals(101, $id);
    }

    public function testCreateUserThrowsExceptionOnFailure()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erreur PDO lors de la création de l\'utilisateur : Mock PDO Error');

        $mockStmt = $this->getMockBuilder(MockPDOStatement::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['bindParam', 'execute', 'errorInfo'])
                                     ->getMock();
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(false);
        $mockStmt->method('errorInfo')->willReturn(['HY000', 1000, 'Mock PDO Error']);

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $data = [
            'username' => 'failuser',
            'email' => 'fail@example.com',
            'password' => 'pass',
            'salt' => 'salt'
        ];
        TestableUserRegister::createUser($data);
    }

    public function testEmailExistsReturnsTrueWhenEmailExists()
    {
        $mockStmt = $this->getMockBuilder(MockPDOStatement::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['bindParam', 'execute', 'fetchColumn'])
                                     ->getMock();
        $mockStmt->expects($this->once())
                 ->method('bindParam');
        $mockStmt->expects($this->once())
                 ->method('execute');
        $mockStmt->expects($this->once())
                 ->method('fetchColumn')
                 ->willReturn(1);

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $exists = TestableUserRegister::emailExists('existing@example.com');
        $this->assertTrue($exists);
    }

    public function testEmailExistsReturnsFalseWhenEmailDoesNotExist()
    {
        $mockStmt = $this->getMockBuilder(MockPDOStatement::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['bindParam', 'execute', 'fetchColumn'])
                                     ->getMock();
        $mockStmt->expects($this->once())
                 ->method('bindParam');
        $mockStmt->expects($this->once())
                 ->method('execute');
        $mockStmt->expects($this->once())
                 ->method('fetchColumn')
                 ->willReturn(0);

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $exists = TestableUserRegister::emailExists('nonexistent@example.com');
        $this->assertFalse($exists);
    }

    public function testFindByEmailReturnsUserData()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['id' => 1, 'username' => 'founduser', 'email' => 'found@example.com'],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $user = TestableUserRegister::findByEmail('found@example.com');
        $this->assertIsArray($user);
        $this->assertEquals('founduser', $user['username']);
    }

    public function testFindByEmailReturnsFalseWhenUserNotFound()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = []; // No data means user not found

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $user = TestableUserRegister::findByEmail('notfound@example.com');
        $this->assertFalse($user);
    }
}