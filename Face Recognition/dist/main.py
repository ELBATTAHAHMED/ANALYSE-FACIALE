import os
import pickle
import cv2
import face_recognition
import numpy as np
import cvzone
import mysql.connector
from mysql.connector import Error
import base64
from datetime import datetime

# Connexion à la base de données MySQL
def connect_to_mysql():
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='analyse_faciale',
            user='root',
            password='',  # Remplacez par votre mot de passe MySQL
            auth_plugin='mysql_native_password'  # Spécifiez le plugin d'authentification
        )
        if connection.is_connected():
            print("Connected to MySQL database")
            return connection
    except Error as e:
        print(f"Error while connecting to MySQL: {e}")
        return None

# Fonction pour récupérer les données d'un employé à partir de la base de données MySQL
def get_employee_data(connection, emp_id):
    try:
        cursor = connection.cursor(dictionary=True)
        sql = "SELECT * FROM employee WHERE emp_id = %s"
        cursor.execute(sql, (emp_id,))
        employee_info = cursor.fetchone()
        cursor.close()
        return employee_info
    except Error as e:
        print(f"Error while getting employee data from MySQL: {e}")
        return None

# Fonction pour récupérer l'image d'un employé à partir de la base de données MySQL
def get_employee_image(connection, emp_id):
    try:
        cursor = connection.cursor()
        sql = "SELECT image FROM EmployeeImages WHERE emp_id = %s"
        cursor.execute(sql, (emp_id,))
        row = cursor.fetchone()
        cursor.close()
        if not row:
            return None
        return row[0]
    except Error as e:
        print(f"Error while getting employee image from MySQL: {e}")
        return None

# Fonction pour mettre à jour les données d'assiduité d'un employé dans la base de données MySQL
def update_attendance_data(connection, emp_id):
    try:
        cursor = connection.cursor()
        sql = "UPDATE employee SET total_attendance = total_attendance + 1, last_attendance_time = %s WHERE emp_id = %s"
        current_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cursor.execute(sql, (current_time, emp_id))
        connection.commit()
        cursor.close()
        print("Attendance data updated successfully in MySQL")
    except Error as e:
        print(f"Error while updating attendance data in MySQL: {e}")

# Fonction pour vérifier si l'employé a déjà été marqué pour la date actuelle dans la table Attendance
def check_attendance_exists(connection, emp_id):
    try:
        cursor = connection.cursor()
        sql = "SELECT * FROM Attendance WHERE emp_id = %s AND attendance_date = CURDATE()"
        cursor.execute(sql, (emp_id,))
        result = cursor.fetchone()
        cursor.close()
        return result is not None  # Retourne True si une entrée existe pour l'employé et la date actuelle
    except Error as e:
        print(f"Error while checking attendance data in MySQL: {e}")
        return False

# Fonction pour insérer les données de présence dans la table Attendance
def insert_attendance_data(connection, emp_id):
    try:
        if not check_attendance_exists(connection, emp_id):
            cursor = connection.cursor()
            sql = "INSERT INTO Attendance (emp_id, attendance_date, attendance_time, status) VALUES (%s, CURDATE(), NOW(), 'Present')"
            cursor.execute(sql, (emp_id,))
            connection.commit()
            cursor.close()
            print("Attendance data inserted successfully in MySQL")
            update_attendance_data(connection, emp_id)  # Mettre à jour total_attendance et last_attendance_time
        else:
            print("Attendance already marked for today")
    except Error as e:
        print(f"Error while inserting attendance data in MySQL: {e}")

