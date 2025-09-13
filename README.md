# Syverum
> Lightweight and modular PHP framework with **MVC** interface  

---

## About SyverumX
SyverumX simplifies routing, controllers, and views on top of a decoupled and modular core.  
Its goal is to minimize accidental complexity and initial setup, while maintaining extensibility through modules and providers.

**Key Features:**
- Simple and fast routing  
- Expressive MVC (Controllers, Views/Blade, Middlewares)  
- Modular core (Boot, Services, Routing, Support, Monitoring Panel)  
- PSR standards and service container (DI/IoC)  
- `.env` configuration and clear project structure  

---

## Requirements
- PHP 8.x  
- Composer  
- Node.js  
- Local server (e.g., XAMPP)  

---

## Installation
```bash
# 1) Install Syverum
composer global require syverum/installer

# 2) Create a new project
syverum new example-app

# 3) Start the application
cd example-app
composer run dev