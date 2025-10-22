<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Les endpoints REST du gestionnaire de tâches ont été retirés : la surface
| d’attaque exposait des données sans authentification et l’application
| n’en a finalement pas besoin. On conserve le fichier pour d’éventuelles
| intégrations futures, mais aucune route n’est enregistrée pour le moment.
|
*/

Route::post('telegram/webhook', [App\Http\Controllers\TelegramWebhookController::class, 'handleWebhook'])
    ->name('api.telegram.webhook');
