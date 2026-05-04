#  Pasttime Clothing Store

A second-hand clothing marketplace built with PHP, MySQL, HTML, CSS and JavaScript as part of a Web Development group assignment.


##  Getting Started

**Requirements:** XAMPP and a web browser.

1. Clone or download the repository
2. Move the `pastimes` folder to `C:\xampp\htdocs\pastimes\`
3. Open XAMPP and start **Apache** and **MySQL**
4. Run the setup script in your browser:
```
http://localhost/pastimes/setup.php
```
5. Open the app:
```
http://localhost/pastimes/index.php
```

---

##  Login Credentials

**Admin**
- Username: `admin`
- Email: `admin@pasttime.co.za`
- Password: `admin123`

**Sample Users** - all use password `password123`
- `johndoe` - john@example.com
- `janesmith` - jane@example.com
- `mikebrown` - mike@example.com
- `sarahlee` - sarah@example.com
- `tomwilson` - tom@example.com

---

##  Features

- User registration with admin approval flow
- Secure password hashing with `password_hash()` and `password_verify()`
- Sticky login form with pending account detection
- Admin dashboard - approve users, add, edit and delete
- Browse clothing items in a grid and table with images
- Add to Cart with selling price popup
- Session-based shopping cart with running total
- Pasttime brand colours - Wisteria, Evolve, Lemon and Gold

---

##  Database Tables

- `tblAdmin` - admin accounts
- `tblUser` - registered buyers and sellers
- `tblClothes` - clothing items for sale
- `tblAorder` - customer orders

---

##  Built With

PHP · MySQL · HTML5 · CSS3 · JavaScript · XAMPP

---

