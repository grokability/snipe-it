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
    case ForceCheckin = 'force checkin';
    case Consumed = 'consumed';
    case Requested = 'requested';
    case RequestCanceled = 'request canceled';
    // Note: no RequestFulfilled - fulfillment piggybacks on the
    // existing Checkout actionlog via CheckoutRequest::checkout_actionlog_id,
    // so writing a separate row here would double the item's history.
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Audit = 'audit';
    case NoteAdded = 'note added';
    case QuantityAdjust = 'adjusted quantity';

    // Users
    case TwoFactorReset = '2FA reset';
    case Merged = 'merged';
    case TokenRevoked = 'token revoked';
    case TokenUnrevoked = 'token unrevoked';
    case Impersonated = 'impersonated';
    case StoppedImpersonating = 'stopped impersonating';

    // Licenses
    case DeleteSeats = 'delete seats';
    case AddSeats = 'add seats';

    // Maintenances
    case MaintenanceComplete = 'completed';

    // File Uploads
    case Uploaded = 'uploaded';
    case UploadDeleted = 'upload deleted';
}
