<?php

class FileUploadService {
    private $uploadDir = 'uploads/';
    
    /**
     * Validates and handles file upload
     * 
     * @param array $file The uploaded file ($_FILES array element)
     * @param array $allowedExtensions Array of allowed file extensions
     * @return array Result with success/error and filepath information
     */
    public function uploadFile($file, $allowedExtensions = ['csv']) {
        $result = [
            'success' => false,
            'error' => '',
            'filepath' => '',
            'original_filename' => ''
        ];
        
        // Check if file was uploaded properly
        if (!isset($file) || $file['error'] != 0) {
            $result['error'] = 'File upload error. Code: ' . ($file['error'] ?? 'unknown');
            return $result;
        }
        
        // Validate file type
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedExtensions)) {
            $result['error'] = 'Only ' . implode(', ', $allowedExtensions) . ' files are allowed';
            return $result;
        }
        
        // Create uploads directory if needed
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid('seo_') . '_' . $file['name'];
        $filepath = $this->uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $result['error'] = 'Failed to upload file. Error code: ' . $file['error'];
            return $result;
        }
        
        $result['success'] = true;
        $result['filepath'] = $filepath;
        $result['original_filename'] = $file['name'];
        
        return $result;
    }
    
    /**
     * Delete a file if it exists
     * 
     * @param string $filepath Path to the file
     * @return bool True if file was deleted or didn't exist, false on error
     */
    public function deleteFile($filepath) {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return true;
    }
}