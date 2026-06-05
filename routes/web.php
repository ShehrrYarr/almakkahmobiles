<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LoginRestrictionController;
use App\Http\Controllers\MasterPasswordController;

use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CustomerMessageController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\BankController;

use App\Http\Controllers\SalesLiveController;

use Illuminate\Support\Facades\Route;
use App\Models\User;


use App\Models\company;
use App\Models\group;
use App\Models\vendor;
use App\Http\Controllers\AccessoryBatchController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


Auth::routes();
Route::post('/logout-user/{user}', [UserController::class, 'logoutUser'])->name('logoutUser');


Route::get('/', function () {
   
    return view('home');

});



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/adminthread', [App\Http\Controllers\AdminThreadController::class, 'index'])->name('adminthread');
Route::get('/fetchthread/{user_id}', [App\Http\Controllers\AdminThreadController::class, 'fetchThread'])->name('fetchthread');

Route::get('/userthread', [App\Http\Controllers\UserThreadController::class, 'index'])->name('userthread');
Route::get('/sendmessage/{message}/{chat_id}', [App\Http\Controllers\UserThreadController::class, 'store'])->name('sendmessage');

Route::post('/logout', [App\Http\Controllers\HomeController::class, 'logout'])->name('logout');



Route::get('/index', [App\Http\Controllers\UserController::class, 'index'])
    ->name('user.index')
    ->middleware(['auth', 'login.time.restrict']);





//vendor routes — require view_vendor_accounts permission
Route::middleware(['auth', 'login.time.restrict', 'permission:view_vendor_accounts'])->group(function () {
    Route::get('/showvendors', [App\Http\Controllers\VendorController::class, 'showVendors'])->name('showvendors');
    Route::post('/vendors/store', [VendorController::class, 'storeVendor'])->name('storeVendor');
    Route::get('/editvendor/{id}', [App\Http\Controllers\VendorController::class, 'editVendor'])->name('editvendor');
    Route::put('/updatevendor', [VendorController::class, 'updateVendor'])->name('updateVendor');
    Route::get('/showvrHistory/{id}', [VendorController::class, 'showVRHistory'])->name('showVRHistory');
    Route::get('/showvsHistory/{id}', [VendorController::class, 'showVSHistory'])->name('showVSHistory');
    Route::get('/vendor-balance/{id}', [VendorController::class, 'getBalance'])->name('vendor.balance');
    Route::get('/vendor-balance', [VendorController::class, 'getBalance'])->name('getVendorBalance');
    Route::get('/receivablevendors', [VendorController::class, 'listReceivables'])->name('receivablevendors');
});
Route::middleware(['auth', 'login.time.restrict', 'permission:delete_records'])->group(function () {
    Route::post('/deletevendor', [VendorController::class, 'destroyVendor'])->name('destroyVendor');
});






//company & group routes — require manage_inventory permission
Route::middleware(['auth', 'login.time.restrict', 'permission:manage_inventory'])->group(function () {
    Route::get('/showcompanies', [App\Http\Controllers\CompanyController::class, 'showCompanies'])->name('showcompanies');
    Route::post('/company/store', [CompanyController::class, 'storeCompany'])->name('storeCompany');
    Route::get('/editcompany/{id}', [App\Http\Controllers\CompanyController::class, 'editCompany'])->name('editcompany');
    Route::put('/updatecompany', [CompanyController::class, 'updateCompany'])->name('updateCompany');

    Route::get('/showgroups', [App\Http\Controllers\GroupController::class, 'showGroups'])->name('showgroups');
    Route::post('/group/store', [GroupController::class, 'storeGroup'])->name('storeGroup');
    Route::get('/editgroup/{id}', [App\Http\Controllers\GroupController::class, 'editGroup'])->name('editGroup');
    Route::put('/updategroup', [GroupController::class, 'updateGroup'])->name('updateGroup');
});
Route::middleware(['auth', 'login.time.restrict', 'permission:delete_records'])->group(function () {
    Route::post('/deletecompany', [CompanyController::class, 'destroyCompany'])->name('destroyCompany');
    Route::post('/deletegroup', [GroupController::class, 'destroyGroup'])->name('destroyGroup');
});

