import pymysql
import csv
from dotenv import load_dotenv
import os
import sys

# Charger les variables d'environnement à partir du fichier .env
load_dotenv()

# Paramètres de connexion à la base de données Moodle depuis les variables d'environnement
DB_HOST = os.getenv("MOODLE_DATABASE_HOST")
DB_USER = os.getenv("MOODLE_DATABASE_USER")
DB_PASSWORD = os.getenv("MOODLE_DATABASE_PASSWORD")
DB_NAME = os.getenv("MOODLE_DATABASE_NAME")

# Connexion à la base de données
conn = pymysql.connect(
    host="172.18.0.2",
    user=DB_USER,
    password=DB_PASSWORD,
    database=DB_NAME,
    charset='utf8mb4',
    cursorclass=pymysql.cursors.DictCursor
)

table_file = sys.argv[1]
TABLE_NAME = sys.argv[1].split(".")[0]

script_dir = os.path.dirname(os.path.abspath(__file__))
mock_dir = os.path.join(script_dir, "..", "mock")  # ".." remonte d'un niveau

table_file_path = os.path.join(mock_dir, table_file)

try:
    with conn.cursor() as cursor:
        # Ouvrir le fichier CSV et insérer les données
        with open(table_file_path, newline='', encoding='utf-8') as csvfile:
            csvreader = csv.reader(csvfile)
            columns = next(csvreader)

            placeholders = ', '.join(['%s'] * len(columns))
            sql = f"INSERT INTO {TABLE_NAME} ({', '.join(columns)}) VALUES ({placeholders})"

            # Insérer les données ligne par ligne
            for row in csvreader:
                cursor.execute(sql, row)

        # Valider les changements
        conn.commit()
        print("Données insérées avec succès !")

finally:
    conn.close()
