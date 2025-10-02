<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Ziggy Routes -->
    @routes(['url' => url('/')])
    
    <!-- Include both JS and CSS -->
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
