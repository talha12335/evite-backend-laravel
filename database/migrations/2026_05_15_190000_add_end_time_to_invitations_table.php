<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEndTimeToInvitationsTable extends Migration
{
    public function up()
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('end_time')->nullable()->after('time');
        });
    }

    public function down()
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('end_time');
        });
    }
}
