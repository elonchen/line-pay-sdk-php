<?php

require __DIR__ . '/_config.php';

// Get saved config
$config = $_SESSION['config'];
// Create LINE Pay client
$linePay = new \yidas\linePay\Client([
    'channelId' => $config['channelId'],
    'channelSecret' => $config['channelSecret'],
    'isSandbox' => ($config['isSandbox']) ? true : false, 
]);

// Successful page URL
$successUrl = './index.php?route=order';
// Get the transactionId from query parameters
$transactionId = (string) $_GET['transactionId'];
// Get the order from session
$order = $_SESSION['linePayOrder'];

// Check transactionId (Optional)
if ($order['transactionId'] != $transactionId) {
    die("<script>alert('TransactionId doesn\'t match');location.href='./index.php';</script>");
}
// var_dump($order);exit;
// Online Confirm API
$response = $linePay->confirm($order['transactionId'], [
    'amount' => (integer) $order['params']['amount'],
    'currency' => $order['params']['currency'],
]);

// Save error info if confirm fails
if (!$response->isSuccessful()) {
    $_SESSION['linePayOrder']['confirmCode'] = $response['returnCode'];
    $_SESSION['linePayOrder']['confirmMessage'] = $response['returnMessage'];
}

// Use Details API to confirm the transaction (Details API verification is  stable then Confirm API)
$response = $linePay->details([
    'transactionId' => [$order['transactionId']],
]);

// Check the transaction
if (!isset($response["info"]) || $response["info"][0]['transactionId'] != $transactionId) {
    $_SESSION['linePayOrder']['isSuccessful'] = false;
    die("<script>alert('Refund Failed\\nErrorCode: {$_SESSION['linePayOrder']['confirmCode']}\\nErrorMessage: {$_SESSION['linePayOrder']['confirmMessage']}');location.href='{$successUrl}';</script>");
}

// Code for saving the successful order into your application database...
$_SESSION['linePayOrder']['isSuccessful'] = true;

// Redirect to successful page
header("Location: {$successUrl}");