<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->dateTime('transaction_date')->nullable(false);
            $table->dateTime('value_date')->nullable(false);
            $table->string('concept',256)->nullable();
            $table->decimal('import',10,2)->nullable(false);
            $table->decimal('balance', 10,2)->nullable(false);

            $table->dateTime('create_at')->default(DB::raw('NOW()'));
            $table->dateTime('update_at')->default(DB::raw('NOW()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            Schema::dropIfExists('transactions');
        });
    }
}
