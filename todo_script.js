let tasks = JSON.parse(localStorage.getItem('tasks')) || [];

function renderTasks() {
    const todoList = document.getElementById('todo-list');
    todoList.innerHTML = '';

    tasks.forEach((task, index) => {
        const li = document.createElement('li');
        
        li.innerHTML = `
            <span>${task}</span>
            <div class="action-buttons">
                <button onclick="editTask(${index})">Edit</button>
                <button onclick="deleteTask(${index})">Delete</button>
            </div>
        `;
        
        todoList.appendChild(li);
    });
}

function addTask() {
    const taskInput = document.getElementById('task-input');
    const newTask = taskInput.value.trim();

    if (newTask !== '') {
        tasks.push(newTask);
        taskInput.value = '';
        saveAndRender();
    }
}

function editTask(index) {
    const updatedTask = prompt("Edit your task:", tasks[index]);
    
    if (updatedTask !== null && updatedTask.trim() !== '') {
        tasks[index] = updatedTask.trim();
        saveAndRender();
    }
}

function deleteTask(index) {
    tasks.splice(index, 1); 
    saveAndRender();
}

function saveAndRender() {
    localStorage.setItem('tasks', JSON.stringify(tasks));
    renderTasks();
}

renderTasks();