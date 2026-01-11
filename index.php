<!-- The home page -->
<?php include './includes/header.php';?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Research Submission System</title>
<link rel="stylesheet" href="./assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- adding JQuery -->
</head>

<body>
    <div class="main-content">
      <section class="intro">
        <h1 class="intro-heading">RESEARCH SUBMISSION <br> AND EVALUATION SYSTEM</h1>
        <p class="intro-msg">Welcome to the web-based project developed for universities to facilitate and manage 
          the submission, evaluation, and feedback process of research papers. This platform is designed to 
          streamline the handling of research papers, and enhance communication between students and supervisors.</p>
        <button class="read-more-btn">Read More</button>
      </section>
      <img id="pages-and-pen" src="./assets/images/mainpageimage.png" alt="Illustration of papers and a fountain pen"/>
    </div>

    <div id="more-info-and-footer" style="display: none;"> <!-- change style to visible when doing changes to the more-info-section -->
      <div class="more-info-section">
        <p>The system goes past the inefficiencies of traditional paper-based submissions as it smoothens the process of research submission by allowing students to submit their documents in PDF or Word format. 
          It tracks progress and streamlines the communication process between students and supervisors. </p>
     <section class="more-info-content-1">
            <div class="text-content">
              <h2>Main Features - For Students</h2>
             <ul>
               <li>Submit research papers effortlessly in PDF or Word format.</li>
               <li>Receive timely feedback and revise submissions with ease.</li>
               <li>Publish your work without additional steps or delays.</li>
             </ul>
            </div>
             <img src="./assets/images/student-view.png" alt="student-view"/>
     </section>

     <section class="more-info-content-2">
         <img src="./assets/images/supervisor-view.png" alt="supervisor-view"/>
         <div class="text-content">
             <h2>Main Features - For Supervisors</h2>
             <ul>
              <li>Assess submissions using given evaluation criteria.</li>
              <li>Upload reviewed papers through a seamless submission interface.</li>
              <li>Publish approved papers directly to the website for public access.</li>
             </ul>
         </div>
     </section>
      </div>
    
      <?php include './includes/footer.php';?>
    </div>

    <!-- The following part displays the more-info-and-footer after the 'read more' button is clicked -->
  <script>
   $(document).ready(function(){
    $(".read-more-btn").click(function(){
      const $button = $(this); //this refers to the button that was clicked.
      $("#more-info-and-footer").slideToggle("slow", function(){
        // toggle button text
        const isVisible = $(this).is(":visible");
        $button.text(isVisible ? "Read Less" : "Read More"); 
      });
    });
   });
  </script>
</body>    
</html>
