# 🚨 Rescue Coordination System

A comprehensive platform for coordinating rescue agencies during natural and man-made disasters. Enables real-time tracking, inter-agency communication, and resource management to enhance response time and save lives.

## 📋 Features

- **Real-time Disaster Map** — Live tracking of disasters and agency locations using OpenStreetMap
- **Agency Registration & Authentication** — Secure registration and login for rescue organizations
- **Disaster Reporting** — Report new disasters with location, severity, and type classification
- **Resource Management** — Track and allocate resources across agencies
- **Inter-Agency Communication** — Coordinate response efforts between organizations
- **AI-Powered Predictions** — Predict disaster progression and optimize resource allocation
- **Analytics Dashboard** — Evaluate response effectiveness

## 🛠️ Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.x |
| Database | MySQL / MariaDB |
| Frontend | HTML, CSS (Tailwind), JavaScript |
| Maps | Leaflet.js + OpenStreetMap |
| Icons | Font Awesome |

## 📁 Project Structure

```
rescue-coordination/
├── api/                    # REST API endpoints
│   ├── get-agencies.php
│   └── get-disasters.php
├── assets/                 # Static assets
│   ├── css/style.css
│   ├── js/map.js
│   └── images/
├── config/                 # Application configuration
│   ├── config.php
│   └── database.php
├── database/               # Database schema
│   └── schema.sql
├── includes/               # Shared PHP includes
│   ├── auth.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── services/               # Service layer
├── .env.example            # Environment variable template
├── .gitignore              # Git ignore rules
├── .htaccess               # Apache configuration
├── index.php               # Main entry point
├── login.php               # Login page
├── logout.php              # Logout handler
├── register.php            # Agency registration
├── disasters.php           # Disasters listing
├── disaster-details.php    # Disaster detail view
├── report-disaster.php     # Report new disaster
├── agencies.php            # Agencies listing
├── resources.php           # Resources directory
├── install.php             # Installation checker
├── setup-database.php      # Database setup
├── test-connection.php     # Connection tester
└── README.md
```

## 🚀 Quick Start

### Prerequisites

- **PHP 8.0+** with PDO MySQL extension
- **MySQL 5.7+** or **MariaDB 10.4+**

### Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/UdayShankarPandey/rescue_coordination.git
   cd rescue_coordination
   ```

2. **Create environment file:**
   ```bash
   cp .env.example .env
   ```

3. **Edit `.env`** with your database credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=rescue_coordination
   DB_USER=root
   DB_PASS=your_password_here
   ```

4. **Create the database:**
   ```bash
   mysql -u root -p -e "CREATE DATABASE rescue_coordination;"
   mysql -u root -p rescue_coordination < database/schema.sql
   ```

5. **Start the development server:**
   ```bash
   php -S localhost:8000
   ```

6. **Open your browser:** [http://localhost:8000](http://localhost:8000)

## 🔒 Security Notes

- **Never commit `.env` files** — they contain database passwords and API keys
- `.env` is listed in `.gitignore` to prevent accidental commits
- Use `.env.example` as a reference template (contains placeholder values only)
- All user passwords are hashed with bcrypt
- SQL injection prevented via PDO prepared statements

## 📊 Database

The database schema is defined in `database/schema.sql` and includes:

- `agencies` — Registered rescue organizations
- `disasters` — Reported disaster events
- `agency_locations` — Agency response tracking
- `resources` — Available resources per agency
- `resource_requests` — Inter-agency resource requests
- `communications` — Agency messaging

## 🗺️ Roadmap

- [x] Core application development
- [x] Project structure cleanup for DevOps
- [ ] Dockerize application (PHP + MySQL containers)
- [ ] CI/CD pipeline with Jenkins
- [ ] Deploy to AWS EKS with Kubernetes
- [ ] Production monitoring & logging

## 📄 License

This project is for educational and portfolio purposes.

## 👤 Author

**Uday Shankar Pandey**
- GitHub: [@UdayShankarPandey](https://github.com/UdayShankarPandey)
