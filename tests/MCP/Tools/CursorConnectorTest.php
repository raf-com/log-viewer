<?php

declare(strict_types=1);

namespace Tests\MCP\Tools;

use PHPUnit\Framework\TestCase;
use Acelle\Extra\LogViewer\MCP\Tools\CursorConnector;

/**
 * Test class for CursorConnector
 * 
 * @package Tests\MCP\Tools
 * @roadmap MCP-EXT-01-TEST
 * @test MCP-EXT-01-TEST
 * @security MCP-EXT-01-SEC
 * @simulation MCP-EXT-01-SIM
 * @qa MCP-EXT-01-QA
 */
class CursorConnectorTest extends TestCase
{
    /**
     * Test cursor connector initialization
     * 
     * @roadmap MCP-EXT-01
     * @test MCP-EXT-01-TEST
     */
    public function test_cursor_connector_initialization(): void
    {
        $connector = new CursorConnector();
        
        $this->assertInstanceOf(CursorConnector::class, $connector);
    }
    
    /**
     * Test code completion functionality
     * 
     * @roadmap MCP-EXT-01
     * @test MCP-EXT-01-TEST
     */
    public function test_code_completion(): void
    {
        $connector = new CursorConnector();
        $code = '<?php class UserController { public function index() {';
        $language = 'php';
        $context = ['framework' => 'laravel'];
        
        $result = $connector->getCodeCompletion($code, $language, $context);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('context', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('php', $result['context']['language']);
        $this->assertEquals('laravel', $result['context']['framework']);
    }
    
    /**
     * Test error detection functionality
     * 
     * @roadmap MCP-EXT-01
     * @test MCP-EXT-01-TEST
     */
    public function test_error_detection(): void
    {
        $connector = new CursorConnector();
        $code = '<?php echo $undefined_variable;';
        $language = 'php';
        
        $result = $connector->detectErrors($code, $language);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertGreaterThan(0, count($result['errors']));
    }
    
    /**
     * Test code refactoring functionality
     * 
     * @roadmap MCP-EXT-01
     * @test MCP-EXT-01-TEST
     */
    public function test_code_refactoring(): void
    {
        $connector = new CursorConnector();
        $code = '<?php function oldFunction() { return "old"; }';
        $refactoringType = 'rename_function';
        $options = ['new_name' => 'newFunction'];
        
        $result = $connector->refactorCode($code, $refactoringType, $options);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('refactored_code', $result);
        $this->assertArrayHasKey('changes', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertStringContainsString('Refactored', $result['refactored_code']);
        $this->assertEquals($refactoringType, $result['changes']['type']);
    }
    
    /**
     * Test documentation generation functionality
     * 
     * @roadmap MCP-EXT-01
     * @test MCP-EXT-01-TEST
     */
    public function test_documentation_generation(): void
    {
        $connector = new CursorConnector();
        $code = '<?php class TestClass { public function testMethod() { } }';
        $type = 'phpdoc';
        $options = ['include_params' => true];
        
        $result = $connector->generateDocumentation($code, $type, $options);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('documentation', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals($type, $result['documentation']['type']);
        $this->assertStringContainsString('Mock documentation', $result['documentation']['content']);
    }
    
    /**
     * Test test generation functionality
     * 
     * @roadmap MCP-EXT-01
     * @test MCP-EXT-01-TEST
     */
    public function test_test_generation(): void
    {
        $connector = new CursorConnector();
        $code = '<?php class Calculator { public function add($a, $b) { return $a + $b; } }';
        $framework = 'phpunit';
        $options = ['coverage' => true];
        
        $result = $connector->generateTests($code, $framework, $options);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('tests', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals($framework, $result['tests']['framework']);
        $this->assertStringContainsString('MockTest', $result['tests']['test_code']);
        $this->assertGreaterThan(0, $result['tests']['coverage']);
    }
    
    /**
     * Test configuration retrieval
     * 
     * @roadmap MCP-EXT-01
     * @test MCP-EXT-01-TEST
     */
    public function test_configuration_retrieval(): void
    {
        $connector = new CursorConnector();
        
        $config = $connector->getConfiguration();
        
        $this->assertIsArray($config);
        $this->assertEquals('Cursor', $config['ide_name']);
        $this->assertEquals('1.0.0', $config['version']);
        $this->assertArrayHasKey('capabilities', $config);
        $this->assertArrayHasKey('settings', $config);
        $this->assertTrue($config['settings']['ai_enabled']);
        $this->assertTrue($config['settings']['auto_completion']);
    }
} 