//password & login restriction routes — admin only
Route::middleware(['auth', 'login.time.restrict', 'role:admin'])->group(function () {
    Route::get('/showpassword', [App\Http\Controllers\MasterPasswordController::class, 'showPassword'])->name('showpassword');
    Route::post('/password/update', [MasterPasswordController::class, 'updatePassword'])->name('updatePassword');
});



//Accounts Routes
Route::middleware(['auth', 'login.time.restrict', 'permission:view_vendor_accounts'])->group(function () {
    Route::get('/accounts/{id}', [AccountsController::class, 'showAccounts'])->name('showAccounts');
    Route::post('/credit', [AccountsController::class, 'creditAmount'])->name('creditAmount');
    Route::post('/debit', [AccountsController::class, 'debitAmount'])->name('debitAmount');
    Route::get('/getaccount/{id}', [App\Http\Controllers\AccountsController::class, 'getaccount'])->name('getaccount');
});
Route::middleware(['auth', 'login.time.restrict', 'permission:delete_records'])->group(function () {
    Route::post('/deleteaccount', [AccountsController::class, 'destroyAccount'])->name('destroyAccount');
});







//Custom Login Restriction Routes — admin only
Route::middleware(['auth', 'login.time.restrict', 'role:admin'])->group(function () {
    Route::get('/showlogin', [LoginRestrictionController::class, 'showLogin'])->name('showlogin');
    Route::post('/admin/login-window', [LoginRestrictionController::class, 'updateLoginWindow'])->name('admin.updateLoginWindow');
});

//Manage user routes — admin only
Route::middleware(['auth', 'login.time.restrict', 'role:admin'])->group(function () {
    Route::get('/showusers', [UserController::class, 'showUsers'])->name('showusers');
    Route::post('/store-user', [UserController::class, 'store'])->name('storeUser');
    Route::get('/edituser/{id}', [App\Http\Controllers\UserController::class, 'editUser'])->name('editUser');
    Route::put('/update-user', [UserController::class, 'update'])->name('updateUser');
});




//Accessory Routes
Route::get('/accessories', [AccessoryController::class, 'index'])->name('accessories.index')->middleware(['auth', 'login.time.restrict']);
Route::get('/filteraccessory', [AccessoryController::class, 'filter'])->name('filter.index')->middleware(['auth', 'login.time.restrict']);
Route::middleware(['auth', 'login.time.restrict', 'permission:manage_inventory'])->group(function () {
    Route::post('/accessories', [AccessoryController::class, 'store'])->name('accessories.store');
    Route::get('/accessoryedit/{id}', [AccessoryController::class, 'edit'])->name('accessories.edit');
    Route::put('/accessories', [AccessoryController::class, 'update'])->name('accessories.update');
});

//Batch Routes
Route::get('/batches', [AccessoryBatchController::class, 'index'])->name('batches.index')->middleware(['auth', 'login.time.restrict']);
Route::get('/batches/{id}/barcode', [AccessoryBatchController::class, 'barcodeInfo'])->name('batches.barcode')->middleware(['auth', 'login.time.restrict']);
Route::middleware(['auth', 'login.time.restrict', 'permission:manage_inventory'])->group(function () {
    Route::post('/batches', [AccessoryBatchController::class, 'store'])->name('batches.store');
});


