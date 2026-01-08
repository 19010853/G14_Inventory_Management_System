<?php

namespace App\AI;

use Illuminate\Support\Facades\Log;

class AIPermissionGuard
{
    /**
     * Permission mapping: intent => required permissions (any one is enough)
     */
    private static array $permissionMap = [
        'product' => ['product.menu', 'all.product'],
        'purchase' => ['purchase.menu', 'all.purchase'],
        'sale' => ['sale.menu', 'all.sale'],
        'transfer' => ['transfers.menu', 'all.transfers'],
        'warehouse' => ['warehouse.menu', 'all.warehouse'],
        'brand' => ['brand.all', 'all.brand'],
        'supplier' => ['supplier.menu', 'all.supplier'],
        'customer' => ['customer.menu', 'all.customer'],
        'category' => ['all.category'],
        'report' => ['reports.all'],
        'role_permission' => ['role_and_permission.all'],
    ];

    /**
     * Authorize user for intent - throws 403 if no permission
     * 
     * @param mixed $user
     * @param string $intent
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public static function authorize($user, string $intent): void
    {
        // General intent doesn't need permission check
        if ($intent === 'general') {
            return;
        }

        // If intent not in map, allow (for safety)
        if (!isset(self::$permissionMap[$intent])) {
            Log::warning("Unknown intent in permission guard: {$intent}");
            return;
        }

        $requiredPermissions = self::$permissionMap[$intent];

        // Check if user has at least one required permission
        foreach ($requiredPermissions as $permission) {
            if ($user->can($permission)) {
                return; // User has permission, allow
            }
        }

        // No permission found - deny access
        abort(403, 'You do not have permission to access this information.');
    }
}
