<?php

use App\Mcp\Servers\SnipeMCPServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();
Mcp::web('/mcp/snipe-it', SnipeMCPServer::class)->middleware(['auth:api', 'api-throttle:api']);
