<?php

namespace App\Enums;
enum ActionType: string
{
    // General
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Restore = 'restore';

    // Assets/Accessories/Components/Licenses/Consumables
    case Checkout = 'checkout';
    case CheckinFrom = 'checkin from';
    case Requested = 'requested';
    case RequestCanceled = 'request canceled';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Audit = 'audit';
    case NoteAdded = 'note added';

    // Users
    case TwoFactorReset = '2FA reset';
    case Merged = 'merged';

    // Licenses
    case DeleteSeats = 'delete seats';
    case AddSeats = 'add seats';

    // File Uploads
    case Uploaded = 'uploaded';
    case UploadDeleted = 'upload deleted';
}