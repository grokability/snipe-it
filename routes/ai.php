<?php

use App\Mcp\Servers\SnipeMCPServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/snipe-it', SnipeMCPServer::class);
