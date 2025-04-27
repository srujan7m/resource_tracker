let userId = null;

fetch('get_user.php')
  .then(res => res.json())
  .then(user => {
    if (!user.id) {
      window.location.href = "login.html";
    } else {
      userId = user.id;
      fetchResources();
    }
  });

function fetchResources() {
  fetch('get_res.php')
    .then(res => res.json())
    .then(data => {
      const list = document.getElementById('resource-list');
      list.innerHTML = "";

      data.forEach(resource => {
        const div = document.createElement("div");
        div.className = "p-4 bg-gray-50 rounded shadow";
        div.innerHTML = `
          <h2 class="text-xl font-semibold">${resource.name}</h2>
          <p>Available: <span id="available-${resource.id}">${resource.available}</span></p>
          <p>Borrowed: <span id="borrowed-${resource.id}">${resource.borrowed}</span></p>
          <div class="mt-2 flex space-x-2">
            <button onclick="updateResource(${resource.id}, 'borrow')" class="bg-blue-500 text-white px-4 py-1 rounded">Borrow</button>
            <button onclick="updateResource(${resource.id}, 'return')" class="bg-green-500 text-white px-4 py-1 rounded">Return</button>
          </div>
        `;
        list.appendChild(div);

        const historyButton = document.createElement("button");
        historyButton.textContent = "View History";
        historyButton.onclick = () => {
          fetch(`borrow_history.php?resource_id=${resource.id}`)
            .then(res => res.json())
            .then(history => {
              let historyHtml = history.map(h => {
                return `<li>${h.name} - ${h.borrow_at} → ${h.returned_at ?? 'Not Returned'}</li>`;
              }).join('');
              const ul = document.createElement("ul");
              ul.innerHTML = `<strong class="block mt-2">History:</strong><ul class="text-sm">${historyHtml}</ul>`;
              div.appendChild(ul);
            });
        };
        div.appendChild(historyButton); 
      });
    });
}

function updateResource(resourceId, action) {
  fetch(`update_resource.php?resource_id=${resourceId}&user_id=${userId}&action=${action}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById(`available-${resourceId}`).textContent = data.available;
      document.getElementById(`borrowed-${resourceId}`).textContent = data.borrowed;
      fetchResources(); 
    });
}
