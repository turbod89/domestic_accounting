<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name',128)->nullable();
            $table->integer('id_user_owner')->nullable(false);

            $table->dateTime('create_at')->default(DB::raw('NOW()'));
            $table->dateTime('update_at')->default(DB::raw('NOW()'));

            $table->index('id_user_owner');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
        Schema::dropIfExists('accounts');
    }
}
