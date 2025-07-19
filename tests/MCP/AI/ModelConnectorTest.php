<?php

declare(strict_types=1);

namespace Tests\MCP\AI;

use PHPUnit\Framework\TestCase;
use Acelle\Extra\LogViewer\MCP\AI\ModelConnector;
use MCP\AI\ModelResponse;
use MCP\AI\ModelRequest;

/**
 * Test class for ModelConnector
 * 
 * @package Tests\MCP\AI
 * @roadmap MCP-AI-01-TEST
 * @test MCP-AI-01-TEST
 * @security MCP-AI-01-SEC
 * @simulation MCP-AI-01-SIM
 * @qa MCP-AI-01-QA
 */
class ModelConnectorTest extends TestCase
{
    /**
     * Test model connector initialization
     * 
     * @roadmap MCP-AI-01
     * @test MCP-AI-01-TEST
     */
    public function test_model_connector_initialization(): void
    {
        $connector = new ModelConnector();
        
        $this->assertInstanceOf(ModelConnector::class, $connector);
    }
    
    /**
     * Test code generation
     * 
     * @roadmap MCP-AI-01
     * @test MCP-AI-01-TEST
     */
    public function test_code_generation(): void
    {
        $connector = new ModelConnector();
        $prompt = 'Create a Laravel controller for user management';
        
        $response = $connector->generateCode($prompt, 'gpt-4', ['framework' => 'laravel']);
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI response', $response->getContent());
        $this->assertEquals('gpt-4', $response->getModel());
    }
    
    /**
     * Test code analysis
     * 
     * @roadmap MCP-AI-01
     * @test MCP-AI-01-TEST
     */
    public function test_code_analysis(): void
    {
        $connector = new ModelConnector();
        $code = '<?php class UserController { public function index() { return view("users"); } }';
        
        $response = $connector->analyzeCode($code, 'gpt-4');
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI response', $response->getContent());
        $this->assertEquals('gpt-4', $response->getModel());
    }
    
    /**
     * Test documentation generation
     * 
     * @roadmap MCP-AI-01
     * @test MCP-AI-01-TEST
     */
    public function test_documentation_generation(): void
    {
        $connector = new ModelConnector();
        $code = '<?php class UserController { public function index() { return view("users"); } }';
        
        $response = $connector->generateDocumentation($code, 'gpt-4');
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI response', $response->getContent());
        $this->assertEquals('gpt-4', $response->getModel());
    }
    
    /**
     * Test unsupported model validation
     * 
     * @roadmap MCP-AI-01
     * @test MCP-AI-01-TEST
     */
    public function test_unsupported_model_validation(): void
    {
        $connector = new ModelConnector();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported AI model: invalid-model');
        
        $connector->generateCode('test prompt', 'invalid-model');
    }
    
    /**
     * Test model response structure
     * 
     * @roadmap MCP-AI-01
     * @test MCP-AI-01-TEST
     */
    public function test_model_response_structure(): void
    {
        $connector = new ModelConnector();
        $prompt = 'Test prompt';
        
        $response = $connector->generateCode($prompt, 'gpt-4');
        
        $usage = $response->getUsage();
        $metadata = $response->getMetadata();
        
        $this->assertArrayHasKey('prompt_tokens', $usage);
        $this->assertArrayHasKey('completion_tokens', $usage);
        $this->assertArrayHasKey('total_tokens', $usage);
        $this->assertArrayHasKey('processing_time', $metadata);
        $this->assertArrayHasKey('confidence', $metadata);
    }
} 