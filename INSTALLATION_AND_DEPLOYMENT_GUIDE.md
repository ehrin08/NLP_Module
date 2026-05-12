# Installation and Deployment Guide for the NLP-Based Sentiment Analysis Module

## 1. Introduction

The NLP-Based Sentiment Analysis Module is a web-based system for collecting customer feedback and automatically classifying each feedback entry as `Positive`, `Neutral`, or `Negative`. The system is designed to support English, Filipino, and Taglish text.

The project uses Laravel for the web application, MySQL for data storage, and a Python Flask API for the machine learning sentiment prediction service. The Flask API uses TF-IDF vectorization and Logistic Regression through scikit-learn. Text preprocessing uses NLTK resources and custom Filipino/Taglish normalization files.

Local deployment uses XAMPP on Windows. XAMPP provides Apache, MySQL/MariaDB, and a local PHP environment. During development, Laravel can be accessed through `php artisan serve`, while the Python Flask API runs separately on another local port.

## 2. System Requirements

Install the following before running the project:

| Requirement | Recommended Version or Notes |
| --- | --- |
| Operating System | Windows 10 or Windows 11 |
| XAMPP | Includes Apache, MySQL/MariaDB, and PHP |
| PHP | PHP 8.2 or higher, required by Laravel 12 |
| Composer | Latest stable version |
| Python | Python 3.10 or higher recommended |
| pip | Included with Python installation |
| MySQL/MariaDB | Included with XAMPP |
| Node.js | Node.js LTS recommended |
| npm | Included with Node.js |
| Git | Required for cloning and pulling updates |

Check installed versions using PowerShell:

```powershell
php --version
composer --version
python --version
pip --version
node --version
npm --version
git --version
```

## 3. Project Extraction / GitHub Pull

The project should be placed inside the XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\Sentiment_Analysis_Module
```

### Option A: Clone the Repository

Use this option if the project is not yet on the computer.

Open PowerShell and run:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/ehrin08/NLP_Module.git Sentiment_Analysis_Module
cd C:\xampp\htdocs\Sentiment_Analysis_Module
```

### Option B: Pull the Latest Changes

Use this option if the project already exists on the computer.

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
git pull origin main
```

## 4. XAMPP Setup

1. Open the XAMPP Control Panel.
2. Start `Apache`.
3. Start `MySQL`.
4. Confirm that XAMPP is running by opening:

```text
http://localhost
```

5. Confirm that phpMyAdmin is accessible:

```text
http://localhost/phpmyadmin
```

Apache is useful when accessing the project through XAMPP. MySQL is required because Laravel stores feedback, users, sessions, cache data, jobs, and dashboard records in the database.

## 5. Laravel Setup

Open PowerShell in the Laravel project root:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
```

Install Laravel/PHP dependencies:

```powershell
composer install
```

Create the Laravel environment file:

```powershell
cp .env.example .env
```

Generate the Laravel application key:

```powershell
php artisan key:generate
```

The `vendor` folder is created by `composer install`. The `.env` file stores local configuration such as database credentials and the Flask API URL.

## 6. Database Configuration

The default database name used by this project is:

```text
sentiment_analysis_module
```

### Create the Database Using phpMyAdmin

1. Open:

```text
http://localhost/phpmyadmin
```

2. Click `New`.
3. Enter this database name:

```text
sentiment_analysis_module
```

4. Choose collation:

```text
utf8mb4_unicode_ci
```

5. Click `Create`.

### Create the Database Using PowerShell

You can also create the database with the MySQL command line tool included in XAMPP:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS sentiment_analysis_module CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Update the Laravel `.env` File

Open this file:

```text
C:\xampp\htdocs\Sentiment_Analysis_Module\.env
```

Set the database configuration to:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sentiment_analysis_module
DB_USERNAME=root
DB_PASSWORD=
```

Also confirm that the Flask API URL is set:

```env
SENTIMENT_API_URL=http://127.0.0.1:5000/predict
```

For a default XAMPP installation, the MySQL username is usually `root` and the password is blank. If you set a MySQL password in XAMPP, place that password after `DB_PASSWORD=`.

## 7. Database Migration

Run the Laravel migrations:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
php artisan migrate
```

This creates the required Laravel tables, including users, cache, jobs, and feedback sentiment records.

Seed the default admin user:

```powershell
php artisan db:seed
```

The included seeder creates this admin login:

```text
Email: admin@example.com
Password: password
```

Optional: import the sample feedback dataset into the dashboard database:

```powershell
php artisan feedback:import-dataset --truncate
```

