<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccessTokenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('access_token', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('access_token')->unique();
			$table->string('refresh_token')->unique();
			$table->integer('user_id')->unsigned();
			$table->integer('expire_time');
			$table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('access_token');
	}

}
