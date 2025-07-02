<?php

namespace Tests\Unit\Models;

use App\Models\Cities;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\MockPDO;
use Tests\Mocks\MockPDOStatement;
use Tests\Mocks\TestableModel;

// Testable Cities class that uses TestableModel's getDB()
class TestableCities extends Cities
{
    protected static function getDB()
    {
        return TestableModel::$mockDb;
    }
}

class CitiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestableModel::$mockDb = new MockPDO();
    }

    public function testSearchByNameReturnsArray()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['ville_id' => 1, 'ville_nom_reel' => 'Paris'],
            ['ville_id' => 2, 'ville_nom_reel' => 'Parisot'],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $cities = TestableCities::searchByName('Par');
        $this->assertIsArray($cities);
        $this->assertCount(2, $cities);
        $this->assertArrayHasKey('ville_nom_reel', $cities[0]);
    }

    public function testFindByIdReturnsArray()
    {
        $mockStmt = new MockPDOStatement();
        $mockStmt->data = [
            ['ville_id' => 1, 'ville_nom_reel' => 'Paris'],
        ];

        TestableModel::$mockDb = $this->getMockBuilder(MockPDO::class)
                                     ->disableOriginalConstructor()
                                     ->onlyMethods(['prepare'])
                                     ->getMock();
        TestableModel::$mockDb->method('prepare')->willReturn($mockStmt);

        $city = TestableCities::findById(1);
        $this->assertIsArray($city);
        $this->assertEquals(1, $city['ville_id']);
    }
}
