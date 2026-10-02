ONLINE DRINK SHOP - PHP + MYSQL (XAMPP)

1. Copy folder online-drink-shop-db to C:\xampp\htdocs\
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin
4. Click Import and choose database.sql from this project.
5. Open http://localhost/online-drink-shop-db/
6. Register an account.
7. To make it Admin, in phpMyAdmin -> SQL run:
   USE online_drink_shop;
   UPDATE users SET role='admin' WHERE email='YOUR_EMAIL';
8. Logout/login again, then open /admin/dashboard.php

Database connection: config/db.php
Default XAMPP MySQL: user=root, password blank.

Features: Register/Login, database menu, drink customization, session cart, checkout, payment record, stock validation/deduction, order tracking, admin inventory and order-status management.


DRINK IMAGE UPLOAD
------------------
Admin Dashboard now supports uploading/changing drink images.
Allowed: JPG, JPEG, PNG, WEBP. Maximum: 5MB.
Images are saved in uploads/drinks/ and the relative path is stored in drinks.image.
If an image is not uploaded, the menu shows the drink emoji placeholder.