//Sales Routes
Route::get('/sales', [App\Http\Controllers\SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/create', [App\Http\Controllers\SaleController::class, 'create'])->name('sales.create');
Route::post('/sales', [App\Http\Controllers\SaleController::class, 'store'])->name('sales.store');
Route::post('/sales/{id}/approve', [SaleController::class, 'approve'])->name('sales.approve')->middleware(['auth', 'login.time.restrict', 'permission:approve_sales']);
Route::get('/sales/pending', [SaleController::class, 'pending'])->name('sales.pending');
Route::get('/sales/approved', [SaleController::class, 'approved'])->name('sales.approved');
Route::get('/sales/all', [\App\Http\Controllers\SaleController::class, 'allSales'])->name('sales.all');
Route::get('/sales/{sale}/items', [\App\Http\Controllers\SaleController::class, 'ajaxSaleItems'])->middleware(['auth', 'login.time.restrict']);



Route::get('/pos', [SaleController::class, 'pos'])->name('sales.pos');
Route::post('/pos/checkout', [SaleController::class, 'checkout'])->name('sales.checkout');
Route::get('/pos/invoice/{sale}', [SaleController::class, 'invoice'])->name('sales.invoice');
Route::get('/reports/sales', [\App\Http\Controllers\SaleController::class, 'salesReport'])->middleware(['auth', 'login.time.restrict', 'permission:view_vendor_accounts']);
Route::get('/accessoryreport', [SaleController::class, 'accessoryReport'])->name('saccessoryreport')->middleware(['auth', 'login.time.restrict', 'permission:view_vendor_accounts']);
// routes/web.php
Route::get('/api/vendor-balance/{id}', [VendorController::class, 'getVBalance']);


//Profit only main account py show ho
// ik new tab ajayE JAHAN ACCOUNTS KI ENTRIES AJAIN
//Vendor ki profit report sales k hisab sy  aye 



//Custoemr Mesage Routes

// Show the message form
Route::get('/send-message-to-customers', [CustomerMessageController::class, 'showSendMessageForm'])->name('send-message-to-customers');

// Handle form post (send messages)
Route::post('/send-message-to-customers', [CustomerMessageController::class, 'sendMessageToAllCustomers'])->name('send.message.submit');




Route::get('/loginhistory', [App\Http\Controllers\LoginHistoryController::class, 'getAllLogins'])->name('loginhistory')->middleware(['auth', 'login.time.restrict', 'role:admin']);

// Return Routes
Route::middleware(['auth', 'login.time.restrict', 'permission:process_returns'])->group(function () {
    Route::post('/sales/{sale}/return', [SaleController::class, 'processReturn'])->name('sales.return');
    Route::get('/sales/refunds', [SaleController::class, 'refundsPage'])->name('sales.refunds');
});

// Route::post('/sales/{sale}/return', [SaleController::class, 'returnItems'])->name('sales.return');

//Petty Cash Routes
Route::middleware(['auth', 'login.time.restrict', 'permission:view_vendor_accounts'])->group(function () {
    Route::get('/petty-cash', [PettyCashController::class, 'index'])->name('pettycash.index');
    Route::post('/petty-cash', [PettyCashController::class, 'store'])->name('pettycash.store');
});

//Bank Routes
Route::middleware(['auth', 'login.time.restrict', 'permission:view_vendor_accounts'])->group(function () {
    Route::get('/banks', [BankController::class, 'index'])->name('banks');
    Route::post('/banks', [BankController::class, 'storeBank'])->name('storeBank');
    Route::get('/getbank/{id}', [BankController::class, 'getBank'])->name('getBank');
    Route::put('/updatebank', [BankController::class, 'updateBank'])->name('updateBank');
});


//Bulk Batch Store
Route::middleware(['auth', 'login.time.restrict', 'permission:manage_inventory'])->group(function () {
    Route::get('/batches/bulk', [AccessoryBatchController::class, 'bulkCreate'])->name('batches.bulk');
    Route::post('/batches/bulk', [AccessoryBatchController::class, 'bulkStore'])->name('batches.bulk.store');
});

Route::get('/vendors/search', function (\Illuminate\Http\Request $request) {
    $q = trim($request->get('q', ''));
    return \App\Models\vendor::query()
        ->when($q !== '', function($qq) use ($q) {
            $qq->where('name', 'like', "%{$q}%")
               ->orWhere('mobile_no', 'like', "%{$q}%");
        })
        ->orderBy('name')
        ->limit(20)
        ->get()
        ->map(fn($v) => ['id' => $v->id, 'text' => $v->name.' ('.$v->mobile_no.')']);
})->name('vendors.search');




//Daily sales--------> Done
//Pictures of accessory  ---> Done
//Whatsapp send
//Printer setting ------> Done
//Comments in sales ------> Done

Route::middleware(['auth', 'login.time.restrict', 'role:admin'])->group(function () {
    Route::get('/sales/live', [SalesLiveController::class, 'index'])->name('sales.live.index');
    Route::get('/sales/live/feed', [SalesLiveController::class, 'feed'])->name('sales.live.feed');
});

//error fix

//Press f2 to hide or show the purchase price , by default hidden rahy (******)
//Claim ki functionality
//Check lagana h agr koi item loss me jarha ho to usko alert dy 
