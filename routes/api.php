    <?php

// use App\Http\Controllers\Client\ChangePasswordController;

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// only authenticated users can access this endpoints
Route::middleware('auth:sanctum')->group(function () {    
});
Route::apiResource('orders', OrderController::class);
