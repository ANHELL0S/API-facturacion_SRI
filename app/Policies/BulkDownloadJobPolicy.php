<?php

namespace App\Policies;

use App\Models\BulkDownloadJob;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BulkDownloadJobPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BulkDownloadJob $bulkDownloadJob): bool
    {
        return $user->id === $bulkDownloadJob->user_id;
    }
}
