import cv2
import face_recognition
import pickle
import os
import mysql.connector
from mysql.connector import Error
import numpy as np

# Fonction pour se connecter à MySQL
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

# Charger les images directement depuis la base de données EmployeeImages
imgList = []
employeeIds = []
connection = connect_to_mysql()
if connection:
    try:
        cursor = connection.cursor()
        cursor.execute("SELECT emp_id, image FROM EmployeeImages")
        rows = cursor.fetchall()
        for emp_id, image_blob in rows:
            if image_blob is None:
                continue
            array = np.frombuffer(image_blob, dtype=np.uint8)
            img = cv2.imdecode(array, cv2.IMREAD_COLOR)
            if img is not None:
                # Assurez-vous que l'image est 216x216 comme exigé
                img = cv2.resize(img, (216, 216))
                imgList.append(img)
                employeeIds.append(str(emp_id))
        cursor.close()
        print(employeeIds)
    except Error as e:
        print(f"Error while reading images from MySQL: {e}")
    finally:
        if connection.is_connected():
            connection.close()

# Fonction pour trouver les encodages faciaux des images
def findEncodings(imagesList):
    encodeList = []
    for img in imagesList:
        img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        encode = face_recognition.face_encodings(img)
        if encode:
            encodeList.append(encode[0])
        else:
            print(f"Warning: No face found in image. Skipping image.")

    return encodeList

print("Encoding Started ........ ")
encodeListKnown = findEncodings(imgList)
encodeListKnownWithIds = [encodeListKnown, employeeIds]
print("Encoding complete")

# Sauvegarde des encodages dans un fichier
file = open("EncodeFile.p", 'wb')
pickle.dump(encodeListKnownWithIds, file)
file.close()
print("File Saved")
