<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$userName = $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {}
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script defer src="home.js"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-800 flex flex-col items-center justify-center min-h-screen transition-colors duration-300">
  <header class="w-full max-w-4xl p-4 bg-white dark:bg-gray-900 rounded-lg shadow-md flex justify-between items-center">
    <h1 class="text-2xl font-bold text-orange-600">Resource Tracker</h1>
    <div class="flex gap-2">
      <button id="light-mode-button" class="bg-yellow-400 text-white px-3 py-2 rounded-md hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-300 dark:hidden">
        Light Mode
      </button>
      <button id="dark-mode-button" class="bg-gray-700 text-white px-3 py-2 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 hidden dark:inline-block">
        Dark Mode
      </button>
      <button id="theme-toggle" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white px-3 py-2 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-500">
        <i class="fas fa-sun dark:hidden"></i>
        <i class="fas fa-moon hidden dark:inline"></i>
      </button>
      <button onclick="window.location.href='logout.php'" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
        Logout
      </button>
    </div>
  </header>

  <main class="w-full max-w-4xl p-8 bg-white dark:bg-gray-700 rounded-lg shadow-md mt-4">
    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Welcome, <span id="user-name"><?php echo htmlspecialchars($userName); ?></span></h2>
    <p class="text-gray-700 dark:text-white mb-6">Here are the available resources:</p>

    <div id="resource-list" class="space-y-4">
    </div>
  </main>

  <footer class="w-full max-w-4xl p-4 bg-white dark:bg-gray-900 rounded-lg shadow-md mt-4 text-center text-gray-700 dark:text-white">
    <p>&copy; 2025 Resource Tracker. All rights reserved.</p>
  </footer>
</body>
</html>
