# db.py
import mysql.connector

def get_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",  # update if your MySQL has a password
        database="sample_1"
    )
