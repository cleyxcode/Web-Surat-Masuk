<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Archive extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'document_date',
        'document_number',
        'uploaded_by',
        'download_count',
        'is_public',
        'tags',
    ];

    protected $casts = [
        'document_date' => 'date',
        'is_public' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Get the user who uploaded this archive
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    /**
     * Available categories
     */
    public static function getCategories()
    {
        return [
            'Surat Masuk' => 'Surat Masuk',
            'Surat Keluar' => 'Surat Keluar',
            'Laporan' => 'Laporan',
            'Proposal' => 'Proposal',
            'SK (Surat Keputusan)' => 'SK (Surat Keputusan)',
            'Memo' => 'Memo',
            'Undangan' => 'Undangan',
            'Notulen' => 'Notulen',
            'Program' => 'Program',
            'Lainnya' => 'Lainnya',
        ];
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%")
              ->orWhere('document_number', 'like', "%{$search}%")
              ->orWhere('tags', 'like', "%{$search}%");
        });
    }
}
