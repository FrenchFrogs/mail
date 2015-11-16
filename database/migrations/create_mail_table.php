<?php

use FrenchFrogs\Laravel\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Models\Business\Mail;

class CreateMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $this->down();

        /**
         *  Creation de la table Mail_status
         */
        Schema::create('mail_status', function(Blueprint $table) {
            $table->string('mail_status_id', 32)->primary();
            $table->string('name');
        });

        /**
         *  Creation de la table Mail_version
         */
        Schema::create('mail_version', function(Blueprint $table) {
            $table->binaryUuid('mail_version_id')->primary();
            $table->string('name')->nullable();
            $table->integer('version_number')->unsigned()->default(1);
            $table->string('controller');
            $table->string('action');
            $table->string('view_hash')->nullable();
            $table->string('view_name')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });

        /**
         *  Creation de la table Mail
         */
        Schema::create('mail', function(Blueprint $table) {
            $table->binaryUuid('mail_id')->primary();
            $table->string('mail_status_id');
            $table->binaryUuid('mail_version_id');
            $table->string('message')->nullable();
            $table->text('args')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();


            $table->foreign('mail_status_id')->references('mail_status_id')->on('mail_status');
            $table->foreign('mail_version_id')->references('mail_version_id')->on('mail_version');
        });

        /**
         *
         * Création des status
         *
         */
        $data = [
            ['mail_status_id' => Mail::STATUS_FILED, 'name' => 'Déposé'],
            ['mail_status_id' => Mail::STATUS_SENT, 'name' => 'Envoyé'],
            ['mail_status_id' => Mail::STATUS_ERROR, 'name' => 'Erreur envoie'],
            ['mail_status_id' => Mail::STATUS_BOUNCED, 'name' => 'Bounced'],
            ['mail_status_id' => Mail::STATUS_OPENED, 'name' => 'Ouvert'],
            ['mail_status_id' => Mail::STATUS_BLOCKED, 'name' => 'Email bloqué'],
            ['mail_status_id' => Mail::STATUS_SENDING, 'name' => 'En cours d\'envoie']
        ];
        \DB::table('mail_status')->insert($data);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_status');
        Schema::dropIfExists('mail_version');
        Schema::dropIfExists('mail');
    }
}
