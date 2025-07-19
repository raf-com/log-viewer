<?php

declare(strict_types=1);

namespace Acelle\Extra\LogViewer\MCP\Protocol;

use MCP\Protocol\ProtocolHandler;

/**
 * LogViewer Protocol Handler for MCP v1
 * 
 * @package Acelle\Extra\LogViewer\MCP\Protocol
 * @roadmap MCP-CORE-03
 * @test MCP-CORE-03-TEST
 * @security MCP-CORE-03-SEC
 * @simulation MCP-CORE-03-SIM
 * @qa MCP-CORE-03-QA
 */
class LogViewerProtocolHandler extends ProtocolHandler
{
    /**
     * Handle protocol message
     * 
     * @param array $message
     * @return array
     */
    public function handleMessage(array $message): array
    {
        return [
            'status' => 'success',
            'data' => 'Mock protocol handler response',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
} 