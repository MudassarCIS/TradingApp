<?php
/**
 * API Test File
 * Standalone PHP file to test Trading App External APIs
 * Usage: Access directly via browser: http://localhost:8000/test_api.php
 * 
 * API Base URL: http://165.22.59.174:8000
 */

// Configuration
$baseUrl = 'http://165.22.59.174:8000';
$cookieFile = sys_get_temp_dir() . '/test_api_cookies.txt';

// Start session for storing tokens and connectors
session_start();

// Token storage functions
function saveTokens($accessToken, $refreshToken) {
    $_SESSION['access_token'] = $accessToken;
    $_SESSION['refresh_token'] = $refreshToken;
}

function getAccessToken() {
    $token = $_SESSION['access_token'] ?? null;
    // Debug: log token retrieval
    if ($token) {
        $_SESSION['debug_token_retrieved'] = 'Token found: ' . substr($token, 0, 30) . '...';
    } else {
        $_SESSION['debug_token_retrieved'] = 'No token in session';
    }
    return $token;
}

function getRefreshToken() {
    return $_SESSION['refresh_token'] ?? null;
}

function clearTokens() {
    unset($_SESSION['access_token']);
    unset($_SESSION['refresh_token']);
}

// Initialize cURL session
function makeRequest($url, $method = 'GET', $data = null, $headers = [], $needsAuth = true) {
    global $cookieFile;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // Set headers
    $defaultHeaders = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];
    
    // Add authorization header if needed
    $accessToken = getAccessToken();
    if ($needsAuth) {
        if ($accessToken) {
            $tokenValue = trim($accessToken);
            $defaultHeaders[] = 'Authorization: Bearer ' . $tokenValue;
            $_SESSION['last_request_token'] = 'Token sent: ' . substr($tokenValue, 0, 30) . '...';
        } else {
            $_SESSION['last_request_token'] = 'ERROR: No token available but auth required!';
        }
    } else {
        $_SESSION['last_request_token'] = 'Auth not required for this endpoint';
    }
    
    $allHeaders = array_merge($defaultHeaders, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
    
    // Store headers sent for debugging
    $_SESSION['last_request_headers'] = $allHeaders;
    $_SESSION['last_request_url'] = $url;
    $_SESSION['last_request_method'] = $method;
    
    // Set HTTP method
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        } else {
            // Even if no data, ensure POST is set
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        }
    } elseif ($method === 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => $httpCode, 'success' => false];
    }
    
    $decoded = json_decode($response, true);
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'data' => $decoded !== null ? $decoded : $response,
        'raw' => $response
    ];
}

