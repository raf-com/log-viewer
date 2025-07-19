<?php

declare(strict_types=1);

namespace Tests\MCP\Tools;

use PHPUnit\Framework\TestCase;
use Acelle\Extra\LogViewer\MCP\Tools\GitHubConnector;

/**
 * Test class for GitHubConnector
 * 
 * @package Tests\MCP\Tools
 * @roadmap MCP-EXT-02-TEST
 * @test MCP-EXT-02-TEST
 * @security MCP-EXT-02-SEC
 * @simulation MCP-EXT-02-SIM
 * @qa MCP-EXT-02-QA
 */
class GitHubConnectorTest extends TestCase
{
    /**
     * Test GitHub connector initialization
     * 
     * @roadmap MCP-EXT-02
     * @test MCP-EXT-02-TEST
     */
    public function test_github_connector_initialization(): void
    {
        $connector = new GitHubConnector();
        
        $this->assertInstanceOf(GitHubConnector::class, $connector);
    }
    
    /**
     * Test pull request creation
     * 
     * @roadmap MCP-EXT-02
     * @test MCP-EXT-02-TEST
     */
    public function test_pull_request_creation(): void
    {
        $connector = new GitHubConnector();
        $data = [
            'title' => 'Test PR',
            'description' => 'Test description',
            'branch' => 'feature/test'
        ];
        
        $result = $connector->createPullRequest($data);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('pull_request', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('Test PR', $result['pull_request']['title']);
        $this->assertEquals('feature/test', $result['pull_request']['branch']);
    }
    
    /**
     * Test issue creation
     * 
     * @roadmap MCP-EXT-02
     * @test MCP-EXT-02-TEST
     */
    public function test_issue_creation(): void
    {
        $connector = new GitHubConnector();
        $data = [
            'title' => 'Test Issue',
            'description' => 'Test issue description',
            'labels' => ['bug', 'urgent']
        ];
        
        $result = $connector->createIssue($data);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('issue', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('Test Issue', $result['issue']['title']);
        $this->assertContains('bug', $result['issue']['labels']);
    }
    
    /**
     * Test CI/CD pipeline execution
     * 
     * @roadmap MCP-EXT-02
     * @test MCP-EXT-02-TEST
     */
    public function test_cicd_pipeline_execution(): void
    {
        $connector = new GitHubConnector();
        $data = [
            'branch' => 'master',
            'commit' => 'abc123def456'
        ];
        
        $result = $connector->runCICD($data);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('pipeline', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('master', $result['pipeline']['branch']);
        $this->assertEquals('running', $result['pipeline']['status']);
    }
    
    /**
     * Test repository information retrieval
     * 
     * @roadmap MCP-EXT-02
     * @test MCP-EXT-02-TEST
     */
    public function test_repository_info_retrieval(): void
    {
        $connector = new GitHubConnector();
        $repository = 'log-viewer';
        
        $result = $connector->getRepositoryInfo($repository);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('repository', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals($repository, $result['repository']['name']);
        $this->assertEquals('PHP', $result['repository']['language']);
    }
    
    /**
     * Test security scanning
     * 
     * @roadmap MCP-EXT-02
     * @test MCP-EXT-02-TEST
     */
    public function test_security_scanning(): void
    {
        $connector = new GitHubConnector();
        $options = ['scan_type' => 'dependency'];
        
        $result = $connector->performSecurityScan($options);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('security_scan', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('completed', $result['security_scan']['status']);
        $this->assertArrayHasKey('vulnerabilities', $result['security_scan']);
        $this->assertArrayHasKey('summary', $result['security_scan']);
    }
    
    /**
     * Test configuration retrieval
     * 
     * @roadmap MCP-EXT-02
     * @test MCP-EXT-02-TEST
     */
    public function test_configuration_retrieval(): void
    {
        $connector = new GitHubConnector();
        
        $config = $connector->getConfiguration();
        
        $this->assertIsArray($config);
        $this->assertEquals('GitHub', $config['platform']);
        $this->assertEquals('1.0.0', $config['version']);
        $this->assertArrayHasKey('capabilities', $config);
        $this->assertArrayHasKey('settings', $config);
        $this->assertTrue($config['settings']['api_enabled']);
        $this->assertTrue($config['settings']['security_scanning']);
    }
} 