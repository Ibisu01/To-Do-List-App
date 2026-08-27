// We start with an empty array. The database will fill this up!
let tasks = [];

// READ: Fetch tasks from the API and display them
async function fetchTasks() {
    try {
        // Call our new API using a GET request
        const response = await fetch('api.php');
        const data = await response.json(); // Parse the returned JSON
        
        // If the API says the user isn't logged in, send them back to the login page
        if (data.error) {
            console.error(data.error);
            if (data.error === "User not logged in") {
                window.location.href = "register_login_page.html";
            }
            return; // Stop the function
        }
        
        tasks = data; // Save the database tasks to our local array
        renderTasks(); // Update the screen
    } catch (error) {
        console.error("Error fetching tasks:", error);
    }
}

// Function to generate the HTML list items dynamically
function renderTasks() {
    const todoList = document.getElementById('todo-list');
    todoList.innerHTML = ''; // Clear the current list before rendering

    // Loop through the tasks array to create visual list elements
    tasks.forEach((task, index) => {
        const li = document.createElement('li');
        
        // Notice we now use task.task_text because our database returns an object with an ID and text
        li.innerHTML = `
            <span>${task.task_text}</span>
            <div class="action-buttons">
                <!-- Attach the item's index to the edit and delete buttons -->
                <button onclick="editTask(${index})">Edit</button>
                <button onclick="deleteTask(${index})">Delete</button>
            </div>
        `;
        
        todoList.appendChild(li); // Add the new item to the page
    });
}

// CREATE: Send a new task to the database
async function addTask() {
    const taskInput = document.getElementById('task-input');
    const newTask = taskInput.value.trim(); // Remove extra whitespace

    if (newTask !== '') {
        try {
            // Send a POST request to api.php to save the new item
            await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_text: newTask })
            });
            
            taskInput.value = ''; // Clear input field for the next task
            fetchTasks(); // Refresh the list from the database
        } catch (error) {
            console.error("Error adding task:", error);
        }
    }
}

// UPDATE: Edit an existing task in the database
async function editTask(index) {
    const task = tasks[index]; // Get the specific task being edited
    // Ask the user for the new text, providing the old text as the default
    const updatedTaskText = prompt("Edit your task:", task.task_text);
    
    // Make sure they didn't cancel and didn't leave it completely blank
    if (updatedTaskText !== null && updatedTaskText.trim() !== '') {
        try {
            // Send a PUT request to api.php with the specific task ID
            await fetch('api.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    id: task.id, 
                    task_text: updatedTaskText.trim() 
                })
            });
            
            fetchTasks(); // Refresh the list to reflect the update
        } catch (error) {
            console.error("Error updating task:", error);
        }
    }
}

// DELETE: Remove a task from the database
async function deleteTask(index) {
    const task = tasks[index]; // Identify which task to delete
    
    try {
        // Send a DELETE request to api.php with the specific task ID
        await fetch('api.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: task.id })
        });
        
        fetchTasks(); // Refresh the list to remove the item from the screen
    } catch (error) {
        console.error("Error deleting task:", error);
    }
}

// Initial fetch to load tasks when the page first opens
fetchTasks();