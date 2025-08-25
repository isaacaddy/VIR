@echo off
echo 🚀 Starting VIR (Vehicle Information Registry) with Docker...

REM Build and start services
echo 📦 Building and starting containers...
docker-compose up -d --build

REM Wait for services to be ready
echo ⏳ Waiting for services to be ready...
timeout /t 10 /nobreak >nul

REM Check service status
echo 🔍 Checking service status...
docker-compose ps

echo.
echo ✅ VIR is now running!
echo 🌐 Frontend: http://localhost
echo 🔧 Backend API: http://localhost:8000
echo 🗄️  phpMyAdmin: http://localhost:8080
echo 📊 MySQL: localhost:3306
echo.
echo To stop services: docker-compose down
echo To view logs: docker-compose logs -f
pause
