import cv2
import face_recognition
import pickle
import os
import mysql.connector
from mysql.connector import Error

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

# Importation des images des employés
folderPath = 'uploads'
pathList = os.listdir(folderPath)
print(pathList)
imgList = []
employeeIds = []
for path in pathList:
    imgList.append(cv2.imread(os.path.join(folderPath, path)))
    employeeIds.append(os.path.splitext(path)[0])

    # Convertir l'image en format blob pour l'insérer dans la base de données
    with open(os.path.join(folderPath, path), 'rb') as file:
        img_blob = file.read()

    # Insérer l'image dans la base de données
    connection = connect_to_mysql()
    if connection:
        try:
            cursor = connection.cursor()
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS EmployeeImages (
                    emp_id VARCHAR(50) PRIMARY KEY,
                    image LONGBLOB
                )
            """)
            sql = "INSERT INTO EmployeeImages (emp_id, image) VALUES (%s, %s) ON DUPLICATE KEY UPDATE image = VALUES(image)"
            cursor.execute(sql, (os.path.splitext(path)[0], img_blob))
            connection.commit()
            print(f"Image '{path}' inserted into MySQL database")
            cursor.close()
        except Error as e:
            print(f"Error while inserting image '{path}' into MySQL: {e}")
        finally:
            if connection.is_connected():
                connection.close()

print(employeeIds)

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
