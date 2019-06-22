<p align="center">
    <a href="https://pay.line.me/" target="_blank">
        <img src="https://scdn.line-apps.com/linepay/partner/images/sp_txt_shape_v4_2.png" width="126px">
    </a>
    <h1 align="center">LINE Pay SDK <i>for</i> PHP</h1>
    <br>
</p>

LINE Pay SDK for PHP

[![Latest Stable Version](https://poser.pugx.org/yidas/line-pay-sdk/v/stable?format=flat-square)](https://packagist.org/packages/yidas/line-pay-sdk)
[![License](https://poser.pugx.org/yidas/line-pay-sdk/license?format=flat-square)](https://packagist.org/packages/yidas/line-pay-sdk)

[English](https://github.com/yidas/line-pay-sdk-php/blob/master/README.md) | 繁體中文

OUTLINE
-------

- [示範](#示範)
- [系統需求](#系統需求)
    - [授權驗證資訊](#授權驗證資訊)
- [安裝](#安裝)
- [使用方法](#使用方法)
    - [Client物件](#client物件)
        - [裝置資訊](#裝置資訊)
    - [Response物件](#response物件)
        - [取得資料](#取得資料)
            - [取得為物件](#取得為物件)
            - [取得為陣列](#取得為陣列)
        - [方法](#方法)
            - [isSuccessful()](#issuccessful)
            - [toArray()](#toarray)
            - [toObject()](#toobject)
            - [getPayInfo()](#getpayinfo)
    - [Online APIs](#online-apis)
        - [取得查看付款紀錄 API](#取得查看付款紀錄-api)
        - [付款reserve API](#付款reserve-api)
        - [付款confirm API](#付款confirm-api)
        - [退款 API](#退款-api)
        - [取得查看授權記錄 API](#取得查看授權記錄-api)
        - [請款 API](#請款-api)
        - [授權作廢 API](#授權作廢-api)
        - [自動付款 API](#自動付款-api)
        - [查看 regKey 狀態 API](#查看-regKey-狀態-api)
        - [註銷 regKey API](#註銷-regkey-api)
    - [Offline APIs](#offline-apis)
        - [Payment](#payment)
        - [Payment Status Check](#payment-status-check)
        - [Void](#void)
        - [Capture](#capture)
        - [Refund](#refund)
        - [Authorization Details](#authorization-details)
- [外部資源](#外部資源)
- [參考](#參考)

---

示範
---

[範例站台程式碼 (Reserve, 付款, 退款)](https://github.com/yidas/line-pay-sdk-php/tree/master/sample)

```php
// Create LINE Pay client
$linePay = new \yidas\linePay\Client([
    'channelId' => 'Your merchant X-LINE-ChannelId',
    'channelSecret' => 'Your merchant X-LINE-ChannelSecret',
    'isSandbox' => true, 
]);

// Online Reserve API
$response = $linePay->reserve([
    'productName' => 'Your product name',
    'amount' => 250,
    'currency' => 'TWD',
    'confirmUrl' => 'https://yourname.com/line-pay/confirm',
    'productImageUrl' => 'https://yourname.com/assets/img/product.png',
    'cancelUrl' => 'https://yourname.com/line-pay/cancel',
    'orderId' => 'Your order ID',
]);

// Check Reserve API result (returnCode "0000" check method)
if (!$response->isSuccessful()) {
    throw new Exception("ErrorCode {$response['returnCode']}: {$response['returnMessage']}");
}

// Redirect to LINE Pay payment URL 
header('Location: '. $response->getPaymentUrl() );
```

---


系統需求
------------
本套件需求:

- PHP 5.4.0+\|7.0+
- guzzlehttp/guzzle 5.3.1+\|6.0+
- [LINE Pay 商家授權驗證資訊](#授權驗證資訊)

### 授權驗證資訊

整合 LINE Pay 時所需的驗證資訊如下：
- channel id
- channel secret key

如何取得授權:

 1. 申請 LINE Pay 商家帳號並通過審核，可以參考 [LINE Pay 商家中心](http://pay.line.me)。
 2. 使用商家帳戶登入 [LINE Pay 商家後台](http://pay.line.me/)，**取得ChannelId/ChannelSecret** (管理付款連結 > 管理連結金鑰)。
 3. 於 [LINE Pay 商家後台](http://pay.line.me/)**設定伺服器IP白名單** (管理付款連結 > 管理付款伺服器 IP)。

> 您也可以馬上建立一個Sandbox商家帳號做測試，請參考 [LINE Pay - 建立Sandbox](https://pay.line.me/tw/developers/techsupport/sandbox/creation?locale=zh_TW)。

---

安裝
------------

在您的專案下執行 Composer 安裝:

    composer require yidas/line-pay-sdk
    
安裝完成且載入 Composer 後，您即可使用 SDK Class 於您的 PHP 專案：

```php
require __DIR__ . '/vendor/autoload.php';

use yidas\linePay\Client;
```

---

使用方法
-----

在使用 LINE Pay SDK 服務之前，您必須先建立與設定一個 Client，在使用這個 Client 去呼叫 API 方法。

### Client物件

建立一個 Client 物件並帶入[授權驗證資訊](#授權驗證資訊)：

```php
$linePay = new \yidas\linePay\Client([
    'channelId' => 'Your merchant X-LINE-ChannelId',
    'channelSecret' => 'Your merchant X-LINE-ChannelSecret',
    'isSandbox' => true, 
]);
```

#### 裝置資訊

您可以於 Client 設定裝置資訊 (非必要)：

```php
$linePay = new \yidas\linePay\Client([
    'channelId' => 'Your merchant X-LINE-ChannelId',
    'channelSecret' => 'Your merchant X-LINE-ChannelSecret',
    'isSandbox' => true, 
    'merchantDeviceType' => 'Device type string',
    'merchantDeviceProfileId' => 'Device profile ID string',
]);
```

### Response物件

每一個API方法皆會回傳 `yidas\linePay\Response` 物件，可以用來拿取相同於 LINE Pay JSON 回傳資料結構的資料。

#### 取得資料

Response物件提供用陣列或物件的方式來取得回傳資料：

##### 取得為物件

```php
// Get LINE Pay results code from response
$returnCode = $response->returnCode;
// Get LINE Pay info.payInfo[] from response
$payinfo = $response->info->payinfo;
```

##### 取得為陣列

```php
// Get LINE Pay results code from response
$returnCode = $response['returnCode'];
// Get LINE Pay info.payInfo[] from response
$payinfo = $response['info']['payinfo'];
```

#### 方法

Response物件提供了一些方法幫助使用：

##### isSuccessful()

LINE Pay API 結果代碼是否成功 (檢查 returnCode 是否為"0000")

*Example:*
```php
if (!$response->isSuccessful()) {
    
    throw new Exception("Code {$response['returnCode']}: {$response['returnMessage']}");
}
```
##### getPaymentUrl()

取得 LINE Pay info.paymentUrl 網址回傳資料 (預設為"web")

##### getPayInfo()

取得陣列格式的 LINE Pay info.payInfo[] 或 info.[$param1].payInfo[] 回傳資料

##### toArray()

取得陣列格式的 LINE Pay 回傳資料

##### toObject()

取得物件格式的 LINE Pay 回傳資料

### Online APIs

線上整合中，商家會 Reserve 一個付款請求並產生付款 URL(QR code) 提供給消費者用 LINE App 掃描。

> 流程: [`付款Reserve`](#付款reserve-api) -> [`付款Confirm`](#付款confirm-api) -> [`取得查看付款紀錄`](#取得查看付款紀錄-api) -> [`退款`](#退款-api)

#### 取得查看付款紀錄 API

取得透過 LINE Pay 進行付款的詳細說明。此 API 只會取得已經請款的付款。

```php
public Response details(array $queryParams=null)
```

*Example:*
```php
$response = $linePay->details([
    "transactionId" => [$transactionId],
]);
```

#### 付款reserve API

在 LINE Pay 中保留付款資訊。 在使用 LINE Pay 付款前確認是否為正常的商家，然後保留付款所需的資訊。成功付款 reserve 後，商家會收到一個「交易編號」，這個鍵值會在付款完成或退款時使用。

```php
public Response reserve(array $optParams=null)
```

*Example:*
```php 
$response = $linePay->reserve([
    'productName' => 'Your product name',
    'amount' => 250,
    'currency' => 'TWD',
    'confirmUrl' => 'https://yourname.com/line-pay/confirm',
    'productImageUrl' => 'https://yourname.com/assets/img/product.png',
    'cancelUrl' => 'https://yourname.com/line-pay/cancel',
    'orderId' => 'Your order ID',
]);
```

#### 付款confirm API

此 API 可讓商家完成付款。商家必須呼叫付款 confirm API，才能實際完成付款。不過，當付款 reserve 的 "capture" 參數為 "false" 時，付款狀態會變為 "AUTHORIZATION"，只有在呼叫 "請款 API" 後才能完成付款。

```php
public Response confirm(integer $transactionId, array $optParams=null)
```

*Example:*
```php
$response = $linePay->confirm($transactionId, [
    "amount" => 250,
    "currency" => 'TWD',
]);
```

#### 退款 API

請求對 LINE Pay 付款完成的項目進行退款。退款時必須提供 LINE Pay 用戶的付款交易編號，也可以視退款金額進行部分退款。

```php
public Response refund(integer $transactionId, array $optParams=null)
```

*Example:*
```php
$response = $linePay->refund($transactionId);
```

*For Partial refund:*
```php
$response = $linePay->refund($transactionId, [
    'refundAmount' => 200,
]);
```

#### 取得查看授權記錄 API

取得透過 LINE Pay 授權項目的詳細說明。此 API 只會取得已經授權或授權已經作廢的資料；已經請款的項目則可使用「[取得查看付款紀錄 API](取得查看付款紀錄-api)」來檢視。

```php
public Response authorizations(array $queryParams=null)
```

*Example:*
```php
$response = $linePay->authorizations([
    "transactionId" => [$transactionId],
]);
```

#### 請款 API

如果商家呼叫付款 reserve API 時 "capture" 為 "false"，則只有呼叫請款 API 後才能完成付款。 confirm API 執行結果只有授權的付款進行請款動作。

```php
public Response authorizationsCapture(integer $transactionId, array $optParams=null)
```

*Example:*
```php
$response = $linePay->authorizationsCapture($transactionId, [
    "amount" => 250,
    "currency" => 'TWD',
]);
```

#### 授權作廢 API

將已授權的交易作廢。 將先前已授權的付款作廢。已經請款的付款可以藉由使用「退款 API」進行退款。

```php
public Response authorizationsVoid(integer $transactionId, array $optParams=null)
```

*Example:*
```php
$response = $linePay->authorizationsVoid($transactionId);
```

#### 自動付款 API

當付款 reserve API 的付款類型設定為 `PREAPPROVED` 時，regKey 會隨付款結果傳回。自動付款 API 會使用 regKey 直接完成付款，無需使用 LINE 應用程式。

```php
public Response preapproved(integer $regKey, array $optParams=null)
```

*Example:*
```php
$response = $linePay->preapproved([
    'productName' => 'Your product name',
    'amount' => 250,
    'currency' => 'TWD',
    'orderId' => 'Your order ID',
]);
```

#### 查看 regKey 狀態 API

使用自動付款 API 前，檢查 regKey 是否存在。

```php
public Response preapprovedCheck(integer $regKey, array $queryParams=null)
```

*Example:*
```php
$response = $linePay->preapprovedCheck($regKey);
```

#### 註銷 regKey API

註銷為自動付款登錄的 regKey 資訊。一旦呼叫此 API，現有的 regKey 將無法繼續用於自動付款。

```php
public Response preapprovedExpire(integer $regKey, array $optParams=null)
```

*Example:*
```php
$response = $linePay->preapprovedExpire($regKey);
```

### Offline APIs

線下整合中，可由LINE Pay用戶主頁面"我的條碼"產生一維碼或QR碼給 POS 設備掃描。

> 流程: [`OneTimeKeysPay`](#payment) -> [`OrdersCheck`](#payment-status-check) -> [`OrdersRefund`](#refund)

#### Payment

此API為商店交易裝置讀取LINE Pay"我的條碼"後傳送付款要求所需的API。

```php
public Response oneTimeKeysPay(array $optParams=null)
```

*Example:*
```php
$response = $linePay->oneTimeKeysPay([
    'productName' => 'Your product name',
    'amount' => 250,
    'currency' => 'TWD',
    'productImageUrl' => 'https://yourname.com/assets/img/product.png',
    'orderId' => 'Your order ID',
    "oneTimeKey"=> 'LINE Pay MyCode',
]);
```

#### Payment Status Check

如果因為回應時間過長導致最終付款狀態無法確認時，請使用這支API
- 交易狀態應在固定的時間間隔中被檢查，建議 3~5 秒即檢查一次。
- 付款的有效期間為"線下付款API"
Response時間後20分鐘內，因此商家最長應檢查交易狀態20分鐘。若超過20分鐘，該筆交易則被取消

```php
public Response ordersCheck(string $orderId, array $$queryParams=null)
```

*Example:*
```php
$response = $linePay->ordersCheck($orderId);
```

#### Void

此API用於進行授權作廢

```php
public Response ordersVoid(string $orderId, array $optParams=null)
```

*Example:*
```php
$response = $linePay->ordersVoid($orderId);
```

#### Capture

此API用於對已取得授權的交易進行請款。

```php
public Response ordersCapture(string $orderId, array $optParams=null)
```

*Example:*
```php
$response = $linePay->ordersCapture($orderId);
```

#### Refund

此API對付款完成的項目進行退款。

```php
public Response ordersRefund(string $orderId, array $optParams=null)
```

*Example:*
```php
$response = $linePay->ordersRefund($orderId);
```

#### Authorization Details

此API用於查看已取得付款授權交易的交易記錄。只有授權或取消授權 (作廢或是過期) 的交易記錄才可使用此API查詢，已經請款的交易紀錄則需使用「查看付款紀錄 API」來查詢。

*Example:*
```php
$response = $linePay->authorizations([
    "orderId" => $orderId,
]);
```

---

外部資源
=========

[LINE Pay 整合指南 v1.1.2 (TW)](https://pay.line.me/file/guidebook/technicallinking/LINE_Pay_Integration_Guide_for_Merchant-v1.1.2-TW(1).pdf)

[LINE Pay 線下實體商店整合指南 申請信箱](mailto:dl_tech_support_tw@linecorp.com)

[LINE Pay Sandbox 商家帳號建立申請](https://pay.line.me/tw/developers/techsupport/sandbox/creation?locale=zh_TW)

[LINE Pay OneTimeKeys 產生器 (台灣商家使用)](https://sandbox-web-pay.line.me/web/sandbox/payment/oneTimeKey?countryCode=TW&paymentMethod=card&preset=1)

---

參考
==========

[LINE Pay - 技術文件](https://pay.line.me/tw/developers/documentation/download/tech?locale=zh_TW)