<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::as('api')->apiResource('projects', ProjectController::class);

Route::as('api')->apiResource('projects.tasks', TaskController::class);
