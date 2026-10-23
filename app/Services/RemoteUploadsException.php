<?php

namespace App\Services;

use RuntimeException;

/**
 * Raised when S3 hosted uploads cannot be copied (or checked) for a backup.
 */
class RemoteUploadsException extends RuntimeException {}
