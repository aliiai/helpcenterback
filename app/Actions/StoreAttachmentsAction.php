<?php

namespace App\Actions;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StoreAttachmentsAction
{
    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, Attachment>
     */
    public function handle(Model $model, array $files, string $directory): array
    {
        $attachments = [];

        foreach ($files as $file) {
            $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $filename, 'public');

            $attachments[] = $model->attachments()->create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => (string) $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return $attachments;
    }
}
