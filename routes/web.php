<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankEmailIntegrationController;
use App\Http\Controllers\BankEmailMessageController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('purchases', PurchaseController::class);

Route::resource('invoices', InvoiceController::class);
Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'viewPdf'])->name('invoices.pdf');
Route::get('invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');

Route::get('bank-accounts/transfer', [BankAccountController::class, 'transferCreate'])->name('bank-accounts.transfer.create');
Route::post('bank-accounts/transfer', [BankAccountController::class, 'transferStore'])->name('bank-accounts.transfer.store');
Route::resource('bank-accounts', BankAccountController::class);

Route::resource('journal-entries', JournalEntryController::class);
Route::post('journal-entries/{journal_entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');

Route::resource('chart-of-accounts', ChartOfAccountController::class);

Route::resource('projects', ProjectController::class);

Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
Route::post('sms/send', [SmsController::class, 'send'])->name('sms.send');
Route::post('invoices/{invoice}/sms', [SmsController::class, 'sendInvoiceNotification'])->name('invoices.sms');

Route::prefix('bank-emails')->name('bank-emails.')->group(function () {
    Route::get('integrations', [BankEmailIntegrationController::class, 'index'])->name('integrations.index');
    Route::get('integrations/create', [BankEmailIntegrationController::class, 'create'])->name('integrations.create');
    Route::post('integrations', [BankEmailIntegrationController::class, 'store'])->name('integrations.store');
    Route::get('integrations/{integration}', [BankEmailIntegrationController::class, 'show'])->name('integrations.show');
    Route::get('integrations/{integration}/edit', [BankEmailIntegrationController::class, 'edit'])->name('integrations.edit');
    Route::put('integrations/{integration}', [BankEmailIntegrationController::class, 'update'])->name('integrations.update');
    Route::delete('integrations/{integration}', [BankEmailIntegrationController::class, 'destroy'])->name('integrations.destroy');
    Route::post('integrations/{integration}/test', [BankEmailIntegrationController::class, 'testConnection'])->name('integrations.test');
    Route::post('integrations/{integration}/sync', [BankEmailIntegrationController::class, 'sync'])->name('integrations.sync');

    Route::get('transactions', [BankTransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/review', [BankTransactionController::class, 'review'])->name('transactions.review');
    Route::get('transactions/{transaction}', [BankTransactionController::class, 'show'])->name('transactions.show');
    Route::put('transactions/{transaction}', [BankTransactionController::class, 'update'])->name('transactions.update');
    Route::post('transactions/{transaction}/confirm', [BankTransactionController::class, 'confirm'])->name('transactions.confirm');
    Route::post('transactions/{transaction}/ignore', [BankTransactionController::class, 'ignore'])->name('transactions.ignore');
    Route::delete('transactions/{transaction}', [BankTransactionController::class, 'destroy'])->name('transactions.destroy');

    Route::get('messages', [BankEmailMessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [BankEmailMessageController::class, 'show'])->name('messages.show');
});

Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
Route::post('settings/categories', [SettingsController::class, 'storeCategory'])->name('settings.categories.store');
Route::delete('settings/categories/{category}', [SettingsController::class, 'destroyCategory'])->name('settings.categories.destroy');
