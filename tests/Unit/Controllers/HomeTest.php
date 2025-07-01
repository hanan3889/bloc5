<?php

namespace Tests\Unit\Controllers;

use App\Controllers\Home;
use PHPUnit\Framework\TestCase;

class HomeTest extends TestCase
{
    public function testIndexActionRendersCorrectTemplate()
    {
        $controller = new Home([]); // Pass empty array as route_params

        ob_start(); // Start output buffering
        $controller->indexAction();
        $output = ob_get_clean(); // Get output and clean buffer

        // Assert that some output was produced (indicating the template was rendered)
        $this->assertNotEmpty($output, 'Expected output from template rendering.');
        // More specific assertions could be added here if the template output is predictable.
    }
}