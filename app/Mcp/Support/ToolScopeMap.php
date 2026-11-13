<?php

namespace App\Mcp\Support;

use App\Enums\McpScope;
use App\Mcp\Tools;

/**
 * Declarative map of MCP tool class => required McpScope.
 *
 * The HasMcpScope trait reads this map to:
 *   1. Emit `_meta.securitySchemes` on each tool's `tools/list`
 *      entry so OpenAI / Anthropic MCP clients can learn which
 *      scope to request for each tool.
 *   2. Short-circuit a tool-level call with a scope_denied error
 *      when the acting token lacks the right scope.
 *   3. Attach `_meta["mcp/www_authenticate"]` to that error so a
 *      capable client can re-initiate authorization and ask for
 *      the missing scope specifically.
 *
 * Keeping the mapping centralized means a new tool only needs to
 * declare its scope here, and both metadata surfaces update at once.
 */
class ToolScopeMap
{
    /**
     * Returns the McpScope the given tool class requires, or null
     * if the tool is unmapped (should never happen in production,
     * but returning null rather than throwing avoids blowing up
     * tools/list for unknown classes during a staged rollout).
     */
    public static function for(string $toolClass): ?McpScope
    {
        return self::MAP[$toolClass] ?? null;
    }

    /**
     * @var array<class-string, McpScope>
     */
    public const MAP = [
        // mcp:read
        Tools\ListAccessoriesTool::class => McpScope::Read,
        Tools\ListAssetModelsTool::class => McpScope::Read,
        Tools\ListAssetNotesTool::class => McpScope::Read,
        Tools\ListAssetsTool::class => McpScope::Read,
        Tools\ListCategoriesTool::class => McpScope::Read,
        Tools\ListCompaniesTool::class => McpScope::Read,
        Tools\ListConsumablesTool::class => McpScope::Read,
        Tools\ListDepreciationsTool::class => McpScope::Read,
        Tools\ListHistoryTool::class => McpScope::Read,
        Tools\ListLicensesTool::class => McpScope::Read,
        Tools\ListLocationsTool::class => McpScope::Read,
        Tools\ListMaintenanceTypesTool::class => McpScope::Read,
        Tools\ListMaintenancesTool::class => McpScope::Read,
        Tools\ListManufacturersTool::class => McpScope::Read,
        Tools\ListStatusLabelsTool::class => McpScope::Read,
        Tools\ListSuppliersTool::class => McpScope::Read,
        Tools\ListUploadsTool::class => McpScope::Read,
        Tools\ListUsersTool::class => McpScope::Read,
        Tools\ShowAssetModelTool::class => McpScope::Read,
        Tools\ShowAssetTool::class => McpScope::Read,
        Tools\ShowCategoryTool::class => McpScope::Read,
        Tools\ShowCompanyTool::class => McpScope::Read,
        Tools\ShowConsumableTool::class => McpScope::Read,
        Tools\ShowDepreciationTool::class => McpScope::Read,
        Tools\ShowLicenseTool::class => McpScope::Read,
        Tools\ShowLocationTool::class => McpScope::Read,
        Tools\ShowManufacturerTool::class => McpScope::Read,
        Tools\ShowStatusLabelTool::class => McpScope::Read,
        Tools\ShowSupplierTool::class => McpScope::Read,
        Tools\ShowUserPermissionsTool::class => McpScope::Read,
        Tools\ShowUserTool::class => McpScope::Read,
        Tools\GetCurrentUserTool::class => McpScope::Read,
        Tools\GetUserAssetsTool::class => McpScope::Read,
        Tools\GetActivityLogTool::class => McpScope::Read,
        Tools\FindSimilarModelsTool::class => McpScope::Read,

        // mcp:write
        Tools\CreateAccessoryTool::class => McpScope::Write,
        Tools\CreateAssetModelTool::class => McpScope::Write,
        Tools\CreateAssetTool::class => McpScope::Write,
        Tools\CreateCategoryTool::class => McpScope::Write,
        Tools\CreateCompanyTool::class => McpScope::Write,
        Tools\CreateComponentTool::class => McpScope::Write,
        Tools\CreateConsumableTool::class => McpScope::Write,
        Tools\CreateDepartmentTool::class => McpScope::Write,
        Tools\CreateDepreciationTool::class => McpScope::Write,
        Tools\CreateLicenseTool::class => McpScope::Write,
        Tools\CreateLocationTool::class => McpScope::Write,
        Tools\CreateMaintenanceTool::class => McpScope::Write,
        Tools\CreateMaintenanceTypeTool::class => McpScope::Write,
        Tools\CreateManufacturerTool::class => McpScope::Write,
        Tools\CreateStatusLabelTool::class => McpScope::Write,
        Tools\CreateSupplierTool::class => McpScope::Write,
        Tools\CreateUserTool::class => McpScope::Write,
        Tools\UpdateAccessoryTool::class => McpScope::Write,
        Tools\UpdateAssetModelTool::class => McpScope::Write,
        Tools\UpdateAssetTool::class => McpScope::Write,
        Tools\UpdateCategoryTool::class => McpScope::Write,
        Tools\UpdateCompanyTool::class => McpScope::Write,
        Tools\UpdateComponentTool::class => McpScope::Write,
        Tools\UpdateConsumableTool::class => McpScope::Write,
        Tools\UpdateDepartmentTool::class => McpScope::Write,
        Tools\UpdateDepreciationTool::class => McpScope::Write,
        Tools\UpdateLicenseTool::class => McpScope::Write,
        Tools\UpdateLocationTool::class => McpScope::Write,
        Tools\UpdateManufacturerTool::class => McpScope::Write,
        Tools\UpdateProfileTool::class => McpScope::Write,
        Tools\UpdateStatusLabelTool::class => McpScope::Write,
        Tools\UpdateSupplierTool::class => McpScope::Write,
        Tools\UpdateUserTool::class => McpScope::Write,
        Tools\AuditAssetTool::class => McpScope::Write,
        Tools\AddAssetNoteTool::class => McpScope::Write,
        Tools\UploadAssetImageTool::class => McpScope::Write,
        Tools\Reset2FATool::class => McpScope::Write,
        Tools\SendPasswordResetTool::class => McpScope::Write,

        // mcp:delete
        Tools\DeleteAccessoryTool::class => McpScope::Delete,
        Tools\DeleteAssetModelTool::class => McpScope::Delete,
        Tools\DeleteAssetTool::class => McpScope::Delete,
        Tools\DeleteCategoryTool::class => McpScope::Delete,
        Tools\DeleteCompanyTool::class => McpScope::Delete,
        Tools\DeleteComponentTool::class => McpScope::Delete,
        Tools\DeleteConsumableTool::class => McpScope::Delete,
        Tools\DeleteDepartmentTool::class => McpScope::Delete,
        Tools\DeleteDepreciationTool::class => McpScope::Delete,
        Tools\DeleteLicenseTool::class => McpScope::Delete,
        Tools\DeleteLocationTool::class => McpScope::Delete,
        Tools\DeleteManufacturerTool::class => McpScope::Delete,
        Tools\DeleteStatusLabelTool::class => McpScope::Delete,
        Tools\DeleteSupplierTool::class => McpScope::Delete,
        Tools\DeleteUserTool::class => McpScope::Delete,
        Tools\RestoreAssetTool::class => McpScope::Delete,
        Tools\RestoreUserTool::class => McpScope::Delete,

        // mcp:checkout
        Tools\CheckoutAccessoryTool::class => McpScope::Checkout,
        Tools\CheckoutAssetTool::class => McpScope::Checkout,
        Tools\CheckoutComponentTool::class => McpScope::Checkout,
        Tools\CheckoutConsumableTool::class => McpScope::Checkout,
        Tools\CheckoutLicenseTool::class => McpScope::Checkout,
        Tools\OffboardUserTool::class => McpScope::Checkout,
        Tools\TransferAssetsTool::class => McpScope::Checkout,

        // mcp:checkin
        Tools\CheckinAccessoryTool::class => McpScope::Checkin,
        Tools\CheckinAssetTool::class => McpScope::Checkin,
        Tools\CheckinComponentTool::class => McpScope::Checkin,
        Tools\CheckinLicenseTool::class => McpScope::Checkin,

        // mcp:superadmin
        Tools\CreateGroupTool::class => McpScope::Superadmin,
        Tools\UpdateGroupTool::class => McpScope::Superadmin,
        Tools\DeleteGroupTool::class => McpScope::Superadmin,
        Tools\ListGroupsTool::class => McpScope::Superadmin,
        Tools\ShowGroupTool::class => McpScope::Superadmin,
    ];
}
