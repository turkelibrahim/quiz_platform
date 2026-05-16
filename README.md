# Quiz Platform 🚀

A modern, full-featured web application for creating, taking, and managing quizzes. Built with PHP, MySQL, and a clean user interface.

## ✨ Features

- **User Authentication**: Secure register and login system.
- **Quiz Management**: Browse available quizzes and take them.
- **Leaderboard**: Compete with others and see your ranking.
- **User Profiles**: Track your progress and performance.
- **Admin Dashboard**:
  - Create and manage quizzes.
  - Add and edit questions for each quiz.
- **Responsive Design**: Works perfectly on desktop and mobile devices.

## 🛠️ Tech Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: FontAwesome (assumed)

## 🚀 Getting Started

### Prerequisites

- A local server environment like **XAMPP**, **WAMP**, or **MAMP**.
- MySQL Database.

### Installation

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/turkelibrahim/quiz_platform.git
    ```
2.  **Database Setup**:
    - Create a database named `quiz_platform` in your MySQL server.
    - Import the SQL schema (if provided) or create the necessary tables.
3.  **Configuration**:
    - Copy `config/db.php.example` to `config/db.php`.
    - Update the database credentials in `config/db.php` if necessary.
    ```php
    $user = 'your_username';
    $pass = 'your_password';
    ```
4.  **Run the application**:
    - Move the project to your server's root directory (e.g., `htdocs` for XAMPP).
    - Access the application via `http://localhost/quiz-platform/public`.

## 📂 Project Structure

- `config/`: Database connection and configuration.
- `includes/`: Reusable PHP components (header, footer, auth).
- `public/`: Frontend pages and assets (CSS, JS).
- `public/assets/`: Stylesheets and JavaScript files.

## 🤝 Contributing

Contributions are welcome! Feel free to open an issue or submit a pull request.

## 📄 License

This project is open-source. Feel free to use and modify it.

---

Made with ❤️ by [Ibrahim Turkel](https://github.com/turkelibrahim)
