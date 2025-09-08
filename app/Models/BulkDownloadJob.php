<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\BulkDownloadStatusEnum;

class BulkDownloadJob extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'status',
        'format',
        'total_files',
        'processed_files',
        'file_path',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => BulkDownloadStatusEnum::class,
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the bulk download job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