# Connexion à la base de données MySQL
connection = connect_to_mysql()
if connection:
    cap = cv2.VideoCapture(0)
    cap.set(3, 640)
    cap.set(4, 480)

    imgBackground = cv2.imread('Resources/background.png')

    # Importer les images de mode dans une liste
    folderModePath = 'Resources/Modes'
    modePathList = os.listdir(folderModePath)
    imgModeList = []
    for path in modePathList:
        imgModeList.append(cv2.imread(os.path.join(folderModePath, path)))

    # Charger le fichier d'encodage
    print("Loading Encode File ...... ")
    file = open('EncodeFile.p', 'rb')
    encodeListKnownWithIds = pickle.load(file)
    file.close()
    encodeListKnown, employeeIds = encodeListKnownWithIds
    print("Encode File Loaded")

    modeType = 0
    counter = 0
    id = -1
    imgEmployee = []

    while True:
        success, img = cap.read()
        imgS = cv2.resize(img, (0, 0), None, 0.25, 0.25)
        imgS = cv2.cvtColor(imgS, cv2.COLOR_BGR2RGB)

        faceCurFrame = face_recognition.face_locations(imgS)
        encodeCurFrame = face_recognition.face_encodings(imgS, faceCurFrame)

        imgBackground[162:162 + 480, 55:55 + 640] = img
        imgBackground[44:44 + 633, 808:808 + 414] = imgModeList[modeType]

        if faceCurFrame:
            for encodeFace, faceLoc in zip(encodeCurFrame, faceCurFrame):
                matches = face_recognition.compare_faces(encodeListKnown, encodeFace)
                faceDis = face_recognition.face_distance(encodeListKnown, encodeFace)

                matchIndex = np.argmin(faceDis)

                if matches[matchIndex]:
                    y1, x2, y2, x1 = faceLoc
                    y1, x2, y2, x1 = y1 * 4, x2 * 4, y2 * 4, x1 * 4
                    bbox = 55 + x1, 162 + y1, x2 - x1, y2 - y1
                    imgBackground = cvzone.cornerRect(imgBackground, bbox, rt=0)
                    id = employeeIds[matchIndex]
                    if counter == 0:
                        cvzone.putTextRect(imgBackground, "Loading", (275, 400))
                        cv2.imshow("Face Attendance", imgBackground)
                        cv2.waitKey(10)
                        counter = 1
                        modeType = 1
                        insert_attendance_data(connection, id)

            if counter != 0:
                if counter == 1:
                    employee_info = get_employee_data(connection, id)
                    print(employee_info)
                    image_data = get_employee_image(connection, id)
                    if image_data is not None:
                        array = np.frombuffer(image_data, dtype=np.uint8)
                        imgEmployee = cv2.imdecode(array, cv2.IMREAD_COLOR)
                        # Resize to exactly 216x216 to match the display area
                        if imgEmployee is not None:
                            imgEmployee = cv2.resize(imgEmployee, (216, 216))
                    else:
                        print("Error: Image data not available")
                    last_attendance_time = employee_info['last_attendance_time']
                    if last_attendance_time:
                        secondsElapsed = (datetime.now() - last_attendance_time).total_seconds()
                    else:
                        secondsElapsed = 31  # Forcer la mise à jour si aucune dernière présence enregistrée
                    print(secondsElapsed)
                    if secondsElapsed > 30:
                        update_attendance_data(connection, id)
                    else:
                        modeType = 3
                        counter = 0
                        imgBackground[44:44 + 633, 808:808 + 414] = imgModeList[modeType]

                if modeType != 3:
                    if 10 < counter < 20:
                        modeType = 2

                    imgBackground[44:44 + 633, 808:808 + 414] = imgModeList[modeType]

                    if counter <= 10:
                        cv2.putText(imgBackground, str(employee_info['total_attendance']), (861, 125),
                                    cv2.FONT_HERSHEY_COMPLEX, 1, (255, 255, 255), 1)
                        cv2.putText(imgBackground, str(employee_info['department']), (1006, 550),
                                    cv2.FONT_HERSHEY_COMPLEX, 0.5, (255, 255, 255), 1)
                        cv2.putText(imgBackground, str(id), (1006, 493),
                                    cv2.FONT_HERSHEY_COMPLEX, 0.5, (255, 255, 255), 1)
                        cv2.putText(imgBackground, str(employee_info['standing']), (910, 625),
                                    cv2.FONT_HERSHEY_COMPLEX, 0.6, (0, 0, 0), 1)
                        cv2.putText(imgBackground, str(employee_info['year']), (1025, 625),
                                    cv2.FONT_HERSHEY_COMPLEX, 0.6, (0, 0, 0), 1)
                        cv2.putText(imgBackground, str(employee_info['Joinin_year']), (1125, 625),
                                    cv2.FONT_HERSHEY_COMPLEX, 0.6, (0, 0, 0), 1)

                        (w, h), _ = cv2.getTextSize(employee_info['name'], cv2.FONT_HERSHEY_COMPLEX, 1, 1)
                        offset = (414 - w) // 2
                        cv2.putText(imgBackground, str(employee_info['name']), (808 + offset, 445),
                                    cv2.FONT_HERSHEY_COMPLEX, 1, (0, 0, 0), 1)

                        imgBackground[175:175 + 216, 909:909 + 216] = imgEmployee

                    counter += 1

                    if counter >= 20:
                        counter = 0
                        modeType = 0
                        employee_info = []
                        imgEmployee = []
                        imgBackground[44:44 + 633, 808:808 + 414] = imgModeList[modeType]
        else:
            modeType = 0
            counter = 0

        cv2.imshow("Face Attendance", imgBackground)
        cv2.waitKey(10)

# Fermez la connexion à la base de données MySQL à la fin
connection.close()
