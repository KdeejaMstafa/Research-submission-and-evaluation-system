document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById('signup-form');
  const errorElement = document.getElementById('signup-error-msg');

  const studentRadio = document.getElementById('student');
  const supervisorRadio = document.getElementById('supervisor');

  const studentFields = document.getElementById('student-fields');
  const supervisorFields = document.getElementById('supervisor-fields');

  const commonFields = document.getElementById('common-fields');
  const signupButtons = document.getElementById('signup-buttons');

  // Show relevant fields based on role
  function toggleFields() {
    if (studentRadio.checked) {
      studentFields.style.display = 'block';
      supervisorFields.style.display = 'none';
    } else if (supervisorRadio.checked) {
      supervisorFields.style.display = 'block';
      studentFields.style.display = 'none';
    }
    commonFields.style.display = 'block';
    signupButtons.style.display = 'block';
  }

  studentRadio.addEventListener('change', toggleFields);
  supervisorRadio.addEventListener('change', toggleFields);

  // Form validation
  form.addEventListener("submit", function (e) {
    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirm-password").value.trim();
    const role = document.querySelector('input[name="role"]:checked');

    let errorMsg = [];

    // Common validations
    if (!username || !email || !password || !confirmPassword || !role) {
      errorMsg.push("All common fields and role selection are mandatory.");
    }

    const namePattern = /^[a-zA-Z\s]+$/;
    if (!namePattern.test(username)) {
      errorMsg.push("Username must contain only letters and spaces.");
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    if (!emailPattern.test(email)) {
      errorMsg.push("Please enter a valid email address.");
    }

    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,30}$/;
    if (!passwordPattern.test(password)) {
      errorMsg.push("Password must be 6–30 characters and include uppercase, lowercase, and a number.");
    }

    if (password !== confirmPassword) {
      errorMsg.push("Passwords do not match.");
    }

    // Student-specific validations
    if (role && role.value === "student") {
      const stuProgram = document.getElementById("stu-program").value.trim();
      const stuYear = document.getElementById("stu-year").value.trim();
      const institute = document.getElementById("institute").value.trim();

      if (!stuProgram || !stuYear || !institute) {
        errorMsg.push("All student fields must be filled.");
      }

      if (institute.length < 2) {
        errorMsg.push("Institute name must be at least 2 characters.");
      }

      if (parseInt(stuYear) < 1 || isNaN(parseInt(stuYear))) {
        errorMsg.push("Year of study must be a valid number greater than 0.");
      }
    }

    // Supervisor-specific validations
    if (role && role.value === "supervisor") {
      const supAffiliation = document.getElementById("sup-affiliation").value.trim();
      const supPublications = document.getElementById("sup-publications").value.trim();
      const expertiseCheckboxes = document.querySelectorAll('input[name="expertise[]"]:checked');

      if (!supAffiliation || supPublications === "") {
        errorMsg.push("All supervisor fields must be filled.");
      }

      if (parseInt(supPublications) < 0 || isNaN(parseInt(supPublications))) {
        errorMsg.push("Number of publications must be a non-negative number.");
      }

      if (expertiseCheckboxes.length === 0) {
        errorMsg.push("Please select at least one area of expertise.");
      }
    }

    // Display errors
    if (errorMsg.length > 0) {
      e.preventDefault();
      errorElement.innerText = errorMsg.join("\n");
      errorElement.style.color = "rgba(218, 68, 68, 1)";
    }
  });

  // Reset form
  form.addEventListener("reset", function () {
  errorElement.innerText = "";
  errorElement.style.color = ""; 
});
});