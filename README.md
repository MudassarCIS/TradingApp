# 🚀 AI Trade App - Complete Trading Platform

A comprehensive Laravel-based trading application with AI-powered agents, real-time market data, and advanced trading features.

## ✨ Features

### 🎯 **Core Features**
- **AI Trading Agents** - Rule-based automated trading with multiple strategies
- **Real-time Market Data** - Live prices from Binance and BingX APIs
- **Wallet System** - Multi-currency support with deposits/withdrawals
- **Referral System** - QR codes and commission tracking
- **Admin Panel** - Complete management interface
- **Customer Panel** - Modern responsive dashboard
- **Notification System** - Real-time alerts and updates

### 🔧 **Technical Features**
- **Laravel 12** with latest features
- **Bootstrap 5** responsive design
- **Real-time WebSocket** server
- **API Integration** (Binance & BingX)
- **Role-based Access Control**
- **Database Migrations** & Seeders
- **Console Commands** for automation

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Node.js (for frontend assets)

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd ai-trade-app
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database setup**
```bash
php artisan migrate
php artisan db:seed
```

5. **Start the application**
```bash
php artisan serve
```

### Default Credentials

**Admin Account:**
- Email: `admin@aitradeapp.com`
- Password: `password`

**Customer Account:**
- Email: `customer@example.com`
- Password: `password`

## 📊 Database Structure

### Core Tables
- `users` - User accounts with roles
- `profiles` - Extended user information
- `wallets` - Multi-currency wallet system
- `transactions` - All financial movements
- `trades` - Trading records
- `agents` - AI trading agents
- `referrals` - Referral system
- `messages` - Support system
- `notifications` - System alerts
- `packages` - Investment packages
- `api_accounts` - Exchange API keys

## 🎮 Usage

### Customer Panel
1. **Dashboard** - Overview of trading activity
2. **Market** - Real-time cryptocurrency prices
3. **Wallet** - Deposit/withdraw funds
4. **AI Agents** - Create and manage trading bots
5. **Referrals** - Share links and earn commissions
6. **Profile** - Account settings and KYC

### Admin Panel
1. **Dashboard** - System overview and analytics
2. **Users** - Manage customer accounts
3. **Trades** - Monitor all trading activity
4. **Transactions** - Approve deposits/withdrawals
5. **Agents** - Manage AI trading agents
6. **Support** - Handle customer messages

## 🤖 AI Trading Engine

### Features
- **Multiple Strategies** - RSI, MACD, Volume, Momentum
- **Risk Management** - Stop loss, take profit
- **Automated Execution** - Real-time order placement
- **Performance Tracking** - Win rate, profit/loss

### Console Commands
```bash
# Run trading engine
php artisan trading:run

# Start WebSocket server
php artisan websocket:start --port=8080
```

## 🔌 API Integration

### Supported Exchanges
- **Binance** - Primary exchange
- **BingX** - Secondary exchange

### API Endpoints
- `GET /api/price/{symbol}` - Get current price
- `GET /api/ticker/{symbol}` - Get 24hr ticker data
- `GET /api/klines/{symbol}/{interval}` - Get candlestick data
- `GET /api/market-data/{symbol}` - Get complete market data
- `POST /api/prices` - Get multiple prices

## 💰 Business Logic

### Profit Sharing
- **Admin Share**: 50% of all trading profits
- **Customer Share**: 50% of trading profits
- **Referral Commission**: 10% of referred user profits

### Trading Rules
- **Minimum Trade**: $10
- **Maximum Risk**: 5% per trade
- **Default Stop Loss**: 5%
- **Default Take Profit**: 10%

## 🎨 Design Features

### Color Scheme
- **Primary**: Blue (#007bff)
- **Secondary**: Green (#28a745)
- **Accent**: Purple (#6f42c1)

### Responsive Design
- **Mobile-first** approach
- **Bootstrap 5** components
- **Custom gradients** and animations
- **Touch-friendly** interface

## 🔒 Security Features

- **Role-based Access Control**
- **Transaction Password** for withdrawals
- **API Key Encryption**
- **Rate Limiting**
- **CSRF Protection**

## 📱 Mobile Support

- **Responsive Layout** - Works on all devices
- **Touch Gestures** - Swipe and tap support
- **Mobile Navigation** - Collapsible sidebar
- **Optimized Performance** - Fast loading

## 🚀 Deployment

### Production Setup
1. **Environment Variables**
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
BINANCE_API_KEY=your_api_key
BINGX_API_KEY=your_api_key
```

2. **Queue Workers**
```bash
php artisan queue:work
```

3. **Trading Engine**
```bash
php artisan trading:run
```

4. **WebSocket Server**
```bash
php artisan websocket:start
```

## 📈 Performance

### Optimization Features
- **Database Indexing** - Optimized queries
- **Caching** - Redis/Memcached support
- **CDN Ready** - Static asset optimization
- **Lazy Loading** - Efficient data loading

## 🔧 Configuration

### Trading Settings
```php
// config/services.php
'trading' => [
    'default_risk_per_trade' => 2, // 2%
    'max_risk_per_trade' => 5, // 5%
    'min_trade_amount' => 10, // $10
    'max_trade_amount' => 10000, // $10,000
    'default_stop_loss' => 5, // 5%
    'default_take_profit' => 10, // 10%
    'commission_rate' => 0.1, // 0.1%
    'admin_profit_share' => 50, // 50%
],
```

## 🐛 Troubleshooting

### Common Issues
1. **API Connection Failed**
   - Check API keys in `.env`
   - Verify network connectivity
   - Check rate limits

2. **Trading Engine Not Working**
   - Ensure database is migrated
   - Check agent configurations
   - Verify API permissions

3. **WebSocket Connection Failed**
   - Check port availability
   - Verify firewall settings
   - Check client-side code

## 📝 License

This project is licensed under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📞 Support

For support and questions:
- **Email**: support@aitradeapp.com
- **Documentation**: [Wiki](link-to-wiki)
- **Issues**: [GitHub Issues](link-to-issues)

---

**Built with ❤️ using Laravel 12, Bootstrap 5, and modern web technologies.**