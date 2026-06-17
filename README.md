# Security Incident Reporting System

## Project Information
- **Project Title**: Security Incident Reporting System
- **Degree Program**: Cyber Security and Digital Forensics Engineering
- **Group Number**: 9
- **Institution**: University of Dodoma
- **Course**: Open Source Technologies (CP 222)
- **Academic Year**: 2026
- **Deadline**: 18th June 2026

## Project Overview
The Security Incident Reporting System is a comprehensive web-based application designed for organizations in Tanzania to effectively record, manage, and analyze security incidents. This system enables security professionals and staff to report security incidents, track their status, and generate reports for incident analysis and response.

## Project Description
This PHP-based application provides a centralized platform for security incident management with the following core functionalities:

### Key Features
1. **Record Security Incidents**
   - Capture incident details (type, severity, date/time, location)
   - Describe incident circumstances and impact
   - Assign incident handlers
   - Track incident status

2. **Display Incident Reports**
   - View comprehensive incident database
   - Display incident status and timeline
   - Generate incident statistics
   - View incident handler assignments

3. **Search Incidents**
   - Search incidents by incident ID
   - Filter by incident type and severity
   - Filter by date range
   - Advanced search capabilities

4. **User Management** (Mandatory Module)
   - User registration and authentication
   - Role-based access control (Admin, Analyst, Operator)
   - User profile management
   - Session management

## Technologies Used
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Web Server**: Apache
- **Version Control**: Git
- **Repository**: GitHub

## Installation Steps

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server with mod_rewrite enabled
- Git
- Web Browser

### Installation Instructions

1. **Clone the Repository**
   ```bash
   git clone https://github.com/SkU1ly00/OpenSource_Assignment_Program_Group_9.git
   cd OpenSource_Assignment_Program_Group_9
   ```

2. **Create Database**
   ```bash
   mysql -u root -p < database/security_incidents.sql
   ```

3. **Configure Database Connection**
   - Copy `config/config.example.php` to `config/config.php`
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'security_incidents_db');
   ```

4. **Set Directory Permissions**
   ```bash
   chmod -R 755 ./
   chmod -R 777 ./uploads/
   ```

5. **Access the Application**
   - Open browser and navigate to: `http://localhost/OpenSource_Assignment_Program_Group_9/`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`

6. **Change Default Password**
   - Log in with default credentials
   - Navigate to Settings > Change Password
   - Update to a secure password

## Project Structure
```
OpenSource_Assignment_Program_Group_9/
├── config/
│   ├── config.example.php       # Database configuration template
│   └── config.php               # Database configuration (create from template)
├── database/
│   └── security_incidents.sql   # Database schema and initial data
├── public/
│   ├── css/
│   │   └── style.css           # Main stylesheet
│   ├── js/
│   │   └── script.js           # JavaScript functionality
│   └── images/
├── src/
│   ├── classes/
│   │   ├── Database.php        # Database connection class
│   │   ├── User.php            # User management class
│   │   ├── Incident.php        # Incident management class
│   │   └── Auth.php            # Authentication class
│   ├── pages/
│   │   ├── dashboard.php       # Dashboard page
│   │   ├── incidents.php       # Incidents listing page
│   │   ├── add_incident.php    # Add incident form
│   │   ├── view_incident.php   # View incident details
│   │   ├── search.php          # Search functionality
│   │   ├── users.php           # User management (admin only)
│   │   ├── profile.php         # User profile
│   │   └── logout.php          # Logout handler
│   └── functions.php           # Utility functions
├── uploads/                     # Directory for incident attachments
├── index.php                    # Application entry point
├── login.php                    # Login page
├── .gitignore                   # Git ignore file
└── README.md                    # This file
```

## Git Commands Used

### Initial Setup
```bash
git init                          # Initialize repository
git add .                         # Add all files
git commit -m "Initial commit"    # Create initial commit
```

