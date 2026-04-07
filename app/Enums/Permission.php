<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum Permission
 *
 * Defines all the granular permissions available in the application.
 * This ensures type safety and avoids hardcoded strings throughout the codebase.
 */
enum Permission: string
{
    // Admin panel access
    case ADMIN_ACCESS = 'admin.access';

    // User Management (Normal users)
    case USER_VIEW = 'user.view';
    case USER_CREATE = 'user.create';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';

    // Admin & Role Management
    case ADMIN_CREATE = 'admin.create';
    case ROLE_ASSIGN = 'role.assign';

    // Content management (Documents)
    case DOCUMENT_ALL = 'document.*';
    case DOCUMENT_READ = 'document.read';
    case DOCUMENT_DOWNLOAD = 'document.download';

    // Content management (Categories & Videos)
    case CATEGORY_ALL = 'category.*';
    case VIDEO_ALL = 'video.*';

    // Front-end features
    case FAVORITE_ALL = 'favorite.*';
    case SEARCH_ALL = 'search.*';
}
