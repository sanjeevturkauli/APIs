<?php

use Illuminate\Support\Facades\Storage;


if (!function_exists('render_file')) {
    function render_file($path, $folder = 'public', $type = 'public')
    {
        if ($type == 'public') {
            return asset($path);
        }
        return Storage::disk($folder)->url($path);
    }
}


if (!function_exists('uploadFile')) {
    function uploadFile($file, $folderName, $prefix = 'file', $oldFilePath = null)
    {
        if (!$file || !$file->isValid()) {
            throw new \InvalidArgumentException('Invalid file provided');
        }

        $destination = public_path($folderName);
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        $filename = $prefix . "_" . time() . "_" . uniqid() . "." . $file->getClientOriginalExtension();

        $file->move($destination, $filename);

        if ($oldFilePath && file_exists(public_path($oldFilePath))) {
            @unlink(public_path($oldFilePath));
        }

        return $folderName . '/' . $filename;
    }
}

if (!function_exists('deleteFile')) {

    function deleteFile($filePath)
    {
        if (!$filePath) {
            return false;
        }

        $fullPath = public_path($filePath);

        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }

        return false;
    }
}

if (!function_exists('uploadMultipleFiles')) {

    function uploadMultipleFiles($files, $folderName, $prefix = 'file', $oldFilePaths = [])
    {
        if (!is_array($files)) {
            throw new \InvalidArgumentException('Files must be an array');
        }

        $uploadedPaths = [];

        if (!empty($oldFilePaths)) {
            foreach ($oldFilePaths as $oldPath) {
                deleteFile($oldPath);
            }
        }

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $uploadedPaths[] = uploadFile($file, $folderName, $prefix);
            }
        }

        return $uploadedPaths;
    }
}
