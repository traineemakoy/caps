from flask import Flask, request, jsonify
from serial_handler import send_command
from db import get_connection
from datetime import datetime, date

app = Flask(__name__)

# 🟡 /enroll?faculty_number=3
@app.route('/enroll', methods=['GET'])
def enroll():
    faculty_number = request.args.get('faculty_number')
    if not faculty_number:
        return jsonify({"status": "Missing faculty_number"}), 400

    # 📤 Send to Arduino
    response = send_command(f"ENROLL:{faculty_number}")

    if response.startswith("ENROLL:"):
        fingerprint_id = int(response.split(":")[1])

        conn = get_connection()
        cursor = conn.cursor()

        try:
            now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            cursor.execute(
                "INSERT INTO faculty_fingerprint (faculty_number, fingerprint_id, date_registered) VALUES (%s, %s, %s)",
                (faculty_number, fingerprint_id, now)
            )
            conn.commit()
            return jsonify({"status": "Enrolled", "fingerprint_id": fingerprint_id})
        except Exception as e:
            return jsonify({"status": "DB Insert Failed", "error": str(e)}), 500
    else:
        return jsonify({"status": response})


# 🟢 /scan - used for time in/out
@app.route('/scan', methods=['GET'])
def scan():
    response = send_command("SCAN")
    
    if response.startswith("FOUND:"):
        fingerprint_id = response.replace("FOUND:", "")
        return process_scan(int(fingerprint_id))
    else:
        return jsonify({"status": response})


def process_scan(fingerprint_id):
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)

    cursor.execute("SELECT faculty_number FROM faculty_fingerprint WHERE fingerprint_id = %s", (fingerprint_id,))
    user = cursor.fetchone()

    if not user:
        return jsonify({"status": "Unregistered fingerprint"})

    faculty_number = user['faculty_number']
    today = date.today()
    now = datetime.now()

    cursor.execute("SELECT * FROM attendance_log WHERE faculty_number = %s AND date = %s", (faculty_number, today))
    row = cursor.fetchone()

    if not row:
        cursor.execute("INSERT INTO attendance_log (faculty_number, time_in, date) VALUES (%s, %s, %s)",
                       (faculty_number, now.strftime("%Y-%m-%d %H:%M:%S"), today))
        conn.commit()
        return jsonify({"status": "Time-in recorded", "faculty_number": faculty_number})
    
    elif not row['time_out']:
        cursor.execute("UPDATE attendance_log SET time_out = %s WHERE id = %s",
                       (now.strftime("%Y-%m-%d %H:%M:%S"), row['id']))
        conn.commit()
        return jsonify({"status": "Time-out recorded", "faculty_number": faculty_number})
    
    else:
        return jsonify({"status": "Already timed-in and out today", "faculty_number": faculty_number})


if __name__ == '__main__':
    app.run(debug=True)
