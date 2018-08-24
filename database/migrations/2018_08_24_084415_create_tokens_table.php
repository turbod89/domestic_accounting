<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tokens');
        Schema::create('tokens', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('type',32)->nullable(false);
            $table->string('value',128)->nullable(false);
            $table->integer('id_user')->nullable(true);
            $table->integer('id_api')->nullable(true);
            $table->dateTime('expire_at')->nullable(false);

            $table->dateTime('create_at')->default(DB::raw('NOW()'));
            $table->dateTime('update_at')->default(DB::raw('NOW()'));

            $table->index('type');
            $table->unique('value');
            $table->index('id_user');
            $table->index('id_api');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tokens', function (Blueprint $table) {
            //
        });
        Schema::dropIfExists('tokens');
    }
}
