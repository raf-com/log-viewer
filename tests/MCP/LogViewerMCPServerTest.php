<?php

declare(strict_types=1);

namespace Tests\MCP;

use PHPUnit\Framework\TestCase;
use Acelle\Extra\LogViewer\MCP\LogViewerMCPServer;
use MCP\Server;
use MCP\Capability;

/**
 * Test class for LogViewerMCPServer
 * 
 * @package Tests\MCP
 * @roadmap MCP-CORE-01-TEST
 * @test MCP-CORE-01-TEST
 * @security MCP-CORE-01-SEC
 * @simulation MCP-CORE-01-SIM
 * @qa MCP-CORE-01-QA
 */
class LogViewerMCPServerTest extends TestCase
{
    /**
     * Test server initialization
     * 
     * @roadmap MCP-CORE-01
     * @test MCP-CORE-01-TEST
     */
    public function test_server_initialization(): void
    {
        $server = new LogViewerMCPServer();
        
        $this->assertInstanceOf(Server::class, $server);
        $this->assertInstanceOf(LogViewerMCPServer::class, $server);
    }
    
    /**
     * Test capability registration
     * 
     * @roadmap MCP-CORE-02
     * @test MCP-CORE-02-TEST
     */
    public function test_capability_registration(): void
    {
        $server = new LogViewerMCPServer();
        
        // Test that capabilities are registered
        $reflection = new \ReflectionClass($server);
        $capabilitiesProperty = $reflection->getProperty('capabilities');
        $capabilitiesProperty->setAccessible(true);
        
        $capabilities = $capabilitiesProperty->getValue($server);
        
        $this->assertIsArray($capabilities);
        $this->assertGreaterThan(0, count($capabilities));
    }
    
    /**
     * Test protocol configuration
     * 
     * @roadmap MCP-CORE-03
     * @test MCP-CORE-03-TEST
     */
    public function test_protocol_configuration(): void
    {
        $server = new LogViewerMCPServer();
        
        // Test that protocols are configured
        $reflection = new \ReflectionClass($server);
        $protocolsProperty = $reflection->getProperty('protocols');
        $protocolsProperty->setAccessible(true);
        
        $protocols = $protocolsProperty->getValue($server);
        
        $this->assertIsArray($protocols);
        $this->assertGreaterThan(0, count($protocols));
    }
    
    /**
     * Test authentication setup
     * 
     * @roadmap MCP-CORE-04
     * @test MCP-CORE-04-TEST
     */
    public function test_authentication_setup(): void
    {
        $server = new LogViewerMCPServer();
        
        // Test that authentication is configured
        $reflection = new \ReflectionClass($server);
        $authProperty = $reflection->getProperty('authentication');
        $authProperty->setAccessible(true);
        
        $authentication = $authProperty->getValue($server);
        
        $this->assertIsArray($authentication);
        $this->assertArrayHasKey('client_id', $authentication);
        $this->assertArrayHasKey('client_secret', $authentication);
        $this->assertArrayHasKey('redirect_uri', $authentication);
        $this->assertArrayHasKey('scopes', $authentication);
        $this->assertEquals('mock_client_id', $authentication['client_id']);
    }
} 