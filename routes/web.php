<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\ClientController;
use App\Controllers\CompanySettingController;
use App\Controllers\DashboardController;
use App\Controllers\ExpenseController;
use App\Controllers\InvoiceController;
use App\Controllers\PaymentController;
use App\Controllers\ProcurementController;
use App\Controllers\ProductController;
use App\Controllers\QuoteController;
use App\Controllers\ReportController;
use App\Controllers\ServiceController;
use App\Controllers\StockController;
use App\Controllers\SupplierController;
use App\Controllers\UnitController;
use App\Controllers\ActivityLogController;
use App\Controllers\UserController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CaisseMiddleware;
use App\Middleware\CommercialMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\StockManagerMiddleware;

$router->get('/', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

$router->get('/settings/company', [CompanySettingController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/settings/company/update', [CompanySettingController::class, 'update'], [AdminMiddleware::class]);
$router->get('/users', [UserController::class, 'index'], [AdminMiddleware::class]);
$router->get('/users/create', [UserController::class, 'create'], [AdminMiddleware::class]);
$router->post('/users/store', [UserController::class, 'store'], [AdminMiddleware::class]);
$router->get('/users/edit', [UserController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/users/update', [UserController::class, 'update'], [AdminMiddleware::class]);
$router->post('/users/delete', [UserController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/clients', [ClientController::class, 'index'], [CaisseMiddleware::class]);
$router->get('/clients/create', [ClientController::class, 'create'], [CaisseMiddleware::class]);
$router->post('/clients/store', [ClientController::class, 'store'], [CaisseMiddleware::class]);
$router->get('/clients/edit', [ClientController::class, 'edit'], [CaisseMiddleware::class]);
$router->post('/clients/update', [ClientController::class, 'update'], [CaisseMiddleware::class]);
$router->post('/clients/delete', [ClientController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/suppliers', [SupplierController::class, 'index'], [StockManagerMiddleware::class]);
$router->get('/suppliers/create', [SupplierController::class, 'create'], [StockManagerMiddleware::class]);
$router->post('/suppliers/store', [SupplierController::class, 'store'], [StockManagerMiddleware::class]);
$router->get('/suppliers/edit', [SupplierController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/suppliers/update', [SupplierController::class, 'update'], [AdminMiddleware::class]);
$router->post('/suppliers/delete', [SupplierController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/categories', [CategoryController::class, 'index'], [StockManagerMiddleware::class]);
$router->get('/categories/create', [CategoryController::class, 'create'], [StockManagerMiddleware::class]);
$router->post('/categories/store', [CategoryController::class, 'store'], [StockManagerMiddleware::class]);
$router->get('/categories/edit', [CategoryController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/categories/update', [CategoryController::class, 'update'], [AdminMiddleware::class]);
$router->post('/categories/delete', [CategoryController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/units', [UnitController::class, 'index'], [StockManagerMiddleware::class]);
$router->get('/units/create', [UnitController::class, 'create'], [StockManagerMiddleware::class]);
$router->post('/units/store', [UnitController::class, 'store'], [StockManagerMiddleware::class]);
$router->get('/units/edit', [UnitController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/units/update', [UnitController::class, 'update'], [AdminMiddleware::class]);
$router->post('/units/delete', [UnitController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/products', [ProductController::class, 'index'], [StockManagerMiddleware::class]);
$router->get('/products/create', [ProductController::class, 'create'], [StockManagerMiddleware::class]);
$router->post('/products/store', [ProductController::class, 'store'], [StockManagerMiddleware::class]);
$router->get('/products/edit', [ProductController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/products/update', [ProductController::class, 'update'], [AdminMiddleware::class]);
$router->post('/products/delete', [ProductController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/services', [ServiceController::class, 'index'], [StockManagerMiddleware::class]);
$router->get('/services/create', [ServiceController::class, 'create'], [StockManagerMiddleware::class]);
$router->post('/services/store', [ServiceController::class, 'store'], [StockManagerMiddleware::class]);
$router->get('/services/edit', [ServiceController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/services/update', [ServiceController::class, 'update'], [AdminMiddleware::class]);
$router->post('/services/delete', [ServiceController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/stock', [StockController::class, 'index'], [StockManagerMiddleware::class]);
$router->post('/stock/adjust', [StockController::class, 'adjust'], [StockManagerMiddleware::class]);

$router->get('/procurements', [ProcurementController::class, 'index'], [StockManagerMiddleware::class]);
$router->get('/procurements/create', [ProcurementController::class, 'create'], [StockManagerMiddleware::class]);
$router->post('/procurements/store', [ProcurementController::class, 'store'], [StockManagerMiddleware::class]);
$router->get('/procurements/edit', [ProcurementController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/procurements/update', [ProcurementController::class, 'update'], [AdminMiddleware::class]);
$router->get('/procurements/show', [ProcurementController::class, 'show'], [StockManagerMiddleware::class]);
$router->post('/procurements/receive', [ProcurementController::class, 'receive'], [AdminMiddleware::class]);
$router->post('/procurements/pay', [ProcurementController::class, 'pay'], [StockManagerMiddleware::class]);
$router->post('/procurements/cancel', [ProcurementController::class, 'cancel'], [AdminMiddleware::class]);
$router->post('/procurements/delete', [ProcurementController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/quotes', [QuoteController::class, 'index'], [CommercialMiddleware::class]);
$router->get('/quotes/create', [QuoteController::class, 'create'], [CommercialMiddleware::class]);
$router->post('/quotes/store', [QuoteController::class, 'store'], [CommercialMiddleware::class]);
$router->get('/quotes/show', [QuoteController::class, 'show'], [CommercialMiddleware::class]);
$router->post('/quotes/convert', [QuoteController::class, 'convert'], [CommercialMiddleware::class]);
$router->post('/quotes/cancel', [QuoteController::class, 'cancel'], [AdminMiddleware::class]);
$router->get('/quotes/pdf', [QuoteController::class, 'pdf'], [CommercialMiddleware::class]);

$router->get('/invoices', [InvoiceController::class, 'index'], [CommercialMiddleware::class]);
$router->get('/invoices/create', [InvoiceController::class, 'create'], [CommercialMiddleware::class]);
$router->post('/invoices/store', [InvoiceController::class, 'store'], [CommercialMiddleware::class]);
$router->get('/invoices/show', [InvoiceController::class, 'show'], [CommercialMiddleware::class]);
$router->post('/invoices/validate', [InvoiceController::class, 'validate'], [CommercialMiddleware::class]);
$router->post('/invoices/cancel', [InvoiceController::class, 'cancel'], [AdminMiddleware::class]);
$router->get('/invoices/pdf', [InvoiceController::class, 'pdf'], [CommercialMiddleware::class]);

$router->get('/payments', [PaymentController::class, 'index'], [CommercialMiddleware::class]);
$router->get('/payments/create', [PaymentController::class, 'create'], [CommercialMiddleware::class]);
$router->post('/payments/store', [PaymentController::class, 'store'], [CommercialMiddleware::class]);

$router->get('/expenses', [ExpenseController::class, 'index'], [CaisseMiddleware::class]);
$router->get('/expenses/create', [ExpenseController::class, 'create'], [CaisseMiddleware::class]);
$router->post('/expenses/store', [ExpenseController::class, 'store'], [CaisseMiddleware::class]);
$router->post('/expenses/suppliers/store', [ExpenseController::class, 'storeSupplier'], [CaisseMiddleware::class]);
$router->get('/expenses/show', [ExpenseController::class, 'show'], [CaisseMiddleware::class]);
$router->get('/expenses/edit', [ExpenseController::class, 'edit'], [CaisseMiddleware::class]);
$router->post('/expenses/update', [ExpenseController::class, 'update'], [CaisseMiddleware::class]);
$router->post('/expenses/pay', [ExpenseController::class, 'pay'], [CaisseMiddleware::class]);
$router->post('/expenses/delete', [ExpenseController::class, 'delete'], [AdminMiddleware::class]);

$router->get('/reports', [ReportController::class, 'index'], [CaisseMiddleware::class]);

$router->get('/activity-logs', [ActivityLogController::class, 'index'], [AdminMiddleware::class]);
