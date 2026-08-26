// We start with an empty array. The database will fill this up!
let tasks = [];

// READ: Fetch tasks from the API and display them
async function fetchTasks() {
    try {
        // Call our new API
        const response = await fetch('api.php');
        const data = await response.json();
        
        // If the API says the user isn't logged in, send them back to the login page
        if (data.error) {
            console.error(data.error);
            if (data.error === "User not logged in") {
                window.location.href = "register_login_page.html";
            }
            return;
        }
        
        tasks = data; // Save the database tasks to our local array
        renderTasks();
    } catch (error) {
        console.error("Error fetching tasks:", error);
    }
}

// Function to generate the HTML list items
function renderTasks() {
    const todoList = document.getElementById('todo-list');
    todoList.innerHTML = '';

    tasks.forEach((task, index) => {
        const li = document.createElement('li');
        
        // Notice we now use task.task_text because our database returns an object with an ID and text
        li.innerHTML = `
            <span>${task.task_text}</span>
            <div class="action-buttons">
                <button onclick="editTask(${index})">Edit</button>
                <button onclick="deleteTask(${index})">Delete</button>
            </div>
        `;
        
        todoList.appendChild(li);
    });
}

// CREATE: Send a new task to the database
async function addTask() {
    const taskInput = document.getElementById('task-input');
    const newTask = taskInput.value.trim();

    if (newTask !== '') {
        try {
            // Send a POST request to api.php
            await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_text: newTask })
            });
            
            taskInput.value = ''; // Clear input field
            fetchTasks(); // Refresh the list from the database
        } catch (error) {
            console.error("Error adding task:", error);
        }
    }
}

// UPDATE: Edit an existing task in the database
async function editTask(index) {
    const task = tasks[index];
    const updatedTaskText = prompt("Edit your task:", task.task_text);
    
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
            
            fetchTasks(); // Refresh the list
        } catch (error) {
            console.error("Error updating task:", error);
        }
    }
}

// DELETE: Remove a task from the database
async function deleteTask(index) {
    const task = tasks[index];
    
    try {
        // Send a DELETE request to api.php with the specific task ID
        await fetch('api.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: task.id })
        });
        
        fetchTasks(); // Refresh the list
    } catch (error) {
        console.error("Error deleting task:", error);
    }
}

// Initial fetch to load tasks when the page first opens
fetchTasks();