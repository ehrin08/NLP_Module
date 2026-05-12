# Sentiment Analysis Module

Laravel web application for collecting customer feedback, predicting sentiment through a local Flask NLP API, and viewing sentiment results in a dashboard.

## Tech Stack

- Laravel 12 / PHP 8.2+
- MySQL or MariaDB
- Vite / Tailwind CSS
- Python Flask sentiment API
- scikit-learn model artifacts in `python_nlp_api/`

## Setup

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Install frontend dependencies:

   ```bash
   npm install
   ```

3. Create the environment file and app key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure the database in `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sentiment_analysis_module
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Restore the included database dump:

   ```bash
   mysql -u root < database/dumps/sentiment_analysis_module.sql
   ```

   On XAMPP for Windows, you can also run:

   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root < database\dumps\sentiment_analysis_module.sql
   ```

6. Start the Laravel app and Vite:

   ```bash
   php artisan serve
   npm run dev
   ```

7. Start the Python sentiment API in a separate terminal:

   ```bash
   cd python_nlp_api
   pip install -r requirements.txt
   python app.py
   ```

The Laravel app calls the Flask API at `http://127.0.0.1:5000/predict` by default. You can change this with `SENTIMENT_API_URL` in `.env`.

## Database

The database export is included at:

```text
database/dumps/sentiment_analysis_module.sql
```

You can also rebuild the schema with Laravel migrations:

```bash
php artisan migrate
```

To import the sample feedback dataset into the database:

```bash
php artisan feedback:import-dataset --truncate
```

## Python NLP API

The Flask API loads `sentiment_model.pkl` and `vectorizer.pkl`. If either artifact is missing, `python_nlp_api/app.py` trains the model automatically before serving predictions.
