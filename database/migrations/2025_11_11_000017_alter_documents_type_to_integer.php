<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedSmallInteger('type_int')->default(0)->after('id');
        });
        // Migrate existing string type values to integers based on config
        $map = config('files.types', []);
        $dirs = array_flip(config('files.dirs', [])); // dir => id
        $conn = Schema::getConnection();
        $docs = $conn->table('documents')->select('id', 'type')->get();
        foreach ($docs as $doc) {
            $id = 0;
            if (isset($map[$doc->type])) {
                $id = (int)$map[$doc->type];
            } elseif (isset($dirs[$doc->type])) {
                $id = (int)$dirs[$doc->type];
            }
            $conn->table('documents')->where('id', $doc->id)->update(['type_int' => $id]);
        }
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->renameColumn('type_int', 'type');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('type_str')->nullable()->after('id');
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['type','parent']);
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->renameColumn('type_str', 'type');
        });
    }
};


