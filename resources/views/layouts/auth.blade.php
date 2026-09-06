<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SEÑAS Teacher Portal – @yield('title', 'Auth')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/senya_face.png') }}">
    <!-- No-cache: login page should never be stored -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Expires" content="0"/>
    <!-- Clear forward-history so the Back/Forward buttons can't reach protected pages -->
    <script>
        // Replace the login page in the history stack so there is no "forward"
        // entry pointing to a protected route. Also wipe any sentinel states
        // left by the app layout guard.
        history.replaceState(null, '', window.location.href);
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        senas: {
                            blue: '#3A9EE4',
                            darkblue: '#1C3D7A',
                            navy: '#132B55',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Scale down UI to fit screen without cropping */
        html { zoom: 90%; }
        html, body { margin: 0; padding: 0; }

        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        * { box-sizing: border-box; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div>
        @yield('content')
    </div>


</body>
</html>
