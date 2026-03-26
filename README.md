# HousingHub - Property Management System

A comprehensive web-based property management system designed for Uganda's real estate market. Built with PHP and MySQL, HousingHub streamlines property management, tenant relations, and financial operations for property owners, managers, and tenants.

## ⚠️ Security Notice

**CRITICAL**: This application contains security vulnerabilities and is NOT ready for production deployment. See [BUILD_PLAN.md](BUILD_PLAN.md) for the security hardening roadmap. Key issues include:

- Exposed credentials in source code
- SQL injection vulnerabilities
- Missing CSRF protection
- Insecure file uploads

**Do not deploy to production until all Phase 1 security fixes are completed.**

## 🌟 Features

### Core Functionality
- **Multi-Role User Management**: Admin, Property Owner, Staff, Tenant, Broker, and Visitor roles
- **Property Management**: Complete CRUD operations for property listings with amenities, images, and pricing
- **Tenant Management**: Lease agreements, tenant profiles, and status tracking
- **Payment Processing**: Integrated with Flutterwave, Mobile Money, and Bank Transfer options
- **Maintenance Requests**: Submit, assign, and track maintenance work orders
- **Guest/Visitor Management**: Registration and approval system for property viewings
- **Complaints System**: Tenant feedback and resolution tracking
- **Inspections**: Schedule and complete property inspections
- **Document Management**: Secure tenant document storage and retrieval
- **Notifications**: Real-time system notifications and messaging
- **Reporting**: Comprehensive analytics and export capabilities (CSV/SQL)

### Advanced Features
- **Commission Management**: Track broker commissions and leads
- **Job Applications**: Employment recruitment system
- **Property Reviews**: Rating and review system
- **Favorites**: Save and bookmark properties
- **Task Management**: Assign and track staff tasks
- **AI Chatbot**: Basic conversational assistant for user support

## 🛠️ Technologies Used

### Backend
- **PHP 7.4+** (Procedural architecture)
- **MySQL/MariaDB** (Database)
- **PHPMailer** (Email handling)
- **Composer** (Dependency management)

### Frontend
- **HTML5** (Structure)
- **CSS3** (Styling)
- **Vanilla JavaScript** (Interactivity)
- **Google Fonts** (Typography)

### Payment Integration
- **Flutterwave API** (Payment processing)
- **Africa's Talking API** (SMS notifications)

### Development Environment
- **WAMP64** (Windows, Apache, MySQL, PHP)
- **Apache** (Web server)
- **phpMyAdmin** (Database management)

## 📋 Prerequisites

Before running this application, ensure you have:

- **WAMP64** installed (or equivalent LAMP stack)
- **PHP 7.4 or higher**
- **MySQL 5.7+**
- **Composer** (for dependency management)
- **Web browser** (Chrome, Firefox, Safari, Edge)

## 🚀 Installation & Setup

### 1. Clone or Download
```bash
# If using Git
git clone https://github.com/yourusername/housinghub.git

# Or download the ZIP file and extract to your WAMP www directory
```

### 2. Database Setup
1. Start WAMP64 and ensure MySQL is running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `housinghub`
4. Import the database schema:
   - Go to the `BIN/` folder
   - Import `housinghub.sql` into your database

### 3. Configuration
1. Copy `config.php` and update database credentials if needed:
   ```php
   $servername = "localhost";
   $username = "root";  // Default WAMP username
   $password = "";      // Default WAMP password (empty)
   $dbname = "housinghub";
   ```

2. Update email configuration in `Send_mail.php`:
   ```php
   $mail->Username = 'your-email@gmail.com';
   $mail->Password = 'your-app-password';
   ```

3. Configure payment gateways in `config.php`:
   - Update Flutterwave API keys
   - Update Africa's Talking credentials

### 4. Install Dependencies
```bash
# Navigate to project directory
cd C:\wamp64\www\Housing_Hub

# Install PHP dependencies
composer install
```

### 5. File Permissions
Ensure the following directories are writable:
- `uploads/`
- `tenant_docs/`
- `property_media/`

### 6. Access the Application
1. Start WAMP64
2. Open your browser and go to: `http://localhost/Housing_Hub`
3. Register as admin using the admin secret (check `auth.php` for current secret)
4. Create additional users through the admin dashboard

## 📖 Usage

### User Roles & Access

#### Admin
- Full system access
- User management
- System configuration
- Reporting and analytics

#### Property Owner
- Manage owned properties
- View tenant information
- Track payments and commissions

#### Staff
- Handle maintenance requests
- Perform property inspections
- Manage tenant relations
- Execute assigned tasks

#### Tenant
- View lease agreements
- Make rent payments
- Submit maintenance requests
- Access documents

#### Broker
- Browse property listings
- Manage leads and commissions
- Submit property applications

#### Visitor/Guest
- Browse available properties
- Schedule property viewings
- Register for guest access

### Key Workflows

1. **Property Onboarding**: Admin/Property Owner adds property → Upload images → Set pricing → Mark as available
2. **Tenant Registration**: Tenant applies → Admin approves → Lease agreement → Payment setup
3. **Rent Collection**: Automated reminders → Payment processing → Receipt generation
4. **Maintenance**: Tenant submits request → Staff assigns → Work completion → Resolution tracking

## 🔧 Configuration

### Environment Variables
Create a `.env` file in the root directory for sensitive data:
```
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=housinghub

MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password

FLUTTERWAVE_PUBLIC_KEY=your-public-key
FLUTTERWAVE_SECRET_KEY=your-secret-key
```

### Payment Gateway Setup
1. Sign up for Flutterwave account
2. Get API keys from dashboard
3. Update webhook URLs in Flutterwave settings
4. Test payments in sandbox mode

## 🐛 Known Issues & Limitations

- Some security hardening required for production use
- File upload validation needs enhancement
- Database relationships could be strengthened
- Code refactoring recommended for scalability

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines
- Follow PHP PSR standards where possible
- Use prepared statements for database queries
- Validate all user inputs
- Comment complex business logic
- Test thoroughly before committing

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- Email: support@housinghub.com
- Documentation: [Wiki](https://github.com/yourusername/housinghub/wiki)
- Issues: [GitHub Issues](https://github.com/yourusername/housinghub/issues)

## 🙏 Acknowledgments

- Built for Uganda's growing property market
- Inspired by modern property management solutions
- Special thanks to the open-source community

---

**HousingHub** - Making property management simple and efficient in Uganda.</content>
<parameter name="filePath">c:\wamp64\www\Housing_Hub\README.md