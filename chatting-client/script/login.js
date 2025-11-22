const BASE_URL = "http://localhost/H-H-logger/Server/users/";
const form = document.querySelector(".form");
if (document.getElementById("adding")) {
  document.getElementById("adding").addEventListener("submit", function (event) {
  event.preventDefault();
  const username=document.getElementById("username").value.trim();
  const email = document.getElementById("em").value.trim();
  const password = document.getElementById("pass").value.trim();
  const confirmation = document.getElementById("cpass").value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address.");
    return;
  }
  if (password.length < 6) {
    alert("Password must be at least 6 characters long.");
    return;
  }
  if (password !== confirmation) {
    alert("Passwords do not match.");
    return;
  }
  const user = {
    username: username,
    email: email,
    password: password,
  };

  document.getElementById("em").value = "";
  document.getElementById("pass").value = "";
  document.getElementById("cpass").value = "";

  axios
    .post(BASE_URL + "create", user)
    .then((response) => {
      console.log(response.data);
      if (response.data.status === 200 && response.data.data.id) {
        alert("User added successfully!");
        window.location.href = "login.html";
      } else {
        alert("Failed to add user: " + (response.data.data.error || response.data.data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred while adding the user.");
    });
  });
}

if (document.getElementById("login")) {
  document.getElementById("login").addEventListener("submit", function (event) {
  event.preventDefault();
  const email = document.getElementById("username").value.trim();
  const password = document.getElementById("pass").value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address.");
    return;
  }
  if (password.length < 6) {
    alert("Password must be at least 6 characters long.");
    return;
  }
  const user = {
    username: email,
    password: password,
  };

  document.getElementById("username").value = "";
  document.getElementById("pass").value = "";
  axios
    .post(BASE_URL + "login", user)
    .then((response) => {
      console.log(response.data);
      if (response.data.status === 200 && response.data.data.user) {
        localStorage.setItem('userEmail', email);
        localStorage.setItem('user_id', response.data.data.user.id.toString());
        window.location.href = "../user/index.html";
      } else {
        alert("Login failed: " + (response.data.data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred while logging in.");
    });
  });
}