Optional: restore the included database dump instead of manually entering existing data:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root sentiment_analysis_module < database\dumps\sentiment_analysis_module.sql
```

Use either migrations and seeders or the database dump depending on the instruction from your adviser or project team.

## 8. Node Module Installation

Install frontend dependencies:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
npm install
```

Run the Vite development server:

```powershell
npm run dev
```

Keep this terminal open while using the Laravel system. Vite compiles the frontend assets used by Laravel, including Tailwind CSS and JavaScript files. If this command is not running during development, styles may not load correctly.

For production build preparation, run:

```powershell
npm run build
```

## 9. Python Environment Setup

Open a new PowerShell terminal and go to the Flask API folder:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
```

Create a Python virtual environment:

```powershell
python -m venv venv
```

Activate the virtual environment:

```powershell
venv\Scripts\activate
```

Install the Python packages:

```powershell
pip install -r requirements.txt
```

The required packages include Flask, NLTK, pandas, NumPy, joblib, and scikit-learn.

When the virtual environment is active, PowerShell usually displays `(venv)` at the start of the command line.

## 10. NLTK Resource Download

Download the required NLTK resources inside the activated Python virtual environment:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
venv\Scripts\activate
python -m nltk.downloader punkt stopwords wordnet omw-1.4
```

This project directly uses `stopwords`, `wordnet`, and `omw-1.4` during preprocessing. The `punkt` package is included because it is commonly required for tokenization-related NLP workflows and may be needed when the preprocessing logic is expanded.

## 11. Model Training

Train the sentiment model from the sample dataset:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
venv\Scripts\activate
python train_model.py
```

The training script reads:

```text
python_nlp_api\sample_feedback_dataset.csv
```

The script preprocesses the English, Filipino, and Taglish feedback text, converts the cleaned text into TF-IDF features, and trains a Logistic Regression classifier.

Expected generated model files:

```text
python_nlp_api\sentiment_model.pkl
python_nlp_api\vectorizer.pkl
```

The Flask API also checks for these files when it starts. If one of the files is missing, the API attempts to train the model automatically before accepting predictions.

## 12. Running the Flask API

Open a PowerShell terminal:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
venv\Scripts\activate
python app.py
```

The Flask API runs on:

```text
http://127.0.0.1:5000
```

Health check endpoint:

```text
http://127.0.0.1:5000/health
```

Prediction endpoint:

```text
http://127.0.0.1:5000/predict
```

Keep this terminal open while using the Laravel system.

### Verify the Flask API

Open another PowerShell terminal and run:

```powershell
Invoke-RestMethod -Uri http://127.0.0.1:5000/health
```

Expected response:

```json
{
  "status": "ok"
}
```

Test a prediction:

```powershell
Invoke-RestMethod -Method Post -Uri http://127.0.0.1:5000/predict -ContentType "application/json" -Body '{"text":"Maganda ang service at mabait ang staff."}'
```

Sample response:

```json
{
  "confidence": 0.9123,
  "sentiment": "Positive"
}
```

The confidence value may differ depending on the trained model and dataset.

## 13. Running the Laravel Server

Open another PowerShell terminal:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
php artisan serve
```

Laravel will usually run on:

```text
http://127.0.0.1:8000
```

Open this URL in a browser:

```text
http://127.0.0.1:8000
```

The feedback form is available at the root page. The login page is available at:

```text
http://127.0.0.1:8000/login
```

The dashboard is available after login:

```text
http://127.0.0.1:8000/dashboard
```

If using Apache through XAMPP instead of `php artisan serve`, the project can also be accessed through:

```text
http://localhost/Sentiment_Analysis_Module/public
```

For Laravel development, `php artisan serve` is usually easier because it avoids common Apache document root and URL rewriting issues.

## 14. API Connection Testing

Laravel communicates with Flask through the `SentimentPredictionService` class. When a user submits feedback, Laravel sends the feedback text to:

```env
SENTIMENT_API_URL=http://127.0.0.1:5000/predict
```

The Flask API returns a sentiment label and confidence score. Laravel then saves the feedback and prediction results in MySQL.

Sample JSON request sent to Flask:

```json
{
  "text": "Sulit yung service at very relaxing ang massage."
}
```

Sample JSON response from Flask:

```json
{
  "sentiment": "Positive",
  "confidence": 0.8842
}
```

PowerShell test command:

```powershell
Invoke-RestMethod -Method Post -Uri http://127.0.0.1:5000/predict -ContentType "application/json" -Body '{"text":"Sulit yung service at very relaxing ang massage."}'
```

Laravel-side verification:

1. Start MySQL in XAMPP.
2. Run the Flask API with `python app.py`.
3. Run Laravel with `php artisan serve`.
4. Run Vite with `npm run dev`.
5. Open `http://127.0.0.1:8000`.
6. Submit a feedback entry.
7. Confirm that the thank-you page displays the predicted sentiment.
8. Log in using `admin@example.com` and `password`.
9. Open the dashboard and confirm that the submitted feedback appears.

