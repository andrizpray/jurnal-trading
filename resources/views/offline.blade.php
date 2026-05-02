<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - Trading Journal</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        
        .offline-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }
        
        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
            font-size: 16px;
        }
        
        .features {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
            text-align: left;
        }
        
        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .feature:last-child {
            margin-bottom: 0;
        }
        
        .feature i {
            color: #0ea5e9;
            margin-right: 12px;
            font-size: 16px;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3);
        }
        
        .button:active {
            transform: translateY(0);
        }
        
        .status {
            margin-top: 20px;
            font-size: 14px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            animation: pulse 2s infinite;
        }
        
        .status-dot.online {
            background: #10b981;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @media (max-width: 480px) {
            .offline-card {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            p {
                font-size: 15px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="offline-card">
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
            
            <h1>You're Offline</h1>
            <p>Don't worry! You can still access your trading journal and record trades while offline.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-check-circle"></i>
                    <span>View your trading history</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Record new trades (will sync later)</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Access your trading plan</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Review analytics dashboard</span>
                </div>
            </div>
            
            <button class="button" onclick="window.location.reload()">
                <i class="fas fa-sync-alt mr-2"></i> Try to Reconnect
            </button>
            
            <div class="status">
                <div class="status-dot" id="statusDot"></div>
                <span id="statusText">Checking connection...</span>
            </div>
        </div>
    </div>

    <script>
        // Check connection status
        function updateConnectionStatus() {
            const statusDot = document.getElementById('statusDot');
            const statusText = document.getElementById('statusText');
            
            if (navigator.onLine) {
                statusDot.classList.add('online');
                statusText.textContent = 'Back online! Refreshing...';
                setTimeout(() => window.location.href = '/', 2000);
            } else {
                statusDot.classList.remove('online');
                statusText.textContent = 'You are offline';
            }
        }
        
        // Initial check
        updateConnectionStatus();
        
        // Listen for connection changes
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // Check every 5 seconds
        setInterval(updateConnectionStatus, 5000);
        
        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('Service Worker registered:', registration);
                })
                .catch(error => {
                    console.log('Service Worker registration failed:', error);
                });
        }
        
        // Check if app is installed
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA install prompt available');
        });
    </script>
</body>
</html>