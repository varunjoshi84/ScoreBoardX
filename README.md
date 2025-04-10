# Sports Application

A web-based sports management application built with PHP and Node.js for managing sports matches, players, and match statistics.

## Project Overview
The Sports Application is a comprehensive platform designed for managing sports matches, players, and match statistics. It consists of both frontend and backend components, providing a seamless experience for sports management.

## Technical Architecture

### Frontend
- Built using Node.js with Express framework
- Uses Express version 5.1.0
- Contains a `public` directory for static assets
- Includes server.js for handling frontend routing

### Backend
- Built using PHP
- RESTful API architecture
- Key API endpoints:
  - Authentication (login, register)
  - User management
  - Match creation and management
  - Playing 11 selection
  - Score tracking
  - Toss management
  - Player statistics

## Database Structure
- Uses SQL database (sportsapp.sql)
- Contains tables for:
  - Users
  - Matches
  - Players
  - Scores
  - Match statistics

## Features

### User Management
- User registration and login
- Password update functionality
- User profile management

### Match Management
- Create new matches
- Set playing 11
- Manage match statistics
- Track scores

### Player Management
- Track current players
- Manage batting and bowling players
- Player statistics

### Game Features
- Toss management
- Score tracking
- Playing 11 selection

## Security Features
- Authentication system
- API endpoints protected with middleware
- Secure password management

## Project Structure
```
Project Root/
├── Sports APP/           # Frontend
│   ├── node_modules/
│   ├── public/
│   ├── package.json
│   └── server.js
│
└── SportsApp/           # Backend
    ├── api/            # API endpoints
    ├── config/         # Configuration files
    ├── controllers/    # Controller logic
    ├── middlewares/    # Middleware functions
    └── index.php       # Main entry point
```

## Getting Started

### Prerequisites
- Node.js
- PHP
- MySQL
- Composer (for PHP dependencies)

### Installation
1. Clone the repository
2. Install frontend dependencies:
   ```bash
   cd "Sports APP"
   npm install
   ```
3. Install backend dependencies:
   ```bash
   cd SportsApp
   composer install
   ```
4. Set up the database:
   - Create a new MySQL database
   - Import the `sportsapp.sql` file
5. Configure the application:
   - Update database credentials in the config file
   - Update API endpoints in the frontend

### Running the Application
1. Start the backend server:
   ```bash
   cd SportsApp
   php -S localhost:8000
   ```
2. Start the frontend server:
   ```bash
   cd "Sports APP"
   npm start
   ```

## Contributing
Please read CONTRIBUTING.md for details on our code of conduct and the process for submitting pull requests.

## License
This project is licensed under the ISC License - see the LICENSE file for details.

## Acknowledgments
- Thanks to all contributors who have helped improve this project
- Special thanks to the open-source community for their support
