<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Old static dirs
        $oldDirs = [
            1 => 'products',
            2 => 'categories',
            3 => 'blog_posts',
        ];
        // New gallery dirs (from config)
        $newDirs = config('files.dirs', [
            1 => 'productgallery',
            2 => 'categorygallery',
            3 => 'bloggallery',
        ]);

        $conn = Schema::getConnection();
        if (!$conn->getSchemaBuilder()->hasTable('documents')) {
            return;
        }

        $docs = $conn->table('documents')->select('id', 'type', 'name')->get();
        foreach ($docs as $doc) {
            $typeId = (int)$doc->type;
            $old = public_path('upload/'.($oldDirs[$typeId] ?? ''));
            $new = public_path('upload/'.($newDirs[$typeId] ?? ''));
            if (!$old || !$new) continue;
            $oldFile = $old.'/'.$doc->name;
            $newFile = $new.'/'.$doc->name;
            if (is_file($oldFile) && !is_file($newFile)) {
                if (!is_dir($new)) {
                    @mkdir($new, 0777, true);
                }
                @rename($oldFile, $newFile);
            }
        }
    }

    public function down(): void
    {
        // No-op: we won't move files back automatically
    }
};


