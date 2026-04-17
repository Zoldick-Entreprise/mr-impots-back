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
    case DOCUMENT_READ = 'document.read';
    case DOCUMENT_DOWNLOAD = 'document.download';

    // Content management (Categories & Videos)
    case CATEGORY_VIEW = 'category.view';
    case CATEGORY_CREATE = 'category.create';
    case CATEGORY_UPDATE = 'category.update';
    case CATEGORY_DELETE = 'category.delete';

    case VIDEO_VIEW = 'video.view';
    case VIDEO_CREATE = 'video.create';
    case VIDEO_UPDATE = 'video.update';
    case VIDEO_DELETE = 'video.delete';

    // Front-end features
    case FAVORITE_ALL = 'favorite.*';
    case SEARCH_ALL = 'search.*';
}