## 15. Common Errors and Fixes

### Missing `vendor` Folder

Cause: PHP dependencies were not installed.

Fix:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
composer install
```

### Composer Is Not Recognized

Cause: Composer is not installed or not added to the Windows PATH.

Fix:

```powershell
composer --version
```

If the command fails, install Composer for Windows from the official Composer installer, then close and reopen PowerShell.

### Missing `.env` File

Cause: Laravel environment file was not created.

Fix:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
cp .env.example .env
php artisan key:generate
```

### Database Connection Error

Common messages include `SQLSTATE[HY000] [1049] Unknown database` and `SQLSTATE[HY000] [2002] Connection refused`.

Fix:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS sentiment_analysis_module CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
```

Also confirm:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sentiment_analysis_module
DB_USERNAME=root
DB_PASSWORD=
```

Make sure MySQL is running in the XAMPP Control Panel.

### Flask Import Errors

Common messages include `ModuleNotFoundError: No module named 'flask'`, `No module named 'sklearn'`, or `No module named 'nltk'`.

Fix:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
venv\Scripts\activate
pip install -r requirements.txt
```

### Missing NLTK Resources

Common messages include `Resource stopwords not found` or `Resource wordnet not found`.

Fix:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
venv\Scripts\activate
python -m nltk.downloader punkt stopwords wordnet omw-1.4
```

### CORS Issues

Cause: The browser is calling the Flask API directly from JavaScript. In this project, Laravel should call Flask server-side, so normal feedback submission should not require browser-to-Flask CORS configuration.

Fix:

1. Submit feedback through the Laravel form at `http://127.0.0.1:8000`.
2. Confirm this `.env` value:

```env
SENTIMENT_API_URL=http://127.0.0.1:5000/predict
```

3. Restart Laravel after changing `.env`:

```powershell
php artisan config:clear
php artisan serve
```

If a future frontend directly calls Flask from browser JavaScript, install and configure Flask-CORS:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
venv\Scripts\activate
pip install flask-cors
```

### Model Not Found

Common messages include `sentiment_model.pkl not found` or `vectorizer.pkl not found`.

Fix:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
venv\Scripts\activate
python train_model.py
```

Confirm that these files exist:

```text
python_nlp_api\sentiment_model.pkl
python_nlp_api\vectorizer.pkl
```

### Localhost Port Conflicts

If Laravel port `8000` is already in use, run Laravel on another port:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
php artisan serve --port=8001
```

Then open:

```text
http://127.0.0.1:8001
```

If Flask port `5000` is already in use, identify the process:

```powershell
Get-NetTCPConnection -LocalPort 5000 | Select-Object LocalAddress,LocalPort,State,OwningProcess
```

Stop the process using port `5000`:

```powershell
Stop-Process -Id (Get-NetTCPConnection -LocalPort 5000).OwningProcess -Force
```

Then restart Flask:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module\python_nlp_api
venv\Scripts\activate
python app.py
```

### Tailwind or Vite Styles Are Not Loading

Cause: The Vite development server is not running.

