<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Export
 */
class ExportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $downloadUrl = null;

        if ($this->status === 'completed' && ! empty($this->storage_path)) {
            $disk = config('filesystems.default', 'local');
            try {
                if (Storage::disk($disk)->providesTemporaryUrls()) {
                    $downloadUrl = Storage::disk($disk)->temporaryUrl(
                        $this->storage_path,
                        now()->addMinutes(30)
                    );
                } else {
                    $downloadUrl = Storage::disk($disk)->url($this->storage_path);
                }
            } catch (\Throwable $e) {
                $downloadUrl = Storage::disk($disk)->url($this->storage_path);
            }
        }

        $response = [
            'export_id' => $this->id,
            'status' => $this->status,
        ];

        if ($this->status === 'completed' && $downloadUrl) {
            $response['download_url'] = $downloadUrl;
        }

        return $response;
    }
}
