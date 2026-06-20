<?php
use App\Http\Controllers\API\{
    AuthController, UserController, MedicamentController,
    CategorieController, FournisseurController, StockController,
    VenteController, AchatController, AlerteController, RapportController
};
use Illuminate\Support\Facades\Route;

// Auth publique
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Resources
    Route::apiResource('users',        UserController::class);
    Route::apiResource('medicaments',  MedicamentController::class);
    Route::apiResource('categories',   CategorieController::class);
    Route::apiResource('fournisseurs', FournisseurController::class);
    Route::apiResource('ventes',       VenteController::class);
    Route::apiResource('achats',       AchatController::class);

    // Stock
    Route::get('/stock',              [StockController::class, 'index']);
    Route::post('/stock/mouvement',   [StockController::class, 'mouvement']);
    Route::get('/stock/mouvements',   [StockController::class, 'mouvements']);

    // Alertes
    Route::get('/alertes',            [AlerteController::class, 'index']);

    Route::post('/achats/{achat}/livraison', [AchatController::class, 'livraison']);

    // Rapports JSON
    Route::get('/rapports/dashboard',         [RapportController::class, 'dashboard']);
    Route::get('/rapports/ventes',            [RapportController::class, 'ventes']);
    Route::get('/rapports/top-produits',      [RapportController::class, 'topProduits']);

    // Exports PDF
    Route::get('/rapports/export-pdf',        [RapportController::class, 'exportPdf']);
    Route::get('/rapports/export-pdf-stock',  [RapportController::class, 'exportPdfStock']);

    // Exports Excel
    Route::get('/rapports/export-excel',          [RapportController::class, 'exportExcel']);
    Route::get('/rapports/export-excel-stock',    [RapportController::class, 'exportExcelStock']);
    Route::get('/rapports/export-excel-complet',  [RapportController::class, 'exportExcelComplet']);
});