### Creating Commits During Development
```bash
git commit -m "Added user registration and authentication module"
git commit -m "Implemented incident recording form and database"
git commit -m "Added search functionality by incident ID"
git commit -m "Implemented incident status display and reporting"
git commit -m "Added documentation and finalized project"
```

### Branch Management
```bash
git branch development            # Create development branch
git checkout development          # Switch to development branch
git add .                        # Stage changes
git commit -m "Added advanced search and filtering features"
git checkout main                # Switch back to main
git merge development            # Merge development into main
```

### Remote Operations
```bash
git remote add origin https://github.com/SkU1ly00/OpenSource_Assignment_Program_Group_9.git
git branch -M main              # Rename branch to main
git push -u origin main         # Push to remote repository
git push origin development     # Push development branch
```

## GitHub Repository Link
- **Repository URL**: https://github.com/SkU1ly00/OpenSource_Assignment_Program_Group_9
- **Clone URL**: https://github.com/SkU1ly00/OpenSource_Assignment_Program_Group_9.git

## Features Breakdown

### User Management Module (Mandatory)
- User registration with email validation
- Login authentication with session management
- Password reset functionality
- Role-based access control (Admin, Analyst, Operator)
- User profile with activity tracking
- Change password functionality

### Incident Recording
- Comprehensive incident form with validation
- Incident type categorization
- Severity level assignment (Critical, High, Medium, Low)
- Location and date/time documentation
- Incident handler assignment
- Status tracking (Open, In Progress, Resolved, Closed)
- Attachment support for incident evidence

### Incident Display
- Paginated incident listing
- Sortable columns (date, severity, status)
- Quick view incident details
- Incident statistics dashboard
- Real-time incident count by severity
- Export to CSV functionality

### Search and Reporting
- Search by incident ID
- Advanced filters (type, severity, date range, status)
- Search result pagination
- Export search results
- Generate incident statistics reports

## Security Features
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF token implementation
- Password hashing with PHP's password_hash()
- Secure session management with timeouts
- Access control verification on all pages
- Activity logging for audit trails

## System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Apache**: 2.4+ with mod_rewrite enabled
- **RAM**: Minimum 512MB
- **Storage**: Minimum 100MB
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

## Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Opera 76+

## Default Login Credentials
```
Username: admin
Password: admin123
```

**Important**: Change the default password immediately after first login.

## Troubleshooting

### Database Connection Error
- Verify MySQL server is running
- Check database credentials in config.php
- Ensure database exists and is accessible
- Verify MySQL user permissions

### Permission Denied Error
```bash
chmod -R 755 ./
chmod -R 777 ./uploads/
```

### Login Issues
- Clear browser cookies and cache
- Reset password using admin panel
- Check user role permissions
- Verify session settings in config.php

## Future Enhancements
- Email notification system for incident updates
- SMS alerts for critical incidents
- Machine learning for incident categorization
- Mobile application
- REST API endpoints for third-party integration
- Incident analytics and predictive analysis
- Automated incident response triggers
- Two-factor authentication (2FA)

## Contributing Guidelines
For group members:
1. Create a feature branch: `git checkout -b feature/your-feature`
2. Make changes and commit: `git commit -m "descriptive message"`
3. Push to branch: `git push origin feature/your-feature`
4. Create Pull Request for review

## Disclaimer
This project is developed for educational purposes as part of the Open Source Technologies course (CP 222) at the University of Dodoma.

## References
- [PHP Documentation](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [OWASP Security Guidelines](https://owasp.org/)

## License
This project is provided for educational purposes under the course requirements of CP 222 - Open Source Technologies at the University of Dodoma.

---

**Last Updated**: June 2026
**Project Status**: Complete and Ready for Submission
**Group**: 9
**Institution**: University of Dodoma

## Contribution by Angela Muro
I contributed by improving the security features documentation and enhancing the clarity of the README file. I followed the Git workflow by working on a feature branch and submitting my changes through a pull request for review

Testing section added
