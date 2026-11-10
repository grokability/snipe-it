<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AuditAssetTool;
use App\Mcp\Tools\CheckinAssetTool;
use App\Mcp\Tools\CheckoutAssetTool;
use App\Mcp\Tools\CreateUserTool;
use App\Mcp\Tools\DeleteAssetTool;
use App\Mcp\Tools\DeleteUserTool;
use App\Mcp\Tools\ListAssetsTool;
use App\Mcp\Tools\ListUsersTool;
use App\Mcp\Tools\ShowAssetTool;
use App\Mcp\Tools\ShowUserTool;
use App\Mcp\Tools\UpdateAssetTool;
use App\Mcp\Tools\UpdateUserTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Snipe-IT MCP Server')]
#[Version('0.0.1')]
#[Instructions('This server allows you to interact with the Snipe-IT asset management database. You can list, view, check out, and check in assets.')]
class SnipeMCPServer extends Server
{
    protected array $tools = [
        ShowAssetTool::class,
        ListAssetsTool::class,
        CheckoutAssetTool::class,
        CheckinAssetTool::class,
        UpdateAssetTool::class,
        DeleteAssetTool::class,
        AuditAssetTool::class,
        ListUsersTool::class,
        ShowUserTool::class,
        CreateUserTool::class,
        UpdateUserTool::class,
        DeleteUserTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
