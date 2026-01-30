![image_alt](https://github.com/ELBATTAHAHMED/ANALYSE-FACIALE/blob/0c862643b4a6ec9d222fbc450d20af3f39976b86/Analyse%20faciale.png)

# ANALYSE-FACIALE

## Overview

ANALYSE-FACIALE is a local facial analysis suite that combines a browser-based emotion detector with a face recognition attendance system. It runs fully on your machine, uses the webcam for live analysis, and keeps data inside a local MySQL database.

It includes two connected apps:

1. Emotion Detection (browser-only, face-api.js)
2. Face Recognition Attendance (PHP + MySQL + Python)

## Project Structure

- `Emotion Detection/` - standalone browser UI for real-time emotion and face detection
  - `index.html`, `script.js`, `style.css`
  - `models/` face-api.js models
  - `labels/` labeled face images
- `Face Recognition/dist/` - PHP dashboard + Python recognition loop
  - `index.php`, `auth-signin.php`, `attendance.php`, `members.php`
  - `config.php` database schema + seed data
  - `EncodeGenerator.py` builds face encodings
  - `main.py` webcam recognition + attendance
  - `Resources/` UI images
- `Face Recognition/requirements.txt` - Python dependencies

## Features

- Real-time emotion detection and face overlay in the browser
- Face recognition attendance with MySQL-backed dashboard
- Employee management, attendance history, and basic analytics

## Requirements

- XAMPP or any Apache + MySQL + PHP stack
- Python 3.11+ with pip
- Webcam access (browser and OS permissions)

## Emotion Detection (Browser)

1. Start a local web server (XAMPP or any static server).
2. Open:
   `http://localhost/ANALYSE-FACIALE/Emotion%20Detection/index.html`
3. Allow camera access.

Notes:
- Models are loaded from `Emotion Detection/models/`.
- Labeled faces are read from `Emotion Detection/labels/<name>/<1..2>.png`.
- Update `labels` in `Emotion Detection/script.js` if you change label folders.

## Face Recognition Attendance (PHP + Python)

### 1) Start the web dashboard

1. Start Apache and MySQL (XAMPP).
2. Open:
   `http://localhost/ANALYSE-FACIALE/Face%20Recognition/dist/index.php`

This loads `Face Recognition/dist/config.php`, which creates the `analyse_faciale` database and tables, then seeds sample users.

Default login (from `config.php`):
- Admin: `admin@example.com` / `admin123`
- Employee: `employee@example.com` / `employee123`

If your MySQL credentials differ, update `Face Recognition/dist/config.php` and `Face Recognition/dist/main.py`.

### 2) Install Python dependencies

From the repository root:

```bash
pip install -r "Face Recognition/requirements.txt"
```

### 3) Generate face encodings

Encodings are built from the `EmployeeImages` table. After adding or changing employee photos, rebuild:

```bash
cd "Face Recognition/dist"
python EncodeGenerator.py
```

This creates `EncodeFile.p` in the same folder.

### 4) Run the recognition loop

```bash
cd "Face Recognition/dist"
python main.py
```

The camera feed will launch and attendance records will be written to MySQL.

## Troubleshooting

- If models fail to load in the browser, make sure you are not using `file://`.
- If the webcam is blank, check OS camera permissions and close any other apps using the camera.
- If face matching fails, re-run `EncodeGenerator.py` after updating employee images.

Author: EL BATTAH Ahmed
