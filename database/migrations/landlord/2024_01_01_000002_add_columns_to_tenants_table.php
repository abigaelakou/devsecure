<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('domain')->nullable()->after('name');
            $table->string('logo')->nullable()->after('domain');
            $table->string('adresse')->nullable()->after('logo');
            $table->string('ville')->nullable()->after('adresse');
            $table->string('pays')->default('CI')->after('ville');
            $table->string('email_contact')->nullable()->after('pays');
            $table->string('telephone')->nullable()->after('email_contact');
            $table->enum('plan', ['gratuit','standard','premium'])->default('gratuit')->after('telephone');
            $table->integer('max_eleves')->default(200)->after('plan');
            $table->integer('max_enseignants')->default(20)->after('max_eleves');
            $table->boolean('actif')->default(true)->after('max_enseignants');
            $table->timestamp('essai_expire_le')->nullable()->after('actif');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'name','domain','logo','adresse','ville','pays',
                'email_contact','telephone','plan','max_eleves',
                'max_enseignants','actif','essai_expire_le',
            ]);
        });
    }
};