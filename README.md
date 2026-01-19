# Digital Wallet API

Laravel 12 ile geliştirilmiş profesyonel dijital cüzdan ve para transferi API sistemi.

## Hızlı Başlangıç

### Gereksinimler
- PHP 8.3+
- Composer
- MySQL 8+ / MariaDB 10.6+
- Redis

### Kurulum
```bash
# 1. Projeyi klonla
git clone <repository-url>
cd digital-wallet-api

# 2. Bağımlılıkları yükle
composer install

# 3. Environment dosyasını oluştur
cp .env.example .env
php artisan key:generate

# 4. .env dosyasını düzenle
DB_DATABASE=digital_wallet
DB_USERNAME=root
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# 5. Veritabanını oluştur
mysql -u root -p
CREATE DATABASE digital_wallet;
exit;

# 6. Migration ve seed çalıştır
php artisan migrate
php artisan db:seed

# 7. Uygulamayı başlat
php artisan serve
```

## Docker ile Kurulum

```bash
# 1. Projeyi klonla
git clone 
cd digital-wallet-api

# 2. Environment dosyasını oluştur
cp .env.example .env

# 3. Docker container'ları başlat
docker-compose up -d

# 4. Container içinde bağımlılıkları yükle
docker-compose exec app composer install

# 5. Uygulama key'i oluştur
docker-compose exec app php artisan key:generate

# 6. Migration ve seed çalıştır
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# 7. İzinleri ayarla
docker-compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

API: `http://localhost:8000/api/v1`

## Demo Kullanıcılar
```
Admin: admin@example.com / password
User:  user@example.com / password
```

## API Endpoints

### Authentication
```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
```

### Wallets
```
GET    /api/v1/wallets
POST   /api/v1/wallets
GET    /api/v1/wallets/{id}
GET    /api/v1/wallets/{id}/balance
GET    /api/v1/wallets/{id}/transactions
```

### Transactions
```
POST   /api/v1/transactions/deposit
POST   /api/v1/transactions/withdraw
POST   /api/v1/transactions/transfer
GET    /api/v1/transactions/{id}
```

## Kullanım Örneği

### 1. Giriş Yap
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@deneme.com",
    "password": "password"
  }'
```

Yanıt:
```json
{
  "user": {...},
  "token": "1|abc123..."
}
```

### 2. Cüzdan Oluştur
```bash
curl -X POST http://localhost:8000/api/v1/wallets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"currency": "TRY"}'
```

### 3. Para Yatır
```bash
curl -X POST http://localhost:8000/api/v1/transactions/deposit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "wallet_id": 1,
    "amount": 5000
  }'
```

### 4. Transfer Yap
```bash
curl -X POST http://localhost:8000/api/v1/transactions/transfer \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "from_wallet_id": 1,
    "to_wallet_id": 4,
    "amount": 1000
  }'
```

---

## Test Araçları

### Postman Koleksiyonu

API'yi hızlıca test etmek için hazır koleksiyon ve ortam dosyaları projenin `/postman` klasöründe yer almaktadır.

**Dosyalar:**

* `postman/Digital Wallet API.postman_collection.json` (İstek listesi)
* `postman/Digital Wallet - Local.postman_environment.json` (Değişkenler ve Base URL)

**Nasıl Kullanılır?**

1. Postman uygulamasını açın ve sol üstteki **Import** butonuna tıklayın.
2. `/postman` klasöründeki iki dosyayı seçerek içeri aktarın.
3. Sağ üstteki ortam listesinden **"Digital Wallet - Local"** seçeneğini aktif edin.
4. `auth/login` isteğini gönderdiğinizde, dönen `access_token` otomatik olarak ortama kaydedilecek ve diğer isteklerde (Bearer Token) otomatik kullanılacaktır.

---

## Testler
```bash
# Tüm testleri çalıştır
php artisan test

# Coverage ile
php artisan test --coverage
```

## Artisan Komutları
```bash
# Günlük rapor
php artisan wallet:daily-reconciliation

# Belirli tarih için
php artisan wallet:daily-reconciliation --date=2025-01-15

## Mimari

### Design Patterns
- ✅ Repository Pattern - Veri erişim soyutlaması
- ✅ Service Layer - Business logic ayrımı
- ✅ Strategy Pattern - Dinamik komisyon hesaplama
- ✅ Pipeline Pattern - Fraud detection kuralları
- ✅ Event/Listener - Asenkron işlemler

### Klasör Yapısı
```
app/
├── Console/Commands/      # Artisan komutları
├── Enums/                 # Enum sınıfları
├── Events/                # Event sınıfları
├── Http/
│   ├── Controllers/       # API controllers
│   ├── Requests/          # Form validation
│   └── Resources/         # JSON resources
├── Listeners/             # Event listeners
├── Models/                # Eloquent models
├── Policies/              # Authorization policies
├── Repositories/          # Repository pattern
├── Rules/                 # Custom validation rules
└── Services/              # Business logic
    ├── FeeCalculator/     # Strategy pattern
    └── FraudDetection/    # Pipeline pattern
```

## Güvenlik

- **Authentication**: Laravel Sanctum (24 saat token)
- **Authorization**: Policy classes + Gate definitions
- **Rate Limiting**: 
  - API: 60 req/min
  - Auth: 5 req/min
  - Transfer: 10 req/min
- **Fraud Detection**: 5 farklı kural
- **Idempotency**: Transfer işlemlerinde tekrar önleme

## İş Kuralları

### Transaction Limits
- **Günlük limit**: 50,000 TRY (eşdeğeri)
- **Tek işlem max**: 10,000 TRY
- **Saatlik limit**: Aynı kullanıcıya max 3 transfer

### Fee Structure
| Tutar | Komisyon |
|-------|----------|
| 0 - 1,000 TRY | 2 TRY (sabit) |
| 1,001 - 10,000 TRY | %0.5 |
| 10,001+ TRY | 2 TRY + kalan için %0.3 |

### Fraud Detection
- 1 saat içinde 5+ farklı kullanıcıya transfer
- Günlük limitin %80'ine ulaşma
- Gece 02:00-06:00 arası 5,000+ TRY
- Yeni hesap (7 gün) + 10,000+ TRY işlem
- Aynı IP'den farklı hesaplar

## İletişim

Sorularınız için: yasinugurb@ymail.com