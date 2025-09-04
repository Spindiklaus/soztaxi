use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientTripController;

Route::get('/client-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientTrips']);