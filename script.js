const container = document.getElementById('container');
const registerBtn = document.getelementbyId('sign-up');
const loginBtn = document.getElementById('sign-in');

registerBtn.addEventListener('click', () =>{
    container.classList.add("active")
});

loginBtn.addEventListener('click', () =>{
    container.classList.remove("active")
});