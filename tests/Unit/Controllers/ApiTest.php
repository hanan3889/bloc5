<?php

namespace Tests\Unit\Controllers;

use App\Controllers\Api;
use PHPUnit\Framework\TestCase;

class TestableApi extends Api
{
    public $jsonResponseData = null;
    public $jsonResponseStatusCode = null;

    protected function sendJsonResponse($data, $statusCode = 200)
    {
        $this->jsonResponseData = $data;
        $this->jsonResponseStatusCode = $statusCode;
    }
}

class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset $_GET for each test
        $_GET = [];
    }

    public function testProductsActionReturnsValidJson()
    {
        // Simulate a GET request for products
        $_GET['sort'] = 'name'; // Or any other valid parameter

        $controller = new TestableApi([]);
        $controller->ProductsAction();

        $this->assertNotNull($controller->jsonResponseData);
        $this->assertIsArray($controller->jsonResponseData);
        $this->assertEquals(200, $controller->jsonResponseStatusCode);
        // Add more specific assertions based on expected data structure if needed
    }

    public function testCitiesActionReturnsValidJsonForValidSearch()
    {
        // Simulate a GET request for cities with a keyword
        $_GET['mot_cle'] = 'Paris';

        $controller = new TestableApi([]);
        $controller->CitiesAction();

        $this->assertNotNull($controller->jsonResponseData);
        $this->assertIsArray($controller->jsonResponseData);
        $this->assertEquals(200, $controller->jsonResponseStatusCode);
        // Add more specific assertions based on expected data structure if needed
    }

    public function testCitiesActionReturnsErrorForMissingMotCle()
    {
        // Simulate a GET request for cities without a keyword
        // $_GET['mot_cle'] is intentionally not set

        $controller = new TestableApi([]);
        $controller->CitiesAction();

        $this->assertNotNull($controller->jsonResponseData);
        $this->assertIsArray($controller->jsonResponseData);
        $this->assertArrayHasKey('error', $controller->jsonResponseData);
        $this->assertEquals('Le paramètre mot_cle est requis.', $controller->jsonResponseData['error']);
        $this->assertEquals(400, $controller->jsonResponseStatusCode);
    }
}