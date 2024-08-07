<?php
// Count total files
$countfiles = count($_FILES['files']['name']);


// Upload Location
$location = "../../uploads/job-resume/";

$path = "wp-content/uploads/job-resume/";
// To store uploaded files path
$files_arr = array();

// Loop all files
for($index = 0;$index < $countfiles;$index++){

    if(isset($_FILES['files']['name'][$index]) && $_FILES['files']['name'][$index] != ''){
        // File name
        $filename = $_FILES['files']['name'][$index];
        
        $filename = str_replace(" ","-",$filename);

        // Get extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Valid image extension
        $valid_ext = array("png","jpeg","jpg", "gif", "mp3", "mp4", "wma");

        // Check extension
        if(in_array($ext, $valid_ext)){

            // File path
            $upload_location = $location.$filename;
            $save_path = $path.$filename;
            // Upload file
            if(move_uploaded_file($_FILES['files']['tmp_name'][$index],$upload_location)){
                $files_arr[] = $save_path;
            }
        }
    }
}

echo json_encode($files_arr);
die;


?>