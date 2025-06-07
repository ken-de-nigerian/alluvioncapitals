<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
    <head>
        <!-- Required meta tags  -->
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
        <style>
            :root {
                --adminuiux-content-font: "Open Sans", sans-serif;
                --adminuiux-content-font-weight: 400;
                --adminuiux-title-font: "Lexend", sans-serif;
                --adminuiux-title-font-weight: 600;
            }
        </style>

        <!-- Vite Assets (will be injected here) -->
        @vite(['resources/js/app.js', 'resources/css/app.css'])

        <!-- Inertia Head -->
        @inertiaHead

        <!-- Ziggy Routes -->
        @routes
    </head>

    <body class="main-bg main-bg-opac main-bg-blur adminuiux-sidebar-fill-white adminuiux-sidebar-boxed theme-blue roundedui"
          data-theme="theme-blue"
          data-sidebarfill="adminuiux-sidebar-fill-white"
          data-bs-spy="scroll"
          data-bs-target="#list-example"
          data-bs-smooth-scroll="true"
          tabindex="0"
    >
        <!-- Inertia Mount Point -->
        @inertia
    </body>
</html>