// Handle form submissions
$action = $_GET['action'] ?? '';
$result = null;
$apiUrl = null;

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    switch ($action) {
        case 'register':
            $apiUrl = $baseUrl . '/auth/register';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest(
                $apiUrl,
                'POST',
                [
                    'name' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? '',
                    'hb_master_password' => $_POST['hb_master_password'] ?? ($_POST['password'] ?? '') . '0'
                ],
                [],
                false // No auth needed for register
            );
            break;
            
        case 'login':
            $apiUrl = $baseUrl . '/auth/login';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest(
                $apiUrl,
                'POST',
                [
                    'email' => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? ''
                ],
                [],
                false // No auth needed for login
            );
            
            // Save tokens if login successful - handle different response structures
            $accessToken = null;
            $refreshToken = null;
            
            // Check if token is in data.access_token
            if (isset($result['data']['access_token'])) {
                $accessToken = $result['data']['access_token'];
                $refreshToken = $result['data']['refresh_token'] ?? null;
            }
            // Check if token is directly in data (some APIs return it this way)
            elseif (isset($result['data']) && is_array($result['data']) && isset($result['data'][0]['access_token'])) {
                $accessToken = $result['data'][0]['access_token'];
                $refreshToken = $result['data'][0]['refresh_token'] ?? null;
            }
            // Check if token is at root level
            elseif (isset($result['access_token'])) {
                $accessToken = $result['access_token'];
                $refreshToken = $result['refresh_token'] ?? null;
            }
            
            if ($accessToken) {
                saveTokens($accessToken, $refreshToken);
                $_SESSION['debug_login_success'] = 'Tokens saved. Access token: ' . substr($accessToken, 0, 30) . '...';
            } else {
                $_SESSION['debug_login_success'] = 'Login failed or no access_token found. Full response: ' . json_encode($result);
            }
            break;
            
        case 'logout':
            $refreshToken = getRefreshToken();
            if ($refreshToken) {
                $apiUrl = $baseUrl . '/auth/logout';
                $_SESSION['last_api_url'] = $apiUrl;
                $result = makeRequest(
                    $apiUrl,
                    'POST',
                    ['refresh_token' => $refreshToken],
                    [],
                    false
                );
                clearTokens();
            } else {
                $result = ['success' => false, 'data' => ['message' => 'No refresh token found']];
            }
            break;
            
        case 'refresh':
            $refreshToken = getRefreshToken();
            if ($refreshToken) {
                $apiUrl = $baseUrl . '/auth/refresh';
                $_SESSION['last_api_url'] = $apiUrl;
                $result = makeRequest(
                    $apiUrl,
                    'POST',
                    ['refresh_token' => $refreshToken],
                    [],
                    false
                );
                
                // Update tokens if refresh successful
                if ($result['success'] && isset($result['data']['access_token'])) {
                    saveTokens(
                        $result['data']['access_token'],
                        $result['data']['refresh_token'] ?? $refreshToken
                    );
                }
            } else {
                $result = ['success' => false, 'data' => ['message' => 'No refresh token found']];
            }
            break;
            
        case 'get_accounts':
            $apiUrl = $baseUrl . '/accounts';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest($apiUrl, 'GET');
            break;
            
        case 'get_account_credentials':
            $accountName = $_POST['account_name'] ?? '';
            if ($accountName) {
                $apiUrl = $baseUrl . '/accounts/' . urlencode($accountName) . '/credentials';
                $_SESSION['last_api_url'] = $apiUrl;
                $result = makeRequest($apiUrl, 'GET');
            } else {
                $result = ['success' => false, 'data' => ['message' => 'Account name required']];
            }
            break;
            
        case 'add_account':
            $accountName = $_POST['account_name'] ?? '';
            if ($accountName) {
                $url = $baseUrl . '/accounts/add-account?account_name=' . urlencode($accountName);
                $_SESSION['last_api_url'] = $url;
                $result = makeRequest($url, 'POST', null, [], true);
            } else {
                $result = ['success' => false, 'data' => ['message' => 'Account name required']];
            }
            break;
            
        case 'get_connectors':
            $apiUrl = $baseUrl . '/connectors';
            $_SESSION['last_api_url'] = $apiUrl;
            $connectorsResult = makeRequest($apiUrl, 'GET', null, [], true);
            $result = $connectorsResult;
            // Store connectors in session for dropdown
            if ($connectorsResult['success']) {
                // Handle different response formats
                $connectors = [];
                if (isset($connectorsResult['data'])) {
                    if (is_array($connectorsResult['data'])) {
                        $connectors = $connectorsResult['data'];
                    }
                } elseif (is_array($connectorsResult['data'])) {
                    $connectors = $connectorsResult['data'];
                }
                $_SESSION['connectors'] = $connectors;
            }
            break;
            
        case 'create_order':
            $apiUrl = $baseUrl . '/trading/orders';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest(
                $apiUrl,
                'POST',
                [
                    'account_name' => $_POST['account_name'] ?? '',
                    'connector_name' => $_POST['connector_name'] ?? '',
                    'trading_pair' => $_POST['trading_pair'] ?? 'BTC-USDT',
                    'trade_type' => $_POST['trade_type'] ?? 'BUY',
                    'amount' => floatval($_POST['amount'] ?? 1),
                    'order_type' => $_POST['order_type'] ?? 'LIMIT',
                    'price' => floatval($_POST['price'] ?? 0),
                    'position_action' => $_POST['position_action'] ?? 'OPEN'
                ]
            );
            break;
            
        case 'list_trades':
            $apiUrl = $baseUrl . '/trading/trades';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest($apiUrl, 'POST');
            break;
            
        case 'get_active_orders':
            $apiUrl = $baseUrl . '/trading/orders/active';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest($apiUrl, 'POST');
            break;
            
        case 'cancel_order':
            $accountName = $_POST['account_name'] ?? '';
            $connectorName = $_POST['connector_name'] ?? '';
            $clientOrderId = $_POST['client_order_id'] ?? '';
            if ($accountName && $connectorName && $clientOrderId) {
                $apiUrl = $baseUrl . '/trading/' . urlencode($accountName) . '/' . urlencode($connectorName) . '/orders/' . urlencode($clientOrderId) . '/cancel';
                $_SESSION['last_api_url'] = $apiUrl;
                $result = makeRequest($apiUrl, 'POST');
            } else {
                $result = ['success' => false, 'data' => ['message' => 'Account name, connector name, and client order ID are required']];
            }
            break;
            
        case 'create_bot':
            // Deploy V2 Script - this creates a bot
            $apiUrl = $baseUrl . '/bot-orchestration/deploy-v2-script';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest($apiUrl, 'POST', null, [], true);
            break;
            
        case 'get_bot_status':
            $botName = $_POST['bot_name'] ?? '';
            if ($botName) {
                $apiUrl = $baseUrl . '/bot-orchestration/' . urlencode($botName) . '/status';
            } else {
                $apiUrl = $baseUrl . '/bot-orchestration/status';
            }
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest($apiUrl, 'GET', null, [], true);
            break;
            
        case 'start_bot':
            $apiUrl = $baseUrl . '/bot-orchestration/start-bot';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest($apiUrl, 'POST', null, [], true);
            break;
            
        case 'stop_bot':
            $apiUrl = $baseUrl . '/bot-orchestration/stop-bot';
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest($apiUrl, 'POST', null, [], true);
            break;
            
        case 'get_bot_runs':
            $limit = $_POST['limit'] ?? 100;
            $offset = $_POST['offset'] ?? 0;
            $apiUrl = $baseUrl . '/bot-orchestration/bot-runs?limit=' . intval($limit) . '&offset=' . intval($offset);
            $_SESSION['last_api_url'] = $apiUrl;
            $result = makeRequest($apiUrl, 'GET', null, [], true);
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test - Trading App External API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin: 30px 0 15px 0;
            font-size: 1.3em;
        }
        h3 {
            color: #666;
            margin: 20px 0 10px 0;
            font-size: 1.1em;
        }
        .section {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #0d6efd;
        }
        button {
            background: #0d6efd;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-top: 10px;
        }
        button:hover {
            background: #0b5ed7;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #bb2d3b;
        }
        .btn-success {
            background: #198754;
        }
        .btn-success:hover {
            background: #157347;
        }
        .btn-warning {
            background: #ffc107;
            color: #000;
        }
        .btn-warning:hover {
            background: #ffb300;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .result.success {
            background: #d1e7dd;
            border-color: #badbcc;
            color: #0f5132;
        }
        .result.error {
            background: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
            margin-top: 10px;
        }
        .row {
            display: flex;
            gap: 15px;
        }
        .col {
            flex: 1;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .token-info {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        small {
            color: #666;
            font-size: 12px;
        }
        .api-url {
            margin-bottom: 10px;
            padding: 8px;
            background: #e9ecef;
            border-left: 3px solid #0d6efd;
            border-radius: 3px;
            font-size: 11px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            color: #495057;
        }
        .api-url strong {
            color: #0d6efd;
        }
        .api-url-before {
            margin-bottom: 8px;
            padding: 6px 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-left: 3px solid #0d6efd;
            border-radius: 3px;
            font-size: 10px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            color: #495057;
        }
        .api-url-before strong {
            color: #0d6efd;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Trading App External API Test Tool</h1>
        
        <div class="info">
            <strong>API Base URL:</strong> <?php echo $baseUrl; ?><br>
            <strong>Note:</strong> This is a standalone test file for testing external APIs. Make sure you login first to get access tokens.
        </div>

        <?php if (getAccessToken()): ?>
        <div class="token-info">
            <strong>✓ Authenticated</strong><br>
            Access Token: <?php echo substr(getAccessToken(), 0, 50); ?>...<br>
            Refresh Token: <?php echo getRefreshToken() ? substr(getRefreshToken(), 0, 50) . '...' : 'Not available'; ?><br>
            <?php if (isset($_SESSION['debug_token_retrieved'])): ?>
                <small style="color: #666;">Debug: <?php echo htmlspecialchars($_SESSION['debug_token_retrieved']); ?></small>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="token-info" style="background: #f8d7da; border-color: #f5c2c7; color: #842029;">
            <strong>✗ Not Authenticated</strong><br>
            Please login first to access protected endpoints.<br>
            <?php if (isset($_SESSION['debug_login_success'])): ?>
                <small style="color: #842029;">Debug: <?php echo htmlspecialchars($_SESSION['debug_login_success']); ?></small>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($result): ?>
        <div class="result <?php echo $result['success'] ? 'success' : 'error'; ?>" style="margin-bottom: 30px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <h3 style="margin: 0;"><?php echo $result['success'] ? '✓ Success' : '✗ Failure'; ?></h3>
                <span style="padding: 4px 12px; border-radius: 12px; background: <?php echo $result['success'] ? '#198754' : '#dc3545'; ?>; color: white; font-size: 12px; font-weight: bold;">
                    <?php echo $result['success'] ? 'SUCCESS' : 'FAILED'; ?>
                </span>
            </div>
            <?php if (isset($_SESSION['last_api_url'])): ?>
                <div class="api-url" style="margin-bottom: 10px;">
                    <strong>Called URL:</strong> <?php echo htmlspecialchars($_SESSION['last_api_url']); ?><br>
                    <strong>Method:</strong> <?php echo htmlspecialchars($_SESSION['last_request_method'] ?? 'GET'); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['last_request_token'])): ?>
                <div style="margin-top: 10px; padding: 8px; background: #fff3cd; border: 1px solid #ffecb5; border-radius: 3px; font-size: 12px;">
                    <strong>Token Status:</strong> <?php echo htmlspecialchars($_SESSION['last_request_token']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['last_request_headers'])): ?>
                <details style="margin-top: 10px;">
                    <summary style="cursor: pointer; font-weight: bold; color: #6c757d;">View Request Headers</summary>
                    <pre style="margin-top: 10px; font-size: 10px; background: #f8f9fa; padding: 10px; border-radius: 3px;"><?php echo htmlspecialchars(print_r($_SESSION['last_request_headers'], true)); ?></pre>
                </details>
            <?php endif; ?>
            <p><strong>HTTP Code:</strong> <?php echo $result['http_code']; ?></p>
            <?php if (isset($result['error'])): ?>
                <p><strong>Error:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
            <?php endif; ?>
            <?php if (isset($result['data']['message'])): ?>
                <div class="result-info">
                    <strong>Message:</strong> <?php echo htmlspecialchars($result['data']['message']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($result['data']['detail'])): ?>
                <div class="result-info" style="background: #f8d7da; border-color: #f5c2c7; color: #842029;">
                    <strong>Detail:</strong> <?php echo htmlspecialchars($result['data']['detail']); ?>
                </div>
            <?php endif; ?>
            <details style="margin-top: 15px;">
                <summary style="cursor: pointer; font-weight: bold; color: #0d6efd;">View Full Response</summary>
                <pre style="margin-top: 10px;"><?php echo json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></pre>
            </details>
        </div>
        <?php endif; ?>

        <!-- Authentication Section -->
        <div class="section">
            <h2>1. Authentication</h2>
            
            <h3>Register User</h3>
            <div class="api-url-before"><strong>API URL:</strong> POST <?php echo $baseUrl; ?>/auth/register</div>
            <form method="POST" action="?action=register">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" name="name" value="Test User" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="test<?php echo time(); ?>@example.com" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" value="12345678" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>HB Master Password:</label>
                            <input type="password" name="hb_master_password" value="1234567890" required>
                            <small>Default: password + "0"</small>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-success">Register</button>
            </form>

            <h3 style="margin-top: 30px;">Login</h3>
            <div class="api-url-before"><strong>API URL:</strong> POST <?php echo $baseUrl; ?>/auth/login</div>
            <form method="POST" action="?action=login">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" placeholder="user@example.com" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" placeholder="password" required>
                        </div>
                    </div>
                </div>
                <button type="submit">Login</button>
            </form>

            <h3 style="margin-top: 20px;">Token Management</h3>
            <div class="api-url-before"><strong>API URL:</strong> POST <?php echo $baseUrl; ?>/auth/refresh</div>
            <form method="POST" action="?action=refresh" style="display: inline-block; margin-right: 10px;">
                <button type="submit" class="btn-warning">Refresh Token</button>
            </form>
            <div class="api-url-before" style="display: inline-block; margin-left: 10px;"><strong>API URL:</strong> POST <?php echo $baseUrl; ?>/auth/logout</div>
            <form method="POST" action="?action=logout" style="display: inline-block;">
                <button type="submit" class="btn-danger">Logout</button>
            </form>
        </div>

        <!-- Accounts Section -->
        <div class="section">
            <h2>2. Accounts</h2>
            
            <h3>Get All Accounts</h3>
            <form method="POST" action="?action=get_accounts">
                <button type="submit">Get Accounts</button>
            </form>

            <h3 style="margin-top: 20px;">Add Account</h3>
            <form method="POST" action="?action=add_account">
                <div class="form-group">
                    <label>Account Name:</label>
                    <input type="text" name="account_name" value="TestAccount<?php echo time(); ?>" required>
                </div>
                <button type="submit" class="btn-success">Add Account</button>
            </form>

            <h3 style="margin-top: 20px;">Get Account Credentials</h3>
            <form method="POST" action="?action=get_account_credentials">
                <div class="form-group">
                    <label>Account Name:</label>
                    <input type="text" name="account_name" placeholder="account-name" required>
                </div>
                <button type="submit">Get Credentials</button>
            </form>
        </div>

        <!-- Connectors Section -->
        <div class="section">
            <h2>3. Connectors</h2>
            
            <h3>Get Connectors List</h3>
            <div class="api-url-before"><strong>API URL:</strong> GET <?php echo $baseUrl; ?>/connectors</div>
            <form method="POST" action="?action=get_connectors">
                <button type="submit">Get Connectors</button>
            </form>

            <?php if (isset($_SESSION['connectors']) && !empty($_SESSION['connectors'])): ?>
            <div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-radius: 5px;">
                <strong>Available Connectors:</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <?php foreach ($_SESSION['connectors'] as $connector): ?>
                        <li>
                            <?php 
                            $name = is_array($connector) ? ($connector['name'] ?? $connector['connector_name'] ?? 'Unknown') : $connector;
                            $id = is_array($connector) ? ($connector['id'] ?? $connector['connector_id'] ?? '') : '';
                            echo htmlspecialchars($name);
                            if ($id) echo ' (ID: ' . htmlspecialchars($id) . ')';
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <!-- Trading Section -->
        <div class="section">
            <h2>4. Trading</h2>
            
            <h3>Create Order (Start Trade)</h3>
            <div class="api-url-before"><strong>API URL:</strong> POST <?php echo $baseUrl; ?>/trading/orders</div>
            <form method="POST" action="?action=create_order">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Account Name:</label>
                            <input type="text" name="account_name" placeholder="master_account" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Connector Name:</label>
                            <select name="connector_name" required>
                                <option value="">-- Select Connector --</option>
                                <?php if (isset($_SESSION['connectors']) && is_array($_SESSION['connectors'])): ?>
                                    <?php foreach ($_SESSION['connectors'] as $connector): ?>
                                        <?php 
                                        $connName = is_array($connector) ? ($connector['name'] ?? $connector['connector_name'] ?? $connector['code'] ?? '') : $connector;
                                        $connValue = is_array($connector) ? ($connector['name'] ?? $connector['connector_name'] ?? $connector['code'] ?? '') : $connector;
                                        ?>
                                        <option value="<?php echo htmlspecialchars($connValue); ?>">
                                            <?php echo htmlspecialchars($connName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small>Click "Get Connectors" first to populate</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Trading Pair:</label>
                            <input type="text" name="trading_pair" value="BTC-USDT" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Trade Type:</label>
                            <select name="trade_type" required>
                                <option value="BUY">BUY</option>
                                <option value="SELL">SELL</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Amount:</label>
                            <input type="number" name="amount" value="1" step="0.0001" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Order Type:</label>
                            <select name="order_type" required>
                                <option value="LIMIT">LIMIT</option>
                                <option value="MARKET">MARKET</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Price:</label>
                            <input type="number" name="price" value="0" step="0.01">
                            <small>Required for LIMIT orders</small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Position Action:</label>
                            <select name="position_action" required>
                                <option value="OPEN">OPEN</option>
                                <option value="CLOSE">CLOSE</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-success">Create Order</button>
            </form>

            <h3 style="margin-top: 30px;">List Trades</h3>
            <form method="POST" action="?action=list_trades">
                <button type="submit">Get Trades</button>
            </form>

            <h3 style="margin-top: 20px;">Get Active Orders</h3>
            <form method="POST" action="?action=get_active_orders">
                <button type="submit">Get Active Orders</button>
            </form>

            <h3 style="margin-top: 20px;">Cancel Order (End Trade)</h3>
            <form method="POST" action="?action=cancel_order">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Account Name:</label>
                            <input type="text" name="account_name" placeholder="master_account" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Connector Name:</label>
                            <select name="connector_name" required>
                                <option value="">-- Select Connector --</option>
                                <?php if (isset($_SESSION['connectors']) && is_array($_SESSION['connectors']) && !empty($_SESSION['connectors'])): ?>
                                    <?php foreach ($_SESSION['connectors'] as $connector): ?>
                                        <?php 
                                        if (is_array($connector)) {
                                            $connName = $connector['name'] ?? $connector['connector_name'] ?? $connector['code'] ?? '';
                                            $connValue = $connector['name'] ?? $connector['connector_name'] ?? $connector['code'] ?? '';
                                        } else {
                                            $connName = $connector;
                                            $connValue = $connector;
                                        }
                                        if ($connValue): ?>
                                        <option value="<?php echo htmlspecialchars($connValue); ?>">
                                            <?php echo htmlspecialchars($connName); ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small>Click "Get Connectors" first to populate</small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Client Order ID:</label>
                    <input type="text" name="client_order_id" placeholder="order_123" required>
                </div>
                <button type="submit" class="btn-danger">Cancel Order</button>
            </form>
        </div>

        <!-- Bot Orchestration Section -->
        <div class="section">
            <h2>5. Bot Orchestration</h2>
            
            <h3>Create Bot</h3>
            <div class="api-url-before"><strong>API URL:</strong> POST <?php echo $baseUrl; ?>/bot-orchestration/deploy-v2-script</div>
            <form method="POST" action="?action=create_bot">
                <button type="submit" class="btn-success">Create Bot (Deploy V2 Script)</button>
            </form>

            <h3 style="margin-top: 20px;">Get Bot Status (Active Bot)</h3>
            <div class="api-url-before"><strong>API URL:</strong> GET <?php echo $baseUrl; ?>/bot-orchestration/status (or /{bot_name}/status)</div>
            <form method="POST" action="?action=get_bot_status">
                <div class="form-group">
                    <label>Bot Name (Optional - leave empty for all bots):</label>
                    <input type="text" name="bot_name" placeholder="example_bot">
                </div>
                <button type="submit">Get Bot Status</button>
            </form>

            <h3 style="margin-top: 20px;">Start Bot</h3>
            <div class="api-url-before"><strong>API URL:</strong> POST <?php echo $baseUrl; ?>/bot-orchestration/start-bot</div>
            <form method="POST" action="?action=start_bot">
                <button type="submit" class="btn-success">Start Bot</button>
            </form>

            <h3 style="margin-top: 20px;">Stop Bot (End Bot)</h3>
            <div class="api-url-before"><strong>API URL:</strong> POST <?php echo $baseUrl; ?>/bot-orchestration/stop-bot</div>
            <form method="POST" action="?action=stop_bot">
                <button type="submit" class="btn-danger">Stop Bot</button>
            </form>

            <h3 style="margin-top: 20px;">Get Bot Runs (List)</h3>
            <div class="api-url-before"><strong>API URL:</strong> GET <?php echo $baseUrl; ?>/bot-orchestration/bot-runs?limit={limit}&offset={offset}</div>
            <form method="POST" action="?action=get_bot_runs">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Limit:</label>
                            <input type="number" name="limit" value="100" min="1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Offset:</label>
                            <input type="number" name="offset" value="0" min="0">
                        </div>
                    </div>
                </div>
                <button type="submit">Get Bot Runs</button>
            </form>
        </div>
    </div>
</body>
</html>
