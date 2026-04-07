<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Default roles for the application.
 *
 * This is mostly used for admin users, as client users are managed through the feature flags system.
 */
enum DefaultRole: string
{
    /**
     * Super admin role.
     *
     * This role has full access to the application and can perform any action.
     */
    case SUPER_ADMIN = 'super-admin';

    /**
     * Admin role.
     *
     * This role has limited access to the application and can only perform actions related to admin users.
     */
    case ADMIN = 'admin';

    /**
     * User role.
     *
     * This role has limited access to the application and can only perform actions related to regular users.
     */
    case USER = 'user';
}
