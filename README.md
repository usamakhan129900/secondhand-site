# Online Second-Hand Sales System

A comprehensive web-based platform for buying and selling second-hand items, built with PHP and SQLite.

## Features

### User Management
- **User Registration & Authentication**: Secure user registration and login system
- **User Profiles**: Personal user profiles with contact information
- **Admin Panel**: Administrative interface for managing users and products
- **Role-Based Access**: Separate access levels for regular users and administrators

### Product Management
- **Product Listings**: Users can upload and manage their second-hand items
- **Image Upload**: Support for product images with automatic file handling
- **Product Categories**: Organized categorization system (Electronics, Furniture, Clothing, etc.)
- **Product Conditions**: Condition status tracking (Excellent, Good, Fair, Poor)
- **Price Management**: Flexible pricing system with search filters

### Search & Discovery
- **Advanced Search**: Search by keywords, category, and price range
- **Category Filtering**: Browse products by specific categories
- **Price Range Filtering**: Filter products within specific price ranges
- **Real-time Results**: Dynamic search results with responsive interface

### Communication Features
- **Product Comments**: Public comment system for product inquiries
- **Seller Contact**: Direct contact information for buyers to reach sellers
- **Email Integration**: Built-in email links for communication

### Administrative Features
- **User Management**: Admin can view, manage, and delete user accounts
- **Product Oversight**: Admin can manage and remove inappropriate products
- **Statistics Dashboard**: Overview of system usage and activity
- **Content Moderation**: Tools for maintaining platform quality

## Technology Stack

### Backend
- **PHP 8.1**: Server-side scripting language
- **SQLite**: Lightweight database for data storage
- **PDO**: Database abstraction layer for secure queries
- **Session Management**: Secure user session handling

### Frontend
- **HTML5**: Modern markup structure
- **CSS3**: Advanced styling with custom design system
- **Bootstrap 5**: Responsive framework for mobile-first design
- **JavaScript**: Interactive functionality and form validation
- **Font Awesome**: Icon library for enhanced UI

### Security Features
- **Password Hashing**: Secure password storage using PHP's password_hash()
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Session-based security measures
- **File Upload Security**: Validated file types and size limits

## Installation Guide

### Prerequisites
- PHP 8.1 or higher
- SQLite3 extension
- Web server (Apache, Nginx, or PHP built-in server)
- Modern web browser

### Setup Instructions

1. **Download and Extract**
   ```bash
   # Extract the project files to your web directory
   unzip secondhand-site.zip
   cd secondhand-site
   ```

2. **Set Permissions**
   ```bash
   # Make uploads directory writable
   chmod 755 uploads/
   chmod 644 *.php
   ```

3. **Database Setup**
   The SQLite database will be created automatically when you first access the site. The system includes:
   - Automatic table creation
   - Default categories insertion
   - Default admin user creation

4. **Start the Server**
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   
   # Or configure your web server to point to the project directory
   ```

5. **Access the Application**
   Open your web browser and navigate to:
   - Local development: `http://localhost:8000`
   - Production: Your configured domain

### Default Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@secondhand.com`

*Important: Change the default admin password after first login*

## File Structure

```
secondhand-site/
├── index.php                 # Homepage with product listings
├── login.php                 # User/Admin login form
├── register.php              # New user registration
├── logout.php                # Session logout handler
├── dashboard.php             # User dashboard and product management
├── upload.php                # Product upload form
├── product.php               # Individual product view with comments
├── admin.php                 # Admin control panel
├── delete_product.php        # Product deletion handler
├── delete_user.php           # User deletion handler (admin only)
├── search.php                # Search form handler
├── config.php                # Database configuration and utilities
├── session.php               # Authentication and session management
├── database.sqlite           # SQLite database file (auto-created)
│
├── uploads/                  # Product image storage
│   └── (uploaded files)
│
├── includes/                 # Reusable PHP components
│   ├── header.php           # HTML header and navigation
│   └── footer.php           # HTML footer
│
├── css/
│   └── style.css            # Custom styling and design system
│
├── js/
│   └── scripts.js           # Interactive JavaScript functionality
│
├── sql/
│   └── schema.sql           # Database schema reference
│
└── README.md                # This documentation file
```

## Database Schema

### Users Table
- `id`: Primary key (auto-increment)
- `username`: Unique username (50 chars)
- `email`: Unique email address (100 chars)
- `password`: Hashed password (255 chars)
- `full_name`: User's full name (100 chars)
- `phone`: Contact phone number (20 chars)
- `address`: User address (text)
- `user_type`: Role (user/admin)
- `created_at`: Registration timestamp
- `updated_at`: Last update timestamp
- `is_active`: Account status (boolean)

### Products Table
- `id`: Primary key (auto-increment)
- `user_id`: Foreign key to users table
- `title`: Product title (200 chars)
- `description`: Product description (text)
- `price`: Product price (decimal 10,2)
- `category`: Product category (50 chars)
- `condition_status`: Item condition (excellent/good/fair/poor)
- `image_path`: Uploaded image filename (255 chars)
- `location`: Seller location (100 chars)
- `is_sold`: Sale status (boolean)
- `created_at`: Listing timestamp
- `updated_at`: Last update timestamp

### Comments Table
- `id`: Primary key (auto-increment)
- `product_id`: Foreign key to products table
- `user_id`: Foreign key to users table
- `comment`: Comment text (text)
- `created_at`: Comment timestamp

### Categories Table
- `id`: Primary key (auto-increment)
- `name`: Category name (50 chars)
- `description`: Category description (text)
- `created_at`: Creation timestamp

