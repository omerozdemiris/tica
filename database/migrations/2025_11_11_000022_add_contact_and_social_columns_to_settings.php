<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'email')) $table->string('email')->nullable()->after('title');
            if (!Schema::hasColumn('settings', 'phone')) $table->string('phone')->nullable()->after('email');
            if (!Schema::hasColumn('settings', 'instagram')) $table->string('instagram')->nullable()->after('phone');
            if (!Schema::hasColumn('settings', 'twitter')) $table->string('twitter')->nullable()->after('instagram');
            if (!Schema::hasColumn('settings', 'facebook')) $table->string('facebook')->nullable()->after('twitter');
            if (!Schema::hasColumn('settings', 'youtube')) $table->string('youtube')->nullable()->after('facebook');
            if (!Schema::hasColumn('settings', 'linkedin')) $table->string('linkedin')->nullable()->after('youtube');
            if (!Schema::hasColumn('settings', 'whatsapp')) $table->string('whatsapp')->nullable()->after('linkedin');
        });
        // Migrate existing JSON values if present
        $row = DB::table('settings')->first();
        if ($row) {
            $contact = json_decode($row->contact ?? 'null', true) ?: [];
            $socials = json_decode($row->socials ?? 'null', true) ?: [];
            DB::table('settings')->update([
                'email' => $contact['email'] ?? ($row->email ?? null),
                'phone' => $contact['phone'] ?? ($row->phone ?? null),
                'instagram' => $socials['instagram'] ?? ($row->instagram ?? null),
                'twitter' => $socials['twitter'] ?? ($row->twitter ?? null),
                'facebook' => $socials['facebook'] ?? ($row->facebook ?? null),
                'youtube' => $socials['youtube'] ?? ($row->youtube ?? null),
                'linkedin' => $socials['linkedin'] ?? ($row->linkedin ?? null),
                'whatsapp' => $socials['whatsapp'] ?? ($row->whatsapp ?? null),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['email','phone','instagram','twitter','facebook','youtube','linkedin','whatsapp'] as $col) {
                if (Schema::hasColumn('settings', $col)) $table->dropColumn($col);
            }
        });
    }
};


