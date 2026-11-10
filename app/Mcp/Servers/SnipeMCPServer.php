<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AuditAssetTool;
use App\Mcp\Tools\CheckinAccessoryTool;
use App\Mcp\Tools\CheckinAssetTool;
use App\Mcp\Tools\CheckinComponentTool;
use App\Mcp\Tools\CheckinLicenseTool;
use App\Mcp\Tools\CheckoutAccessoryTool;
use App\Mcp\Tools\CheckoutAssetTool;
use App\Mcp\Tools\CheckoutComponentTool;
use App\Mcp\Tools\CheckoutConsumableTool;
use App\Mcp\Tools\CheckoutLicenseTool;
use App\Mcp\Tools\CreateAccessoryTool;
use App\Mcp\Tools\CreateAssetModelTool;
use App\Mcp\Tools\CreateAssetTool;
use App\Mcp\Tools\CreateCategoryTool;
use App\Mcp\Tools\CreateCompanyTool;
use App\Mcp\Tools\CreateComponentTool;
use App\Mcp\Tools\CreateConsumableTool;
use App\Mcp\Tools\CreateDepartmentTool;
use App\Mcp\Tools\CreateDepreciationTool;
use App\Mcp\Tools\CreateGroupTool;
use App\Mcp\Tools\CreateLicenseTool;
use App\Mcp\Tools\CreateLocationTool;
use App\Mcp\Tools\CreateMaintenanceTool;
use App\Mcp\Tools\CreateManufacturerTool;
use App\Mcp\Tools\CreateStatusLabelTool;
use App\Mcp\Tools\CreateSupplierTool;
use App\Mcp\Tools\CreateUserTool;
use App\Mcp\Tools\DeleteAccessoryTool;
use App\Mcp\Tools\DeleteAssetModelTool;
use App\Mcp\Tools\DeleteAssetTool;
use App\Mcp\Tools\DeleteCategoryTool;
use App\Mcp\Tools\DeleteCompanyTool;
use App\Mcp\Tools\DeleteComponentTool;
use App\Mcp\Tools\DeleteConsumableTool;
use App\Mcp\Tools\DeleteDepartmentTool;
use App\Mcp\Tools\DeleteDepreciationTool;
use App\Mcp\Tools\DeleteGroupTool;
use App\Mcp\Tools\DeleteLicenseTool;
use App\Mcp\Tools\DeleteLocationTool;
use App\Mcp\Tools\DeleteManufacturerTool;
use App\Mcp\Tools\DeleteStatusLabelTool;
use App\Mcp\Tools\DeleteSupplierTool;
use App\Mcp\Tools\DeleteUserTool;
use App\Mcp\Tools\GetActivityLogTool;
use App\Mcp\Tools\GetCurrentUserTool;
use App\Mcp\Tools\GetUserAssetsTool;
use App\Mcp\Tools\ListAssetModelsTool;
use App\Mcp\Tools\ListAssetsTool;
use App\Mcp\Tools\ListCategoriesTool;
use App\Mcp\Tools\ListCompaniesTool;
use App\Mcp\Tools\ListConsumablesTool;
use App\Mcp\Tools\ListDepreciationsTool;
use App\Mcp\Tools\ListGroupsTool;
use App\Mcp\Tools\ListLicensesTool;
use App\Mcp\Tools\ListLocationsTool;
use App\Mcp\Tools\ListMaintenancesTool;
use App\Mcp\Tools\ListManufacturersTool;
use App\Mcp\Tools\ListStatusLabelsTool;
use App\Mcp\Tools\ListSuppliersTool;
use App\Mcp\Tools\ListUsersTool;
use App\Mcp\Tools\Reset2FATool;
use App\Mcp\Tools\RestoreAssetTool;
use App\Mcp\Tools\RestoreUserTool;
use App\Mcp\Tools\ShowAssetModelTool;
use App\Mcp\Tools\ShowAssetTool;
use App\Mcp\Tools\ShowCategoryTool;
use App\Mcp\Tools\ShowCompanyTool;
use App\Mcp\Tools\ShowConsumableTool;
use App\Mcp\Tools\ShowDepreciationTool;
use App\Mcp\Tools\ShowGroupTool;
use App\Mcp\Tools\ShowLicenseTool;
use App\Mcp\Tools\ShowLocationTool;
use App\Mcp\Tools\ShowManufacturerTool;
use App\Mcp\Tools\ShowStatusLabelTool;
use App\Mcp\Tools\ShowSupplierTool;
use App\Mcp\Tools\ShowUserTool;
use App\Mcp\Tools\UpdateAccessoryTool;
use App\Mcp\Tools\UpdateAssetModelTool;
use App\Mcp\Tools\UpdateAssetTool;
use App\Mcp\Tools\UpdateCategoryTool;
use App\Mcp\Tools\UpdateCompanyTool;
use App\Mcp\Tools\UpdateComponentTool;
use App\Mcp\Tools\UpdateConsumableTool;
use App\Mcp\Tools\UpdateDepartmentTool;
use App\Mcp\Tools\UpdateDepreciationTool;
use App\Mcp\Tools\UpdateGroupTool;
use App\Mcp\Tools\UpdateLicenseTool;
use App\Mcp\Tools\UpdateLocationTool;
use App\Mcp\Tools\UpdateManufacturerTool;
use App\Mcp\Tools\UpdateStatusLabelTool;
use App\Mcp\Tools\UpdateSupplierTool;
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
        // Assets
        ShowAssetTool::class,
        ListAssetsTool::class,
        CreateAssetTool::class,
        UpdateAssetTool::class,
        DeleteAssetTool::class,
        RestoreAssetTool::class,
        CheckoutAssetTool::class,
        CheckinAssetTool::class,
        AuditAssetTool::class,

        // Users
        ListUsersTool::class,
        ShowUserTool::class,
        CreateUserTool::class,
        UpdateUserTool::class,
        DeleteUserTool::class,
        RestoreUserTool::class,
        GetCurrentUserTool::class,
        GetUserAssetsTool::class,
        Reset2FATool::class,

        // Accessories
        CreateAccessoryTool::class,
        UpdateAccessoryTool::class,
        DeleteAccessoryTool::class,
        CheckoutAccessoryTool::class,
        CheckinAccessoryTool::class,

        // Components
        CreateComponentTool::class,
        UpdateComponentTool::class,
        DeleteComponentTool::class,
        CheckoutComponentTool::class,
        CheckinComponentTool::class,

        // Consumables
        ListConsumablesTool::class,
        ShowConsumableTool::class,
        CreateConsumableTool::class,
        UpdateConsumableTool::class,
        DeleteConsumableTool::class,
        CheckoutConsumableTool::class,

        // Licenses
        ListLicensesTool::class,
        ShowLicenseTool::class,
        CreateLicenseTool::class,
        UpdateLicenseTool::class,
        DeleteLicenseTool::class,
        CheckoutLicenseTool::class,
        CheckinLicenseTool::class,

        // Departments
        CreateDepartmentTool::class,
        UpdateDepartmentTool::class,
        DeleteDepartmentTool::class,

        // Companies
        ListCompaniesTool::class,
        ShowCompanyTool::class,
        CreateCompanyTool::class,
        UpdateCompanyTool::class,
        DeleteCompanyTool::class,

        // Categories
        ListCategoriesTool::class,
        ShowCategoryTool::class,
        CreateCategoryTool::class,
        UpdateCategoryTool::class,
        DeleteCategoryTool::class,

        // Manufacturers
        ListManufacturersTool::class,
        ShowManufacturerTool::class,
        CreateManufacturerTool::class,
        UpdateManufacturerTool::class,
        DeleteManufacturerTool::class,

        // Suppliers
        ListSuppliersTool::class,
        ShowSupplierTool::class,
        CreateSupplierTool::class,
        UpdateSupplierTool::class,
        DeleteSupplierTool::class,

        // Status Labels
        ListStatusLabelsTool::class,
        ShowStatusLabelTool::class,
        CreateStatusLabelTool::class,
        UpdateStatusLabelTool::class,
        DeleteStatusLabelTool::class,

        // Locations
        ListLocationsTool::class,
        ShowLocationTool::class,
        CreateLocationTool::class,
        UpdateLocationTool::class,
        DeleteLocationTool::class,

        // Asset Models
        ListAssetModelsTool::class,
        ShowAssetModelTool::class,
        CreateAssetModelTool::class,
        UpdateAssetModelTool::class,
        DeleteAssetModelTool::class,

        // Depreciations
        ListDepreciationsTool::class,
        ShowDepreciationTool::class,
        CreateDepreciationTool::class,
        UpdateDepreciationTool::class,
        DeleteDepreciationTool::class,

        // Groups
        ListGroupsTool::class,
        ShowGroupTool::class,
        CreateGroupTool::class,
        UpdateGroupTool::class,
        DeleteGroupTool::class,

        // Maintenance
        ListMaintenancesTool::class,
        CreateMaintenanceTool::class,

        // Activity Log
        GetActivityLogTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
