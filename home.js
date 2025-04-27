document.addEventListener("DOMContentLoaded", () => {
  initTheme();
  document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
  
  // Add event listeners for the new light and dark mode buttons
  const lightModeButton = document.getElementById('light-mode-button');
  const darkModeButton = document.getElementById('dark-mode-button');
  
  if (lightModeButton) {
    lightModeButton.addEventListener('click', () => {
      setLightMode();
    });
  }
  
  if (darkModeButton) {
    darkModeButton.addEventListener('click', () => {
      setDarkMode();
    });
  }
  
  fetchResources();
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
    setLightMode();
  } else {
    setDarkMode();
  }
}

function setLightMode() {
  document.documentElement.classList.remove('dark');
  localStorage.setItem('theme', 'light');
}

function setDarkMode() {
  document.documentElement.classList.add('dark');
  localStorage.setItem('theme', 'dark');
}

function fetchResources() {
  const list = document.getElementById("resource-list");
  list.innerHTML = '<p class="text-gray-700 dark:text-white text-center py-4">Loading resources...</p>';
  
  fetch("get_res.php")
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP error! Status: ${res.status}`);
      }
      return res.json();
    })
    .then(data => {
      if (data.error) {
        console.error(data.error);
        if (data.error.includes("not logged in")) {
          window.location.href = "index.php";
        }
        list.innerHTML = `<p class="text-gray-700 dark:text-white text-center py-4">Error: ${data.error}</p>`;
        return;
      }
      
      list.innerHTML = "";

      if (data.length === 0) {
        list.innerHTML = '<p class="text-gray-700 dark:text-white text-center py-4">No resources available.</p>';
        return;
      }

      data.forEach(resource => {
        const item = document.createElement("div");
        item.className = "p-4 border rounded flex justify-between items-center bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white mb-4";

        const canBorrow = parseInt(resource.available) > 0;
        
        item.innerHTML = `
          <div>
            <h3 class="text-lg font-bold">${resource.name}</h3>
            <p class="text-sm">Available: <span class="text-green-600 dark:text-green-400 font-medium">${resource.available}</span></p>
            <p class="text-sm">Borrowed: <span class="text-red-600 dark:text-red-400 font-medium">${resource.borrowed}</span></p>
          </div>
          <div>
            <button 
              onclick="borrow(${resource.id})" 
              class="bg-blue-600 text-white px-3 py-1 rounded mr-2 hover:bg-blue-700 ${!canBorrow ? 'opacity-50 cursor-not-allowed' : ''}" 
              ${!canBorrow ? 'disabled' : ''}
            >
              Borrow
            </button>
            <button onclick="returnRes(${resource.id})" class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700">
              Return
            </button>
          </div>
        `;
        list.appendChild(item);
      });
    })
    .catch(error => {
      console.error("Error fetching resources:", error);
      list.innerHTML = 
        '<p class="text-gray-700 dark:text-white text-center py-4">Error loading resources. Please try again later.</p>';
    });
}

function showNotification(message, isError = false) {
  const existingNotifications = document.querySelectorAll('.notification');
  existingNotifications.forEach(notification => notification.remove());
  
  const notification = document.createElement("div");
  notification.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg notification ${isError ? 'bg-red-500' : 'bg-green-500'} text-white`;
  notification.textContent = message;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.classList.add('opacity-0', 'transition-opacity');
    setTimeout(() => notification.remove(), 500);
  }, 3000);
}

function borrow(resourceId) {
  const buttons = document.querySelectorAll(`button[onclick="borrow(${resourceId})"]`);
  buttons.forEach(button => {
    button.disabled = true;
    button.classList.add('opacity-50', 'cursor-not-allowed');
  });
  
  fetch("update_res.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ resource_id: resourceId, action: "borrow" })
  })
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP error! Status: ${res.status}`);
      }
      return res.json();
    })
    .then(data => {
      if (data.error) {
        showNotification(data.error, true);
      } else {
        showNotification(data.message || "Resource borrowed successfully");
        fetchResources();
      }
    })
    .catch(error => {
      console.error("Error borrowing resource:", error);
      showNotification("Failed to borrow resource. Please try again.", true);
    })
    .finally(() => {
      buttons.forEach(button => {
        button.disabled = false;
        button.classList.remove('opacity-50', 'cursor-not-allowed');
      });
    });
}

function returnRes(resourceId) {
  const buttons = document.querySelectorAll(`button[onclick="returnRes(${resourceId})"]`);
  buttons.forEach(button => {
    button.disabled = true;
    button.classList.add('opacity-50', 'cursor-not-allowed');
  });
  
  fetch("update_res.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ resource_id: resourceId, action: "return" })
  })
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP error! Status: ${res.status}`);
      }
      return res.json();
    })
    .then(data => {
      if (data.error) {
        showNotification(data.error, true);
      } else {
        showNotification(data.message || "Resource returned successfully");
        fetchResources();
      }
    })
    .catch(error => {
      console.error("Error returning resource:", error);
      showNotification("Failed to return resource. Please try again.", true);
    })
    .finally(() => {
      buttons.forEach(button => {
        button.disabled = false;
        button.classList.remove('opacity-50', 'cursor-not-allowed');
      });
    });
}

function logout() {
  fetch('logout.php')
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP error! Status: ${res.status}`);
      }
      return res.json();
    })
    .then(() => {
      window.location.href = 'index.php';
    })
    .catch(error => {
      console.error("Error logging out:", error);
      showNotification("Failed to log out. Please try again.", true);
    });
}
