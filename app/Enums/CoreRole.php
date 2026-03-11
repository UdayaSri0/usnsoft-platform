<?php

namespace App\Enums;

enum CoreRole: string
{
    case User = 'user';
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Editor = 'editor';
    case ProductManager = 'product_manager';
    case SalesManager = 'sales_manager';
    case Developer = 'developer';
    case SupportOperations = 'support_operations';

    public function isInternal(): bool
    {
        return $this !== self::User;
    }

    /**
     * @return list<self>
     */
    public static function internalRoles(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $role): bool => $role->isInternal(),
        ));
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
