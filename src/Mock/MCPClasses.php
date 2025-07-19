<?php

declare(strict_types=1);

namespace MCP;

/**
 * Mock MCP Server class for testing
 */
class Server
{
    protected array $capabilities = [];
    protected array $protocols = [];
    protected array $authentication = [];
    
    public function __construct()
    {
        // Mock constructor
    }
    
    public function registerCapability(Capability $capability): void
    {
        $this->capabilities[] = $capability;
    }
    
    public function registerProtocol(string $name, $handler): void
    {
        $this->protocols[$name] = $handler;
    }
    
    public function configureOAuth2(array $config): void
    {
        $this->authentication = $config;
    }
}

/**
 * Mock MCP Capability class for testing
 */
class Capability
{
    private string $name;
    private string $description;
    private array $methods;
    
    public function __construct(string $name, string $description, array $methods = [])
    {
        $this->name = $name;
        $this->description = $description;
        $this->methods = $methods;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function getMethods(): array
    {
        return $this->methods;
    }
}

/**
 * Mock MCP Resource class for testing
 */
class Resource
{
    private string $uri;
    private string $mimeType;
    private string $content;
    
    public function __construct(string $uri, string $mimeType, string $content = '')
    {
        $this->uri = $uri;
        $this->mimeType = $mimeType;
        $this->content = $content;
    }
    
    public function getUri(): string
    {
        return $this->uri;
    }
    
    public function getMimeType(): string
    {
        return $this->mimeType;
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
}

namespace MCP\Tools;

use MCP\Capability;

/**
 * Mock MCP ToolConnector class for testing
 */
abstract class ToolConnector
{
    protected string $name;
    protected string $description;
    protected array $capabilities = [];
    
    public function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
    }
    
    protected function registerCapability(Capability $capability): void
    {
        $this->capabilities[] = $capability;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }
}

namespace MCP\Protocol;

/**
 * Mock MCP ProtocolHandler class for testing
 */
abstract class ProtocolHandler
{
    public function __construct()
    {
        // Mock constructor
    }
}

namespace MCP\AI;

/**
 * Mock MCP ModelConnectorInterface for testing
 */
interface ModelConnectorInterface
{
    public function generateCode(string $prompt, string $model = 'gpt-4', array $context = []): ModelResponse;
    public function analyzeCode(string $code, string $model = 'gpt-4', array $context = []): ModelResponse;
    public function generateDocumentation(string $code, string $model = 'gpt-4', array $context = []): ModelResponse;
}

/**
 * Mock MCP ModelHandlerInterface for testing
 */
interface ModelHandlerInterface
{
    public function handleCodeGeneration(array $request): ModelResponse;
    public function handleCodeAnalysis(array $request): ModelResponse;
    public function handleDocumentationGeneration(array $request): ModelResponse;
}

/**
 * Mock MCP ModelRequest class for testing
 */
class ModelRequest
{
    private array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public function getPrompt(): string
    {
        return $this->data['prompt'] ?? '';
    }
    
    public function getModel(): string
    {
        return $this->data['model'] ?? 'gpt-4';
    }
    
    public function getContext(): array
    {
        return $this->data['context'] ?? [];
    }
    
    public function getMaxTokens(): int
    {
        return $this->data['max_tokens'] ?? 4096;
    }
    
    public function getTemperature(): float
    {
        return $this->data['temperature'] ?? 0.7;
    }
}

/**
 * Mock MCP ModelResponse class for testing
 */
class ModelResponse
{
    private array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public function getContent(): string
    {
        return $this->data['content'] ?? '';
    }
    
    public function getModel(): string
    {
        return $this->data['model'] ?? '';
    }
    
    public function getUsage(): array
    {
        return $this->data['usage'] ?? [];
    }
    
    public function getMetadata(): array
    {
        return $this->data['metadata'] ?? [];
    }
} 