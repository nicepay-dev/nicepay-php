<?php

use App\Http\Controllers\api\accessToken\GenerateAccessTokenController;
use App\Http\Controllers\api\ewallet\GenerateEwalletController;
use App\Http\Controllers\api\ewallet\InquiryEwalletController;
use App\Http\Controllers\api\ewallet\RefundEwalletController;
use App\Http\Controllers\api\payout\ApprovePayoutController;
use App\Http\Controllers\api\payout\CancelPayoutController;
use App\Http\Controllers\api\payout\GeneratePayoutController;
use App\Http\Controllers\api\payout\InquiryBalancePayoutController;
use App\Http\Controllers\api\payout\InquiryPayoutController;
use App\Http\Controllers\api\payout\RejectPayoutController;
use App\Http\Controllers\api\qris\GenerateQrisController;
use App\Http\Controllers\api\qris\InquiryQrisController;
use App\Http\Controllers\api\qris\RefundQrisController;
use App\Http\Controllers\api\va\DeleteVirtualAccountController;
use App\Http\Controllers\api\va\GenerateVirtualAccountController;
use App\Http\Controllers\api\va\InquiryVirtualAccountController;
use App\Http\Controllers\api\v2\CreditCardV2Controller;
use App\Http\Controllers\api\v2\VirtualAccountV2Controller;
use App\Http\Controllers\api\v2\CvsV2Controller;
use App\Http\Controllers\api\v2\DirectDebitV2Controller;
use App\Http\Controllers\api\v2\EwalletV2Controller;
use App\Http\Controllers\api\v2\PayloanV2Controller;
use App\Http\Controllers\api\v2\QrisV2Controller;
use App\Http\Controllers\api\v2\PayoutV2Controller;
use App\Http\Controllers\api\v2\FixOpenV2Controller;
use App\Http\Controllers\api\v2\RedirectV2Controller;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('template.index');
});

//Generate token
Route::get('/generate-access-token', [GenerateAccessTokenController::class, 'generateAccessToken']);


//VA
Route::get('/generate-virtual-account', [GenerateVirtualAccountController::class, 'generateVirtualAccount']);
Route::get('/inquiry-virtual-account', [InquiryVirtualAccountController::class, 'inquiryVirtualAccount']);
Route::get('/delete-virtual-account', [DeleteVirtualAccountController::class, 'deleteVirtualAccount']);

//E-Wallet
Route::get('/payment-host-to-host', [GenerateEwalletController::class, 'generateEwallet']);
Route::get('/status-ewallet', [InquiryEwalletController::class, 'inquiryEwallet']);
Route::get('/refund-ewallet', [RefundEwalletController::class, 'refundEwallet']);

//QRIS
Route::get('/generate-qr', [GenerateQrisController::class, 'generateQris']);
Route::get('/status-qr', [InquiryQrisController::class, 'inquiryQris']);
Route::get('/refund-qr', [RefundQrisController::class, 'refundQris']);

//Payout
Route::get('/registration-payout', [GeneratePayoutController::class, 'generatePayout']);
Route::get('/approve-payout', [ApprovePayoutController::class, 'approvePayout']);
Route::get('/cancel-payout', [CancelPayoutController::class, 'cancelPayout']);
Route::get('/inquiry-balance-payout', [InquiryBalancePayoutController::class, 'inquiryBalancePayout']);
Route::get('/inquiry-payout', [InquiryPayoutController::class, 'inquiryPayout']);
Route::get('/reject-payout', [RejectPayoutController::class, 'rejectPayout']);

//V2
Route::get('/v2/cc/registration', [CreditCardV2Controller::class, 'registration']);
Route::get('/v2/cc/inquiry', [CreditCardV2Controller::class, 'inquiry']);
Route::get('/v2/cc/cancel', [CreditCardV2Controller::class, 'cancel']);
Route::get('/v2/cc/payment', [CreditCardV2Controller::class, 'payment']);

Route::get('/v2/va/registration', [VirtualAccountV2Controller::class, 'registration']);
Route::get('/v2/va/inquiry', [VirtualAccountV2Controller::class, 'inquiry']);
Route::get('/v2/va/cancel', [VirtualAccountV2Controller::class, 'cancel']);

Route::get('/v2/ewallet/registration', [EwalletV2Controller::class, 'registration']);
Route::get('/v2/ewallet/inquiry', [EwalletV2Controller::class, 'inquiry']);
Route::get('/v2/ewallet/cancel', [EwalletV2Controller::class, 'cancel']);
Route::get('/v2/ewallet/payment', [EwalletV2Controller::class, 'payment']);


Route::get('/v2/cvs/registration', [CvsV2Controller::class, 'registration']);
Route::get('/v2/cvs/inquiry', [CvsV2Controller::class, 'inquiry']);
Route::get('/v2/cvs/cancel', [CvsV2Controller::class, 'cancel']);

Route::get('/v2/clickpay/registration', [DirectDebitV2Controller::class, 'registration']);
Route::get('/v2/clickpay/inquiry', [DirectDebitV2Controller::class, 'inquiry']);
Route::get('/v2/clickpay/cancel', [DirectDebitV2Controller::class, 'cancel']);
Route::get('/v2/clickpay/payment', [DirectDebitV2Controller::class, 'payment']);


Route::get('/v2/payloan/registration', [PayloanV2Controller::class, 'registration']);
Route::get('/v2/payloan/inquiry', [PayloanV2Controller::class, 'inquiry']);
Route::get('/v2/payloan/cancel', [PayloanV2Controller::class, 'cancel']);
Route::get('/v2/payloan/payment', [PayloanV2Controller::class, 'payment']);

Route::get('/v2/qris/registration', [QrisV2Controller::class, 'registration']);
Route::get('/v2/qris/inquiry', [QrisV2Controller::class, 'inquiry']);
Route::get('/v2/qris/cancel', [QrisV2Controller::class, 'cancel']);
Route::get('/v2/qris/payment', [QrisV2Controller::class, 'payment']);

Route::get('/v2/payout/registration', [PayoutV2Controller::class, 'registration']);
Route::get('/v2/payout/inquiry', [PayoutV2Controller::class, 'inquiry']);
Route::get('/v2/payout/cancel', [PayoutV2Controller::class, 'cancel']);
Route::get('/v2/payout/approve', [PayoutV2Controller::class, 'approve']);
Route::get('/v2/payout/reject', [PayoutV2Controller::class, 'reject']);
Route::get('/v2/payout/balance', [PayoutV2Controller::class, 'balance']);

Route::get('/v2/fixopen/registcust', [FixOpenV2Controller::class, 'vacctCustomerRegist']);
Route::get('/v2/fixopen/inquirycust', [FixOpenV2Controller::class, 'vacctCustomerInquiry']);
Route::get('/v2/fixopen/depositinquiry', [FixOpenV2Controller::class, 'vacctDepositInquiry']);
Route::get('/v2/fixopen/custupdate', [FixOpenV2Controller::class, 'vacctCustomerUpdate']);

Route::get('/v2/redirect/registration', [RedirectV2Controller::class, 'registration']);
Route::get('/v2/redirect/inquiry', [RedirectV2Controller::class, 'inquiry']);
Route::get('/v2/redirect/cancel', [RedirectV2Controller::class, 'cancel']);
