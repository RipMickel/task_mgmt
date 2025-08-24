<?php
// inc/config.php
// DB settings - change to your credentials
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'task_manager');
define('DB_USER', 'root');
define('DB_PASS', ''); // set your password

// Site
define('SITE_NAME', 'Task Manager - Curriculum & Instruction');
define('ALLOWED_EMAIL_DOMAIN', 'nbsc.edu.ph'); // <--- only this domain allowed

// Google OAuth (optional)
// If you plan to use Google OAuth, set these. Otherwise leave blank.
define('GOOGLE_CLIENT_ID', '');     // e.g. your-client-id.apps.googleusercontent.com
define('GOOGLE_CLIENT_SECRET', ''); // your client secret
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/google_callback.php'); // set to your callback

session_start();
