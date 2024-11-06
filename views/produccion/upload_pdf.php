<?php
require_once '../../models/Conexion.php';

  $conexion = new Conexion();
  $conn = $conexion->getConexion();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdfFile'])) {

    $actionId = $_POST['action_id'];

    $pdfFile = $_FILES['pdfFile'];

    if ($pdfFile['error'] != 0) {
        echo 'Error al cargar el archivo.';
        exit;
    }

    if ($pdfFile['type'] != 'application/pdf') {
        echo 'El archivo debe ser un PDF.';
        exit;
    }

    $uploadDir = '../../uploads/pdfs/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid('pdf_') . '_' . basename($pdfFile['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($pdfFile['tmp_name'], $filePath)) {
        $sql = "INSERT INTO pdf_files (action_id, file_name, file_path) VALUES (:action_id, :file_name, :file_path)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':action_id', $actionId);
        $stmt->bindParam(':file_name', $fileName);
        $stmt->bindParam(':file_path', $filePath);

        if ($stmt->execute()) {
            echo 'PDF subido y registrado correctamente.';
            header("Location: ../../views/produccion/indexP.php?action=viewPDF&id=$actionId");
            exit;
        } else {
            echo 'Error al registrar el archivo en la base de datos.';
        }
    } else {
        echo 'Error al mover el archivo.';
    }
}
?>