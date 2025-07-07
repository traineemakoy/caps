#include <Adafruit_Fingerprint.h>
#include <SoftwareSerial.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// === Pins ===
SoftwareSerial mySerial(2, 3); // RX, TX
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&mySerial);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// === State flags ===
int mode = 0; // 0 = SCAN/LOGIN (default), 1 = ENROLL
int enroll_id = -1;

void setup() {
  Serial.begin(57600);
  mySerial.begin(57600);

  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Initializing...");
  
  if (finger.verifyPassword()) {
    lcd.setCursor(0, 1);
    lcd.print("Sensor found!");
  } else {
    lcd.setCursor(0, 1);
    lcd.print("Sensor ERROR!");
    while (1);
  }

  delay(2000);
  lcd.clear();
  lcd.print("Ready to scan...");
}

void loop() {
  if (Serial.available()) {
    String command = Serial.readStringUntil('\n');
    command.trim();

    if (command.startsWith("ENROLL:")) {
      enroll_id = command.substring(7).toInt();
      mode = 1;
    } else if (command == "SCAN") {
      mode = 0;
    }
  }

  if (mode == 1 && enroll_id >= 0) {
    enrollFinger(enroll_id);
    mode = 0;
    enroll_id = -1;
    lcd.clear();
    lcd.print("Back to login");
    delay(2000);
    lcd.clear();
    lcd.print("Ready to scan...");
  } else if (mode == 0) {
    scanFinger();
  }

  delay(100); // slight pause
}

void enrollFinger(int id) {
  uint8_t p;

  lcd.clear(); lcd.print("Place finger 1");
  while ((p = finger.getImage()) != FINGERPRINT_OK);
  if (finger.image2Tz(1) != FINGERPRINT_OK) return;

  lcd.clear(); lcd.print("Remove finger");
  delay(2000); // let them remove
  while (finger.getImage() != FINGERPRINT_NOFINGER);

  lcd.clear(); lcd.print("Place finger 2");
  while ((p = finger.getImage()) != FINGERPRINT_OK);
  if (finger.image2Tz(2) != FINGERPRINT_OK) return;

  // ✅ Now match both scans
  p = finger.createModel();
  if (p != FINGERPRINT_OK) {
    lcd.clear(); lcd.print("Mismatch!");
    Serial.println("Fingerprint mismatch");
    return;
  }

  // ✅ Save fingerprint
  p = finger.storeModel(id);
  if (p == FINGERPRINT_OK) {
    lcd.clear(); lcd.print("Enroll success!");
    Serial.print("ENROLL:"); Serial.println(id);
    Serial.flush();
    delay(100);
  } else {
    lcd.clear(); lcd.print("Store failed!");
    Serial.print("Store failed. Err code: "); Serial.println(p);
  }

  delay(2000);
}



// ========== Login/Scan Mode ==========
void scanFinger() {
  uint8_t p = finger.getImage();
  if (p != FINGERPRINT_OK) return;

  p = finger.image2Tz();
  if (p != FINGERPRINT_OK) return;

  p = finger.fingerSearch();
  if (p == FINGERPRINT_OK) {
    lcd.clear();
    lcd.print("ID: ");
    lcd.print(finger.fingerID);
    Serial.print("FOUND:");
    Serial.println(finger.fingerID);
    delay(5000); // cooldown
    lcd.clear();
    lcd.print("Ready to scan...");
  } else {
    lcd.clear();
    lcd.print("Not found");
    delay(2000);
    lcd.clear();
    lcd.print("Ready to scan...");
  }
}
