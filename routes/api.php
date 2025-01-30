<?php

use App\Http\Controllers\ServerController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\Api\OptionGroupController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\PublisherController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SaleReturnController;
use App\Http\Controllers\Api\CustomerPaymentController;

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\DamagedItemController;

use App\Http\Controllers\Api\Reports\CustomerPaymentReportController;
use App\Http\Controllers\Api\Reports\SaleReportController;
use App\Http\Controllers\Api\Reports\PurchaseReportController;
use App\Http\Controllers\Api\Reports\StockReportController;
use App\Http\Controllers\Api\ProductAttributeController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */
Route::get('/check-server', [ServerController::class,'checkServer']);
Route::group([

    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth',

], function ($router) {

    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');
});

Route::middleware('JWT')->group(function () {
    Route::get('get-books', [BookController::class, 'getBooks']);
    Route::get('get-product-attributes', [ProductAttributeController::class, 'getProductAttributes']);
    Route::get('get-categories', [CategoryController::class, 'getCategories']);
    Route::get('get-option-groups', [OptionGroupController::class, 'getOptionGroups']);
    Route::get('get-roles', [RoleController::class, 'getRoles']);
    Route::get('get-permissions', [PermissionController::class, 'getPermissions']);
    Route::get('get-authors', [AuthorController::class, 'getAuthors']);
    Route::get('get-publishers', [PublisherController::class, 'getPublishers']);
    Route::get('get-suppliers', [SupplierController::class, 'getSuppliers']);
    Route::get('get-customers', [CustomerController::class, 'getCustomers']);
    Route::get('get-payment-methods', [OptionController::class, 'getPaymentMethods']);
    Route::get('get-stock-quantity/{id}', [BookController::class, 'getStockQuantity']);
    Route::get('get-customer-balance/{id}', [CustomerController::class, 'getBalance']);
    Route::get('get-customer-discount-percentage/{id}', [CustomerController::class, 'getDiscountPercentage']);
    Route::get('/user/{id}/permissions', [PermissionController::class, 'getUserPermissions']);
    Route::get('get-category-wise-sub-categories', [SubCategoryController::class, 'getCategoryWiseSubCategories'])->name('getCategoryWiseSubCategories');

    Route::apiResource('/option-groups', OptionGroupController::class);
    Route::apiResource('/options', OptionController::class);
    Route::apiResource('/categories', CategoryController::class);
    Route::apiResource('/sub-categories', SubCategoryController::class);

    Route::apiResource('/users', UserController::class);
    Route::apiResource('/roles', RoleController::class);    
    Route::apiResource('/manage-permission', PermissionController::class);
    Route::get('/permissions/grouped', [PermissionController::class,'groupedPermissions']);

    Route::apiResource('/authors', AuthorController::class);
    Route::post('/authors/{id}',[AuthorController::class,'update']);

    Route::apiResource('/publishers', PublisherController::class);
    Route::post('/publishers/{id}',[PublisherController::class,'update']);

    Route::apiResource('/customers', CustomerController::class);// Custom PUT Api for edit
    Route::post('/customers/{id}',[CustomerController::class,'update']);

    Route::apiResource('/suppliers', SupplierController::class);
    Route::post('/suppliers/{id}',[SupplierController::class,'update']);
    
    
    Route::apiResource('/books',BookController::class);
    Route::post('/books/{id}',[BookController::class,'update']);

    Route::apiResource('/purchases',PurchaseController::class);
    Route::post('/purchases/{id}',[PurchaseController::class,'update']);

    Route::apiResource('/sales',SaleController::class);
    Route::post('/sales/{id}',[SaleController::class,'update']);
    Route::get('export-sale-invoice-pdf/{id}', [SaleController::class, 'exportInvoicePdf'])->name('export-sale-invoice-pdf');
    
    Route::apiResource('/sale-returns',SaleReturnController::class);
    Route::post('/sale-returns/{id}',[SaleReturnController::class,'update']);
    Route::get('export-sale-return-invoice-pdf/{id}', [SaleReturnController::class, 'exportInvoicePdf'])->name('export-sale-return-invoice-pdf');
    
    Route::apiResource('/customer-payments',CustomerPaymentController::class);
    Route::post('/customer-payments/{id}',[CustomerPaymentController::class,'update']);
    Route::get('export-customer-payment-invoice-pdf/{id}', [CustomerPaymentController::class, 'exportInvoicePdf'])->name('export-customer-payment-invoice-pdf');
    
    Route::apiResource('/manage-employee', EmployeeController::class);
    Route::apiResource('/manage-product', ProductController::class);
    Route::apiResource('/manage-expense', ExpenseController::class);
    
    # Damage Item
    Route::apiResource('/damage-items', DamagedItemController::class);
    Route::post('/damage-items/{id}',[DamagedItemController::class,'update']);

    Route::get('category-export', [CategoryController::class, 'export'])->name('category.export');
    Route::get('category-export-pdf', [CategoryController::class, 'exportPdf'])->name('category.export.pdf');

    Route::post('/salary/paid/{id}', [SalaryController::class, 'Paid']);
    Route::get('/salary', [SalaryController::class, 'AllSalary']);

    Route::get('/salary/view/{id}', [SalaryController::class, 'ViewSalary']);
    Route::get('/edit/salary/{id}', [SalaryController::class, 'EditSalary']);
    Route::post('/salary/update/{id}', [SalaryController::class, 'SalaryUpdate']);

    Route::post('/stock/update/{id}', [ProductController::class, 'StockUpdate']);

    Route::get('/getting/product/{id}', [PosController::class, 'GetProduct']);

// Add to cart Route
    Route::get('/addToCart/{id}', [CartController::class, 'AddToCart']);
    Route::get('/cart/product', [CartController::class, 'CartProduct']);

    Route::get('/remove/cart/{id}', [CartController::class, 'removeCart']);

    Route::get('/increment/{id}', [CartController::class, 'increment']);
    Route::get('/decrement/{id}', [CartController::class, 'decrement']);

// Vat Route
    Route::get('/vats', [CartController::class, 'Vats']);
    Route::post('/orderdone', [PosController::class, 'OrderDone']);

// Order Route
    Route::get('/orders', [OrderController::class, 'TodayOrder']);

    Route::get('/order/details/{id}', [OrderController::class, 'OrderDetails']);
    Route::get('/order/orderdetails/{id}', [OrderController::class, 'OrderDetailsAll']);

    Route::post('/search/order', [PosController::class, 'SearchOrderDate']);

// Admin Dashboard Route
    Route::get('/report/today-sale', [DashboardController::class, 'todaySale']);
    Route::get('/report/today-purchase', [DashboardController::class, 'todayPurchase']);
    Route::get('/report/total-due', [DashboardController::class, 'totalDue']);
    Route::get('/report/total-customer', [DashboardController::class, 'totalCustomer']);
    Route::get('/report/out-of-stock', [DashboardController::class, 'outOfStock']);
    Route::get('/report/stock-alerts', [DashboardController::class, 'stockAlerts']);

    # Report Routes    
    Route::post('/report/customer-payment',[CustomerPaymentReportController::class,'index']);
    Route::post('/report/customer-wise-sale',[SaleReportController::class,'customerWiseSale']);
    Route::post('/report/category-wise-sale',[SaleReportController::class,'categoryWiseSale']);

    Route::post('/report/supplier-wise-purchase',[PurchaseReportController::class,'supplierWisePurchase']);
    Route::post('/report/category-wise-purchase',[PurchaseReportController::class,'categoryWisePurchase']);

    Route::post('/report/stock',[StockReportController::class,'stockReport']);
    Route::post('/report/stock-alert',[StockReportController::class,'stockAlertReport']);
});
