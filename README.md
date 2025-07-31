# Class Scheduling REST API using Qwen3's generated files

A full-featured, modular class scheduling system built with PHP, Slim Framework 4, Eloquent ORM, and jQuery. This system automates weekly class scheduling while respecting academic constraints, teacher qualifications, room availability, and student curricula — with support for manual overrides, conflict exemptions, and secure JWT authentication.

## Features

- Automated Scheduling Engine
- Manual Class Overrides
- Conflict Detection & Exemptions
- JWT-Based Authentication
- RESTful API with Full CRUD
- Eloquent ORM + MySQL Persistence
- Frontend Dashboard (jQuery + Bootstrap)
- Input Validation & Error Handling
- Modular, Maintainable Codebase

## Project Structure
```
/scheduling-system/
├── config/               # Configuration files
├── public/               # Public entry point (index.php)
├── src/
│   ├── Controllers/      # API logic
│   ├── Models/           # Eloquent models
│   ├── Services/         # Business logic (e.g., SchedulingEngine)
│   ├── Middleware/       # Request processing (auth, JSON parsing)
│   └── Routes/           # API route definitions
├── migrations/           # Database schema migrations
├── public_html/          # Frontend (HTML, JS, CSS)
├── .env                  # Environment variables
└── composer.json         # PHP dependencies
```
## Getting Started

1. Clone the Repository
```
   git clone https://github.com/yourusername/scheduling-system.git
   cd scheduling-system
```
2. Install Dependencies
```
   composer install
```
3. Set Up Environment

    Create .env file:
```
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=scheduling_db
   DB_USERNAME=root
   DB_PASSWORD=password
   
   JWT_SECRET=your_strong_32char_secret_key_here_12345678
   JWT_EXPIRATION=3600
```
4. Set Up Database
   
    Run migrations manually or use Phinx:
```
   CREATE DATABASE scheduling_db;
   -- Run SQL from migrations/*.php
```
Seed sample data (optional).

5. Start the Server
```
   php -S localhost:8080 -t public
```
6. Launch Frontend

   Serve the frontend:
```
   npx http-server public_html -p 3000
```

Visit: http://localhost:3000

## Authentication

The API uses JWT (JSON Web Tokens) for secure access.

Login
```
curl -X POST http://localhost:8080/login \
-H "Content-Type: application/json" \
-d '{
    "username": "admin",
    "password": "secret123"
}'
```
Save the returned token and include it in future requests:
```
Authorization: Bearer <your-jwt-token>
```

## Sample curl Commands

#### Generate Schedule
```
curl -X POST http://localhost:8080/classes/generate \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{"term": "Fall2024"}'
```

#### List All Classes
```
curl http://localhost:8080/classes \
-H "Authorization: Bearer <token>"
```
#### Add Conflict Exemption
```
curl -X POST http://localhost:8080/exemptions \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
    "type": "student",
    "entity_id": "s1",
    "conflict_type": "schedule",
    "reason": "Double major override",
    "expires_at": "2025-01-15 00:00:00"
}'
```
## Frontend Dashboard

Located in public_html/, the frontend provides:
- Login screen with JWT authentication
- Schedule generation panel
- Class list with edit functionality
- Conflict exemption management
- Responsive design using Bootstrap 5

Built with jQuery and AJAX for seamless backend integration.

## Services & Engines

### SchedulingEngine
- Automatically generates conflict-free class schedules
- Respects:
    - Curriculum-subject mappings
    - Teacher qualifications
    - Room capacity and exclusivity
    - Student and teacher availability
- Supports manual overrides and exemptions

### ValidationService
- Reusable validation logic
- Rules: required, string, int, array, email, time, unique, exists_in, custom rules
- Returns structured error messages

## Security

- JWT Authentication for all protected routes
- Password hashing using password_hash()
- Input validation and sanitization
- CSRF-safe (stateless tokens)
- Role-based extensibility (admin, scheduler, user)

#### Use HTTPS in production.

## Testing

Use curl or Postman to test endpoints.

Ensure:
- Tokens are included in protected requests
- JSON payloads are valid
- Required fields are present

## Future Enhancements

- Docker Setup
- PHPUnit Tests
- Vue.js Dashboard
- Calendar Export (ICS)
- Role-Based Access Control (RBAC)
- Refresh Tokens
- Admin User Management

## API Reference

### Base URL
```
http://localhost:8080
```
All responses are in JSON format. Authentication required unless noted.

### Authentication

#### POST /login
Authenticate and get JWT token.
    
##### Request
```
    {
    "username": "admin",
    "password": "secret123"
    }
```
##### Response (200)
```
    {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.xxxxx",
    "user": {
    "id": 1,
    "username": "admin",
    "role": "admin",
    "email": "admin@example.com"
    }
    }
```
##### Error (401)
```
    { "error": "Invalid username or password" }
```

### User Profile
#### GET /profile
Get current user info.

##### Headers
```
Authorization: Bearer <token>
```
###### Response (200)
```
{ "user": { "id": 1, "username": "admin", "role": "admin" } }
```

### Students
#### POST /students
Create a student.

#### Body
```
{
    "id": "s1",
    "name": "Alice",
    "curriculumId": "csci1",
    "enrollmentCount": 25
}
```
#### GET /students
List all students.

Optional: `?term=Fall2024`

### Teachers

#### POST /teachers
```
{
    "id": "t1",
    "name": "Dr. Smith",
    "qualifiedSubjectIds": ["cs101"]
}
```

#### GET /teachers
List all teachers.

### Rooms

#### POST /rooms
```
{
"id": "r1",
"capacity": 30
}
```

#### GET /rooms

### Subjects
#### POST /subjects
```
{
    "id": "cs101",
    "title": "Intro to Programming",
    "units": 3,
    "weeklyHours": 3
}
```

#### GET /subjects

### Curriculums

##### POST /curriculums
```
{
"id": "csci1",
"name": "CS Year 1",
"term": "Fall2024",
"subjectIds": ["cs101", "cs201"]
}
```
#### GET /curriculums
Optional: `?term=Fall2024`

### Time Slots
#### POST /time-slots
```
{
    "label": "Morning Block",
    "start_time": "08:00",
    "end_time": "09:00",
    "is_active": true
}
```

#### GET /time-slots
Optional: `?active=true`

##### PUT /time-slots/{id}

### Classes

#### POST /classes/generate
Generate schedule.

Body
```
{ "term": "Fall2024" }
```

Response
```
{
    "classes": [
        {
            "class_id": "cls_abc123",
            "subject_id": "cs101",
            "teacher_id": "t1",
            "room_id": "r1",
            "time_slot_id": "ts1",
            "day": "Mon",
            "term": "Fall2024",
            "is_override": false
        }
        ...
    ]
}
```
### GET /classes
List all scheduled classes.
Optional: `?term=Fall2024`

### PUT /classes/{id}
Update a class (marks as override).
#### Body
```
{ 
    "day": "Fri", 
    "time_slot_id": "ts5", 
    "room_id": "r2" 
}
```
### DELETE /classes/{id}
Delete only if is_override = true.

## Conflict Exemptions

### POST /exemptions
Allow temporary conflict.
#### Body
```
{
    "type": "student",
    "entity_id": "s1",
    "conflict_type": "schedule",
    "reason": "Double major",
    "expires_at": "2025-01-15 00:00:00"
}
```
### GET /exemptions
List active exemptions.

## License

MIT License. See LICENSE for details.

## Contact

For support or contributions, open an issue or contact the maintainer.

Built with PHP, Slim, Eloquent, and jQuery.