### Messages Table (Future Enhancement)
- `id`: Primary key (auto-increment)
- `sender_id`: Foreign key to users table
- `receiver_id`: Foreign key to users table
- `product_id`: Foreign key to products table
- `subject`: Message subject (200 chars)
- `message`: Message content (text)
- `is_read`: Read status (boolean)
- `created_at`: Message timestamp

## Usage Guide

### For Regular Users

#### Registration and Login
1. Click "Register" to create a new account
2. Fill in required information (username, email, full name, password)
3. Optional: Add phone number and address for better seller contact
4. Login with your credentials to access the dashboard

#### Selling Items
1. Navigate to "Sell Item" or click "Add New Product" from dashboard
2. Fill in product details:
   - Title and description
   - Price and category
   - Condition status
   - Location (optional)
   - Upload product image (optional)
3. Submit to list your item for sale

#### Buying Items
1. Browse the homepage for available products
2. Use search filters to find specific items
3. Click on products to view detailed information
4. Contact sellers through provided email/phone information
5. Leave comments or questions on product pages

#### Managing Your Products
1. Access your dashboard to view all your listings
2. Monitor comments and inquiries on your products
3. Delete products when sold or no longer available

### For Administrators

#### Admin Panel Access
1. Login with admin credentials
2. Access "Admin Panel" from the navigation menu
3. View system statistics and recent activity

#### User Management
1. View all registered users in the admin panel
2. Monitor user activity and registration dates
3. Delete problematic user accounts when necessary
4. User deletion automatically removes their products and comments

#### Product Management
1. View all products listed on the platform
2. Monitor product quality and appropriateness
3. Remove inappropriate or spam listings
4. Track sales and platform activity

#### System Monitoring
1. Review platform statistics (users, products, comments)
2. Monitor recent registrations and product listings
3. Ensure platform quality and user safety

## Security Considerations

### Password Security
- All passwords are hashed using PHP's `password_hash()` function
- Passwords must be at least 6 characters long
- No plain text password storage

### Database Security
- All database queries use prepared statements
- SQL injection protection through PDO
- Foreign key constraints maintain data integrity

### File Upload Security
- File type validation (JPEG, PNG, GIF only)
- File size limits (5MB maximum)
- Unique filename generation to prevent conflicts
- Upload directory outside of web root recommended for production

### Session Security
- Secure session management with PHP sessions
- Session timeout and proper logout handling
- Role-based access control for admin functions

### Input Validation
- All user inputs are sanitized and validated
- HTML special characters are escaped
- XSS protection through proper output encoding

## Customization Options

### Styling and Branding
- Modify `css/style.css` to change colors, fonts, and layout
- Update site name and branding in `config.php`
- Customize navigation and footer in `includes/` files

### Categories
- Add or modify product categories in the database
- Update category descriptions and organization
- Customize category-specific features

### Email Integration
- Configure SMTP settings for automated emails
- Customize email templates for notifications
- Add email verification for new registrations

### Payment Integration
- Add payment gateway integration for transactions
- Implement escrow or payment protection features
- Add transaction history and management

## Troubleshooting

### Common Issues

#### Database Connection Errors
- Ensure SQLite extension is installed and enabled
- Check file permissions on the database file
- Verify the database path in `config.php`

#### File Upload Issues
- Check `uploads/` directory permissions (755 recommended)
- Verify PHP file upload settings (`upload_max_filesize`, `post_max_size`)
- Ensure sufficient disk space for uploads

#### Session Problems
- Check PHP session configuration
- Verify session directory permissions
- Clear browser cookies and cache

#### Permission Errors
- Set appropriate file permissions (644 for PHP files, 755 for directories)
- Ensure web server has read access to all files
- Check ownership of files and directories

### Performance Optimization

#### Database Optimization
- Add indexes for frequently searched columns
- Implement database cleanup for old records
- Consider migration to MySQL/PostgreSQL for larger datasets

#### File Management
- Implement image compression for uploads
- Add CDN integration for static assets
- Optimize CSS and JavaScript files

#### Caching
- Add page caching for frequently accessed content
- Implement database query caching
- Use browser caching for static resources

## Future Enhancements

### Planned Features
- Private messaging system between buyers and sellers
- Advanced search with location-based filtering
- User ratings and review system
- Wishlist and favorites functionality
- Mobile application development
- Payment gateway integration
- Multi-language support
- Social media integration

### Technical Improvements
- Migration to modern PHP framework (Laravel, Symfony)
- API development for mobile applications
- Real-time notifications system
- Advanced analytics and reporting
- Automated testing implementation
- Docker containerization

## Support and Maintenance

### Regular Maintenance Tasks
- Database backup and cleanup
- Security updates and patches
- Performance monitoring and optimization
- User feedback collection and implementation

### Backup Procedures
- Regular database backups (SQLite file copy)
- Image file backups from uploads directory
- Configuration file backups
- Version control for code changes

### Monitoring
- Server resource usage monitoring
- Database performance tracking
- User activity and engagement metrics
- Error logging and analysis

## License and Credits

This project is developed as a comprehensive example of a second-hand sales platform. It demonstrates modern web development practices with PHP and includes all essential features for a functional marketplace.

### Technologies Used
- PHP 8.1+ for server-side development
- SQLite for lightweight database management
- Bootstrap 5 for responsive design
- Font Awesome for iconography
- JavaScript for interactive functionality

### Development Notes
- Built with security and scalability in mind
- Follows PHP best practices and coding standards
- Responsive design for all device types
- Comprehensive error handling and validation
- Modular code structure for easy maintenance

---

*For technical support or feature requests, please refer to the project documentation or contact the development team.*

