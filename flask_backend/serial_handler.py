import serial
import time
import json

SERIAL_PORT = 'COM3'
BAUD_RATE = 57600

def send_command(command):
    try:
        with serial.Serial(SERIAL_PORT, BAUD_RATE, timeout=10) as ser:
            time.sleep(2)
            ser.write((command + "\n").encode())
            print(f">> Sent to Arduino: {command}")

            response = ""
            while True:
                line = ser.readline().decode().strip()
                if not line:
                    break
                response = line
                if response:
                    break

            print(f"<< Arduino says (RAW): '{response}'")

            with open('../registrar/latest_fp_status.txt', 'w') as f:
                json.dump({"status": response}, f)

            return response

    except Exception as e:
        print(f"[ERROR] Serial communication failed: {e}")
        with open('../registrar/latest_fp_status.txt', 'w') as f:
            json.dump({"status": "error", "details": str(e)}, f)
        return "error"
