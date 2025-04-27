<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In</title>
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
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      initTheme();
      document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
    });

    function initTheme() {
      const userPreference = localStorage.getItem('theme');
      if (userPreference === 'light') {
        document.documentElement.classList.remove('dark');
      } else {
        document.documentElement.classList.add('dark');
      }
    }

    function toggleTheme() {
      if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
      } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
      }
    }
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-800 flex items-center justify-center min-h-screen transition-colors duration-300">
  <div class="w-full max-w-md p-8 bg-white dark:bg-gray-700 rounded-lg shadow-md" id="signIn">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-orange-600">Sign In</h1>
      
    </div>
    
    <?php if(isset($_SESSION['login_error'])): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php 
          echo $_SESSION['login_error']; 
          unset($_SESSION['login_error']); 
        ?>
      </div>
    <?php endif; ?>
    
    <form method="post" action="register.php" class="space-y-4">
      <div>
        <label for="signInEmail" class="block text-sm font-medium text-gray-700 dark:text-white">Email</label>
        <input type="email" name="email" id="signInEmail" placeholder="Email"
               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 dark:text-white dark:border-gray-600"
               required />
      </div>

      <div>
        <label for="signInPassword" class="block text-sm font-medium text-gray-700 dark:text-white">Password</label>
        <input type="password" name="password" id="signInPassword" placeholder="Password"
               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 dark:text-white dark:border-gray-600"
               required />
      </div>

      <button type="submit" name="signIn"
              class="w-full bg-orange-500 text-white py-2 rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500">
        Sign In
      </button>
    </form>

    <div class="text-center text-gray-700 dark:text-white mt-4">
      <p>Don't have an account yet?</p>
      <a href="signup.php" class="text-orange-400 hover:underline">Sign Up</a>
    </div>
  </div>
</body>
</html>