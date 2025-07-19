<?php

declare(strict_types=1);

namespace Tests\MCP\AI;

use PHPUnit\Framework\TestCase;
use Acelle\Extra\LogViewer\MCP\AI\ModelHandler;
use Acelle\Extra\LogViewer\MCP\AI\ModelConnector;
use MCP\AI\ModelResponse;

/**
 * Test class for ModelHandler
 * 
 * @package Tests\MCP\AI
 * @roadmap MCP-AI-02-TEST
 * @test MCP-AI-02-TEST
 * @security MCP-AI-02-SEC
 * @simulation MCP-AI-02-SIM
 * @qa MCP-AI-02-QA
 */
class ModelHandlerTest extends TestCase
{
    /**
     * Test model handler initialization
     * 
     * @roadmap MCP-AI-02
     * @test MCP-AI-02-TEST
     */
    public function test_model_handler_initialization(): void
    {
        $connector = new ModelConnector();
        $handler = new ModelHandler($connector);
        
        $this->assertInstanceOf(ModelHandler::class, $handler);
    }
    
    /**
     * Test code generation handling
     * 
     * @roadmap MCP-AI-02
     * @test MCP-AI-02-TEST
     */
    public function test_code_generation_handling(): void
    {
        $connector = new ModelConnector();
        $handler = new ModelHandler($connector);
        
        $request = [
            'prompt' => 'Create a Laravel controller',
            'model' => 'gpt-4',
            'context' => ['framework' => 'laravel']
        ];
        
        $response = $handler->handleCodeGeneration($request);
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI response', $response->getContent());
    }
    
    /**
     * Test code analysis handling
     * 
     * @roadmap MCP-AI-02
     * @test MCP-AI-02-TEST
     */
    public function test_code_analysis_handling(): void
    {
        $connector = new ModelConnector();
        $handler = new ModelHandler($connector);
        
        $request = [
            'code' => '<?php class Test { }',
            'model' => 'gpt-4',
            'context' => ['analysis_type' => 'quality']
        ];
        
        $response = $handler->handleCodeAnalysis($request);
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI response', $response->getContent());
    }
    
    /**
     * Test documentation generation handling
     * 
     * @roadmap MCP-AI-02
     * @test MCP-AI-02-TEST
     */
    public function test_documentation_generation_handling(): void
    {
        $connector = new ModelConnector();
        $handler = new ModelHandler($connector);
        
        $request = [
            'code' => '<?php class Test { }',
            'model' => 'gpt-4',
            'context' => ['documentation_type' => 'comprehensive']
        ];
        
        $response = $handler->handleDocumentationGeneration($request);
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI response', $response->getContent());
    }
    
    /**
     * Test log analysis handling
     * 
     * @roadmap MCP-AI-02
     * @test MCP-AI-02-TEST
     */
    public function test_log_analysis_handling(): void
    {
        $connector = new ModelConnector();
        $handler = new ModelHandler($connector);
        
        $request = [
            'logs' => '2024-01-01 10:00:00 [INFO] Application started',
            'model' => 'gpt-4',
            'context' => ['analysis_type' => 'log_analysis']
        ];
        
        $response = $handler->handleLogAnalysis($request);
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI handler response', $response->getContent());
    }
    
    /**
     * Test security analysis handling
     * 
     * @roadmap MCP-AI-02
     * @test MCP-AI-02-TEST
     */
    public function test_security_analysis_handling(): void
    {
        $connector = new ModelConnector();
        $handler = new ModelHandler($connector);
        
        $request = [
            'code' => '<?php echo $_GET["input"]; ?>',
            'model' => 'gpt-4',
            'context' => ['analysis_type' => 'security']
        ];
        
        $response = $handler->handleSecurityAnalysis($request);
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI handler response', $response->getContent());
    }
    
    /**
     * Test performance optimization handling
     * 
     * @roadmap MCP-AI-02
     * @test MCP-AI-02-TEST
     */
    public function test_performance_optimization_handling(): void
    {
        $connector = new ModelConnector();
        $handler = new ModelHandler($connector);
        
        $request = [
            'code' => '<?php foreach($items as $item) { process($item); } ?>',
            'model' => 'gpt-4',
            'context' => ['analysis_type' => 'performance']
        ];
        
        $response = $handler->handlePerformanceOptimization($request);
        
        $this->assertInstanceOf(ModelResponse::class, $response);
        $this->assertStringContainsString('Mock AI handler response', $response->getContent());
    }
    
    /**
     * Test handler response metadata
     * 
     * @roadmap MCP-AI-02
     * @test MCP-AI-02-TEST
     */
    public function test_handler_response_metadata(): void
    {
        $connector = new ModelConnector();
        $handler = new ModelHandler($connector);
        
        // Use a method that calls processRequest directly
        $request = ['logs' => 'Test logs', 'model' => 'gpt-4'];
        $response = $handler->handleLogAnalysis($request);
        
        $usage = $response->getUsage();
        $metadata = $response->getMetadata();
        
        $this->assertArrayHasKey('prompt_tokens', $usage);
        $this->assertArrayHasKey('completion_tokens', $usage);
        $this->assertArrayHasKey('total_tokens', $usage);
        $this->assertArrayHasKey('processing_time', $metadata);
        $this->assertArrayHasKey('confidence', $metadata);
        $this->assertArrayHasKey('handler_type', $metadata);
        $this->assertEquals('model_handler', $metadata['handler_type']);
    }
} 