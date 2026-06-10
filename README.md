# 📚 SENYAS Database Guide

> Database setup, migrations, table structure, seeding, and troubleshooting guide for the SENYAS Project.

# 1. Clone the repository
git clone [your-repo-url]
cd Senas_TeacherWebDashboard

# 2. Install dependencies
composer install
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Start Docker containers (or use XAMPP)
docker start senas-mysql phpmyadmin

# 6. Run migrations and seeders
php artisan migrate:fresh --seed

# 7. Start the server
php artisan serve

# 1. Start XAMPP (Apache + MySQL)

# 2. Create database 'senyas' in phpMyAdmin

# 3. Update .env file:
DB_DATABASE=senyas
DB_USERNAME=root
DB_PASSWORD=

# 4. Run:
php artisan config:clear
php artisan migrate:fresh --seed
php artisan serve
---

## 📑 Table of Contents

- [Database Access](#-database-access)
- [Starting the Database](#-starting-the-database)
- [Running Migrations](#-running-migrations)
- [Database Schema](#-database-schema)
  - [Users Table](#users-table)
  - [Teachers Table](#teachers-table)
  - [Students Table](#students-table)
  - [Schools Table](#schools-table)
- [ENUM Values](#-enum-values)
- [Common Laravel Commands](#-common-laravel-commands)
- [Sample Teacher Seeder](#-sample-teacher-seeder)
- [Sample Student Record](#-sample-student-record)
- [Troubleshooting](#-troubleshooting)
- [Database ERD](#-database-erd-entity-relationship-diagram)
- [Current Seeded Data](#-current-seeded-data)
- [Quick Setup Checklist](#-quick-setup-checklist-for-groupmate)

---

# 🗄️ Database Access

### phpMyAdmin

| Setting | Value |
|----------|--------|
| URL | http://localhost:8080 |
| Username | `root` |
| Password | `root` |

---

# 🚀 Starting the Database

## Start MySQL and phpMyAdmin

```bash
docker start senas-mysql phpmyadmin
```

## Stop Services

```bash
docker stop senas-mysql phpmyadmin
```

## Check Running Containers

```bash
docker ps
```

---

# 🔄 Running Migrations

## Fresh Migration (Deletes All Data)

> ⚠️ Warning: This removes all existing data and recreates the database tables.

```bash
php artisan migrate:fresh
```

## Run New Migrations Only

Keeps existing data while applying new migration files.

```bash
php artisan migrate
```

## Check Migration Status

```bash
php artisan migrate:status
```

---

# 🏗️ Database Schema

## Users Table

Laravel default authentication table with additional project-specific fields.

| Column | Type | Rules | Nullable |
|----------|----------|----------|----------|
| `id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `username` | STRING | Unique | ❌ |
| `email` | STRING | Unique | ✅ |
| `password` | STRING | Hashed Password | ✅ |
| `google_id` | STRING | Unique | ✅ |
| `role` | ENUM | `teacher`, `student` | ❌ |
| `status` | ENUM | `active`, `inactive` | ❌ |
| `created_at` | TIMESTAMP | Auto Generated | ✅ |
| `updated_at` | TIMESTAMP | Auto Generated | ✅ |

---

## Teachers Table

| Column | Type | Rules | Nullable |
|----------|----------|----------|----------|
| `teacher_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `user_id` | BIGINT | Foreign Key → `users.id` | ❌ |
| `first_name` | STRING | — | ❌ |
| `last_name` | STRING | — | ❌ |
| `specialization` | ENUM | `SNED`, `Regular` | ❌ |
| `created_at` | TIMESTAMP | Auto Generated | ✅ |
| `updated_at` | TIMESTAMP | Auto Generated | ✅ |

---

## Students Table

| Column | Type | Rules | Nullable |
|----------|----------|----------|----------|
| `student_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `user_id` | BIGINT | Foreign Key → `users.id` | ❌ |
| `teacher_id` | BIGINT | Foreign Key → `teachers.teacher_id` | ❌ |
| `lrn` | STRING(12) | Unique, Exactly 12 Digits | ❌ |
| `pin` | STRING(4) | 4-Digit PIN | ❌ |
| `first_name` | STRING | — | ❌ |
| `last_name` | STRING | — | ❌ |
| `age` | INTEGER | — | ❌ |
| `grade_level` | STRING | Null for self-contained and transition students | ✅ |
| `section` | STRING | Null for self-contained and transition students | ✅ |
| `program_type` | ENUM | (Regular, Self-Contained, Transition, Inlcusion) | ❌ |
| `created_at` | TIMESTAMP | Auto Generated | ✅ |
| `updated_at` | TIMESTAMP | Auto Generated | ✅ |

---

## Schools Table *(Optional / Future Use)*

| Column | Type | Rules | Nullable |
|----------|----------|----------|----------|
| `id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `name` | STRING | — | ❌ |
| `address` | STRING | — | ❌ |
| `created_at` | TIMESTAMP | Auto Generated | ✅ |
| `updated_at` | TIMESTAMP | Auto Generated | ✅ |

---
## Lessons & Quizzes Tables

### Lessons Table

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `lesson_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `teacher_id` | BIGINT | Foreign Key → `teachers.teacher_id` | ❌ |
| `title` | STRING | — | ❌ |
| `description` | TEXT | — | ✅ |
| `lesson_type` | ENUM | `text`, `video`, `interactive`, `gesture` | ❌ |
| `difficulty` | ENUM | `beginner`, `intermediate`, `advanced` | ❌ |
| `module_order` | INTEGER | Default 0 | ❌ |
| `status` | ENUM | `draft`, `published`, `archived` | ❌ |
| `created_at` | TIMESTAMP | Auto | ✅ |
| `updated_at` | TIMESTAMP | Auto | ✅ |

---

### Lesson Contents Table (Step-by-step)

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `content_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `lesson_id` | BIGINT | Foreign Key → `lessons.lesson_id` | ❌ |
| `step_number` | INTEGER | Order of content (1,2,3...) | ❌ |
| `content_type` | ENUM | `text`, `image`, `video`, `gesture_demo` | ❌ |
| `title` | STRING | — | ✅ |
| `content_text` | TEXT | Description/explanation | ✅ |
| `media_url` | STRING | Path to image/video | ✅ |
| `gesture_name` | STRING | For gesture recognition: `letter_a`, `hello`, etc. | ✅ |

---

### Quizzes Table

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `quiz_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `lesson_id` | BIGINT | Foreign Key → `lessons.lesson_id` | ❌ |
| `title` | STRING | — | ❌ |
| `description` | TEXT | — | ✅ |
| `total_points` | INTEGER | Default 0 | ❌ |
| `passing_score` | INTEGER | Default 70 (percentage) | ❌ |

---

### Quiz Questions Table

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `question_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `quiz_id` | BIGINT | Foreign Key → `quizzes.quiz_id` | ❌ |
| `question_number` | INTEGER | Order of questions | ❌ |
| `question_type` | ENUM | `multiple_choice`, `gesture_recognition`, `true_false` | ❌ |
| `question_text` | TEXT | "Which sign shows letter A?" | ❌ |
| `media_url` | STRING | Image/video for question | ✅ |
| `gesture_required` | STRING | For gesture recognition: `letter_a` | ✅ |
| `points` | INTEGER | Default 1 | ❌ |

---

### Quiz Options Table (for Multiple Choice)

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `option_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `question_id` | BIGINT | Foreign Key → `quiz_questions.question_id` | ❌ |
| `option_text` | STRING | Answer text | ❌ |
| `option_media_url` | STRING | Image of the sign | ✅ |
| `is_correct` | BOOLEAN | Default false | ❌ |

---

### Quiz Attempts Table (Student takes quiz)

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `attempt_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `student_id` | BIGINT | Foreign Key → `students.student_id` | ❌ |
| `quiz_id` | BIGINT | Foreign Key → `quizzes.quiz_id` | ❌ |
| `score` | INTEGER | Points earned | ❌ |
| `total_points` | INTEGER | Maximum possible points | ❌ |
| `percentage` | FLOAT | Score percentage | ❌ |
| `status` | ENUM | `in_progress`, `completed`, `failed` | ❌ |
| `started_at` | TIMESTAMP | When quiz began | ❌ |
| `completed_at` | TIMESTAMP | When quiz finished | ✅ |

---

### Student Answers Table (Detailed per question)

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `answer_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `attempt_id` | BIGINT | Foreign Key → `quiz_attempts.attempt_id` | ❌ |
| `question_id` | BIGINT | Foreign Key → `quiz_questions.question_id` | ❌ |
| `selected_option_id` | BIGINT | Foreign Key → `quiz_options.option_id` | ✅ |
| `gesture_recognized` | STRING | Captured gesture name | ✅ |
| `is_correct` | BOOLEAN | Whether answer was correct | ❌ |
| `points_earned` | INTEGER | Points for this question | ❌ |

---

### Student Lesson Progress Table

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `progress_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `student_id` | BIGINT | Foreign Key → `students.student_id` | ❌ |
| `lesson_id` | BIGINT | Foreign Key → `lessons.lesson_id` | ❌ |
| `current_step` | INTEGER | Which content step they're on | ❌ |
| `lesson_completed` | BOOLEAN | Default false | ❌ |
| `quiz_completed` | BOOLEAN | Default false | ❌ |
| `quiz_score` | INTEGER | Last quiz score | ✅ |
| `last_accessed_at` | TIMESTAMP | Last time they opened lesson | ❌ |

---

### Gestures Library Table

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `gesture_id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `name` | STRING | Unique identifier: `letter_a`, `hello` | ❌ |
| `display_name` | STRING | User friendly: `Letter A`, `Hello` | ❌ |
| `description` | TEXT | How to perform the sign | ✅ |
| `image_url` | STRING | Reference image path | ✅ |
| `video_url` | STRING | Tutorial video path | ✅ |
| `model_file` | STRING | Path to `.h5` model file | ✅ |
| `difficulty` | ENUM | `beginner`, `intermediate`, `advanced` | ❌ |

---

### Lesson Gestures (Which gestures are taught in each lesson)

| Column | Type | Rules | Nullable |
|--------|------|-------|----------|
| `id` | BIGINT | Primary Key, Auto Increment | ❌ |
| `lesson_id` | BIGINT | Foreign Key → `lessons.lesson_id` | ❌ |
| `gesture_id` | BIGINT | Foreign Key → `gestures.gesture_id` | ❌ |



# 📝 ENUM Values

## Role (`users.role`)

| Value | Description |
|---------|-------------|
| `teacher` | Teacher / Administrator Account |
| `student` | Student Account (LRN + PIN Login) |

---

## Status (`users.status`)

| Value | Description |
|---------|-------------|
| `active` | User can log in |
| `inactive` | Account is disabled |

---

## Specialization (`teachers.specialization`)

| Value | Description |
|---------|-------------|
| `SNED` | Special Needs Education Teacher |
| `Regular` | Regular Classroom Teacher |

---

## Program Type (`students.program_type`)

| Value | Description |
|---------|-------------|
| `Regular` | Mainstream Classroom |
| `Inclusion` | Regular Classroom with Additional Support |
| `Transition` | About to Graduate |
| `Self-contained` | Separate Special Education Classroom |

## Additional ENUM Values

### Lesson Type (`lessons.lesson_type`)

| Value | Description |
|-------|-------------|
| `text` | Text-based lesson |
| `video` | Video lesson |
| `interactive` | Interactive content |
| `gesture` | Gesture recognition lesson |

### Difficulty (`lessons.difficulty`, `gestures.difficulty`)

| Value | Description |
|-------|-------------|
| `beginner` | Easy, basic signs |
| `intermediate` | Moderate difficulty |
| `advanced` | Complex signs |

### Lesson Status (`lessons.status`)

| Value | Description |
|-------|-------------|
| `draft` | Being edited, not visible to students |
| `published` | Visible to students |
| `archived` | Hidden, kept for reference |

### Content Type (`lesson_contents.content_type`)

| Value | Description |
|-------|-------------|
| `text` | Text explanation |
| `image` | Image showing the sign |
| `video` | Video tutorial |
| `gesture_demo` | Requires camera for practice |

### Question Type (`quiz_questions.question_type`)

| Value | Description |
|-------|-------------|
| `multiple_choice` | Choose from options |
| `gesture_recognition` | Camera captures student's sign |
| `true_false` | True or false answer |

### Attempt Status (`quiz_attempts.status`)

| Value | Description |
|-------|-------------|
| `in_progress` | Student currently taking quiz |
| `completed` | Finished and passed |
| `failed` | Finished but below passing score |

---

# 🛠️ Common Laravel Commands

## Create a Migration

```bash
php artisan make:migration migration_name
```

## Run All Pending Migrations

```bash
php artisan migrate
```

## Fresh Migration (Deletes Data)

```bash
php artisan migrate:fresh
```

## Rollback Last Migration

```bash
php artisan migrate:rollback
```

## View Migration Status

```bash
php artisan migrate:status
```

---

# 👩‍🏫 Sample Teacher Seeder

-check the DatabaseSeeder.php

# ✅ Current Seeded Data

After running:

```bash
php artisan migrate:fresh --seed
```

the database will be populated with sample records for development and testing.

## School

| id | name | address | region | division |
|----|------|----------|----------|----------|
| 1 | Nasugbu West Central School | Concepcion St., Barangay IV, Nasugbu, Batangas | IV-A | Batangas Province |

---

## Users

| id | username | email | role | status |
|----|----------|--------|--------|--------|
| 1 | emmaruth | emmaruth@deped.gov.ph | teacher | active |
| 2 | juandelacruz | NULL | student | active |
| 3 | mariasantos | NULL | student | active |
| 4 | pedroreyes | NULL | student | active |
| 5 | anasalvador | NULL | student | active |

---

## Teachers

| teacher_id | user_id | first_name | last_name | specialization |
|------------|---------|------------|------------|----------------|
| 1 | 1 | Emma | Ruth | SNED |

---

## Students

| student_id | user_id | lrn | pin | first_name | last_name | program_type |
|------------|---------|-----|-----|------------|------------|--------------|
| 1 | 2 | 123456789012 | 1234 | Juan | Dela Cruz | Regular |
| 2 | 3 | 234567890123 | 2345 | Maria | Santos | Inclusion |
| 3 | 4 | 345678901234 | 3456 | Pedro | Reyes | Self-contained |
| 4 | 5 | 456789012345 | 4567 | Ana | Salvador | Transition |

---

## Test Credentials

| Role | Login Method | Credentials |
|--------|--------------|-------------|
| Teacher | Email + Password | `emmaruth@deped.gov.ph` / `password123` |
| Student 1 | LRN + PIN | `123456789012` / `1234` |
| Student 2 | LRN + PIN | `234567890123` / `2345` |
| Student 3 | LRN + PIN | `345678901234` / `3456` |
| Student 4 | LRN + PIN | `456789012345` / `4567` |

> ⚠️ These credentials are for development/testing only and should never be used in production.
---
## 📊 Full Database ERD

```text
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   users     │     │  teachers   │     │  schools    │
├─────────────┤     ├─────────────┤     ├─────────────┤
│ id (PK)     │────<│ user_id (FK)│     │ id (PK)     │
│ username    │     │ teacher_id  │     │ name        │
│ email       │     │ school_id   │>────│ address     │
│ password    │     │ first_name  │     │ region      │
│ google_id   │     │ last_name   │     │ division    │
│ role        │     │ specialization│   └─────────────┘
│ status      │     └──────┬──────┘
└─────────────┘            │
                           │
                           ▼
                     ┌─────────────┐
                     │  students   │
                     ├─────────────┤
                     │ student_id  │
                     │ user_id (FK)│
                     │ teacher_id  │
                     │ school_id   │
                     │ lrn         │
                     │ pin         │
                     │ first_name  │
                     │ last_name   │
                     │ age         │
                     │ grade_level │
                     │ section     │
                     │ program_type│
                     └──────┬──────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
┌───────────────┐   ┌───────────────┐   ┌───────────────┐
│    lessons    │   │ quiz_attempts │   │student_lesson │
├───────────────┤   ├───────────────┤   │   _progress   │
│ lesson_id (PK)│   │ attempt_id(PK)│   ├───────────────┤
│ teacher_id(FK)│   │ student_id(FK)│   │ progress_id   │
│ title         │   │ quiz_id(FK)   │   │ student_id(FK)│
│ description   │   │ score         │   │ lesson_id(FK) │
│ lesson_type   │   │ percentage    │   │ current_step  │
│ difficulty    │   │ status        │   │ completed     │
│ status        │   └───────┬───────┘   └───────────────┘
└───────┬───────┘           │
        │                   ▼
        ▼           ┌───────────────┐
┌───────────────┐   │student_answers│
│lesson_contents│   ├───────────────┤
├───────────────┤   │ answer_id(PK) │
│ content_id(PK)│   │ attempt_id(FK)│
│ lesson_id(FK) │   │ question_id   │
│ step_number   │   │ is_correct    │
│ content_type  │   │ points_earned │
│ content_text  │   └───────────────┘
│ media_url     │
│ gesture_name  │
└───────────────┘
        │
        ▼
┌───────────────┐   ┌───────────────┐
│    quizzes    │   │   gestures    │
├───────────────┤   ├───────────────┤
│ quiz_id (PK)  │   │ gesture_id(PK)│
│ lesson_id(FK) │◄──│ name          │
│ title         │   │ display_name  │
│ total_points  │   │ model_file    │
│ passing_score │   │ difficulty    │
└───────┬───────┘   └───────────────┘
        │
        ▼
┌───────────────┐
│quiz_questions │
├───────────────┤
│question_id(PK)│
│ quiz_id(FK)   │
│ question_text │
│ question_type │
│ points        │
└───────┬───────┘
        │
        ▼
┌───────────────┐
│ quiz_options  │
├───────────────┤
│ option_id(PK) │
│ question_id   │
│ option_text   │
│ is_correct    │
└───────────────┘
```

### Relationship Summary

| Parent Table | Child Table | Relationship |
|-------------|-------------|-------------|
| users | teachers | One-to-One |
| users | students | One-to-One |
| schools | teachers | One-to-Many |
| schools | students | One-to-Many |
| teachers | students | One-to-Many |



# 🚨 Troubleshooting

## Base Table or View Not Found

```bash
php artisan migrate:fresh
```

---

## Duplicate Column Name

1. Rollback the last migration:

```bash
php artisan migrate:rollback
```

2. Fix the migration file.

3. Run migrations again:

```bash
php artisan migrate
```

---

## Run it:

```bash
php artisan migrate:fresh --seed

---

## Cannot Add Foreign Key Constraint

Verify that:

- Parent tables exist before child tables.
- Migration order follows:

```text
users
 └── teachers
      └── students
```

Migration order should be:

```text
users → teachers → students
```

---

## Forgot to Add a Column

Create a new migration:

```bash
php artisan make:migration add_column_name_to_table_name
```

Then update the migration using:

```php
Schema::table('table_name', function (Blueprint $table) {
    $table->string('new_column');
});
```

Finally run:

```bash
php artisan migrate
```

---

# ✅ Notes

- Always backup important data before running `migrate:fresh`.
- Never edit migration files that have already been executed in production.
- Use seeders for default teacher and student accounts.
- Ask the team before modifying table relationships or foreign keys.

---

**Need help? Ask before running migrations! 🚀**

# 🖥️ Using XAMPP Instead of Docker

If you are not using Docker, you can run the database using XAMPP.

## 1. Install XAMPP

Download and install XAMPP:

https://www.apachefriends.org/

During installation, make sure the following components are included:

- Apache
- MySQL
- phpMyAdmin

---

## 2. Start Required Services

Open **XAMPP Control Panel** and start:

| Service | Required |
|----------|----------|
| Apache | ✅ Yes |
| MySQL | ✅ Yes |
| FileZilla | ❌ No |
| Mercury | ❌ No |
| Tomcat | ❌ No |

Your control panel should show both Apache and MySQL running.

---

## 3. Access phpMyAdmin

Open your browser and go to:

```text
http://localhost/phpmyadmin
```

Default credentials:

| Setting | Value |
|----------|--------|
| Username | root |
| Password | *(leave blank)* |

> ⚠️ Most XAMPP installations use an empty password for the root user.

---

## 4. Create the Database

Inside phpMyAdmin:

1. Click **New** in the left sidebar.
2. Enter the database name:

```text
senyas
```

3. Select:

```text
utf8mb4_unicode_ci
```

4. Click **Create**.

---

## 5. Configure Laravel Environment

Open the project's `.env` file.

Replace the database section with:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=senyas
DB_USERNAME=root
DB_PASSWORD=
```

> Leave `DB_PASSWORD` empty unless you configured a MySQL password in XAMPP.

---

## 6. Clear Laravel Configuration Cache

After changing the `.env` file, run:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 7. Run Migrations

Create all database tables:

```bash
php artisan migrate
```

For a fresh installation:

```bash
php artisan migrate:fresh
```

---

## 8. Verify Database Connection

Check if Laravel can connect:

```bash
php artisan migrate:status
```

If migrations are displayed, the connection is working correctly.

---

# 🔧 Common XAMPP Issues

## MySQL Won't Start

Usually caused by another application using port 3306.

Common causes:

- Existing MySQL installation
- MariaDB
- WAMP
- Docker MySQL container

### Check Port Usage

Windows Command Prompt:

```bash
netstat -ano | findstr :3306
```

If another service is using port 3306:

- Stop the conflicting service, OR
- Change MySQL's port in XAMPP.

---

## Access Denied for User 'root'

Verify `.env`:

```env
DB_USERNAME=root
DB_PASSWORD=
```

If you created a password in XAMPP, update:

```env
DB_PASSWORD=your_password
```

Then clear Laravel cache:

```bash
php artisan config:clear
```

---

## phpMyAdmin Not Loading

Ensure Apache is running.

Visit:

```text
http://localhost
```

If localhost doesn't open, Apache is not running.

---

## SQLSTATE[HY000] [2002] Connection Refused

Verify:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
```

and ensure MySQL is running in XAMPP.

---

# 📋 XAMPP Quick Start

```bash
# Start Apache and MySQL from XAMPP

# Create database: senyas

# Configure .env

php artisan config:clear
php artisan cache:clear

# Run migrations

php artisan migrate

# Check status

php artisan migrate:status
```

✅ If `php artisan migrate:status` shows the migration list, the database setup is complete.

## 🗄️ Database Schema

| Table Name                | Primary Key Column |
|--------------------------|--------------------|
| users                    | id                 |
| teachers                 | id                 |
| students                 | student_id         |
| schools                  | id                 |
| lessons                  | lesson_id          |
| quizzes                  | quiz_id            |
| quiz_questions           | question_id        |
| quiz_options             | option_id          |
| quiz_attempts            | attempt_id         |
| student_answers          | answer_id          |
| student_lesson_progress  | progress_id        |
| gestures                 | gesture_id         |
