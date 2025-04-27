# BLOGr - Content Management System (CMS)

BLOGr is a lightweight Content Management System designed for bloggers. It provides a platform for users to create accounts, write blog posts, and interact with others through likes and comments. Administrators have tools to manage users and moderate content.

## Features

### User Features
- **Registration**: Create an account with personal details, including a profile picture and bio.
- **Login**: Secure login with password encryption.
- **Profile Management**: Update personal information and upload a profile picture.
- **Create Blog Posts**: Write and publish blog posts with optional images.
- **Engagement**: Like and comment on blog posts.
- **Notifications**: Stay updated with notifications for likes and comments.

### Admin Features
- **User Management**: View and manage user accounts.
- **Content Moderation**: Approve or delete blog posts and comments.
- **Admin Access**: Special admin privileges with a secure admin password.

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/BLOGr.git

2. Set up the database:

* Import the provided SQL file into your MySQL database.
* Update the database credentials in db_config.php.

3. Start a local PHP server: 
```bash
    php -S localhost:8000
````
4. Open the application in your browser
```bash
  http://localhost:8000/login.php
````
## Project Structure
* Backend: PHP and MySQL for server-side logic and database management.
* Frontend: HTML, CSS, and Bootstrap for responsive design.
* File Uploads: User profile pictures and post images are stored in the uploads/ directory.