Fix:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
npm run dev
```

### Laravel Configuration Cache Uses Old `.env` Values

Cause: Laravel cached a previous configuration.

Fix:

```powershell
cd C:\xampp\htdocs\Sentiment_Analysis_Module
php artisan config:clear
php artisan cache:clear
```

## 16. Project Folder Structure

Clean project structure:

```text
Sentiment_Analysis_Module/
|-- app/
|   |-- Console/
|   |-- Http/
|   |-- Models/
|   |-- Providers/
|   |-- Services/
|       |-- SentimentPredictionService.php
|-- bootstrap/
|-- config/
|   |-- app.php
|   |-- database.php
|   |-- services.php
|-- database/
|   |-- dumps/
|   |   |-- sentiment_analysis_module.sql
|   |-- factories/
|   |-- migrations/
|   |-- seeders/
|       |-- AdminUserSeeder.php
|       |-- DatabaseSeeder.php
|-- public/
|   |-- index.php
|-- python_nlp_api/
|   |-- app.py
|   |-- evaluate_model.py
|   |-- filipino_stopwords.txt
|   |-- import_datasets.py
|   |-- normalization_dictionary.json
|   |-- preprocess.py
|   |-- requirements.txt
|   |-- sample_feedback_dataset.csv
|   |-- sentiment_model.pkl
|   |-- test_predictions.py
|   |-- train_model.py
|   |-- vectorizer.pkl
|-- resources/
|   |-- css/
|   |-- js/
|   |-- views/
|-- routes/
|   |-- console.php
|   |-- web.php
|-- storage/
|-- tests/
|-- .env.example
|-- artisan
|-- composer.json
|-- package.json
|-- vite.config.js
```

Generated folders after setup:

```text
vendor/
node_modules/
python_nlp_api/venv/
public/build/
```

These generated folders should not be manually edited. They can be recreated using Composer, npm, Python venv, and Vite commands.

## 17. Final System Access URLs

Laravel development server:

```text
http://127.0.0.1:8000
```

Laravel login page:

```text
http://127.0.0.1:8000/login
```

Laravel dashboard:

```text
http://127.0.0.1:8000/dashboard
```

Laravel through XAMPP Apache:

```text
http://localhost/Sentiment_Analysis_Module/public
```

Flask API base URL:

```text
http://127.0.0.1:5000
```

Flask health check:

```text
http://127.0.0.1:5000/health
```

Flask prediction endpoint:

```text
http://127.0.0.1:5000/predict
```

phpMyAdmin:

```text
http://localhost/phpmyadmin
```

## 18. Deployment Best Practices

Keep `.env` private. Do not upload `.env` to GitHub because it contains database settings, app keys, and service URLs.

Use `.gitignore` correctly. The following should not be committed:

```text
.env
vendor/
node_modules/
python_nlp_api/venv/
public/build/
storage/*.key
```

Back up the database before major changes:

```powershell
C:\xampp\mysql\bin\mysqldump.exe -u root sentiment_analysis_module > database\dumps\sentiment_analysis_module.sql
```

Before submitting or transferring the project, confirm that the database export is updated:

```text
database\dumps\sentiment_analysis_module.sql
```

For VPS or production deployment preparation:

1. Use a real web server configuration for Laravel, such as Nginx or Apache pointing to the `public` folder.
2. Set `APP_ENV=production`.
3. Set `APP_DEBUG=false`.
4. Use strong database passwords.
5. Use a process manager for Flask, such as Waitress on Windows or Gunicorn on Linux.
6. Run `npm run build` instead of `npm run dev`.
7. Run Laravel optimization commands after setting production `.env` values:

```powershell
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not expose the Flask API publicly unless it is protected by firewall rules, authentication, or reverse proxy restrictions. For this project, Laravel is the user-facing application and Flask is the internal prediction service.

## 19. Final Verification Checklist

Before presenting or submitting the system, verify the following:

- [ ] XAMPP Apache is running.
- [ ] XAMPP MySQL is running.
- [ ] The database `sentiment_analysis_module` exists.
- [ ] The `.env` file exists.
- [ ] The Laravel app key has been generated.
- [ ] Composer dependencies are installed in `vendor/`.
- [ ] npm dependencies are installed in `node_modules/`.
- [ ] Vite is running through `npm run dev`.
- [ ] Laravel is accessible at `http://127.0.0.1:8000`.
- [ ] Flask is accessible at `http://127.0.0.1:5000/health`.
- [ ] NLTK resources are downloaded.
- [ ] `sentiment_model.pkl` exists.
- [ ] `vectorizer.pkl` exists.
- [ ] The model has been trained using `python train_model.py`.
- [ ] The feedback form submits successfully.
- [ ] Predictions return `Positive`, `Neutral`, or `Negative`.
- [ ] Feedback records are saved in MySQL.
- [ ] Admin login works using `admin@example.com` and `password`.
- [ ] The dashboard displays feedback and sentiment data.

## 20. Output Requirements

This guide is prepared as a professional Markdown document for thesis appendix or project documentation use. It includes:

- Sequential setup instructions for Windows and XAMPP.
- Real project commands for Laravel, MySQL, Node/Vite, and Python Flask.
- Database configuration and migration instructions.
- NLP model training and API testing instructions.
- Common errors and troubleshooting steps.
- Final URLs and verification checklist.

Recommended startup order for local demonstration:

```text
1. Start Apache and MySQL in XAMPP.
2. Start Flask API with python app.py.
3. Start Laravel with php artisan serve.
4. Start Vite with npm run dev.
5. Open http://127.0.0.1:8000 in the browser.
```
