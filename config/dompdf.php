<?php

return array(

    'show_warnings' => false,   // Throw an Exception on warnings from dompdf

    'public_path' => public_path(),  // Set the public path

    'convert_entities' => true,

    'options' => array(

        // Font directory located in public/font
        "font_dir" => public_path('fonts'), 

        // Font cache directory (can be in storage to avoid public exposure)
        "font_cache" => storage_path('fonts'),

        // Temporary directory for dompdf
        "temp_dir" => sys_get_temp_dir(),

        "chroot" => realpath(base_path()),

        'allowed_protocols' => [
            "file://" => ["rules" => []],
            "http://" => ["rules" => []],
            "https://" => ["rules" => []]
        ],

        'log_output_file' => null,

        "enable_font_subsetting" => false,

        "pdf_backend" => "CPDF",

        "default_media_type" => "screen",

        "default_paper_size" => "a4",

        'default_paper_orientation' => "portrait",

        "default_font" => "serif",

        "dpi" => 96,

        "enable_php" => false,

        "enable_javascript" => true,

        "enable_remote" => true,

        "font_height_ratio" => 1.1,

        "enable_html5_parser" => true,
    ),
);
